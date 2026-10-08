<?php
session_start();
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/mailer.php';
require_once __DIR__ . '/inc/rate-limit.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cbt-code.php');
    exit;
}

unset(
    $_SESSION['applicant_email'],
    $_SESSION['applicant_admission_id'],
    $_SESSION['applicant_code_request'],
    $_SESSION['applicant_org_id'],
    $_SESSION['applicant_exam_id'],
    $_SESSION['applicant_exam_title'],
    $_SESSION['applicant_exam_expires_at'],
    $_SESSION['applicant_assessment_expires_at'],
    $_SESSION['applicant_result_id']
);

$email = strtolower(trim($_POST['email'] ?? ''));
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: cbt-code.php?status=error&msg=' . urlencode('Enter a valid application email address.'));
    exit;
}

$redirectToCodeEntry = static function (): void {
    header('Location: cbt-code-entry.php?status=success&msg=' . urlencode('If an eligible assessment exists for that email, an access code has been sent.'));
    exit;
};

$storeCodeRequestSession = static function (string $requestedEmail, int $requestedAdmissionId): void {
    $_SESSION['applicant_email'] = $requestedEmail;
    $_SESSION['applicant_admission_id'] = $requestedAdmissionId;
    $_SESSION['applicant_code_request'] = true;
};

$requestIp = $_SERVER['REMOTE_ADDR'] ?? '';
if ($requestIp === '') {
    error_log('CBT access-code request did not include a remote IP address.');
    http_response_code(500);
    exit('The CBT request service is unavailable.');
}

try {
    $retryAfter = associa8_rate_limit_retry_after($conn, 'cbt_code_request_ip', $requestIp, 20, 3600);
} catch (RuntimeException $error) {
    http_response_code(500);
    exit('The CBT request service is unavailable.');
}
if ($retryAfter > 0) {
    header('Retry-After: ' . $retryAfter);
    header('Location: cbt-code.php?status=error&msg=' . urlencode('Too many code requests. Please wait before trying again.'));
    exit;
}

$statement = mysqli_prepare($conn, "SELECT a.id, a.org_id, a.applicant_name, a.email, a.cbt_exam_id, a.cbt_scheduled_at, a.exam_code_hash, a.cbt_attempt_started_at, a.exam_expires_at, a.cbt_response_deadline, e.title AS exam_title, e.duration_minutes FROM admissions a LEFT JOIN cbt_exams e ON e.id = a.cbt_exam_id AND e.org_id = a.org_id AND e.status = 'active' AND EXISTS (SELECT 1 FROM cbt_questions q WHERE q.exam_id = e.id) AND NOT EXISTS (SELECT 1 FROM cbt_questions q WHERE q.exam_id = e.id AND (q.question_text = '' OR q.option_a = '' OR q.option_b = '' OR q.option_c = '' OR q.option_d = '' OR q.correct_option NOT IN ('A','B','C','D','E') OR (q.correct_option = 'E' AND q.option_e = ''))) WHERE LOWER(a.email) = ? AND a.org_id IS NOT NULL AND a.status = 'under_review' AND (a.cbt_response_deadline IS NULL OR a.cbt_response_deadline > UTC_TIMESTAMP()) AND (a.cbt_scheduled_at IS NULL OR a.cbt_scheduled_at <= UTC_TIMESTAMP()) LIMIT 1 FOR UPDATE");
if (!$statement) {
    error_log('CBT email lookup prepare failed: ' . mysqli_error($conn));
    header('Location: cbt-code.php?status=error&msg=' . urlencode('The CBT email service is unavailable.'));
    exit;
}

mysqli_begin_transaction($conn);
mysqli_stmt_bind_param($statement, 's', $email);
if (!mysqli_stmt_execute($statement)) {
    mysqli_rollback($conn);
    error_log('CBT email lookup failed: ' . mysqli_stmt_error($statement));
    header('Location: cbt-code.php?status=error&msg=' . urlencode('The CBT email service is unavailable.'));
    exit;
}
$result = mysqli_stmt_get_result($statement);
$admission = $result ? mysqli_fetch_assoc($result) : null;
if (!$admission) {
    mysqli_rollback($conn);
    $storeCodeRequestSession($email, 0);
    $redirectToCodeEntry();
}

$attemptStatement = mysqli_prepare($conn, 'SELECT id FROM cbt_results WHERE admission_id = ? LIMIT 1');
if (!$attemptStatement) {
    mysqli_rollback($conn);
    error_log('CBT attempt lookup prepare failed: ' . mysqli_error($conn));
    header('Location: cbt-code.php?status=error&msg=' . urlencode('The CBT attempt service is unavailable.'));
    exit;
}
mysqli_stmt_bind_param($attemptStatement, 'i', $admission['id']);
if (!mysqli_stmt_execute($attemptStatement)) {
    mysqli_rollback($conn);
    error_log('CBT attempt lookup failed: ' . mysqli_stmt_error($attemptStatement));
    header('Location: cbt-code.php?status=error&msg=' . urlencode('The CBT attempt service is unavailable.'));
    exit;
}
$attemptResult = mysqli_stmt_get_result($attemptStatement);
if ($attemptResult && mysqli_num_rows($attemptResult) > 0) {
    mysqli_rollback($conn);
    $storeCodeRequestSession($email, (int) $admission['id']);
    $redirectToCodeEntry();
}

if ($admission['cbt_attempt_started_at'] !== null) {
    mysqli_rollback($conn);
    $storeCodeRequestSession($email, (int) $admission['id']);
    $redirectToCodeEntry();
}

if ($admission['cbt_response_deadline'] === null) {
    $deadlineUpdate = mysqli_prepare($conn, 'UPDATE admissions SET cbt_response_deadline = DATE_ADD(UTC_TIMESTAMP(), INTERVAL 36 HOUR) WHERE id = ? AND status = \'under_review\' AND cbt_response_deadline IS NULL');
    if (!$deadlineUpdate) {
        mysqli_rollback($conn);
        error_log('CBT deadline prepare failed: ' . mysqli_error($conn));
        header('Location: cbt-code.php?status=error&msg=' . urlencode('The CBT deadline service is unavailable.'));
        exit;
    }
    mysqli_stmt_bind_param($deadlineUpdate, 'i', $admission['id']);
    if (!mysqli_stmt_execute($deadlineUpdate)) {
        mysqli_rollback($conn);
        error_log('CBT deadline update failed: ' . mysqli_stmt_error($deadlineUpdate));
        header('Location: cbt-code.php?status=error&msg=' . urlencode('The CBT deadline could not be set.'));
        exit;
    }
    $deadlineLookup = mysqli_prepare($conn, 'SELECT cbt_response_deadline FROM admissions WHERE id = ?');
    if (!$deadlineLookup) {
        mysqli_rollback($conn);
        error_log('CBT deadline lookup prepare failed: ' . mysqli_error($conn));
        header('Location: cbt-code.php?status=error&msg=' . urlencode('The CBT deadline service is unavailable.'));
        exit;
    }
    mysqli_stmt_bind_param($deadlineLookup, 'i', $admission['id']);
    if (!mysqli_stmt_execute($deadlineLookup)) {
        mysqli_rollback($conn);
        error_log('CBT deadline lookup failed: ' . mysqli_stmt_error($deadlineLookup));
        header('Location: cbt-code.php?status=error&msg=' . urlencode('The CBT deadline service is unavailable.'));
        exit;
    }
    $deadlineResult = mysqli_stmt_get_result($deadlineLookup);
    $deadlineRow = $deadlineResult ? mysqli_fetch_assoc($deadlineResult) : null;
    $admission['cbt_response_deadline'] = $deadlineRow['cbt_response_deadline'] ?? null;
}

if ($admission['cbt_exam_id'] === null) {
    $examStatement = mysqli_prepare($conn, "SELECT e.id, e.title, e.duration_minutes FROM cbt_exams e WHERE e.org_id = ? AND e.status = 'active' AND EXISTS (SELECT 1 FROM cbt_questions q WHERE q.exam_id = e.id) AND NOT EXISTS (SELECT 1 FROM cbt_questions q WHERE q.exam_id = e.id AND (q.question_text = '' OR q.option_a = '' OR q.option_b = '' OR q.option_c = '' OR q.option_d = '' OR q.correct_option NOT IN ('A','B','C','D','E') OR (q.correct_option = 'E' AND q.option_e = ''))) ORDER BY e.id LIMIT 1");
    if (!$examStatement) {
        mysqli_rollback($conn);
        error_log('CBT exam lookup prepare failed: ' . mysqli_error($conn));
        header('Location: cbt-code.php?status=error&msg=' . urlencode('The CBT exam service is unavailable.'));
        exit;
    }
    mysqli_stmt_bind_param($examStatement, 'i', $admission['org_id']);
    if (!mysqli_stmt_execute($examStatement)) {
        mysqli_rollback($conn);
        error_log('CBT exam lookup failed: ' . mysqli_stmt_error($examStatement));
        header('Location: cbt-code.php?status=error&msg=' . urlencode('The CBT exam service is unavailable.'));
        exit;
    }
    $examResult = mysqli_stmt_get_result($examStatement);
    $exam = $examResult ? mysqli_fetch_assoc($examResult) : null;
    if (!$exam) {
        mysqli_rollback($conn);
        $storeCodeRequestSession($email, (int) $admission['id']);
        $redirectToCodeEntry();
    }
    $admission['cbt_exam_id'] = $exam['id'];
    $admission['exam_title'] = $exam['title'];
    $admission['duration_minutes'] = $exam['duration_minutes'];
} elseif ($admission['exam_title'] === null) {
    mysqli_rollback($conn);
    $storeCodeRequestSession($email, (int) $admission['id']);
    $redirectToCodeEntry();
}

$alphabet = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
$examCode = '';
for ($index = 0; $index < 6; $index++) {
    $examCode .= $alphabet[random_int(0, strlen($alphabet) - 1)];
}
$examCodeHash = password_hash($examCode, PASSWORD_DEFAULT);
$update = mysqli_prepare($conn, 'UPDATE admissions SET cbt_exam_id = ?, exam_code = NULL, exam_code_hash = ?, cbt_attempt_started_at = NULL, exam_expires_at = cbt_response_deadline WHERE id = ? AND status = \'under_review\' AND cbt_attempt_started_at IS NULL AND cbt_response_deadline > UTC_TIMESTAMP() AND (cbt_scheduled_at IS NULL OR cbt_scheduled_at <= UTC_TIMESTAMP())');
if (!$update) {
    mysqli_rollback($conn);
    error_log('CBT code update prepare failed: ' . mysqli_error($conn));
    header('Location: cbt-code.php?status=error&msg=' . urlencode('The CBT code service is unavailable.'));
    exit;
}
mysqli_stmt_bind_param($update, 'isi', $admission['cbt_exam_id'], $examCodeHash, $admission['id']);
if (!mysqli_stmt_execute($update)) {
    mysqli_rollback($conn);
    error_log('CBT code update failed: ' . mysqli_stmt_error($update));
    header('Location: cbt-code.php?status=error&msg=' . urlencode('The CBT code service is unavailable.'));
    exit;
}
if (mysqli_stmt_affected_rows($update) !== 1) {
    mysqli_rollback($conn);
    $storeCodeRequestSession($email, (int) $admission['id']);
    $redirectToCodeEntry();
}

mysqli_commit($conn);

$subject = 'Your Associa8 CBT access code';
$body = '<p>Hello ' . htmlspecialchars($admission['applicant_name'], ENT_QUOTES, 'UTF-8') . ',</p>'
    . '<p>Your access code for <strong>' . htmlspecialchars($admission['exam_title'], ENT_QUOTES, 'UTF-8') . '</strong> is:</p>'
    . '<p style="font-size: 24px; font-weight: 700; letter-spacing: 6px;">' . htmlspecialchars($examCode, ENT_QUOTES, 'UTF-8') . '</p>'
    . '<p>The assessment timer starts after you enter the code. You will have ' . (int) $admission['duration_minutes'] . ' minutes to complete the assessment.</p>';
if (!send_app_mail($admission['email'], $admission['applicant_name'], $subject, $body)) {
    error_log('CBT access code email failed for admission ' . $admission['id'] . '.');
}

$storeCodeRequestSession($email, (int) $admission['id']);
$redirectToCodeEntry();
