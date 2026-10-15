<?php
require_once 'inc/auth.php';
require_once '../inc/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: messages.php');
    exit;
}

$csrfToken = $_POST['csrf_token'] ?? '';
$sessionToken = $_SESSION['admin_message_csrf'] ?? '';
if (!is_string($csrfToken) || !is_string($sessionToken) || $sessionToken === ''
    || !hash_equals($sessionToken, $csrfToken)) {
    header('Location: messages.php?status=csrf');
    exit;
}

$threadId = filter_input(INPUT_POST, 'thread_id', FILTER_VALIDATE_INT);
$body = $_POST['body'] ?? '';
if ($threadId === false || $threadId === null || $threadId < 1 || !is_string($body)) {
    header('Location: messages.php?status=invalid');
    exit;
}
$body = trim($body);
if ($body === '' || strlen($body) > 5000) {
    header('Location: messages.php?thread=' . (int) $threadId . '&status=invalid');
    exit;
}

$accountId = (int) $_SESSION['user_id'];
$orgId = (int) $_SESSION['org_id'];
$adminName = trim((string) ($_SESSION['admin_name'] ?? 'Organization staff'));
if (!mysqli_begin_transaction($conn)) {
    error_log('Staff reply transaction could not be started: ' . mysqli_error($conn));
    header('Location: messages.php?thread=' . (int) $threadId . '&status=error');
    exit;
}
$rollbackAndRedirect = static function (string $status) use ($conn, $threadId): void {
    if (!mysqli_rollback($conn)) {
        error_log('Staff reply transaction rollback failed: ' . mysqli_error($conn));
    }
    header('Location: messages.php?thread=' . (int) $threadId . '&status=' . rawurlencode($status));
    exit;
};

$threadLookup = mysqli_prepare(
    $conn,
    'SELECT id FROM message_threads WHERE id = ? AND org_id = ? LIMIT 1 FOR UPDATE'
);
if (!$threadLookup) {
    error_log('Staff reply thread lookup could not be prepared: ' . mysqli_error($conn));
    $rollbackAndRedirect('error');
}
mysqli_stmt_bind_param($threadLookup, 'ii', $threadId, $orgId);
if (!mysqli_stmt_execute($threadLookup)) {
    error_log('Staff reply thread lookup failed: ' . mysqli_stmt_error($threadLookup));
    mysqli_stmt_close($threadLookup);
    $rollbackAndRedirect('error');
}
$threadResult = mysqli_stmt_get_result($threadLookup);
$thread = $threadResult ? mysqli_fetch_assoc($threadResult) : null;
mysqli_stmt_close($threadLookup);
if (!$thread) {
    $rollbackAndRedirect('invalid');
}

$rateLimit = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS message_count FROM message_replies
     WHERE admin_account_id = ? AND sender_type = 'staff'
       AND sent_at > DATE_SUB(UTC_TIMESTAMP(6), INTERVAL 1 MINUTE)"
);
if (!$rateLimit) {
    error_log('Staff reply rate check could not be prepared: ' . mysqli_error($conn));
    $rollbackAndRedirect('error');
}
mysqli_stmt_bind_param($rateLimit, 'i', $accountId);
if (!mysqli_stmt_execute($rateLimit)) {
    error_log('Staff reply rate check failed: ' . mysqli_stmt_error($rateLimit));
    mysqli_stmt_close($rateLimit);
    $rollbackAndRedirect('error');
}
$rateResult = mysqli_stmt_get_result($rateLimit);
$rateRow = $rateResult ? mysqli_fetch_assoc($rateResult) : null;
mysqli_stmt_close($rateLimit);
if ((int) ($rateRow['message_count'] ?? 0) >= 10) {
    $rollbackAndRedirect('limited');
}

$insert = mysqli_prepare(
    $conn,
    "INSERT INTO message_replies (thread_id, member_id, admin_account_id, sender_type, sender_name, body, sent_at)
     VALUES (?, NULL, ?, 'staff', ?, ?, UTC_TIMESTAMP(6))"
);
if (!$insert) {
    error_log('Staff reply could not be prepared: ' . mysqli_error($conn));
    $rollbackAndRedirect('error');
}
mysqli_stmt_bind_param($insert, 'iiss', $threadId, $accountId, $adminName, $body);
$inserted = mysqli_stmt_execute($insert);
if (!$inserted) {
    error_log('Staff reply could not be saved: ' . mysqli_stmt_error($insert));
}
mysqli_stmt_close($insert);
if (!$inserted) {
    $rollbackAndRedirect('error');
}

$markRead = mysqli_prepare(
    $conn,
    'UPDATE message_threads SET staff_last_read_at = UTC_TIMESTAMP(6) WHERE id = ? AND org_id = ?'
);
if (!$markRead) {
    error_log('Staff thread read update could not be prepared: ' . mysqli_error($conn));
    $rollbackAndRedirect('error');
}
mysqli_stmt_bind_param($markRead, 'ii', $threadId, $orgId);
$readUpdated = mysqli_stmt_execute($markRead);
if (!$readUpdated) {
    error_log('Staff thread read update failed: ' . mysqli_stmt_error($markRead));
}
mysqli_stmt_close($markRead);
if (!$readUpdated) {
    $rollbackAndRedirect('error');
}

if (!mysqli_commit($conn)) {
    error_log('Staff reply transaction commit failed: ' . mysqli_error($conn));
    $rollbackAndRedirect('error');
}
header('Location: messages.php?thread=' . (int) $threadId . '&status=sent');
exit;
