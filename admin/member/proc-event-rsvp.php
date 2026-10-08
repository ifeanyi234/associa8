<?php
require_once 'inc/auth.php';
require_once '../../inc/db.php';

function memberEventRedirect(string $type, string $text): void
{
    $_SESSION['member_event_notice'] = ['type' => $type, 'text' => $text];
    header('Location: events.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: events.php');
    exit;
}
$token = $_POST['csrf_token'] ?? '';
$sessionToken = $_SESSION['member_event_csrf'] ?? '';
if (!is_string($token) || !is_string($sessionToken) || $sessionToken === '' || !hash_equals($sessionToken, $token)) {
    memberEventRedirect('error', 'Your form expired. Reload the page and try again.');
}

$eventId = filter_var($_POST['event_id'] ?? null, FILTER_VALIDATE_INT);
$action = (string) ($_POST['action'] ?? '');
$memberId = (int) $_SESSION['member_id'];
$orgId = (int) $_SESSION['member_org_id'];
if (!$eventId || $orgId < 1 || !in_array($action, ['book', 'cancel'], true)) {
    memberEventRedirect('error', 'Choose a valid event action.');
}
if (!mysqli_begin_transaction($conn)) {
    error_log('Member RSVP transaction could not start: ' . mysqli_error($conn));
    memberEventRedirect('error', 'Your event booking could not be updated. Please try again later.');
}

$eventStmt = mysqli_prepare($conn, 'SELECT event_date, event_time, capacity, status FROM events WHERE id = ? AND org_id = ? FOR UPDATE');
if (!$eventStmt) {
    mysqli_rollback($conn);
    error_log('Member RSVP event lookup could not be prepared: ' . mysqli_error($conn));
    memberEventRedirect('error', 'Your event booking could not be updated. Please try again later.');
}
mysqli_stmt_bind_param($eventStmt, 'ii', $eventId, $orgId);
if (!mysqli_stmt_execute($eventStmt)) {
    mysqli_rollback($conn);
    error_log('Member RSVP event lookup failed: ' . mysqli_stmt_error($eventStmt));
    mysqli_stmt_close($eventStmt);
    memberEventRedirect('error', 'Your event booking could not be updated. Please try again later.');
}
$eventResult = mysqli_stmt_get_result($eventStmt);
$event = $eventResult ? mysqli_fetch_assoc($eventResult) : null;
mysqli_stmt_close($eventStmt);
if (!$event) {
    mysqli_rollback($conn);
    memberEventRedirect('error', 'That event is not available in your organization.');
}

$rsvpStmt = mysqli_prepare($conn, 'SELECT id, status FROM event_rsvps WHERE event_id = ? AND member_id = ? FOR UPDATE');
if (!$rsvpStmt) {
    mysqli_rollback($conn);
    error_log('Member RSVP lookup could not be prepared: ' . mysqli_error($conn));
    memberEventRedirect('error', 'Your event booking could not be updated. Please try again later.');
}
mysqli_stmt_bind_param($rsvpStmt, 'ii', $eventId, $memberId);
if (!mysqli_stmt_execute($rsvpStmt)) {
    mysqli_rollback($conn);
    error_log('Member RSVP lookup failed: ' . mysqli_stmt_error($rsvpStmt));
    mysqli_stmt_close($rsvpStmt);
    memberEventRedirect('error', 'Your event booking could not be updated. Please try again later.');
}
$rsvpResult = mysqli_stmt_get_result($rsvpStmt);
$rsvp = $rsvpResult ? mysqli_fetch_assoc($rsvpResult) : null;
mysqli_stmt_close($rsvpStmt);

$lagosNow = new DateTimeImmutable('now', new DateTimeZone('Africa/Lagos'));
$eventStart = new DateTimeImmutable($event['event_date'] . ' ' . ($event['event_time'] ?: '23:59:59'), new DateTimeZone('Africa/Lagos'));
$eventStarted = $eventStart <= $lagosNow;
if ($action === 'book') {
    if ($event['status'] !== 'scheduled' || $eventStarted) {
        mysqli_rollback($conn);
        memberEventRedirect('error', 'Booking for this event is closed.');
    }
    if ($rsvp && in_array($rsvp['status'], ['booked', 'waitlisted'], true)) {
        mysqli_rollback($conn);
        memberEventRedirect('success', $rsvp['status'] === 'booked' ? 'You already have a seat booked.' : 'You are already on the waitlist.');
    }
    $countStmt = mysqli_prepare($conn, "SELECT COUNT(*) AS booked_count FROM event_rsvps WHERE event_id = ? AND status = 'booked'");
    if (!$countStmt) {
        mysqli_rollback($conn);
        error_log('Member RSVP capacity query could not be prepared: ' . mysqli_error($conn));
        memberEventRedirect('error', 'Your event booking could not be updated. Please try again later.');
    }
    mysqli_stmt_bind_param($countStmt, 'i', $eventId);
    if (!mysqli_stmt_execute($countStmt)) {
        mysqli_rollback($conn);
        error_log('Member RSVP capacity query failed: ' . mysqli_stmt_error($countStmt));
        mysqli_stmt_close($countStmt);
        memberEventRedirect('error', 'Your event booking could not be updated. Please try again later.');
    }
    $countResult = mysqli_stmt_get_result($countStmt);
    $bookedCount = (int) (mysqli_fetch_assoc($countResult)['booked_count'] ?? 0);
    mysqli_stmt_close($countStmt);
    $hasSeatLimit = (int) $event['capacity'] > 0;
    $newStatus = !$hasSeatLimit || $bookedCount < (int) $event['capacity'] ? 'booked' : 'waitlisted';
    if ($rsvp) {
        $saveStmt = mysqli_prepare($conn, 'UPDATE event_rsvps SET status = ?, booked_at = CURRENT_TIMESTAMP WHERE id = ?');
        if ($saveStmt) {
            mysqli_stmt_bind_param($saveStmt, 'si', $newStatus, $rsvp['id']);
        }
    } else {
        $saveStmt = mysqli_prepare($conn, 'INSERT INTO event_rsvps (member_id, event_id, status) VALUES (?, ?, ?)');
        if ($saveStmt) {
            mysqli_stmt_bind_param($saveStmt, 'iis', $memberId, $eventId, $newStatus);
        }
    }
    if (!$saveStmt || !mysqli_stmt_execute($saveStmt)) {
        mysqli_rollback($conn);
        error_log('Member RSVP save failed: ' . ($saveStmt ? mysqli_stmt_error($saveStmt) : mysqli_error($conn)));
        if ($saveStmt) {
            mysqli_stmt_close($saveStmt);
        }
        memberEventRedirect('error', 'Your event booking could not be saved. Please try again later.');
    }
    mysqli_stmt_close($saveStmt);
    if (!mysqli_commit($conn)) {
        error_log('Member RSVP transaction could not commit: ' . mysqli_error($conn));
        mysqli_rollback($conn);
        memberEventRedirect('error', 'Your event booking could not be saved. Please try again later.');
    }
    memberEventRedirect('success', $newStatus === 'booked' ? 'Your seat is booked.' : 'The event is full. You have been added to the waitlist.');
}

if (!$rsvp || !in_array($rsvp['status'], ['booked', 'waitlisted'], true)) {
    mysqli_rollback($conn);
    memberEventRedirect('error', 'You do not have an active booking for this event.');
}
$wasBooked = $rsvp['status'] === 'booked';
$cancelStmt = mysqli_prepare($conn, "UPDATE event_rsvps SET status = 'cancelled' WHERE id = ? AND status IN ('booked', 'waitlisted')");
if (!$cancelStmt) {
    mysqli_rollback($conn);
    error_log('Member RSVP cancellation could not be prepared: ' . mysqli_error($conn));
    memberEventRedirect('error', 'Your booking could not be cancelled. Please try again later.');
}
mysqli_stmt_bind_param($cancelStmt, 'i', $rsvp['id']);
if (!mysqli_stmt_execute($cancelStmt)) {
    mysqli_rollback($conn);
    error_log('Member RSVP cancellation failed: ' . mysqli_stmt_error($cancelStmt));
    mysqli_stmt_close($cancelStmt);
    memberEventRedirect('error', 'Your booking could not be cancelled. Please try again later.');
}
mysqli_stmt_close($cancelStmt);
if ($wasBooked && $event['status'] === 'scheduled' && !$eventStarted) {
    $nextStmt = mysqli_prepare($conn, "SELECT id FROM event_rsvps WHERE event_id = ? AND status = 'waitlisted' ORDER BY booked_at, id LIMIT 1 FOR UPDATE");
    if (!$nextStmt) {
        mysqli_rollback($conn);
        error_log('Waitlist promotion lookup could not be prepared: ' . mysqli_error($conn));
        memberEventRedirect('error', 'Your booking could not be cancelled because the waitlist update failed.');
    }
    mysqli_stmt_bind_param($nextStmt, 'i', $eventId);
    if (!mysqli_stmt_execute($nextStmt)) {
        mysqli_rollback($conn);
        error_log('Waitlist promotion lookup failed: ' . mysqli_stmt_error($nextStmt));
        mysqli_stmt_close($nextStmt);
        memberEventRedirect('error', 'Your booking could not be cancelled because the waitlist update failed.');
    }
    $nextResult = mysqli_stmt_get_result($nextStmt);
    $next = $nextResult ? mysqli_fetch_assoc($nextResult) : null;
    mysqli_stmt_close($nextStmt);
    if ($next) {
        $promoteStmt = mysqli_prepare($conn, "UPDATE event_rsvps SET status = 'booked' WHERE id = ? AND status = 'waitlisted'");
        if (!$promoteStmt) {
            mysqli_rollback($conn);
            error_log('Waitlist promotion could not be prepared: ' . mysqli_error($conn));
            memberEventRedirect('error', 'Your booking could not be cancelled because the waitlist update failed.');
        }
        mysqli_stmt_bind_param($promoteStmt, 'i', $next['id']);
        if (!mysqli_stmt_execute($promoteStmt)) {
            mysqli_rollback($conn);
            error_log('Waitlist promotion failed: ' . mysqli_stmt_error($promoteStmt));
            mysqli_stmt_close($promoteStmt);
            memberEventRedirect('error', 'Your booking could not be cancelled because the waitlist update failed.');
        }
        mysqli_stmt_close($promoteStmt);
    }
}
if (!mysqli_commit($conn)) {
    error_log('Member RSVP cancellation transaction could not commit: ' . mysqli_error($conn));
    mysqli_rollback($conn);
    memberEventRedirect('error', 'Your booking could not be cancelled. Please try again later.');
}
memberEventRedirect('success', $wasBooked ? 'Your seat has been cancelled. The next waitlisted member was promoted if one was waiting.' : 'You have left the waitlist.');
