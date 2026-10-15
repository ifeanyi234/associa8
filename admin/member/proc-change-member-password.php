<?php
require_once 'inc/auth.php';
require_once '../../inc/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: settings.php');
    exit;
}

$csrfToken = $_POST['csrf_token'] ?? '';
$sessionToken = $_SESSION['member_settings_csrf'] ?? '';
if (!is_string($csrfToken) || !is_string($sessionToken) || $sessionToken === ''
    || !hash_equals($sessionToken, $csrfToken)) {
    header('Location: settings.php?status=csrf');
    exit;
}

$currentPassword = $_POST['current_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';
if (!is_string($currentPassword) || !is_string($newPassword) || !is_string($confirmPassword)
    || strlen($newPassword) < 12 || strlen($newPassword) > 72) {
    header('Location: settings.php?status=password-invalid');
    exit;
}
if (!hash_equals($newPassword, $confirmPassword)) {
    header('Location: settings.php?status=password-mismatch');
    exit;
}

$memberId = (int) $_SESSION['member_id'];
$memberOrgId = (int) $_SESSION['member_org_id'];
$passwordQuery = mysqli_prepare($conn, 'SELECT password FROM members WHERE id = ? AND org_id = ? LIMIT 1');
if (!$passwordQuery) {
    error_log('Member password verification lookup could not be prepared: ' . mysqli_error($conn));
    header('Location: settings.php?status=error');
    exit;
}
mysqli_stmt_bind_param($passwordQuery, 'ii', $memberId, $memberOrgId);
if (!mysqli_stmt_execute($passwordQuery)) {
    error_log('Member password verification lookup failed: ' . mysqli_stmt_error($passwordQuery));
    mysqli_stmt_close($passwordQuery);
    header('Location: settings.php?status=error');
    exit;
}
$passwordResult = mysqli_stmt_get_result($passwordQuery);
$account = $passwordResult ? mysqli_fetch_assoc($passwordResult) : null;
mysqli_stmt_close($passwordQuery);
if (!$account || !is_string($account['password'] ?? null)
    || !password_verify($currentPassword, (string) $account['password'])) {
    header('Location: settings.php?status=password-error');
    exit;
}

$passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
if (!is_string($passwordHash)) {
    error_log('Member password hashing failed.');
    header('Location: settings.php?status=error');
    exit;
}
$update = mysqli_prepare($conn, 'UPDATE members SET password = ? WHERE id = ? AND org_id = ?');
if (!$update) {
    error_log('Member password update could not be prepared: ' . mysqli_error($conn));
    header('Location: settings.php?status=error');
    exit;
}
mysqli_stmt_bind_param($update, 'sii', $passwordHash, $memberId, $memberOrgId);
$updated = mysqli_stmt_execute($update);
if (!$updated) {
    error_log('Member password update failed: ' . mysqli_stmt_error($update));
}
$affectedRows = mysqli_stmt_affected_rows($update);
mysqli_stmt_close($update);
if (!$updated || $affectedRows !== 1) {
    header('Location: settings.php?status=error');
    exit;
}

unset($_SESSION['member_settings_csrf']);
header('Location: settings.php?status=password-success');
exit;
