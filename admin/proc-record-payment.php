<?php
require_once 'inc/auth.php';
require_once '../inc/db.php';

function paymentRedirect(string $type, string $text): void
{
    $_SESSION['finance_notice'] = ['type' => $type, 'text' => $text];
    header('Location: ' . ($type === 'success' ? 'financial.php' : 'add-payment.php'));
    exit;
}

$adminRole = (string) ($_SESSION['admin_role'] ?? '');
if (!in_array($adminRole, ['admin', 'super_admin'], true)) {
    admin_render_access_denied('Only organization administrators can record offline payments.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: financial.php');
    exit;
}

$csrfToken = $_POST['csrf_token'] ?? '';
$sessionToken = $_SESSION['finance_csrf'] ?? '';
if (!is_string($csrfToken) || !is_string($sessionToken) || $sessionToken === '' || !hash_equals($sessionToken, $csrfToken)) {
    paymentRedirect('error', 'Your form expired. Reload the page and try again.');
}

$memberId = filter_var($_POST['member_id'] ?? null, FILTER_VALIDATE_INT);
$amount = trim((string) ($_POST['amount'] ?? ''));
$type = (string) ($_POST['type'] ?? '');
$paymentMethod = (string) ($_POST['payment_method'] ?? '');
$paidAtInput = (string) ($_POST['paid_at'] ?? '');
$notes = trim((string) ($_POST['notes'] ?? ''));
$allowedTypes = ['membership_dues', 'admission_fee', 'event', 'other'];
$allowedPaymentMethods = ['cash', 'bank_transfer', 'other'];
$noteLength = preg_match_all('/./us', $notes, $noteCharacters);

if (
    !$memberId
    || !preg_match('/^\d{1,10}(?:\.\d{1,2})?$/', $amount)
    || (float) $amount <= 0
    || (float) $amount > 9999999999.99
    || !in_array($type, $allowedTypes, true)
    || !in_array($paymentMethod, $allowedPaymentMethods, true)
    || $noteLength === false
    || $noteLength > 100
) {
    paymentRedirect('error', 'Enter a valid member, positive amount, payment type, method, and note.');
}

$lagosTimezone = new DateTimeZone('Africa/Lagos');
$utcTimezone = new DateTimeZone('UTC');
$paidAt = DateTime::createFromFormat('!Y-m-d\TH:i', $paidAtInput, $lagosTimezone);
$dateErrors = DateTime::getLastErrors();
if (!$paidAt || ($dateErrors && ($dateErrors['warning_count'] || $dateErrors['error_count']))) {
    paymentRedirect('error', 'Enter a valid date and time for when the payment was received.');
}

if ($paidAt > new DateTimeImmutable('now', $lagosTimezone)) {
    paymentRedirect('error', 'A payment date cannot be in the future.');
}

$paidAtUtc = $paidAt->setTimezone($utcTimezone)->format('Y-m-d H:i:s');
$orgId = (int) ($_SESSION['org_id'] ?? 0);
$canViewAllOrganizations = $adminRole === 'super_admin' && $orgId < 1;
$memberSql = $canViewAllOrganizations
    ? 'SELECT id FROM members WHERE id = ? LIMIT 1'
    : 'SELECT id FROM members WHERE id = ? AND org_id = ? LIMIT 1';
$memberStmt = mysqli_prepare($conn, $memberSql);
if (!$memberStmt) {
    error_log('Finance member validation could not be prepared: ' . mysqli_error($conn));
    paymentRedirect('error', 'Payment could not be recorded. Please try again later.');
}
if ($canViewAllOrganizations) {
    mysqli_stmt_bind_param($memberStmt, 'i', $memberId);
} else {
    mysqli_stmt_bind_param($memberStmt, 'ii', $memberId, $orgId);
}

if (!mysqli_stmt_execute($memberStmt)) {
    error_log('Finance member validation failed: ' . mysqli_stmt_error($memberStmt));
    mysqli_stmt_close($memberStmt);
    paymentRedirect('error', 'Payment could not be recorded. Please try again later.');
}
$memberResult = mysqli_stmt_get_result($memberStmt);
$memberExists = $memberResult && mysqli_num_rows($memberResult) === 1;
mysqli_stmt_close($memberStmt);
if (!$memberExists) {
    paymentRedirect('error', 'That member is not available in your organization.');
}

$reference = 'OFF-' . gmdate('Ymd') . '-' . strtoupper(bin2hex(random_bytes(6)));
$actor = trim((string) ($_SESSION['username'] ?? 'administrator'));
$methodLabels = [
    'cash' => 'cash',
    'bank_transfer' => 'bank transfer',
    'other' => 'other offline method',
];
$auditDescription = 'Offline payment ' . $reference . '; NGN ' . number_format((float) $amount, 2, '.', '')
    . '; ' . str_replace('_', ' ', $type)
    . '; method: ' . $methodLabels[$paymentMethod]
    . '; recorded by: ' . $actor;
if ($notes !== '') {
    $auditDescription .= '; note: ' . $notes;
}
$auditLength = preg_match_all('/./us', $auditDescription, $auditCharacters);
if ($auditLength === false) {
    paymentRedirect('error', 'The payment note contains invalid text. Please remove unusual characters and try again.');
}
if ($auditLength > 255) {
    $auditDescription = implode('', array_slice($auditCharacters[0], 0, 255));
}

if (!mysqli_begin_transaction($conn)) {
    error_log('Finance transaction could not be started: ' . mysqli_error($conn));
    paymentRedirect('error', 'Payment could not be recorded. Please try again later.');
}

$insertPayment = mysqli_prepare(
    $conn,
    "INSERT INTO finance_transactions (reference, member_id, amount, type, status, paid_at)
     VALUES (?, ?, ?, ?, 'successful', ?)"
);
if (!$insertPayment) {
    mysqli_rollback($conn);
    error_log('Finance payment insert could not be prepared: ' . mysqli_error($conn));
    paymentRedirect('error', 'Payment could not be recorded. Please try again later.');
}
mysqli_stmt_bind_param($insertPayment, 'sidss', $reference, $memberId, $amount, $type, $paidAtUtc);
if (!mysqli_stmt_execute($insertPayment)) {
    mysqli_rollback($conn);
    error_log('Finance payment insert failed: ' . mysqli_stmt_error($insertPayment));
    mysqli_stmt_close($insertPayment);
    paymentRedirect('error', 'Payment could not be recorded. Please try again later.');
}
mysqli_stmt_close($insertPayment);

$insertAudit = mysqli_prepare(
    $conn,
    "INSERT INTO activity_log (member_id, action_type, description)
     VALUES (?, 'offline_payment_recorded', ?)"
);
if (!$insertAudit) {
    mysqli_rollback($conn);
    error_log('Finance audit insert could not be prepared: ' . mysqli_error($conn));
    paymentRedirect('error', 'Payment could not be recorded because its audit entry could not be prepared.');
}
mysqli_stmt_bind_param($insertAudit, 'is', $memberId, $auditDescription);
if (!mysqli_stmt_execute($insertAudit)) {
    mysqli_rollback($conn);
    error_log('Finance audit insert failed: ' . mysqli_stmt_error($insertAudit));
    mysqli_stmt_close($insertAudit);
    paymentRedirect('error', 'Payment could not be recorded because its audit entry failed.');
}
mysqli_stmt_close($insertAudit);

if (!mysqli_commit($conn)) {
    error_log('Finance payment transaction could not be committed: ' . mysqli_error($conn));
    mysqli_rollback($conn);
    paymentRedirect('error', 'Payment could not be recorded. Please try again later.');
}

paymentRedirect('success', 'Offline payment recorded successfully. Reference: ' . $reference);
