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
$answers = is_array($_POST['answers'] ?? null) ? $_POST['answers'] : [];
if ($admissionId < 1 || $examId < 1) {
    header('Location: cbt-code.php?status=error&msg=' . urlencode('Your CBT session has expired. Enter your access code again.'));
    exit;
}

$assessmentStatement = mysqli_prepare($conn, "SELECT a.id, a.org_id, a.applicant_name, a.email, a.status, a.exam_expires_at, e.id AS exam_id, e.pass_mark FROM admissions a INNER JOIN cbt_exams e ON e.id = a.cbt_exam_id AND e.org_id = a.org_id WHERE a.id = ? AND e.id = ? AND a.org_id IS NOT NULL AND a.status = 'under_review' LIMIT 1");
if (!$assessmentStatement) {
    header('Location: cbt-code.php?status=error&msg=' . urlencode('The assessment service is unavailable.'));
    exit;
}
mysqli_stmt_bind_param($assessmentStatement, 'ii', $admissionId, $examId);
mysqli_stmt_execute($assessmentStatement);
$assessmentResult = mysqli_stmt_get_result($assessmentStatement);
$assessment = $assessmentResult ? mysqli_fetch_assoc($assessmentResult) : null;
if (!$assessment) {
    header('Location: cbt-code.php?status=error&msg=' . urlencode('This assessment is no longer available.'));
    exit;
}
if (empty($assessment['exam_expires_at']) || strtotime($assessment['exam_expires_at'] . ' UTC') <= time()) {
    header('Location: cbt-code.php?status=error&msg=' . urlencode('Your assessment time has expired.'));
    exit;
}

$attemptStatement = mysqli_prepare($conn, 'SELECT id FROM cbt_results WHERE admission_id = ? LIMIT 1');
mysqli_stmt_bind_param($attemptStatement, 'i', $admissionId);
mysqli_stmt_execute($attemptStatement);
$attemptResult = mysqli_stmt_get_result($attemptStatement);
if ($attemptResult && mysqli_num_rows($attemptResult) > 0) {
    header('Location: cbt-code.php?status=error&msg=' . urlencode('This CBT attempt has already been submitted.'));
    exit;
}

$questionStatement = mysqli_prepare($conn, 'SELECT id, correct_option FROM cbt_questions WHERE exam_id = ? ORDER BY id');
mysqli_stmt_bind_param($questionStatement, 'i', $examId);
mysqli_stmt_execute($questionStatement);
$questionResult = mysqli_stmt_get_result($questionStatement);
$questions = [];
if ($questionResult) {
    while ($question = mysqli_fetch_assoc($questionResult)) {
        $questions[] = $question;
    }
}
if (!$questions) {
    header('Location: cbt-code.php?status=error&msg=' . urlencode('This exam has no questions to score.'));
    exit;
}

$correct = 0;
foreach ($questions as $question) {
    $answer = strtoupper(trim((string) ($answers[(int) $question['id']] ?? '')));
    if ($answer !== '' && $answer === $question['correct_option']) {
        $correct++;
    }
}
$score = (int) round(($correct / count($questions)) * 100);
$resultStatus = $score >= (int) $assessment['pass_mark'] ? 'passed' : 'failed';

mysqli_begin_transaction($conn);
$insert = mysqli_prepare($conn, 'INSERT INTO cbt_results (org_id, exam_id, admission_id, member_id, score, status) VALUES (?, ?, ?, NULL, ?, ?)');
$update = mysqli_prepare($conn, "UPDATE admissions SET status = 'cbt_completed' WHERE id = ? AND status = 'under_review'");
if (!$insert || !$update) {
    mysqli_rollback($conn);
    header('Location: cbt-code.php?status=error&msg=' . urlencode('The assessment result could not be saved.'));
    exit;
}
$orgId = $assessment['org_id'] !== null ? (int) $assessment['org_id'] : null;
mysqli_stmt_bind_param($insert, 'iiiis', $orgId, $examId, $admissionId, $score, $resultStatus);
mysqli_stmt_bind_param($update, 'i', $admissionId);
$inserted = mysqli_stmt_execute($insert);
$resultId = $inserted ? (int) mysqli_insert_id($conn) : 0;
$updated = mysqli_stmt_execute($update);
if (!$inserted || !$updated || mysqli_stmt_affected_rows($update) !== 1) {
    mysqli_rollback($conn);
    header('Location: cbt-code.php?status=error&msg=' . urlencode('The assessment result could not be completed.'));
    exit;
}
mysqli_commit($conn);

notify_admission_status($conn, $admissionId, 'cbt_completed', $assessment['applicant_name'], $assessment['email'], $score, $resultStatus);
$_SESSION['applicant_result_id'] = $resultId;
unset($_SESSION['applicant_admission_id'], $_SESSION['applicant_org_id'], $_SESSION['applicant_exam_id'], $_SESSION['applicant_exam_title'], $_SESSION['applicant_exam_expires_at'], $_SESSION['applicant_assessment_expires_at']);

header('Location: cbt-result.php');
exit;
