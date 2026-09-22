<?php
session_start();
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cbt-code.php');
    exit;
}

$email = strtolower(trim($_POST['email'] ?? ''));
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: cbt-code.php?status=error&msg=' . urlencode('Enter a valid application email address.'));
    exit;
}

$statement = mysqli_prepare($conn, "SELECT a.id, a.org_id, a.applicant_name, a.email, e.title AS exam_title, e.duration_minutes FROM admissions a INNER JOIN cbt_exams e ON e.id = a.cbt_exam_id AND e.org_id = a.org_id WHERE LOWER(a.email) = ? AND a.org_id IS NOT NULL AND a.status = 'cbt_scheduled' LIMIT 1");
if (!$statement) {
    header('Location: cbt-code.php?status=error&msg=' . urlencode('The CBT email service is unavailable.'));
    exit;
}
mysqli_stmt_bind_param($statement, 's', $email);
mysqli_stmt_execute($statement);
$result = mysqli_stmt_get_result($statement);
$admission = $result ? mysqli_fetch_assoc($result) : null;

if (!$admission) {
    header('Location: cbt-code.php?status=error&msg=' . urlencode('No active CBT assessment was found for this email.'));
    exit;
}

$attemptStatement = mysqli_prepare($conn, 'SELECT id FROM cbt_results WHERE admission_id = ? LIMIT 1');
if (!$attemptStatement) {
    header('Location: cbt-code.php?status=error&msg=' . urlencode('The CBT attempt service is unavailable.'));
    exit;
}
mysqli_stmt_bind_param($attemptStatement, 'i', $admission['id']);
mysqli_stmt_execute($attemptStatement);
$attemptResult = mysqli_stmt_get_result($attemptStatement);
if ($attemptResult && mysqli_num_rows($attemptResult) > 0) {
    header('Location: cbt-code.php?status=error&msg=' . urlencode('This CBT attempt has already been submitted.'));
    exit;
}

$examCode = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
$update = mysqli_prepare($conn, 'UPDATE admissions SET exam_code = ?, exam_expires_at = NULL WHERE id = ? AND status = \'cbt_scheduled\'');
if (!$update) {
    header('Location: cbt-code.php?status=error&msg=' . urlencode('The CBT code service is unavailable.'));
    exit;
}
mysqli_stmt_bind_param($update, 'si', $examCode, $admission['id']);
if (!mysqli_stmt_execute($update) || mysqli_stmt_affected_rows($update) !== 1) {
    header('Location: cbt-code.php?status=error&msg=' . urlencode('The access code could not be generated. Please try again.'));
    exit;
}

$subject = 'Your Associa8 CBT access code';
$body = '<p>Hello ' . htmlspecialchars($admission['applicant_name'], ENT_QUOTES, 'UTF-8') . ',</p>'
    . '<p>Your access code for <strong>' . htmlspecialchars($admission['exam_title'], ENT_QUOTES, 'UTF-8') . '</strong> is:</p>'
    . '<p style="font-size: 24px; font-weight: 700; letter-spacing: 6px;">' . htmlspecialchars($examCode, ENT_QUOTES, 'UTF-8') . '</p>'
    . '<p>The assessment timer starts only after you enter the correct code. You will have ' . (int) $admission['duration_minutes'] . ' minutes to complete the assessment.</p>';
if (!send_app_mail($admission['email'], $admission['applicant_name'], $subject, $body)) {
    error_log('CBT access code email failed for admission ' . $admission['id'] . '.');
    header('Location: cbt-code.php?status=error&msg=' . urlencode('The code was generated, but we could not send the email. Please try again.'));
    exit;
}

$_SESSION['applicant_email'] = $admission['email'];
$_SESSION['applicant_admission_id'] = (int) $admission['id'];
header('Location: cbt-code-entry.php?status=success&msg=' . urlencode('Your access code has been sent to your email.'));
exit;
