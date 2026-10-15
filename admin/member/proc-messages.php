<?php
require_once 'inc/auth.php';
require_once '../../inc/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: messages.php');
    exit;
}

$csrfToken = $_POST['csrf_token'] ?? '';
$sessionToken = $_SESSION['member_message_csrf'] ?? '';
if (!is_string($csrfToken) || !is_string($sessionToken) || $sessionToken === ''
    || !hash_equals($sessionToken, $csrfToken)) {
    header('Location: messages.php?status=csrf');
    exit;
}

$action = $_POST['action'] ?? '';
$body = $_POST['body'] ?? '';
if (!is_string($action) || !is_string($body)) {
    header('Location: messages.php?status=invalid');
    exit;
}
$body = trim($body);
if ($body === '' || strlen($body) > 5000) {
    header('Location: messages.php?status=invalid');
    exit;
}

$memberId = (int) $_SESSION['member_id'];
$orgId = (int) $_SESSION['member_org_id'];
$memberName = trim((string) ($_SESSION['member_name'] ?? 'Member'));
$initials = strtoupper(substr($memberName, 0, 1));
$nameParts = preg_split('/\s+/', $memberName) ?: [];
if (count($nameParts) > 1) {
    $initials .= strtoupper(substr((string) end($nameParts), 0, 1));
}

if ($action === 'create') {
    $subject = $_POST['subject'] ?? '';
    if (!is_string($subject)) {
        header('Location: messages.php?status=invalid');
        exit;
    }
    $subject = trim($subject);
    if ($subject === '' || strlen($subject) > 150) {
        header('Location: messages.php?status=invalid');
        exit;
    }

    $rateLimit = mysqli_prepare(
        $conn,
        "SELECT
            (SELECT COUNT(*) FROM message_threads
             WHERE member_id = ? AND org_id = ? AND sent_at > DATE_SUB(UTC_TIMESTAMP(6), INTERVAL 1 MINUTE))
            +
            (SELECT COUNT(*) FROM message_replies
             WHERE member_id = ? AND sender_type = 'member'
               AND sent_at > DATE_SUB(UTC_TIMESTAMP(6), INTERVAL 1 MINUTE)) AS message_count"
    );
    if (!$rateLimit) {
        error_log('Member message rate check could not be prepared: ' . mysqli_error($conn));
        header('Location: messages.php?status=error');
        exit;
    }
    mysqli_stmt_bind_param($rateLimit, 'iii', $memberId, $orgId, $memberId);
    if (!mysqli_stmt_execute($rateLimit)) {
        error_log('Member message rate check failed: ' . mysqli_stmt_error($rateLimit));
        mysqli_stmt_close($rateLimit);
        header('Location: messages.php?status=error');
        exit;
    }
    $rateResult = mysqli_stmt_get_result($rateLimit);
    $rateRow = $rateResult ? mysqli_fetch_assoc($rateResult) : null;
    mysqli_stmt_close($rateLimit);
    if ((int) ($rateRow['message_count'] ?? 0) >= 10) {
        header('Location: messages.php?status=limited');
        exit;
    }

    $insert = mysqli_prepare(
        $conn,
        'INSERT INTO message_threads
            (member_id, org_id, sender_name, sender_avatar_initials, subject, body, tag, member_last_read_at, sent_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, UTC_TIMESTAMP(6), UTC_TIMESTAMP(6))'
    );
    if (!$insert) {
        error_log('Member message could not be prepared: ' . mysqli_error($conn));
        header('Location: messages.php?status=error');
        exit;
    }
    $tag = 'Member message';
    mysqli_stmt_bind_param($insert, 'iisssss', $memberId, $orgId, $memberName, $initials, $subject, $body, $tag);
    $inserted = mysqli_stmt_execute($insert);
    if (!$inserted) {
        error_log('Member message could not be saved: ' . mysqli_stmt_error($insert));
        mysqli_stmt_close($insert);
        header('Location: messages.php?status=error');
        exit;
    }
    $threadId = (int) mysqli_insert_id($conn);
    mysqli_stmt_close($insert);
    header('Location: messages.php?thread=' . $threadId . '&status=sent');
    exit;
}

if ($action !== 'reply') {
    header('Location: messages.php?status=invalid');
    exit;
}
$threadId = filter_input(INPUT_POST, 'thread_id', FILTER_VALIDATE_INT);
if ($threadId === false || $threadId === null || $threadId < 1) {
    header('Location: messages.php?status=invalid');
    exit;
}

$ownership = mysqli_prepare(
    $conn,
    'SELECT id FROM message_threads WHERE id = ? AND member_id = ? AND org_id = ? LIMIT 1'
);
if (!$ownership) {
    error_log('Member message ownership check could not be prepared: ' . mysqli_error($conn));
    header('Location: messages.php?status=error');
    exit;
}
mysqli_stmt_bind_param($ownership, 'iii', $threadId, $memberId, $orgId);
if (!mysqli_stmt_execute($ownership)) {
    error_log('Member message ownership check failed: ' . mysqli_stmt_error($ownership));
    mysqli_stmt_close($ownership);
    header('Location: messages.php?status=error');
    exit;
}
$ownershipResult = mysqli_stmt_get_result($ownership);
$thread = $ownershipResult ? mysqli_fetch_assoc($ownershipResult) : null;
mysqli_stmt_close($ownership);
if (!$thread) {
    http_response_code(404);
    exit('That conversation could not be found.');
}

$rateLimit = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS message_count FROM message_replies
     WHERE member_id = ? AND sender_type = 'member'
       AND sent_at > DATE_SUB(UTC_TIMESTAMP(6), INTERVAL 1 MINUTE)"
);
if (!$rateLimit) {
    error_log('Member reply rate check could not be prepared: ' . mysqli_error($conn));
    header('Location: messages.php?thread=' . $threadId . '&status=error');
    exit;
}
mysqli_stmt_bind_param($rateLimit, 'i', $memberId);
if (!mysqli_stmt_execute($rateLimit)) {
    error_log('Member reply rate check failed: ' . mysqli_stmt_error($rateLimit));
    mysqli_stmt_close($rateLimit);
    header('Location: messages.php?thread=' . $threadId . '&status=error');
    exit;
}
$rateResult = mysqli_stmt_get_result($rateLimit);
$rateRow = $rateResult ? mysqli_fetch_assoc($rateResult) : null;
mysqli_stmt_close($rateLimit);
if ((int) ($rateRow['message_count'] ?? 0) >= 10) {
    header('Location: messages.php?thread=' . $threadId . '&status=limited');
    exit;
}

$insert = mysqli_prepare(
    $conn,
    "INSERT INTO message_replies (thread_id, member_id, sender_type, sender_name, body, sent_at)
     VALUES (?, ?, 'member', ?, ?, UTC_TIMESTAMP(6))"
);
if (!$insert) {
    error_log('Member reply could not be prepared: ' . mysqli_error($conn));
    header('Location: messages.php?thread=' . $threadId . '&status=error');
    exit;
}
mysqli_stmt_bind_param($insert, 'iiss', $threadId, $memberId, $memberName, $body);
$inserted = mysqli_stmt_execute($insert);
if (!$inserted) {
    error_log('Member reply could not be saved: ' . mysqli_stmt_error($insert));
}
mysqli_stmt_close($insert);
if (!$inserted) {
    header('Location: messages.php?thread=' . $threadId . '&status=error');
    exit;
}
header('Location: messages.php?thread=' . $threadId . '&status=sent');
exit;
