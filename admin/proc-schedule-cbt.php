<?php
require_once 'inc/auth.php';
require_once '../inc/db.php';
require_once '../inc/admission-notifications.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: schedule-cbt.php');
    exit;
}

$admissionId = (int) ($_POST['admission_id'] ?? 0);
$examId = (int) ($_POST['exam_id'] ?? 0);
$scheduledInput = trim($_POST['scheduled_at'] ?? '');
$adminRole = $_SESSION['admin_role'] ?? 'admin';
$orgId = isset($_SESSION['org_id']) && $_SESSION['org_id'] !== null ? (int) $_SESSION['org_id'] : null;

if ($admissionId < 1 || $examId < 1 || $scheduledInput === '') {
    header('Location: schedule-cbt.php?status=error&msg=' . urlencode('Choose an applicant, an active exam, and a scheduled time.'));
    exit;
}
if ($adminRole !== 'super_admin' && ($orgId === null || $orgId < 1)) {
    header('Location: schedule-cbt.php?status=error&msg=' . urlencode('Your account is not linked to an organization.'));
    exit;
}

$scheduleTimezone = new DateTimeZone('Africa/Lagos');
$scheduledAt = DateTime::createFromFormat('!Y-m-d\\TH:i', $scheduledInput, $scheduleTimezone);
$validationErrors = DateTime::getLastErrors();
if (!$scheduledAt || $scheduledAt->format('Y-m-d\\TH:i') !== $scheduledInput || ($validationErrors !== false && ($validationErrors['warning_count'] > 0 || $validationErrors['error_count'] > 0)) || $scheduledAt <= new DateTime('now', $scheduleTimezone)) {
    header('Location: schedule-cbt.php?status=error&msg=' . urlencode('Choose a valid future scheduled time.'));
    exit;
}

$admissionSql = 'SELECT id, applicant_name, email, status, org_id FROM admissions WHERE id = ' . $admissionId;
$examSql = "SELECT id, title, duration_minutes, status, org_id FROM cbt_exams WHERE id = " . $examId . " AND status = 'active' AND EXISTS (SELECT 1 FROM cbt_questions q WHERE q.exam_id = cbt_exams.id) AND NOT EXISTS (SELECT 1 FROM cbt_questions q WHERE q.exam_id = cbt_exams.id AND (q.question_text = '' OR q.option_a = '' OR q.option_b = '' OR q.option_c = '' OR q.option_d = '' OR q.correct_option NOT IN ('A','B','C','D','E') OR (q.correct_option = 'E' AND q.option_e = '')))";
if ($adminRole !== 'super_admin') {
    $admissionSql .= ' AND org_id = ' . (int) $orgId;
    $examSql .= ' AND org_id = ' . (int) $orgId;
}
$admissionSql .= ' LIMIT 1';
$examSql .= ' LIMIT 1';

$admissionResult = mysqli_query($conn, $admissionSql);
$examResult = mysqli_query($conn, $examSql);
if (!$admissionResult || !$examResult) {
    error_log('CBT schedule lookup query failed: ' . mysqli_error($conn));
    header('Location: schedule-cbt.php?status=error&msg=' . urlencode('The CBT scheduling lookup failed. Check the database error log.'));
    exit;
}
$admission = $admissionResult ? mysqli_fetch_assoc($admissionResult) : null;
$exam = $examResult ? mysqli_fetch_assoc($examResult) : null;

if (!$admission) {
    header('Location: schedule-cbt.php?status=error&msg=' . urlencode('The applicant could not be found in your organization.'));
    exit;
}
if (!$exam) {
    header('Location: schedule-cbt.php?status=error&msg=' . urlencode('Choose an active exam with at least one complete question in your organization.'));
    exit;
}
$targetOrgId = (int) ($admission['org_id'] ?? 0);
if ($admission['status'] !== 'under_review' || $targetOrgId < 1 || $targetOrgId !== (int) ($exam['org_id'] ?? 0)) {
    header('Location: schedule-cbt.php?status=error&msg=' . urlencode('The applicant and active exam must belong to the same organization, and the applicant must be under review.'));
    exit;
}

$scheduledUtc = $scheduledAt->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
$scheduledDisplay = $scheduledAt->format('Y-m-d H:i') . ' Africa/Lagos time';

mysqli_begin_transaction($conn);
$update = mysqli_prepare($conn, 'UPDATE admissions a INNER JOIN cbt_exams e ON e.id = ? AND e.org_id = ? AND e.status = \'active\' SET a.cbt_exam_id = ?, a.cbt_scheduled_at = ?, a.cbt_response_deadline = DATE_ADD(?, INTERVAL 36 HOUR), a.exam_code = NULL, a.exam_code_hash = NULL, a.cbt_attempt_started_at = NULL, a.exam_expires_at = NULL WHERE a.id = ? AND a.org_id = ? AND a.status = \'under_review\' AND a.cbt_attempt_started_at IS NULL AND NOT EXISTS (SELECT 1 FROM cbt_results r WHERE r.admission_id = a.id)');
$notification = mysqli_prepare($conn, 'INSERT INTO notifications (admission_id, type, title, message) VALUES (?, \'cbt_scheduled\', ?, ?)');
$title = 'CBT exam scheduled';
$basePath = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\');
$assessmentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $basePath . '/cbt-code.php';
$message = 'Your CBT exam is scheduled for ' . $scheduledDisplay . '. You can request your access code from that time. Your response window ends 36 hours later.';

if (!$update || !$notification) {
    mysqli_rollback($conn);
    header('Location: schedule-cbt.php?status=error&msg=' . urlencode('The CBT schedule could not be prepared.'));
    exit;
}

mysqli_stmt_bind_param($update, 'iisssii', $examId, $targetOrgId, $examId, $scheduledUtc, $scheduledUtc, $admissionId, $targetOrgId);
mysqli_stmt_bind_param($notification, 'iss', $admissionId, $title, $message);
$updated = mysqli_stmt_execute($update);
if (!$updated || mysqli_stmt_affected_rows($update) !== 1) {
    mysqli_rollback($conn);
    header('Location: schedule-cbt.php?status=error&msg=' . urlencode('The CBT schedule could not be saved.'));
    exit;
}
$notified = mysqli_stmt_execute($notification);
if (!$notified) {
    mysqli_rollback($conn);
    header('Location: schedule-cbt.php?status=error&msg=' . urlencode('The CBT schedule could not be saved.'));
    exit;
}
mysqli_commit($conn);

$subject = 'Your Associa8 CBT exam is scheduled';
$body = '<p>Hello ' . htmlspecialchars($admission['applicant_name'], ENT_QUOTES, 'UTF-8') . ',</p>'
    . '<p>Your CBT exam, <strong>' . htmlspecialchars($exam['title'], ENT_QUOTES, 'UTF-8') . '</strong>, is scheduled for <strong>' . htmlspecialchars($scheduledDisplay, ENT_QUOTES, 'UTF-8') . '</strong>.</p>'
    . '<p>You can request your one-time access code from that time. Your response window ends 36 hours later. The exam timer starts when you enter the code.</p>'
    . '<p><a href="' . htmlspecialchars($assessmentUrl, ENT_QUOTES, 'UTF-8') . '">Open the CBT assessment</a></p>';
$emailSent = send_app_mail($admission['email'], $admission['applicant_name'], $subject, $body);
if (!$emailSent) {
    error_log('CBT schedule email failed for admission ' . $admissionId . '.');
}

$scheduleMessage = $emailSent
    ? 'CBT scheduled and assessment link sent to the applicant.'
    : 'CBT scheduled, but the notification email could not be sent.';
header('Location: schedule-cbt.php?status=' . ($emailSent ? 'success' : 'error') . '&msg=' . urlencode($scheduleMessage));
exit;
