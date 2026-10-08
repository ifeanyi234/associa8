<?php
require_once 'inc/auth.php';
require_once '../inc/db.php';

function attendanceRedirect(string $type, string $text): void
{
    $_SESSION['attendance_notice'] = ['type' => $type, 'text' => $text];
    header('Location: ' . ($type === 'success' ? 'attendance.php' : 'add-attendance.php'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: attendance.php');
    exit;
}

$csrfToken = $_POST['csrf_token'] ?? '';
$sessionToken = $_SESSION['attendance_csrf'] ?? '';
if (!is_string($csrfToken) || !is_string($sessionToken) || $sessionToken === '' || !hash_equals($sessionToken, $csrfToken)) {
    attendanceRedirect('error', 'Your form expired. Reload the page and try again.');
}

$memberId = filter_var($_POST['member_id'] ?? null, FILTER_VALIDATE_INT);
$status = (string) ($_POST['status'] ?? '');
$checkInInput = (string) ($_POST['check_in'] ?? '');
$validStatuses = ['present', 'late', 'absent'];
$lagosTimezone = new DateTimeZone('Africa/Lagos');
$utcTimezone = new DateTimeZone('UTC');
$checkIn = DateTime::createFromFormat('!Y-m-d\TH:i', $checkInInput, $lagosTimezone);
$dateErrors = DateTime::getLastErrors();

if (!$memberId || !in_array($status, $validStatuses, true) || !$checkIn || ($dateErrors && ($dateErrors['warning_count'] || $dateErrors['error_count']))) {
    attendanceRedirect('error', 'Choose a member, a valid attendance status, and a valid date and time.');
}

$nowInLagos = new DateTimeImmutable('now', $lagosTimezone);
if ($checkIn > $nowInLagos) {
    attendanceRedirect('error', 'Attendance cannot be recorded for a future date or time.');
}

$checkInSql = $checkIn->setTimezone($utcTimezone)->format('Y-m-d H:i:s');
$localDayStart = DateTimeImmutable::createFromFormat('!Y-m-d', $checkIn->format('Y-m-d'), $lagosTimezone);
$localDayEnd = $localDayStart->modify('+1 day');
$localDayStartUtc = $localDayStart->setTimezone($utcTimezone)->format('Y-m-d H:i:s');
$localDayEndUtc = $localDayEnd->setTimezone($utcTimezone)->format('Y-m-d H:i:s');
$orgId = isset($_SESSION['org_id']) ? (int) $_SESSION['org_id'] : 0;
$isPlatformAdmin = ($_SESSION['admin_role'] ?? '') === 'super_admin';
$canViewAllOrganizations = $isPlatformAdmin && $orgId < 1;
$memberScope = $canViewAllOrganizations ? '' : ' AND org_id = ?';

$memberSql = 'SELECT id FROM members WHERE id = ?' . $memberScope . ' LIMIT 1';
$memberStmt = mysqli_prepare($conn, $memberSql);
if (!$memberStmt) {
    error_log('Attendance member validation could not be prepared: ' . mysqli_error($conn));
    attendanceRedirect('error', 'Attendance could not be recorded. Please try again later.');
}
if ($canViewAllOrganizations) {
    mysqli_stmt_bind_param($memberStmt, 'i', $memberId);
} else {
    mysqli_stmt_bind_param($memberStmt, 'ii', $memberId, $orgId);
}

if (!mysqli_stmt_execute($memberStmt)) {
    error_log('Attendance member validation failed: ' . mysqli_stmt_error($memberStmt));
    mysqli_stmt_close($memberStmt);
    attendanceRedirect('error', 'Attendance could not be recorded. Please try again later.');
}
$memberResult = mysqli_stmt_get_result($memberStmt);
$memberExists = $memberResult && mysqli_num_rows($memberResult) === 1;
mysqli_stmt_close($memberStmt);
if (!$memberExists) {
    attendanceRedirect('error', 'That member is not available in your organization.');
}

if (!mysqli_begin_transaction($conn)) {
    error_log('Attendance transaction could not be started: ' . mysqli_error($conn));
    attendanceRedirect('error', 'Attendance could not be recorded. Please try again later.');
}
$duplicateStmt = mysqli_prepare($conn, 'SELECT id FROM attendance_logs WHERE member_id = ? AND check_in >= ? AND check_in < ? LIMIT 1 FOR UPDATE');
if (!$duplicateStmt) {
    mysqli_rollback($conn);
    error_log('Attendance duplicate check could not be prepared: ' . mysqli_error($conn));
    attendanceRedirect('error', 'Attendance could not be recorded. Please try again later.');
}
mysqli_stmt_bind_param($duplicateStmt, 'iss', $memberId, $localDayStartUtc, $localDayEndUtc);
if (!mysqli_stmt_execute($duplicateStmt)) {
    mysqli_rollback($conn);
    error_log('Attendance duplicate check failed: ' . mysqli_stmt_error($duplicateStmt));
    mysqli_stmt_close($duplicateStmt);
    attendanceRedirect('error', 'Attendance could not be recorded. Please try again later.');
}
$duplicateResult = mysqli_stmt_get_result($duplicateStmt);
$alreadyRecorded = $duplicateResult && mysqli_num_rows($duplicateResult) > 0;
mysqli_stmt_close($duplicateStmt);
if ($alreadyRecorded) {
    mysqli_rollback($conn);
    attendanceRedirect('error', 'Attendance has already been recorded for this member on that date.');
}

$insertStmt = mysqli_prepare($conn, 'INSERT INTO attendance_logs (member_id, check_in, status) VALUES (?, ?, ?)');
if (!$insertStmt) {
    mysqli_rollback($conn);
    error_log('Attendance insert could not be prepared: ' . mysqli_error($conn));
    attendanceRedirect('error', 'Attendance could not be recorded. Please try again later.');
}
mysqli_stmt_bind_param($insertStmt, 'iss', $memberId, $checkInSql, $status);
if (!mysqli_stmt_execute($insertStmt)) {
    mysqli_rollback($conn);
    error_log('Attendance insert failed: ' . mysqli_stmt_error($insertStmt));
    mysqli_stmt_close($insertStmt);
    attendanceRedirect('error', 'Attendance could not be recorded. Please try again later.');
}
mysqli_stmt_close($insertStmt);
if (!mysqli_commit($conn)) {
    error_log('Attendance transaction could not be committed: ' . mysqli_error($conn));
    mysqli_rollback($conn);
    attendanceRedirect('error', 'Attendance could not be recorded. Please try again later.');
}

attendanceRedirect('success', 'Attendance record saved.');
