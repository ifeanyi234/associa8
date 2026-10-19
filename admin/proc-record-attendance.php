<?php
require_once 'inc/auth.php';
require_once '../inc/db.php';

function attendanceRedirect(string $type, string $text, array $oldStatuses = []): void
{
    $_SESSION['attendance_notice'] = ['type' => $type, 'text' => $text];
    if ($oldStatuses) {
        $_SESSION['attendance_old_statuses'] = $oldStatuses;
    } else {
        unset($_SESSION['attendance_old_statuses']);
    }
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

$submittedStatuses = $_POST['statuses'] ?? null;
if (!is_array($submittedStatuses)) {
    attendanceRedirect('error', 'Choose an attendance status for at least one member.');
}
$rosterCount = filter_var($_POST['roster_count'] ?? null, FILTER_VALIDATE_INT);
if (!$rosterCount || $rosterCount < 1 || count($submittedStatuses) !== $rosterCount) {
    attendanceRedirect('error', 'The full member roster was not submitted. Reload the page and try again.');
}
$statuses = [];
$validStatuses = ['present', 'late', 'absent'];
foreach ($submittedStatuses as $memberIdInput => $status) {
    $memberId = filter_var($memberIdInput, FILTER_VALIDATE_INT);
    if (!$memberId || $memberId < 1 || !is_string($status)) {
        attendanceRedirect('error', 'Choose valid member attendance statuses.');
    }
    if ($status === '') {
        continue;
    }
    if (!in_array($status, $validStatuses, true)) {
        attendanceRedirect('error', 'Choose valid member attendance statuses.');
    }
    $statuses[(int) $memberId] = $status;
}
if (!$statuses) {
    attendanceRedirect('error', 'Choose an attendance status for at least one member.');
}

$checkInInput = (string) ($_POST['check_in'] ?? '');
$lagosTimezone = new DateTimeZone('Africa/Lagos');
$utcTimezone = new DateTimeZone('UTC');
$checkIn = DateTime::createFromFormat('!Y-m-d\TH:i', $checkInInput, $lagosTimezone);
$dateErrors = DateTime::getLastErrors();

if (!$checkIn || ($dateErrors && ($dateErrors['warning_count'] || $dateErrors['error_count']))) {
    attendanceRedirect('error', 'Choose a valid date and time.');
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
$memberIds = array_keys($statuses);
$memberPlaceholders = implode(',', array_fill(0, count($memberIds), '?'));
$memberSql = 'SELECT id FROM members WHERE id IN (' . $memberPlaceholders . ')';
if (!$canViewAllOrganizations) {
    $memberSql .= ' AND org_id = ?';
}
$memberSql .= ' FOR UPDATE';
$memberStmt = mysqli_prepare($conn, $memberSql);
if (!$memberStmt) {
    error_log('Attendance member validation could not be prepared: ' . mysqli_error($conn));
    attendanceRedirect('error', 'Attendance could not be recorded. Please try again later.');
}
$memberParams = $memberIds;
$memberParamTypes = str_repeat('i', count($memberIds));
if (!$canViewAllOrganizations) {
    $memberParams[] = $orgId;
    $memberParamTypes .= 'i';
}
if (!mysqli_stmt_bind_param($memberStmt, $memberParamTypes, ...$memberParams)) {
    error_log('Attendance member validation parameters could not be bound: ' . mysqli_stmt_error($memberStmt));
    mysqli_stmt_close($memberStmt);
    attendanceRedirect('error', 'Attendance could not be recorded. Please try again later.');
}
if (!mysqli_begin_transaction($conn)) {
    error_log('Attendance transaction could not be started: ' . mysqli_error($conn));
    mysqli_stmt_close($memberStmt);
    attendanceRedirect('error', 'Attendance could not be recorded. Please try again later.');
}
if (!mysqli_stmt_execute($memberStmt)) {
    mysqli_rollback($conn);
    error_log('Attendance member validation failed: ' . mysqli_stmt_error($memberStmt));
    mysqli_stmt_close($memberStmt);
    attendanceRedirect('error', 'Attendance could not be recorded. Please try again later.');
}
$memberResult = mysqli_stmt_get_result($memberStmt);
if (!$memberResult) {
    mysqli_rollback($conn);
    error_log('Attendance member validation result could not be read: ' . mysqli_stmt_error($memberStmt));
    mysqli_stmt_close($memberStmt);
    attendanceRedirect('error', 'Attendance could not be recorded. Please try again later.');
}
$availableMemberIds = [];
while ($memberRow = mysqli_fetch_assoc($memberResult)) {
    $availableMemberIds[] = (int) $memberRow['id'];
}
mysqli_stmt_close($memberStmt);
if (count($availableMemberIds) !== count($statuses)) {
    mysqli_rollback($conn);
    attendanceRedirect('error', 'That member is not available in your organization.');
}

$duplicateSql = 'SELECT member_id FROM attendance_logs WHERE member_id IN (' . $memberPlaceholders . ') AND check_in >= ? AND check_in < ? FOR UPDATE';
$duplicateStmt = mysqli_prepare($conn, $duplicateSql);
if (!$duplicateStmt) {
    mysqli_rollback($conn);
    error_log('Attendance duplicate check could not be prepared: ' . mysqli_error($conn));
    attendanceRedirect('error', 'Attendance could not be recorded. Please try again later.');
}
$duplicateParams = array_merge($memberIds, [$localDayStartUtc, $localDayEndUtc]);
$duplicateParamTypes = str_repeat('i', count($memberIds)) . 'ss';
if (!mysqli_stmt_bind_param($duplicateStmt, $duplicateParamTypes, ...$duplicateParams)) {
    mysqli_rollback($conn);
    error_log('Attendance duplicate check parameters could not be bound: ' . mysqli_stmt_error($duplicateStmt));
    mysqli_stmt_close($duplicateStmt);
    attendanceRedirect('error', 'Attendance could not be recorded. Please try again later.');
}
if (!mysqli_stmt_execute($duplicateStmt)) {
    mysqli_rollback($conn);
    error_log('Attendance duplicate check failed: ' . mysqli_stmt_error($duplicateStmt));
    mysqli_stmt_close($duplicateStmt);
    attendanceRedirect('error', 'Attendance could not be recorded. Please try again later.');
}
$duplicateResult = mysqli_stmt_get_result($duplicateStmt);
if (!$duplicateResult) {
    mysqli_rollback($conn);
    error_log('Attendance duplicate check result could not be read: ' . mysqli_stmt_error($duplicateStmt));
    mysqli_stmt_close($duplicateStmt);
    attendanceRedirect('error', 'Attendance could not be recorded. Please try again later.');
}
$duplicateMemberIds = [];
while ($duplicate = mysqli_fetch_assoc($duplicateResult)) {
    $duplicateMemberIds[] = (int) $duplicate['member_id'];
}
mysqli_stmt_close($duplicateStmt);
if ($duplicateMemberIds) {
    mysqli_rollback($conn);
    $retryStatuses = $statuses;
    foreach ($duplicateMemberIds as $duplicateMemberId) {
        unset($retryStatuses[$duplicateMemberId]);
    }
    attendanceRedirect(
        'error',
        'Some selected members already have attendance records for that date. Their selections were cleared; review and save the rest.',
        $retryStatuses
    );
}

$insertStmt = mysqli_prepare($conn, 'INSERT INTO attendance_logs (member_id, check_in, status) VALUES (?, ?, ?)');
if (!$insertStmt) {
    mysqli_rollback($conn);
    error_log('Attendance insert could not be prepared: ' . mysqli_error($conn));
    attendanceRedirect('error', 'Attendance could not be recorded. Please try again later.');
}
foreach ($statuses as $memberId => $status) {
    mysqli_stmt_bind_param($insertStmt, 'iss', $memberId, $checkInSql, $status);
    if (!mysqli_stmt_execute($insertStmt)) {
        mysqli_rollback($conn);
        error_log('Attendance insert failed: ' . mysqli_stmt_error($insertStmt));
        mysqli_stmt_close($insertStmt);
        attendanceRedirect('error', 'Attendance could not be recorded. Please try again later.');
    }
}
mysqli_stmt_close($insertStmt);
if (!mysqli_commit($conn)) {
    error_log('Attendance transaction could not be committed: ' . mysqli_error($conn));
    mysqli_rollback($conn);
    attendanceRedirect('error', 'Attendance could not be recorded. Please try again later.');
}

attendanceRedirect('success', count($statuses) . ' ' . (count($statuses) === 1 ? 'member' : 'members') . ' marked.');
