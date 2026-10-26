<?php
require_once 'inc/auth.php';
require_once '../../inc/db.php';
require_once '../../inc/paystack.php';
require_once '../../inc/admin-notifications.php';

function memberEventPaymentRedirect(string $type, string $text): void
{
    $_SESSION['member_event_notice'] = ['type' => $type, 'text' => $text];
    header('Location: events.php');
    exit;
}

$memberId = (int) ($_SESSION['member_id'] ?? 0);
$orgId = (int) ($_SESSION['member_org_id'] ?? 0);
$reference = trim((string) ($_GET['reference'] ?? $_POST['reference'] ?? ''));
if ($memberId < 1 || $orgId < 1 || $reference === '') {
    memberEventPaymentRedirect('error', 'Your payment was not completed. Please try again.');
}

$verification = associa8_paystack_verify_reference($reference);
if (
    !$verification
    || ($verification['status'] ?? '') !== 'success'
    || !hash_equals($reference, (string) ($verification['reference'] ?? ''))
    || ($verification['currency'] ?? '') !== 'NGN'
) {
    memberEventPaymentRedirect('error', 'The event payment could not be verified. Please try again or contact the organization.');
}

$eventColumnCheck = mysqli_query($conn, "SHOW COLUMNS FROM events LIKE 'booking_fee'");
if ($eventColumnCheck && mysqli_num_rows($eventColumnCheck) === 0) {
    mysqli_query($conn, "ALTER TABLE events ADD COLUMN booking_fee DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER capacity");
}

if (!mysqli_begin_transaction($conn)) {
    error_log('Event payment verification transaction could not start: ' . mysqli_error($conn));
    memberEventPaymentRedirect('error', 'The event payment could not be confirmed. Please try again later.');
}

$transactionStmt = mysqli_prepare($conn, 'SELECT id, member_id, amount, status, reference, type FROM finance_transactions WHERE reference = ? AND member_id = ? FOR UPDATE');
if (!$transactionStmt) {
    mysqli_rollback($conn);
    error_log('Event payment transaction lookup could not be prepared: ' . mysqli_error($conn));
    memberEventPaymentRedirect('error', 'The event payment could not be confirmed. Please try again later.');
}
mysqli_stmt_bind_param($transactionStmt, 'si', $reference, $memberId);
if (!mysqli_stmt_execute($transactionStmt)) {
    mysqli_rollback($conn);
    error_log('Event payment transaction lookup failed: ' . mysqli_stmt_error($transactionStmt));
    mysqli_stmt_close($transactionStmt);
    memberEventPaymentRedirect('error', 'The event payment could not be confirmed. Please try again later.');
}
$transactionResult = mysqli_stmt_get_result($transactionStmt);
$transaction = $transactionResult ? mysqli_fetch_assoc($transactionResult) : null;
mysqli_stmt_close($transactionStmt);

if (!$transaction) {
    mysqli_rollback($conn);
    memberEventPaymentRedirect('error', 'The payment reference does not match your account.');
}
if ($transaction['type'] !== 'event') {
    mysqli_rollback($conn);
    memberEventPaymentRedirect('error', 'The payment reference is not for an event booking.');
}
if ($transaction['status'] === 'successful') {
    if (!mysqli_commit($conn)) {
        mysqli_rollback($conn);
        error_log('Already-confirmed event payment transaction commit failed: ' . mysqli_error($conn));
        memberEventPaymentRedirect('error', 'Your event payment could not be confirmed. Please try again later.');
    }
    memberEventPaymentRedirect('success', 'Your event payment was already confirmed.');
}
if ((int) ($verification['amount'] ?? 0) !== (int) round((float) $transaction['amount'] * 100)) {
    mysqli_rollback($conn);
    error_log('Paystack event amount mismatch for reference ' . $reference . '.');
    memberEventPaymentRedirect('error', 'The verified amount did not match your event payment. Contact the organization with your reference.');
}

$eventMeta = is_array($verification['metadata'] ?? null) ? $verification['metadata'] : [];
$referenceMatched = preg_match('/^EVT-(\d+)-(\d+)-/i', $reference, $matches);
$eventId = $referenceMatched ? (int) $matches[1] : 0;
$referenceMemberId = $referenceMatched ? (int) $matches[2] : 0;
if ($eventId < 1 || $referenceMemberId !== $memberId) {
    mysqli_rollback($conn);
    memberEventPaymentRedirect('error', 'The event could not be matched to this payment.');
}
if (
    (isset($eventMeta['event_id']) && (int) $eventMeta['event_id'] !== $eventId)
    || (int) ($eventMeta['member_id'] ?? 0) !== $memberId
    || (int) ($eventMeta['org_id'] ?? 0) !== $orgId
) {
    mysqli_rollback($conn);
    memberEventPaymentRedirect('error', 'The payment details could not be matched to your account.');
}

$eventStmt = mysqli_prepare($conn, 'SELECT title, event_date, event_time, capacity, status, booking_fee FROM events WHERE id = ? AND org_id = ? FOR UPDATE');
if (!$eventStmt) {
    mysqli_rollback($conn);
    error_log('Event payment event lookup could not be prepared: ' . mysqli_error($conn));
    memberEventPaymentRedirect('error', 'The event could not be confirmed. Please try again later.');
}
mysqli_stmt_bind_param($eventStmt, 'ii', $eventId, $orgId);
if (!mysqli_stmt_execute($eventStmt)) {
    mysqli_rollback($conn);
    error_log('Event payment event lookup failed: ' . mysqli_stmt_error($eventStmt));
    mysqli_stmt_close($eventStmt);
    memberEventPaymentRedirect('error', 'The event could not be confirmed. Please try again later.');
}
$eventResult = mysqli_stmt_get_result($eventStmt);
$event = $eventResult ? mysqli_fetch_assoc($eventResult) : null;
mysqli_stmt_close($eventStmt);

if (!$event || $event['status'] !== 'scheduled') {
    mysqli_rollback($conn);
    memberEventPaymentRedirect('error', 'That event is no longer open for booking.');
}
if ($transaction['status'] !== 'pending') {
    mysqli_rollback($conn);
    memberEventPaymentRedirect('error', 'This payment is not awaiting confirmation. Contact the organization if your account was debited.');
}

$rsvpStmt = mysqli_prepare($conn, 'SELECT id, status FROM event_rsvps WHERE event_id = ? AND member_id = ? FOR UPDATE');
if (!$rsvpStmt) {
    mysqli_rollback($conn);
    error_log('Event payment RSVP lookup could not be prepared: ' . mysqli_error($conn));
    memberEventPaymentRedirect('error', 'Your booking could not be saved. Please try again later.');
}
mysqli_stmt_bind_param($rsvpStmt, 'ii', $eventId, $memberId);
if (!mysqli_stmt_execute($rsvpStmt)) {
    mysqli_rollback($conn);
    error_log('Event payment RSVP lookup failed: ' . mysqli_stmt_error($rsvpStmt));
    mysqli_stmt_close($rsvpStmt);
    memberEventPaymentRedirect('error', 'Your booking could not be saved. Please try again later.');
}
$rsvpResult = mysqli_stmt_get_result($rsvpStmt);
$rsvp = $rsvpResult ? mysqli_fetch_assoc($rsvpResult) : null;
mysqli_stmt_close($rsvpStmt);

$bookedCountStmt = mysqli_prepare($conn, "SELECT COUNT(*) AS booked_count FROM event_rsvps WHERE event_id = ? AND status = 'booked'");
if (!$bookedCountStmt) {
    mysqli_rollback($conn);
    error_log('Event payment capacity query could not be prepared: ' . mysqli_error($conn));
    memberEventPaymentRedirect('error', 'Your booking could not be saved. Please try again later.');
}
mysqli_stmt_bind_param($bookedCountStmt, 'i', $eventId);
if (!mysqli_stmt_execute($bookedCountStmt)) {
    mysqli_rollback($conn);
    error_log('Event payment capacity query failed: ' . mysqli_stmt_error($bookedCountStmt));
    mysqli_stmt_close($bookedCountStmt);
    memberEventPaymentRedirect('error', 'Your booking could not be saved. Please try again later.');
}
$bookedCountResult = mysqli_stmt_get_result($bookedCountStmt);
$bookedCount = (int) (mysqli_fetch_assoc($bookedCountResult)['booked_count'] ?? 0);
mysqli_stmt_close($bookedCountStmt);

$hasSeatLimit = (int) ($event['capacity'] ?? 0) > 0;
$newStatus = !$hasSeatLimit || $bookedCount < (int) ($event['capacity'] ?? 0) ? 'booked' : 'waitlisted';
if ($rsvp) {
    $saveStmt = mysqli_prepare($conn, 'UPDATE event_rsvps SET status = ?, booked_at = CURRENT_TIMESTAMP WHERE id = ?');
    if (!$saveStmt) {
        mysqli_rollback($conn);
        error_log('Event payment RSVP update could not be prepared: ' . mysqli_error($conn));
        memberEventPaymentRedirect('error', 'Your booking could not be saved. Please try again later.');
    }
    mysqli_stmt_bind_param($saveStmt, 'si', $newStatus, $rsvp['id']);
    if (!mysqli_stmt_execute($saveStmt)) {
        mysqli_rollback($conn);
        error_log('Event payment RSVP update failed: ' . mysqli_stmt_error($saveStmt));
        mysqli_stmt_close($saveStmt);
        memberEventPaymentRedirect('error', 'Your booking could not be saved. Please try again later.');
    }
    mysqli_stmt_close($saveStmt);
} else {
    $saveStmt = mysqli_prepare($conn, 'INSERT INTO event_rsvps (member_id, event_id, status) VALUES (?, ?, ?)');
    if (!$saveStmt) {
        mysqli_rollback($conn);
        error_log('Event payment RSVP insert could not be prepared: ' . mysqli_error($conn));
        memberEventPaymentRedirect('error', 'Your booking could not be saved. Please try again later.');
    }
    mysqli_stmt_bind_param($saveStmt, 'iis', $memberId, $eventId, $newStatus);
    if (!mysqli_stmt_execute($saveStmt)) {
        mysqli_rollback($conn);
        error_log('Event payment RSVP insert failed: ' . mysqli_stmt_error($saveStmt));
        mysqli_stmt_close($saveStmt);
        memberEventPaymentRedirect('error', 'Your booking could not be saved. Please try again later.');
    }
    mysqli_stmt_close($saveStmt);
}

$approvedAt = date('Y-m-d H:i:s');
$paidStatus = 'successful';
$updateTxStmt = mysqli_prepare($conn, 'UPDATE finance_transactions SET status = ?, paid_at = ? WHERE id = ? AND status <> ?');
if (!$updateTxStmt) {
    mysqli_rollback($conn);
    error_log('Event payment transaction update could not be prepared: ' . mysqli_error($conn));
    memberEventPaymentRedirect('error', 'The payment could not be marked as received. Please contact the organization.');
}
mysqli_stmt_bind_param($updateTxStmt, 'sssi', $paidStatus, $approvedAt, $transaction['id'], $paidStatus);
if (!mysqli_stmt_execute($updateTxStmt)) {
    mysqli_rollback($conn);
    error_log('Event payment transaction update failed: ' . mysqli_stmt_error($updateTxStmt));
    mysqli_stmt_close($updateTxStmt);
    memberEventPaymentRedirect('error', 'The payment could not be marked as received. Please contact the organization.');
}
mysqli_stmt_close($updateTxStmt);

if (!mysqli_commit($conn)) {
    mysqli_rollback($conn);
    error_log('Event payment transaction could not commit: ' . mysqli_error($conn));
    memberEventPaymentRedirect('error', 'The event payment could not be confirmed. Please try again later.');
}

admin_notify_organization(
    $conn,
    $orgId,
    'event_payment',
    'Paid event booking received',
    'A member paid NGN ' . number_format((float) $transaction['amount'], 2) . ' for ' . (string) $event['title'] . '.',
    'events.php'
);
memberEventPaymentRedirect('success', $newStatus === 'booked'
    ? 'Payment received. Your seat is booked.'
    : 'Payment received. The event is full, so you were placed on the waitlist.');
