<?php
require_once 'inc/auth.php';
require_once '../inc/db.php';

function eventActionRedirect(string $type, string $text): void
{
    $_SESSION['event_notice'] = ['type' => $type, 'text' => $text];
    header('Location: events.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: events.php');
    exit;
}
$token = $_POST['csrf_token'] ?? '';
$sessionToken = $_SESSION['event_csrf'] ?? '';
if (!is_string($token) || !is_string($sessionToken) || $sessionToken === '' || !hash_equals($sessionToken, $token)) {
    eventActionRedirect('error', 'Your form expired. Reload the page and try again.');
}
$eventId = filter_var($_POST['event_id'] ?? null, FILTER_VALIDATE_INT);
$orgId = (int) ($_SESSION['org_id'] ?? 0);
if (!$eventId || $orgId < 1 || ($_POST['action'] ?? '') !== 'cancel') {
    eventActionRedirect('error', 'Choose a valid event action.');
}

$stmt = mysqli_prepare($conn, "UPDATE events SET status = 'cancelled' WHERE id = ? AND org_id = ? AND status = 'scheduled'");
if (!$stmt) {
    error_log('Event cancellation could not be prepared: ' . mysqli_error($conn));
    eventActionRedirect('error', 'The event could not be cancelled. Please try again later.');
}
mysqli_stmt_bind_param($stmt, 'ii', $eventId, $orgId);
if (!mysqli_stmt_execute($stmt)) {
    error_log('Event cancellation failed: ' . mysqli_stmt_error($stmt));
    mysqli_stmt_close($stmt);
    eventActionRedirect('error', 'The event could not be cancelled. Please try again later.');
}
$changed = mysqli_stmt_affected_rows($stmt) === 1;
mysqli_stmt_close($stmt);
eventActionRedirect($changed ? 'success' : 'error', $changed ? 'Event cancelled. Members can no longer book it.' : 'That event was not found or has already been cancelled.');
