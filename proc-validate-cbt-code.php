<?php
session_start();
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/rate-limit.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cbt-code.php');
    exit;
}

$email = strtolower(trim($_SESSION['applicant_email'] ?? ''));
$admissionId = (int) ($_SESSION['applicant_admission_id'] ?? 0);
$codeParts = $_POST['code'] ?? [];
$examCode = strtoupper(implode('', array_map('trim', (array) $codeParts)));

if (empty($_SESSION['applicant_code_request']) || !filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/^[A-Z0-9]{6}$/', $examCode)) {
    header('Location: cbt-code-entry.php?status=error&msg=' . urlencode('Enter the six-character access code sent to your email.'));
    exit;
}

$requestIp = $_SERVER['REMOTE_ADDR'] ?? '';
if ($requestIp === '') {
    error_log('CBT code verification did not include a remote IP address.');
    http_response_code(500);
    exit('The CBT verification service is unavailable.');
}

try {
    $ipRetryAfter = associa8_rate_limit_retry_after($conn, 'cbt_code_verify_ip', $requestIp, 20, 900);
    $admissionRetryAfter = associa8_rate_limit_retry_after($conn, 'cbt_code_verify_email', $email, 5, 900);
} catch (RuntimeException $error) {
    http_response_code(500);
    exit('The CBT verification service is unavailable.');
}
if ($ipRetryAfter > 0 || $admissionRetryAfter > 0) {
    $retryAfter = max($ipRetryAfter, $admissionRetryAfter);
    header('Retry-After: ' . $retryAfter);
    header('Location: cbt-code-entry.php?status=error&msg=' . urlencode('Too many code attempts. Please wait before trying again.'));
    exit;
}

mysqli_begin_transaction($conn);
$statement = mysqli_prepare($conn, "SELECT a.id, a.org_id, a.applicant_name, a.email, a.exam_code_hash, a.exam_expires_at, a.cbt_exam_id, e.title AS exam_title, e.duration_minutes FROM admissions a INNER JOIN cbt_exams e ON e.id = a.cbt_exam_id AND e.org_id = a.org_id AND e.status = 'active' AND EXISTS (SELECT 1 FROM cbt_questions q WHERE q.exam_id = e.id) AND NOT EXISTS (SELECT 1 FROM cbt_questions q WHERE q.exam_id = e.id AND (q.question_text = '' OR q.option_a = '' OR q.option_b = '' OR q.option_c = '' OR q.option_d = '' OR q.correct_option NOT IN ('A','B','C','D','E') OR (q.correct_option = 'E' AND q.option_e = ''))) WHERE a.id = ? AND LOWER(a.email) = ? AND a.org_id IS NOT NULL AND a.cbt_attempt_started_at IS NULL AND a.status = 'under_review' AND a.exam_code_hash IS NOT NULL AND a.cbt_response_deadline > UTC_TIMESTAMP() AND a.exam_expires_at > UTC_TIMESTAMP() AND (a.cbt_scheduled_at IS NULL OR a.cbt_scheduled_at <= UTC_TIMESTAMP()) LIMIT 1 FOR UPDATE");
if (!$statement) {
    mysqli_rollback($conn);
    error_log('CBT code lookup prepare failed: ' . mysqli_error($conn));
    header('Location: cbt-code-entry.php?status=error&msg=' . urlencode('The CBT code service is unavailable.'));
    exit;
}

mysqli_stmt_bind_param($statement, 'is', $admissionId, $email);
if (!mysqli_stmt_execute($statement)) {
    mysqli_rollback($conn);
    error_log('CBT code lookup failed: ' . mysqli_stmt_error($statement));
    header('Location: cbt-code-entry.php?status=error&msg=' . urlencode('The CBT code service is unavailable.'));
    exit;
}
$result = mysqli_stmt_get_result($statement);
$admission = $result ? mysqli_fetch_assoc($result) : null;
if (!$admission) {
    mysqli_rollback($conn);
    header('Location: cbt-code-entry.php?status=error&msg=' . urlencode('That access code is not valid for this assessment.'));
    exit;
}
if (!password_verify($examCode, $admission['exam_code_hash'])) {
    mysqli_rollback($conn);
    header('Location: cbt-code-entry.php?status=error&msg=' . urlencode('That access code is not valid for this assessment.'));
    exit;
}

$attemptStatement = mysqli_prepare($conn, 'SELECT id FROM cbt_results WHERE admission_id = ? LIMIT 1');
if (!$attemptStatement) {
    mysqli_rollback($conn);
    error_log('CBT attempt lookup prepare failed: ' . mysqli_error($conn));
    header('Location: cbt-code-entry.php?status=error&msg=' . urlencode('The CBT attempt service is unavailable.'));
    exit;
}
mysqli_stmt_bind_param($attemptStatement, 'i', $admission['id']);
if (!mysqli_stmt_execute($attemptStatement)) {
    mysqli_rollback($conn);
    error_log('CBT attempt lookup failed: ' . mysqli_stmt_error($attemptStatement));
    header('Location: cbt-code-entry.php?status=error&msg=' . urlencode('The CBT attempt service is unavailable.'));
    exit;
}
$attemptResult = mysqli_stmt_get_result($attemptStatement);
if ($attemptResult && mysqli_num_rows($attemptResult) > 0) {
    mysqli_rollback($conn);
    header('Location: cbt-code-entry.php?status=error&msg=' . urlencode('This CBT attempt has already been submitted.'));
    exit;
}

$durationMinutes = max(1, (int) $admission['duration_minutes']);
$expiryUpdate = mysqli_prepare($conn, 'UPDATE admissions SET exam_code = NULL, exam_code_hash = NULL, cbt_attempt_started_at = UTC_TIMESTAMP(), exam_expires_at = DATE_ADD(UTC_TIMESTAMP(), INTERVAL ? MINUTE) WHERE id = ? AND exam_code_hash = ? AND cbt_attempt_started_at IS NULL AND status = \'under_review\' AND cbt_response_deadline > UTC_TIMESTAMP() AND exam_expires_at > UTC_TIMESTAMP() AND (cbt_scheduled_at IS NULL OR cbt_scheduled_at <= UTC_TIMESTAMP())');
if (!$expiryUpdate) {
    mysqli_rollback($conn);
    error_log('CBT timer update prepare failed: ' . mysqli_error($conn));
    header('Location: cbt-code-entry.php?status=error&msg=' . urlencode('The assessment timer service is unavailable.'));
    exit;
}
mysqli_stmt_bind_param($expiryUpdate, 'iis', $durationMinutes, $admission['id'], $admission['exam_code_hash']);
if (!mysqli_stmt_execute($expiryUpdate) || mysqli_stmt_affected_rows($expiryUpdate) !== 1) {
    mysqli_rollback($conn);
    header('Location: cbt-code-entry.php?status=error&msg=' . urlencode('The assessment timer could not be started. Please request a new code.'));
    exit;
}
mysqli_commit($conn);

session_regenerate_id(true);
$assessmentExpiresAt = time() + ($durationMinutes * 60);
$_SESSION['applicant_admission_id'] = (int) $admission['id'];
$_SESSION['applicant_org_id'] = (int) $admission['org_id'];
$_SESSION['applicant_exam_id'] = (int) $admission['cbt_exam_id'];
$_SESSION['applicant_exam_title'] = $admission['exam_title'];
$_SESSION['applicant_exam_expires_at'] = gmdate('Y-m-d H:i:s', $assessmentExpiresAt);
$_SESSION['applicant_assessment_expires_at'] = $assessmentExpiresAt;
unset($_SESSION['applicant_email']);

header('Location: cbt-assessment.php');
exit;
