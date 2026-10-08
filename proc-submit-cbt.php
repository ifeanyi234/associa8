<?php
session_start();
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/admission-notifications.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cbt-code.php');
    exit;
}

$admissionId = (int) ($_SESSION['applicant_admission_id'] ?? 0);
$examId = (int) ($_SESSION['applicant_exam_id'] ?? 0);
$sessionOrgId = (int) ($_SESSION['applicant_org_id'] ?? 0);
$answers = $_POST['answers'] ?? [];
if ($admissionId < 1 || $examId < 1 || $sessionOrgId < 1 || !is_array($answers)) {
    header('Location: cbt-code.php?status=error&msg=' . urlencode('Your CBT session has expired. Enter your access code again.'));
    exit;
}

mysqli_begin_transaction($conn);
$assessmentStatement = mysqli_prepare($conn, "SELECT a.id, a.org_id, a.applicant_name, a.email, a.status, a.exam_expires_at, a.cbt_attempt_started_at, e.id AS exam_id, e.pass_mark, (a.exam_expires_at > UTC_TIMESTAMP()) AS is_unexpired FROM admissions a INNER JOIN cbt_exams e ON e.id = a.cbt_exam_id AND e.org_id = a.org_id WHERE a.id = ? AND a.org_id = ? AND e.id = ? AND a.cbt_attempt_started_at IS NOT NULL AND a.status = 'under_review' LIMIT 1 FOR UPDATE");
if (!$assessmentStatement) {
    mysqli_rollback($conn);
    error_log('CBT submission lookup prepare failed: ' . mysqli_error($conn));
    header('Location: cbt-code.php?status=error&msg=' . urlencode('The assessment service is unavailable.'));
    exit;
}
mysqli_stmt_bind_param($assessmentStatement, 'iii', $admissionId, $sessionOrgId, $examId);
if (!mysqli_stmt_execute($assessmentStatement)) {
    mysqli_rollback($conn);
    error_log('CBT submission lookup failed: ' . mysqli_stmt_error($assessmentStatement));
    header('Location: cbt-code.php?status=error&msg=' . urlencode('The assessment service is unavailable.'));
    exit;
}
$assessmentResult = mysqli_stmt_get_result($assessmentStatement);
$assessment = $assessmentResult ? mysqli_fetch_assoc($assessmentResult) : null;
if (!$assessment || (int) $assessment['is_unexpired'] !== 1) {
    mysqli_rollback($conn);
    header('Location: cbt-code.php?status=error&msg=' . urlencode('This assessment is no longer available or its time has expired.'));
    exit;
}

$attemptStatement = mysqli_prepare($conn, 'SELECT id FROM cbt_results WHERE admission_id = ? LIMIT 1');
if (!$attemptStatement) {
    mysqli_rollback($conn);
    error_log('CBT duplicate attempt lookup prepare failed: ' . mysqli_error($conn));
    header('Location: cbt-code.php?status=error&msg=' . urlencode('The CBT attempt service is unavailable.'));
    exit;
}
mysqli_stmt_bind_param($attemptStatement, 'i', $admissionId);
if (!mysqli_stmt_execute($attemptStatement)) {
    mysqli_rollback($conn);
    error_log('CBT duplicate attempt lookup failed: ' . mysqli_stmt_error($attemptStatement));
    header('Location: cbt-code.php?status=error&msg=' . urlencode('The CBT attempt service is unavailable.'));
    exit;
}
$attemptResult = mysqli_stmt_get_result($attemptStatement);
if ($attemptResult && mysqli_num_rows($attemptResult) > 0) {
    mysqli_rollback($conn);
    header('Location: cbt-code.php?status=error&msg=' . urlencode('This CBT attempt has already been submitted.'));
    exit;
}

$questionStatement = mysqli_prepare($conn, 'SELECT id, correct_option, option_a, option_b, option_c, option_d, option_e FROM cbt_questions WHERE exam_id = ? ORDER BY id');
if (!$questionStatement) {
    mysqli_rollback($conn);
    error_log('CBT question lookup prepare failed: ' . mysqli_error($conn));
    header('Location: cbt-code.php?status=error&msg=' . urlencode('The assessment questions are unavailable.'));
    exit;
}
mysqli_stmt_bind_param($questionStatement, 'i', $examId);
if (!mysqli_stmt_execute($questionStatement)) {
    mysqli_rollback($conn);
    error_log('CBT question lookup failed: ' . mysqli_stmt_error($questionStatement));
    header('Location: cbt-code.php?status=error&msg=' . urlencode('The assessment questions are unavailable.'));
    exit;
}
$questionResult = mysqli_stmt_get_result($questionStatement);
$questions = [];
if ($questionResult) {
    while ($question = mysqli_fetch_assoc($questionResult)) {
        $questions[(int) $question['id']] = $question;
    }
}
if (!$questions) {
    mysqli_rollback($conn);
    header('Location: cbt-code.php?status=error&msg=' . urlencode('This exam has no questions to score.'));
    exit;
}

$normalizedAnswers = [];
foreach ($answers as $questionId => $answer) {
    if (!ctype_digit((string) $questionId) || !isset($questions[(int) $questionId]) || !is_string($answer)) {
        mysqli_rollback($conn);
        header('Location: cbt-code.php?status=error&msg=' . urlencode('The submitted answers do not match this assessment.'));
        exit;
    }
    $answer = strtoupper(trim($answer));
    $question = $questions[(int) $questionId];
    $validOptions = [];
    foreach (['A' => 'option_a', 'B' => 'option_b', 'C' => 'option_c', 'D' => 'option_d', 'E' => 'option_e'] as $option => $column) {
        if ($question[$column] !== null && $question[$column] !== '') {
            $validOptions[] = $option;
        }
    }
    if (!in_array($answer, $validOptions, true)) {
        mysqli_rollback($conn);
        header('Location: cbt-code.php?status=error&msg=' . urlencode('One or more selected answers are invalid.'));
        exit;
    }
    $normalizedAnswers[(int) $questionId] = $answer;
}

$correct = 0;
foreach ($questions as $questionId => $question) {
    if (($normalizedAnswers[$questionId] ?? null) === $question['correct_option']) {
        $correct++;
    }
}
$score = (int) round(($correct / count($questions)) * 100);
$resultStatus = $score >= (int) $assessment['pass_mark'] ? 'passed' : 'failed';
$orgId = (int) $assessment['org_id'];

$insert = mysqli_prepare($conn, 'INSERT INTO cbt_results (org_id, exam_id, admission_id, member_id, score, status, taken_at) VALUES (?, ?, ?, NULL, ?, ?, UTC_TIMESTAMP())');
$update = mysqli_prepare($conn, "UPDATE admissions SET status = 'cbt_completed', exam_code = NULL, exam_code_hash = NULL WHERE id = ? AND org_id = ? AND cbt_exam_id = ? AND cbt_attempt_started_at IS NOT NULL AND status = 'under_review' AND exam_expires_at > UTC_TIMESTAMP()");
if (!$insert || !$update) {
    mysqli_rollback($conn);
    error_log('CBT result save prepare failed: ' . mysqli_error($conn));
    header('Location: cbt-code.php?status=error&msg=' . urlencode('The assessment result could not be saved.'));
    exit;
}
mysqli_stmt_bind_param($insert, 'iiiis', $orgId, $examId, $admissionId, $score, $resultStatus);
if (!mysqli_stmt_execute($insert)) {
    mysqli_rollback($conn);
    error_log('CBT result insert failed: ' . mysqli_stmt_error($insert));
    header('Location: cbt-code.php?status=error&msg=' . urlencode('The assessment result could not be saved.'));
    exit;
}
$resultId = (int) mysqli_insert_id($conn);
mysqli_stmt_bind_param($update, 'iii', $admissionId, $orgId, $examId);
if (!mysqli_stmt_execute($update) || mysqli_stmt_affected_rows($update) !== 1) {
    mysqli_rollback($conn);
    error_log('CBT admission completion update failed: ' . mysqli_stmt_error($update));
    header('Location: cbt-code.php?status=error&msg=' . urlencode('The assessment result could not be completed.'));
    exit;
}
mysqli_commit($conn);

notify_admission_status($conn, $admissionId, 'cbt_completed', $assessment['applicant_name'], $assessment['email'], $score, $resultStatus);
$_SESSION['applicant_result_id'] = $resultId;
unset($_SESSION['applicant_admission_id'], $_SESSION['applicant_org_id'], $_SESSION['applicant_exam_id'], $_SESSION['applicant_exam_title'], $_SESSION['applicant_exam_expires_at'], $_SESSION['applicant_assessment_expires_at']);

header('Location: cbt-result.php');
exit;
