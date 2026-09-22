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
if ($adminRole !== 'super_admin' && $orgId === null) {
    header('Location: schedule-cbt.php?status=error&msg=' . urlencode('Your account is not linked to an organization.'));
    exit;
}

$scheduledAt = DateTime::createFromFormat('Y-m-d\\TH:i', $scheduledInput);
$validationErrors = DateTime::getLastErrors();
if (!$scheduledAt || ($validationErrors !== false && ($validationErrors['warning_count'] > 0 || $validationErrors['error_count'] > 0)) || $scheduledAt <= new DateTime()) {
    header('Location: schedule-cbt.php?status=error&msg=' . urlencode('Choose a valid future scheduled time.'));
    exit;
}

$admissionSql = 'SELECT id, applicant_name, email, status, org_id FROM admissions WHERE id = ' . $admissionId;
$examSql = "SELECT id, title, duration_minutes, status, org_id FROM cbt_exams WHERE id = " . $examId . " AND status = 'active'";
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
    error_log('CBT schedule applicant lookup failed: admission_id=' . $admissionId . ', session_org_id=' . var_export($orgId, true) . ', role=' . $adminRole);
    header('Location: schedule-cbt.php?status=error&msg=' . urlencode('The applicant was not found in your organization. Check the applicant organization ID.'));
    exit;
}
if (!$exam) {
    $examDebug = mysqli_prepare($conn, 'SELECT status, org_id FROM cbt_exams WHERE id = ? LIMIT 1');
    $examStatus = 'missing';
    $examOrg = 'unknown';
    if ($examDebug) {
        mysqli_stmt_bind_param($examDebug, 'i', $examId);
        mysqli_stmt_execute($examDebug);
        $examDebugResult = mysqli_stmt_get_result($examDebug);
        $examDebugRow = $examDebugResult ? mysqli_fetch_assoc($examDebugResult) : null;
        if ($examDebugRow) {
            $examStatus = $examDebugRow['status'];
            $examOrg = $examDebugRow['org_id'] === null ? 'NULL' : (string) $examDebugRow['org_id'];
        }
    }
    error_log('CBT schedule exam lookup failed: exam_id=' . $examId . ', exam_status=' . $examStatus . ', exam_org_id=' . $examOrg . ', session_org_id=' . var_export($orgId, true) . ', role=' . $adminRole);
    $message = $examStatus === 'missing'
        ? 'The selected exam no longer exists. Refresh the page and choose an available exam.'
        : 'The selected exam is ' . $examStatus . ' for organization ' . $examOrg . ', but your session is organization ' . ($orgId === null ? 'NULL' : $orgId) . '. Refresh the page or correct the exam organization.';
    header('Location: schedule-cbt.php?status=error&msg=' . urlencode($message));
    exit;
}
if ($admission['status'] !== 'under_review') {
    header('Location: schedule-cbt.php?status=error&msg=' . urlencode('This applicant is no longer under review. Refresh the scheduling page and try again.'));
    exit;
}

$expiresAt = clone $scheduledAt;
$expiresAt->modify('+' . (int) $exam['duration_minutes'] . ' minutes');
$scheduledSqlValue = $scheduledAt->format('Y-m-d H:i:s');
$expiresSqlValue = $expiresAt->format('Y-m-d H:i:s');

mysqli_begin_transaction($conn);
$update = mysqli_prepare($conn, 'UPDATE admissions SET cbt_exam_id = ?, exam_code = NULL, exam_scheduled_at = ?, exam_expires_at = ?, status = \'cbt_scheduled\' WHERE id = ? AND status = \'under_review\'');
$notification = mysqli_prepare($conn, 'INSERT INTO notifications (admission_id, type, title, message) VALUES (?, \'cbt_scheduled\', ?, ?)');
$title = 'CBT exam scheduled';
$basePath = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\');
$assessmentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $basePath . '/cbt-code.php';
$message = 'Your CBT exam is scheduled for ' . $scheduledSqlValue . '. Use the assessment link in your email to request your access code.';

if (!$update || !$notification) {
    mysqli_rollback($conn);
    header('Location: schedule-cbt.php?status=error&msg=' . urlencode('The CBT schedule could not be prepared.'));
    exit;
}

mysqli_stmt_bind_param($update, 'issi', $examId, $scheduledSqlValue, $expiresSqlValue, $admissionId);
mysqli_stmt_bind_param($notification, 'iss', $admissionId, $title, $message);
$updated = mysqli_stmt_execute($update);
$notified = mysqli_stmt_execute($notification);
if (!$updated || !$notified || mysqli_stmt_affected_rows($update) !== 1) {
    mysqli_rollback($conn);
    header('Location: schedule-cbt.php?status=error&msg=' . urlencode('The CBT schedule could not be saved.'));
    exit;
}
mysqli_commit($conn);

$subject = 'Your Associa8 CBT exam is scheduled';
$body = '<p>Hello ' . htmlspecialchars($admission['applicant_name'], ENT_QUOTES, 'UTF-8') . ',</p>'
    . '<p>Your CBT exam, <strong>' . htmlspecialchars($exam['title'], ENT_QUOTES, 'UTF-8') . '</strong>, is scheduled for <strong>' . htmlspecialchars($scheduledSqlValue, ENT_QUOTES, 'UTF-8') . '</strong>.</p>'
    . '<p>When you are ready, open the assessment link below and enter this email address to receive your one-time access code.</p>'
    . '<p><a href="' . htmlspecialchars($assessmentUrl, ENT_QUOTES, 'UTF-8') . '">Open the CBT assessment</a></p>'
    . '<p>The access code will expire at <strong>' . htmlspecialchars($expiresSqlValue, ENT_QUOTES, 'UTF-8') . '</strong>.</p>';
$emailSent = send_app_mail($admission['email'], $admission['applicant_name'], $subject, $body);
if (!$emailSent) {
    error_log('CBT schedule email failed for admission ' . $admissionId . '.');
}

$scheduleMessage = $emailSent
    ? 'CBT scheduled and assessment link sent to the applicant.'
    : 'CBT scheduled, but the notification email could not be sent.';
header('Location: schedule-cbt.php?status=' . ($emailSent ? 'success' : 'error') . '&msg=' . urlencode($scheduleMessage));
exit;
