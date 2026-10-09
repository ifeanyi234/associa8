<?php
require_once 'inc/auth.php';
require_once '../inc/db.php';

function userActionRedirect(string $type, string $message): void
{
    $_SESSION['user_control_notice'] = ['type' => $type, 'message' => $message];
    header('Location: user-controls.php');
    exit;
}

if (!in_array($_SESSION['admin_role'] ?? '', ['super_admin', 'admin'], true)) {
    admin_render_access_denied('Only organization administrators can manage staff access.');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: user-controls.php');
    exit;
}
$token = $_POST['csrf_token'] ?? '';
$sessionToken = $_SESSION['user_control_csrf'] ?? '';
if (!is_string($token) || !is_string($sessionToken) || $sessionToken === '' || !hash_equals($sessionToken, $token)) {
    userActionRedirect('error', 'Your form expired. Reload the page and try again.');
}

$accountId = filter_var($_POST['account_id'] ?? null, FILTER_VALIDATE_INT);
$action = (string) ($_POST['action'] ?? '');
$actorId = (int) ($_SESSION['user_id'] ?? 0);
$actorRole = (string) $_SESSION['admin_role'];
$orgId = (int) ($_SESSION['org_id'] ?? 0);
if (!$accountId || $accountId === $actorId || $orgId < 1 || !in_array($action, ['disable', 'enable', 'change_role'], true)) {
    userActionRedirect('error', 'Choose a valid account action.');
}

$lookup = mysqli_prepare(
    $conn,
    'SELECT a.id, a.status, a.username, ai.role
     FROM `acc-info` a
     INNER JOIN `admin-info` ai ON ai.acc_id = a.id
     WHERE a.id = ? AND a.org_id = ? AND ai.org_id = ?
     LIMIT 1 FOR UPDATE'
);
if (!$lookup || !mysqli_begin_transaction($conn)) {
    if ($lookup) {
        mysqli_stmt_close($lookup);
    }
    error_log('Staff account action transaction could not start: ' . mysqli_error($conn));
    userActionRedirect('error', 'The account could not be updated. Please try again later.');
}
mysqli_stmt_bind_param($lookup, 'iii', $accountId, $orgId, $orgId);
if (!mysqli_stmt_execute($lookup)) {
    mysqli_rollback($conn);
    error_log('Staff account lookup failed: ' . mysqli_stmt_error($lookup));
    mysqli_stmt_close($lookup);
    userActionRedirect('error', 'The account could not be updated. Please try again later.');
}
$result = mysqli_stmt_get_result($lookup);
$target = $result ? mysqli_fetch_assoc($result) : null;
mysqli_stmt_close($lookup);
if (!$target || $target['role'] === 'super_admin' || ($actorRole !== 'super_admin' && $target['role'] === 'admin')) {
    mysqli_rollback($conn);
    userActionRedirect('error', 'You are not allowed to change that account.');
}

if ($action === 'change_role') {
    $newRole = (string) ($_POST['new_role'] ?? '');
    $allowedRoles = $actorRole === 'super_admin' ? ['admin', 'manager', 'staff'] : ['manager', 'staff'];
    if (!in_array($newRole, $allowedRoles, true)) {
        mysqli_rollback($conn);
        userActionRedirect('error', 'Choose a role that you are allowed to assign.');
    }
    $roleUpdate = mysqli_prepare($conn, 'UPDATE `admin-info` SET role = ? WHERE acc_id = ? AND org_id = ?');
    if (!$roleUpdate) {
        mysqli_rollback($conn);
        error_log('Staff role update could not be prepared: ' . mysqli_error($conn));
        userActionRedirect('error', 'The account role could not be changed. Please try again later.');
    }
    mysqli_stmt_bind_param($roleUpdate, 'sii', $newRole, $accountId, $orgId);
    if (!mysqli_stmt_execute($roleUpdate)) {
        mysqli_rollback($conn);
        error_log('Staff role update failed: ' . mysqli_stmt_error($roleUpdate));
        mysqli_stmt_close($roleUpdate);
        userActionRedirect('error', 'The account role could not be changed. Please try again later.');
    }
    mysqli_stmt_close($roleUpdate);
} else {
    $newStatus = $action === 'disable' ? 'disabled' : 'active';
    $update = mysqli_prepare($conn, 'UPDATE `acc-info` SET status = ? WHERE id = ? AND org_id = ?');
    if (!$update) {
        mysqli_rollback($conn);
        error_log('Staff account status update could not be prepared: ' . mysqli_error($conn));
        userActionRedirect('error', 'The account could not be updated. Please try again later.');
    }
    mysqli_stmt_bind_param($update, 'sii', $newStatus, $accountId, $orgId);
    $updated = mysqli_stmt_execute($update) && mysqli_stmt_affected_rows($update) === 1;
    if (!$updated) {
        mysqli_rollback($conn);
        error_log('Staff account status update failed: ' . mysqli_stmt_error($update));
        mysqli_stmt_close($update);
        userActionRedirect('error', 'The account could not be updated. Please try again later.');
    }
    mysqli_stmt_close($update);
}

if (!mysqli_commit($conn)) {
    mysqli_rollback($conn);
    error_log('Staff account action could not commit: ' . mysqli_error($conn));
    userActionRedirect('error', 'The account could not be updated. Please try again later.');
}

if ($action === 'change_role') {
    userActionRedirect('success', 'Staff role updated. The account receives its new access on the next request.');
}
userActionRedirect('success', $action === 'disable' ? 'Staff account disabled.' : 'Staff account enabled.');
