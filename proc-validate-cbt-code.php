<?php
session_start();
require_once __DIR__ . '/inc/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cbt-code.php');
    exit;
}

$email = strtolower(trim($_SESSION['applicant_email'] ?? ''));
$codeParts = $_POST['code'] ?? [];
$examCode = strtoupper(implode('', array_map('trim', (array) $codeParts)));

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/^[A-Z0-9]{6}$/', $examCode)) {
    header('Location: cbt-code-entry.php?status=error&msg=' . urlencode('Enter the six-character access code sent to your email.'));
    exit;
}

$statement = mysqli_prepare($conn, "SELECT a.id, a.org_id, a.applicant_name, a.email, a.exam_code, a.exam_expires_at, a.cbt_exam_id, e.title AS exam_title, e.duration_minutes FROM admissions a INNER JOIN cbt_exams e ON e.id = a.cbt_exam_id AND e.org_id = a.org_id WHERE LOWER(a.email) = ? AND a.exam_code = ? AND a.org_id IS NOT NULL AND a.status = 'under_review' AND a.exam_expires_at IS NOT NULL AND a.exam_expires_at > UTC_TIMESTAMP() LIMIT 1");
if (!$statement) {
    header('Location: cbt-code-entry.php?status=error&msg=' . urlencode('The CBT code service is unavailable.'));
    exit;
}

mysqli_stmt_bind_param($statement, 'ss', $email, $examCode);
mysqli_stmt_execute($statement);
$result = mysqli_stmt_get_result($statement);
$admission = $result ? mysqli_fetch_assoc($result) : null;

if (!$admission) {
    header('Location: cbt-code-entry.php?status=error&msg=' . urlencode('That access code is not valid for this assessment.'));
    exit;
}

$expiryUpdate = mysqli_prepare($conn, 'UPDATE admissions SET exam_expires_at = DATE_ADD(UTC_TIMESTAMP(), INTERVAL ? MINUTE) WHERE id = ? AND exam_code = ? AND status = \'under_review\'');
if (!$expiryUpdate) {
    header('Location: cbt-code-entry.php?status=error&msg=' . urlencode('The assessment timer service is unavailable.'));
    exit;
}
$durationMinutes = max(1, (int) $admission['duration_minutes']);
mysqli_stmt_bind_param($expiryUpdate, 'iis', $durationMinutes, $admission['id'], $examCode);
if (!mysqli_stmt_execute($expiryUpdate) || mysqli_stmt_affected_rows($expiryUpdate) !== 1) {
    header('Location: cbt-code-entry.php?status=error&msg=' . urlencode('The assessment timer could not be started. Please try again.'));
    exit;
}

$attemptStatement = mysqli_prepare($conn, 'SELECT id FROM cbt_results WHERE admission_id = ? LIMIT 1');
if (!$attemptStatement) {
    header('Location: cbt-code-entry.php?status=error&msg=' . urlencode('The CBT attempt service is unavailable.'));
    exit;
}
mysqli_stmt_bind_param($attemptStatement, 'i', $admission['id']);
mysqli_stmt_execute($attemptStatement);
$attemptResult = mysqli_stmt_get_result($attemptStatement);
if ($attemptResult && mysqli_num_rows($attemptResult) > 0) {
    header('Location: cbt-code-entry.php?status=error&msg=' . urlencode('This CBT attempt has already been submitted.'));
    exit;
}

session_regenerate_id(true);
$assessmentExpiresAt = time() + ($durationMinutes * 60);
$_SESSION['applicant_admission_id'] = (int) $admission['id'];
$_SESSION['applicant_org_id'] = $admission['org_id'] !== null ? (int) $admission['org_id'] : null;
$_SESSION['applicant_exam_id'] = (int) $admission['cbt_exam_id'];
$_SESSION['applicant_exam_title'] = $admission['exam_title'];
$_SESSION['applicant_exam_expires_at'] = gmdate('Y-m-d H:i:s', $assessmentExpiresAt);
$_SESSION['applicant_assessment_expires_at'] = $assessmentExpiresAt;
unset($_SESSION['applicant_email']);

header('Location: cbt-assessment.php');
exit;
