<?php
require_once 'inc/auth.php';
require_once '../inc/db.php';

function eventFormRedirect(string $type, string $text): void
{
    $_SESSION['event_notice'] = ['type' => $type, 'text' => $text];
    header('Location: ' . ($type === 'success' ? 'events.php' : 'add-event.php'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: events.php');
    exit;
}
$token = $_POST['csrf_token'] ?? '';
$sessionToken = $_SESSION['event_csrf'] ?? '';
if (!is_string($token) || !is_string($sessionToken) || $sessionToken === '' || !hash_equals($sessionToken, $token)) {
    eventFormRedirect('error', 'Your form expired. Reload the page and try again.');
}

$orgId = (int) ($_SESSION['org_id'] ?? 0);
$title = trim((string) ($_POST['title'] ?? ''));
$eventType = trim((string) ($_POST['event_type'] ?? ''));
$eventDateInput = (string) ($_POST['event_date'] ?? '');
$eventTimeInput = trim((string) ($_POST['event_time'] ?? ''));
$venue = trim((string) ($_POST['venue'] ?? ''));
$description = trim((string) ($_POST['description'] ?? ''));
$capacityInput = trim((string) ($_POST['capacity'] ?? ''));
$date = DateTimeImmutable::createFromFormat('!Y-m-d', $eventDateInput, new DateTimeZone('Africa/Lagos'));
$dateErrors = DateTimeImmutable::getLastErrors();
$time = $eventTimeInput === '' ? null : DateTimeImmutable::createFromFormat('!H:i', $eventTimeInput);
$timeErrors = DateTimeImmutable::getLastErrors();
$eventDateTime = DateTimeImmutable::createFromFormat(
    '!Y-m-d H:i',
    $eventDateInput . ' ' . ($eventTimeInput === '' ? '23:59' : $eventTimeInput),
    new DateTimeZone('Africa/Lagos')
);
$eventDateTimeErrors = DateTimeImmutable::getLastErrors();
$nowInLagos = new DateTimeImmutable('now', new DateTimeZone('Africa/Lagos'));
$capacity = null;
if ($capacityInput !== '') {
    $capacity = filter_var($capacityInput, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 4294967295]]);
}
if (
    $orgId < 1
    || $title === ''
    || strlen($title) > 200
    || strlen($eventType) > 50
    || strlen($venue) > 255
    || strlen($description) > 5000
    || !$date
    || ($dateErrors && ($dateErrors['warning_count'] || $dateErrors['error_count']))
    || $date->format('Y-m-d') !== $eventDateInput
    || !$eventDateTime
    || ($eventDateTimeErrors && ($eventDateTimeErrors['warning_count'] || $eventDateTimeErrors['error_count']))
    || $eventDateTime < $nowInLagos
    || ($eventTimeInput !== '' && (!$time || ($timeErrors && ($timeErrors['warning_count'] || $timeErrors['error_count'])) || $time->format('H:i') !== $eventTimeInput))
    || ($capacityInput !== '' && $capacity === false)
) {
    eventFormRedirect('error', 'Enter a valid event name, future date, time, and optional seat limit.');
}

$timeSql = $eventTimeInput === '' ? null : $time->format('H:i:s');
$capacitySql = $capacityInput === '' ? null : (int) $capacity;
$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO events (org_id, title, event_type, event_date, event_time, venue, capacity, description, status)
     VALUES (?, ?, NULLIF(?, ''), ?, ?, NULLIF(?, ''), ?, NULLIF(?, ''), 'scheduled')"
);
if (!$stmt) {
    error_log('Event insert could not be prepared: ' . mysqli_error($conn));
    eventFormRedirect('error', 'The event could not be saved. Please try again later.');
}
$timeValue = $timeSql ?? '';
mysqli_stmt_bind_param($stmt, 'isssssis', $orgId, $title, $eventType, $eventDateInput, $timeValue, $venue, $capacitySql, $description);
if (!mysqli_stmt_execute($stmt)) {
    error_log('Event insert failed: ' . mysqli_stmt_error($stmt));
    mysqli_stmt_close($stmt);
    eventFormRedirect('error', 'The event could not be saved. Please try again later.');
}
mysqli_stmt_close($stmt);
eventFormRedirect('success', 'Event created. Members in your organization can now see it.');
