<?php
require_once 'inc/auth.php';
require_once '../../inc/db.php';
require_once '../../inc/paystack.php';
require_once __DIR__ . '/inc/annual-dues.php';
require_once __DIR__ . '/../inc/finance-levies.php';

function memberPaymentRedirect(string $type, string $text): void
{
    $_SESSION['member_payment_notice'] = ['type' => $type, 'text' => $text];
    header('Location: payment.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: payment.php');
    exit;
}
$token = $_POST['csrf_token'] ?? '';
$sessionToken = $_SESSION['member_payment_csrf'] ?? '';
if (!is_string($token) || !is_string($sessionToken) || $sessionToken === '' || !hash_equals($sessionToken, $token)) {
    memberPaymentRedirect('error', 'Your form expired. Reload the page and try again.');
}
if (!associa8_paystack_enabled()) {
    memberPaymentRedirect('error', 'Online payments are not configured. Please contact your organization to pay offline.');
}

$memberId = (int) ($_SESSION['member_id'] ?? 0);
$orgId = (int) ($_SESSION['member_org_id'] ?? 0);
$type = (string) ($_POST['type'] ?? '');
$levyId = filter_var($_POST['levy_id'] ?? null, FILTER_VALIDATE_INT);
if ($memberId < 1 || $orgId < 1 || !in_array($type, ['membership_dues', 'levy'], true)) {
    memberPaymentRedirect('error', 'Choose a valid payment type.');
}

$memberStmt = mysqli_prepare($conn, 'SELECT email FROM members WHERE id = ? AND org_id = ? AND status <> ? LIMIT 1');
if (!$memberStmt) {
    error_log('Member payment profile query could not be prepared: ' . mysqli_error($conn));
    memberPaymentRedirect('error', 'Your payment could not be started. Please try again later.');
}
$blockedStatus = 'suspended';
mysqli_stmt_bind_param($memberStmt, 'iis', $memberId, $orgId, $blockedStatus);
if (!mysqli_stmt_execute($memberStmt)) {
    error_log('Member payment profile query failed: ' . mysqli_stmt_error($memberStmt));
    mysqli_stmt_close($memberStmt);
    memberPaymentRedirect('error', 'Your payment could not be started. Please try again later.');
}
$memberResult = mysqli_stmt_get_result($memberStmt);
$member = $memberResult ? mysqli_fetch_assoc($memberResult) : null;
mysqli_stmt_close($memberStmt);
if (!$member || !filter_var((string) $member['email'], FILTER_VALIDATE_EMAIL)) {
    memberPaymentRedirect('error', 'A valid email address is required on your member profile before you can pay online.');
}

$levyIdValue = null;
if ($type === 'membership_dues') {
    if (isset($_POST['levy_id']) && $_POST['levy_id'] !== '') {
        memberPaymentRedirect('error', 'The payment details were invalid. Please try again.');
    }
    $dues = member_load_annual_dues($conn, $memberId, $orgId);
    if ($dues === null) {
        memberPaymentRedirect('error', 'Your dues balance could not be loaded. Please try again later.');
    }
    $amount = round((float) $dues['outstanding'], 2);
} else {
    if (!$levyId || $levyId < 1 || !admin_finance_levies_available($conn)) {
        memberPaymentRedirect('error', 'Choose a valid assigned levy.');
    }
    $levyStmt = mysqli_prepare(
        $conn,
        "SELECT ml.amount, COALESCE(SUM(ft.amount), 0) AS paid_amount
         FROM member_levies ml
         INNER JOIN levies l ON l.id = ml.levy_id AND l.org_id = ?
         LEFT JOIN finance_transactions ft ON ft.levy_id = ml.levy_id AND ft.member_id = ml.member_id
           AND ft.type = 'levy' AND ft.status = 'successful'
         WHERE ml.levy_id = ? AND ml.member_id = ?
         GROUP BY ml.amount"
    );
    if (!$levyStmt) {
        error_log('Member levy payment balance query could not be prepared: ' . mysqli_error($conn));
        memberPaymentRedirect('error', 'Your levy balance could not be loaded. Please try again later.');
    }
    mysqli_stmt_bind_param($levyStmt, 'iii', $orgId, $levyId, $memberId);
    if (!mysqli_stmt_execute($levyStmt)) {
        error_log('Member levy payment balance query failed: ' . mysqli_stmt_error($levyStmt));
        mysqli_stmt_close($levyStmt);
        memberPaymentRedirect('error', 'Your levy balance could not be loaded. Please try again later.');
    }
    $levyResult = mysqli_stmt_get_result($levyStmt);
    $levy = $levyResult ? mysqli_fetch_assoc($levyResult) : null;
    mysqli_stmt_close($levyStmt);
    if (!$levy) {
        memberPaymentRedirect('error', 'That levy is not assigned to your account.');
    }
    $amount = round(max(0.0, (float) $levy['amount'] - (float) $levy['paid_amount']), 2);
    $levyIdValue = $levyId;
}

$amountKobo = (int) round($amount * 100);
if ($amountKobo < 1 || $amount > 9999999999.99) {
    memberPaymentRedirect('error', 'There is no outstanding balance to pay.');
}

$reference = 'PAY-' . $memberId . '-' . gmdate('YmdHis') . '-' . strtoupper(bin2hex(random_bytes(4)));
$callbackUrl = associa8_paystack_base_url() . '/admin/member/handle-payment.php';
$checkoutUrl = associa8_paystack_initialize_checkout(
    $amountKobo,
    (string) $member['email'],
    $reference,
    $callbackUrl,
    ['member_id' => $memberId, 'org_id' => $orgId, 'type' => $type, 'levy_id' => $levyIdValue]
);
if ($checkoutUrl === null) {
    memberPaymentRedirect('error', 'The payment gateway could not be reached. No payment was started; please try again later.');
}

$status = 'pending';
if ($type === 'levy') {
    $insert = mysqli_prepare($conn, 'INSERT INTO finance_transactions (reference, member_id, levy_id, amount, type, status) VALUES (?, ?, ?, ?, ?, ?)');
    if ($insert) {
        mysqli_stmt_bind_param($insert, 'siidss', $reference, $memberId, $levyIdValue, $amount, $type, $status);
    }
} else {
    $insert = mysqli_prepare($conn, 'INSERT INTO finance_transactions (reference, member_id, amount, type, status) VALUES (?, ?, ?, ?, ?)');
    if ($insert) {
        mysqli_stmt_bind_param($insert, 'sidss', $reference, $memberId, $amount, $type, $status);
    }
}
if (!$insert || !mysqli_stmt_execute($insert)) {
    error_log('Online payment transaction could not be saved: ' . ($insert ? mysqli_stmt_error($insert) : mysqli_error($conn)));
    if ($insert) {
        mysqli_stmt_close($insert);
    }
    memberPaymentRedirect('error', 'The payment could not be started. Please try again later.');
}
mysqli_stmt_close($insert);
header('Location: ' . $checkoutUrl);
exit;
