<?php
require_once 'inc/auth.php';
require_once '../inc/db.php';

function eventAttendanceRedirect(int $eventId, string $type, string $text): void
{
    $_SESSION['event_attendance_notice'] = ['type' => $type, 'text' => $text];
    header('Location: event-attendance.php?id=' . $eventId);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: events.php');
    exit;
}

$eventId = filter_var($_POST['event_id'] ?? null, FILTER_VALIDATE_INT);
$token = $_POST['csrf_token'] ?? '';
$sessionToken = $_SESSION['event_csrf'] ?? '';
if (!$eventId || $eventId < 1) {
    header('Location: events.php');
    exit;
}
if (!is_string($token) || !is_string($sessionToken) || $sessionToken === '' || !hash_equals($sessionToken, $token)) {
    eventAttendanceRedirect($eventId, 'error', 'Your form expired. Reload the page and try again.');
}

$submittedAttendance = $_POST['attendance'] ?? null;
if (!is_array($submittedAttendance)) {
    eventAttendanceRedirect($eventId, 'error', 'Choose at least one attendance status.');
}

$orgId = (int) ($_SESSION['org_id'] ?? 0);
if ($orgId < 1) {
    admin_render_access_denied('Your account is not connected to an organization. Contact your administrator for help.');
}
if (!mysqli_begin_transaction($conn)) {
    error_log('Event attendance transaction could not start: ' . mysqli_error($conn));
    eventAttendanceRedirect($eventId, 'error', 'Attendance could not be saved. Please try again later.');
}

$eventStmt = mysqli_prepare(
    $conn,
    'SELECT event_date, event_time, status FROM events WHERE id = ? AND org_id = ? FOR UPDATE'
);
if (!$eventStmt) {
    mysqli_rollback($conn);
    error_log('Event attendance event lookup could not be prepared: ' . mysqli_error($conn));
    eventAttendanceRedirect($eventId, 'error', 'Attendance could not be saved. Please try again later.');
}
mysqli_stmt_bind_param($eventStmt, 'ii', $eventId, $orgId);
if (!mysqli_stmt_execute($eventStmt)) {
    mysqli_rollback($conn);
    error_log('Event attendance event lookup failed: ' . mysqli_stmt_error($eventStmt));
    mysqli_stmt_close($eventStmt);
    eventAttendanceRedirect($eventId, 'error', 'Attendance could not be saved. Please try again later.');
}
$eventResult = mysqli_stmt_get_result($eventStmt);
$event = $eventResult ? mysqli_fetch_assoc($eventResult) : null;
mysqli_stmt_close($eventStmt);
if (!$event) {
    mysqli_rollback($conn);
    http_response_code(404);
    exit('That event could not be found in your organization.');
}

$eventTimezone = new DateTimeZone('Africa/Lagos');
$eventEndsAt = new DateTimeImmutable(
    $event['event_date'] . ' ' . ($event['event_time'] ?: '23:59:59'),
    $eventTimezone
);
if ($event['status'] !== 'scheduled' || $eventEndsAt > new DateTimeImmutable('now', $eventTimezone)) {
    mysqli_rollback($conn);
    eventAttendanceRedirect($eventId, 'error', 'Attendance can only be recorded after a scheduled event has ended.');
}

$activeRsvpStmt = mysqli_prepare(
    $conn,
    "SELECT r.member_id
     FROM event_rsvps r
     INNER JOIN members m ON m.id = r.member_id AND m.org_id = ?
     WHERE r.event_id = ? AND r.status IN ('booked', 'waitlisted')"
);
if (!$activeRsvpStmt) {
    mysqli_rollback($conn);
    error_log('Event attendance RSVP validation could not be prepared: ' . mysqli_error($conn));
    eventAttendanceRedirect($eventId, 'error', 'Attendance could not be saved. Please try again later.');
}
mysqli_stmt_bind_param($activeRsvpStmt, 'ii', $orgId, $eventId);
if (!mysqli_stmt_execute($activeRsvpStmt)) {
    mysqli_rollback($conn);
    error_log('Event attendance RSVP validation failed: ' . mysqli_stmt_error($activeRsvpStmt));
    mysqli_stmt_close($activeRsvpStmt);
    eventAttendanceRedirect($eventId, 'error', 'Attendance could not be saved. Please try again later.');
}
$activeRsvpResult = mysqli_stmt_get_result($activeRsvpStmt);
$activeRsvpMemberIds = [];
while ($activeRsvpResult && ($row = mysqli_fetch_assoc($activeRsvpResult))) {
    $activeRsvpMemberIds[(int) $row['member_id']] = true;
}
mysqli_stmt_close($activeRsvpStmt);

$allowedStatuses = ['attended', 'missed', 'excused'];
$attendanceToSave = [];
foreach ($submittedAttendance as $submittedMemberId => $status) {
    $memberId = filter_var($submittedMemberId, FILTER_VALIDATE_INT);
    if (!$memberId || !isset($activeRsvpMemberIds[$memberId]) || !is_string($status)) {
        mysqli_rollback($conn);
        eventAttendanceRedirect($eventId, 'error', 'Attendance includes a member without an active RSVP.');
    }
    if ($status === '') {
        continue;
    }
    if (!in_array($status, $allowedStatuses, true)) {
        mysqli_rollback($conn);
        eventAttendanceRedirect($eventId, 'error', 'Choose a valid attendance status for each selected member.');
    }
    $attendanceToSave[$memberId] = $status;
}
if (!$attendanceToSave) {
    mysqli_rollback($conn);
    eventAttendanceRedirect($eventId, 'error', 'Choose at least one attendance status to save.');
}

$saveStmt = mysqli_prepare(
    $conn,
    'INSERT INTO event_attendance (member_id, event_id, status, recorded_at)
     VALUES (?, ?, ?, CURRENT_TIMESTAMP)
     ON DUPLICATE KEY UPDATE status = VALUES(status), recorded_at = CURRENT_TIMESTAMP'
);
if (!$saveStmt) {
    mysqli_rollback($conn);
    error_log('Event attendance save could not be prepared: ' . mysqli_error($conn));
    eventAttendanceRedirect($eventId, 'error', 'Attendance could not be saved. Please try again later.');
}
foreach ($attendanceToSave as $memberId => $status) {
    mysqli_stmt_bind_param($saveStmt, 'iis', $memberId, $eventId, $status);
    if (!mysqli_stmt_execute($saveStmt)) {
        mysqli_rollback($conn);
        error_log('Event attendance save failed: ' . mysqli_stmt_error($saveStmt));
        mysqli_stmt_close($saveStmt);
        eventAttendanceRedirect($eventId, 'error', 'Attendance could not be saved. Please try again later.');
    }
}
mysqli_stmt_close($saveStmt);

if (!mysqli_commit($conn)) {
    error_log('Event attendance transaction could not commit: ' . mysqli_error($conn));
    mysqli_rollback($conn);
    eventAttendanceRedirect($eventId, 'error', 'Attendance could not be saved. Please try again later.');
}

eventAttendanceRedirect($eventId, 'success', 'Event attendance was saved.');
