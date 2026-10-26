<?php
require_once 'inc/auth.php';
require_once '../inc/db.php';
require_once '../inc/admin-notifications.php';
require_once __DIR__ . '/inc/finance-levies.php';

function paymentRedirect(string $type, string $text): void
{
    $_SESSION['finance_notice'] = ['type' => $type, 'text' => $text];
    $tab = ($_SESSION['finance_payment_type'] ?? '') === 'levy' ? '?tab=dues-levies' : '';
    unset($_SESSION['finance_payment_type']);
    header('Location: ' . ($type === 'success' ? 'financial.php' . $tab : 'add-payment.php'));
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
$levySchemaAvailable = admin_finance_levies_available($conn);

$memberId = filter_var($_POST['member_id'] ?? null, FILTER_VALIDATE_INT);
$amount = trim((string) ($_POST['amount'] ?? ''));
$type = (string) ($_POST['type'] ?? '');
$levyId = filter_var($_POST['levy_id'] ?? null, FILTER_VALIDATE_INT);
$paymentMethod = (string) ($_POST['payment_method'] ?? '');
$paidAtInput = (string) ($_POST['paid_at'] ?? '');
$notes = trim((string) ($_POST['notes'] ?? ''));
$allowedTypes = ['membership_dues', 'admission_fee', 'event', 'levy', 'other'];
$allowedPaymentMethods = ['cash', 'bank_transfer', 'other'];
$noteLength = preg_match_all('/./us', $notes, $noteCharacters);

if (
    !$memberId
    || !preg_match('/^\d{1,10}(?:\.\d{1,2})?$/', $amount)
    || (float) $amount <= 0
    || (float) $amount > 9999999999.99
    || !in_array($type, $allowedTypes, true)
    || ($type === 'levy' && !$levySchemaAvailable)
    || ($type === 'levy' && (!$levyId || $levyId < 1))
    || ($type !== 'levy' && isset($_POST['levy_id']) && $_POST['levy_id'] !== '')
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
$memberSql = 'SELECT id FROM members WHERE id = ? AND org_id = ? LIMIT 1';
$memberStmt = mysqli_prepare($conn, $memberSql);
if (!$memberStmt) {
    error_log('Finance member validation could not be prepared: ' . mysqli_error($conn));
    paymentRedirect('error', 'Payment could not be recorded. Please try again later.');
}
mysqli_stmt_bind_param($memberStmt, 'ii', $memberId, $orgId);

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
if (!mysqli_begin_transaction($conn)) {
    error_log('Finance transaction could not be started: ' . mysqli_error($conn));
    paymentRedirect('error', 'Payment could not be recorded. Please try again later.');
}

$levyTitle = '';
if ($type === 'levy') {
    $levyValidationSql = 'SELECT ml.amount, l.title FROM member_levies ml INNER JOIN levies l ON l.id = ml.levy_id WHERE ml.levy_id = ? AND ml.member_id = ? AND l.org_id = ? FOR UPDATE';
    $levyValidation = mysqli_prepare($conn, $levyValidationSql);
    if (!$levyValidation) {
        mysqli_rollback($conn);
        error_log('Finance levy validation could not be prepared: ' . mysqli_error($conn));
        paymentRedirect('error', 'Levy payment could not be recorded. Please try again later.');
    }
    mysqli_stmt_bind_param($levyValidation, 'iii', $levyId, $memberId, $orgId);
    if (!mysqli_stmt_execute($levyValidation)) {
        mysqli_rollback($conn);
        error_log('Finance levy validation failed: ' . mysqli_stmt_error($levyValidation));
        mysqli_stmt_close($levyValidation);
        paymentRedirect('error', 'Levy payment could not be recorded. Please try again later.');
    }
    $levyResult = mysqli_stmt_get_result($levyValidation);
    $levyAssignment = $levyResult ? mysqli_fetch_assoc($levyResult) : null;
    mysqli_stmt_close($levyValidation);
    if (!$levyAssignment) {
        mysqli_rollback($conn);
        paymentRedirect('error', 'That member does not have this levy assigned.');
    }

    $paidStmt = mysqli_prepare(
        $conn,
        "SELECT COALESCE(SUM(amount), 0) AS paid_amount
         FROM finance_transactions
         WHERE levy_id = ? AND member_id = ? AND type = 'levy' AND status = 'successful'"
    );
    if (!$paidStmt) {
        mysqli_rollback($conn);
        error_log('Finance levy balance query could not be prepared: ' . mysqli_error($conn));
        paymentRedirect('error', 'Levy payment could not be recorded. Please try again later.');
    }
    mysqli_stmt_bind_param($paidStmt, 'ii', $levyId, $memberId);
    if (!mysqli_stmt_execute($paidStmt)) {
        mysqli_rollback($conn);
        error_log('Finance levy balance query failed: ' . mysqli_stmt_error($paidStmt));
        mysqli_stmt_close($paidStmt);
        paymentRedirect('error', 'Levy payment could not be recorded. Please try again later.');
    }
    $paidResult = mysqli_stmt_get_result($paidStmt);
    $paidRow = $paidResult ? mysqli_fetch_assoc($paidResult) : null;
    mysqli_stmt_close($paidStmt);
    $assessedCents = (int) round((float) $levyAssignment['amount'] * 100);
    $paidCents = (int) round((float) ($paidRow['paid_amount'] ?? 0) * 100);
    $paymentCents = (int) round((float) $amount * 100);
    if ($paymentCents > $assessedCents - $paidCents) {
        mysqli_rollback($conn);
        paymentRedirect('error', 'This payment is greater than the remaining balance for the selected levy.');
    }
    $levyTitle = (string) $levyAssignment['title'];
}

$auditDescription = 'Offline payment ' . $reference . '; NGN ' . number_format((float) $amount, 2, '.', '')
    . '; ' . ($type === 'levy' ? 'levy: ' . $levyTitle : str_replace('_', ' ', $type))
    . '; method: ' . $methodLabels[$paymentMethod]
    . '; recorded by: ' . $actor;
if ($notes !== '') {
    $auditDescription .= '; note: ' . $notes;
}
$auditLength = preg_match_all('/./us', $auditDescription, $auditCharacters);
if ($auditLength === false) {
    mysqli_rollback($conn);
    paymentRedirect('error', 'The payment note contains invalid text. Please remove unusual characters and try again.');
}
if ($auditLength > 255) {
    $auditDescription = implode('', array_slice($auditCharacters[0], 0, 255));
}

$insertPayment = $type === 'levy'
    ? mysqli_prepare(
        $conn,
        "INSERT INTO finance_transactions (reference, member_id, amount, type, status, paid_at, levy_id)
         VALUES (?, ?, ?, 'levy', 'successful', ?, ?)"
    )
    : mysqli_prepare(
        $conn,
        "INSERT INTO finance_transactions (reference, member_id, amount, type, status, paid_at)
         VALUES (?, ?, ?, ?, 'successful', ?)"
    );
if (!$insertPayment) {
    mysqli_rollback($conn);
    error_log('Finance payment insert could not be prepared: ' . mysqli_error($conn));
    paymentRedirect('error', 'Payment could not be recorded. Please try again later.');
}
if ($type === 'levy') {
    mysqli_stmt_bind_param($insertPayment, 'sidsi', $reference, $memberId, $amount, $paidAtUtc, $levyId);
} else {
    mysqli_stmt_bind_param($insertPayment, 'sidss', $reference, $memberId, $amount, $type, $paidAtUtc);
}
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

admin_notify_organization(
    $conn,
    $orgId,
    'payment',
    'Offline payment recorded',
    'An offline payment of NGN ' . number_format((float) $amount, 2) . ' was recorded for a member.',
    'financial.php'
);

if ($type === 'levy') {
    $_SESSION['finance_payment_type'] = 'levy';
}
paymentRedirect('success', 'Reference: ' . $reference);
