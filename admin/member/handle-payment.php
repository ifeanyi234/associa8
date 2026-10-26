<?php
require_once 'inc/auth.php';
require_once '../../inc/db.php';
require_once '../../inc/paystack.php';
require_once '../../inc/admin-notifications.php';

function memberPaymentCallbackRedirect(string $type, string $text): void
{
    $_SESSION['member_payment_notice'] = ['type' => $type, 'text' => $text];
    header('Location: payment.php');
    exit;
}

$memberId = (int) ($_SESSION['member_id'] ?? 0);
$orgId = (int) ($_SESSION['member_org_id'] ?? 0);
$reference = trim((string) ($_GET['reference'] ?? ''));
if ($memberId < 1 || $orgId < 1 || $reference === '' || strlen($reference) > 50) {
    memberPaymentCallbackRedirect('error', 'The payment could not be confirmed. Please try again.');
}

$verification = associa8_paystack_verify_reference($reference);
if (
    !$verification
    || ($verification['status'] ?? '') !== 'success'
    || !hash_equals($reference, (string) ($verification['reference'] ?? ''))
    || ($verification['currency'] ?? '') !== 'NGN'
) {
    memberPaymentCallbackRedirect('error', 'The payment could not be verified. If your account was debited, contact the organization with your payment reference.');
}

if (!mysqli_begin_transaction($conn)) {
    error_log('Payment callback transaction could not start: ' . mysqli_error($conn));
    memberPaymentCallbackRedirect('error', 'Your payment could not be confirmed. Please try again later.');
}
$transactionStmt = mysqli_prepare(
    $conn,
    "SELECT id, amount, type, status, levy_id
     FROM finance_transactions
     WHERE reference = ? AND member_id = ? AND type IN ('membership_dues', 'levy')
     FOR UPDATE"
);
if (!$transactionStmt) {
    mysqli_rollback($conn);
    error_log('Payment callback transaction lookup could not be prepared: ' . mysqli_error($conn));
    memberPaymentCallbackRedirect('error', 'Your payment could not be confirmed. Please try again later.');
}
mysqli_stmt_bind_param($transactionStmt, 'si', $reference, $memberId);
if (!mysqli_stmt_execute($transactionStmt)) {
    mysqli_rollback($conn);
    error_log('Payment callback transaction lookup failed: ' . mysqli_stmt_error($transactionStmt));
    mysqli_stmt_close($transactionStmt);
    memberPaymentCallbackRedirect('error', 'Your payment could not be confirmed. Please try again later.');
}
$transactionResult = mysqli_stmt_get_result($transactionStmt);
$transaction = $transactionResult ? mysqli_fetch_assoc($transactionResult) : null;
mysqli_stmt_close($transactionStmt);
if (!$transaction) {
    mysqli_rollback($conn);
    memberPaymentCallbackRedirect('error', 'The payment reference does not match your account.');
}
$metadata = is_array($verification['metadata'] ?? null) ? $verification['metadata'] : [];
if (
    (int) ($metadata['member_id'] ?? 0) !== $memberId
    || (int) ($metadata['org_id'] ?? 0) !== $orgId
    || ($metadata['type'] ?? '') !== $transaction['type']
) {
    mysqli_rollback($conn);
    memberPaymentCallbackRedirect('error', 'The payment details could not be matched to your account.');
}
if ($transaction['status'] === 'successful') {
    if (!mysqli_commit($conn)) {
        mysqli_rollback($conn);
        error_log('Already-confirmed payment callback transaction commit failed: ' . mysqli_error($conn));
        memberPaymentCallbackRedirect('error', 'Your payment could not be confirmed. Please try again later.');
    }
    memberPaymentCallbackRedirect('success', 'Your payment was already confirmed.');
}
$expectedKobo = (int) round((float) $transaction['amount'] * 100);
if ((int) ($verification['amount'] ?? 0) !== $expectedKobo) {
    mysqli_rollback($conn);
    error_log('Paystack amount mismatch for reference ' . $reference . '.');
    memberPaymentCallbackRedirect('error', 'The verified amount did not match your payment. Contact the organization with your reference.');
}
if ($transaction['type'] === 'levy') {
    if ((int) ($metadata['levy_id'] ?? 0) !== (int) $transaction['levy_id']) {
        mysqli_rollback($conn);
        memberPaymentCallbackRedirect('error', 'The payment details could not be matched to your account.');
    }
}

$paidStatus = 'successful';
$paidAt = gmdate('Y-m-d H:i:s');
$update = mysqli_prepare($conn, 'UPDATE finance_transactions SET status = ?, paid_at = ? WHERE id = ? AND status = \'pending\'');
if (!$update) {
    mysqli_rollback($conn);
    error_log('Payment callback update could not be prepared: ' . mysqli_error($conn));
    memberPaymentCallbackRedirect('error', 'Your payment could not be confirmed. Please try again later.');
}
mysqli_stmt_bind_param($update, 'ssi', $paidStatus, $paidAt, $transaction['id']);
if (!mysqli_stmt_execute($update) || mysqli_stmt_affected_rows($update) !== 1) {
    mysqli_rollback($conn);
    error_log('Payment callback update failed: ' . mysqli_stmt_error($update));
    mysqli_stmt_close($update);
    memberPaymentCallbackRedirect('error', 'Your payment could not be confirmed. Please try again later.');
}
mysqli_stmt_close($update);
if (!mysqli_commit($conn)) {
    mysqli_rollback($conn);
    error_log('Payment callback commit failed: ' . mysqli_error($conn));
    memberPaymentCallbackRedirect('error', 'Your payment could not be confirmed. Please try again later.');
}

$title = $transaction['type'] === 'levy' ? 'Levy payment received' : 'Membership dues payment received';
$message = 'A member payment of NGN ' . number_format((float) $transaction['amount'], 2) . ' was confirmed.';
admin_notify_organization($conn, $orgId, 'payment', $title, $message, 'financial.php');
memberPaymentCallbackRedirect('success', 'Payment received successfully. Reference: ' . $reference);
