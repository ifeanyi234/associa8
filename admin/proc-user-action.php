<?php
require_once 'inc/auth.php';
require_once '../inc/db.php';
require_once 'inc/scope.php';

function userActionRedirect(string $type, string $message): void
{
    $_SESSION['user_control_notice'] = ['type' => $type, 'message' => $message];
    header('Location: user-controls.php');
    exit;
}

if (!admin_is_super_admin()) {
    admin_render_access_denied('Only the Super Admin can manage admin access.');
}
$scopeContext = admin_scope_context($conn);
if (empty($scopeContext['valid'])) {
    admin_render_access_denied('Your account does not have a valid zone or sub-zone assignment.');
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
$orgId = (int) ($_SESSION['org_id'] ?? 0);
if (!$accountId || $accountId === $actorId || $orgId < 1 || !in_array($action, ['disable', 'enable', 'change_role'], true)) {
    userActionRedirect('error', 'Choose a valid account action.');
}

$lookup = mysqli_prepare(
    $conn,
    'SELECT a.id, a.status, a.username, ai.email, ai.role, ai.zone_id, ai.subzone_id
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
if (
    !$target
    || $target['role'] === 'super_admin'
) {
    mysqli_rollback($conn);
    userActionRedirect('error', 'You are not allowed to change that account.');
}

if ($action === 'change_role') {
    $newRole = (string) ($_POST['new_role'] ?? '');
    if (!in_array($newRole, ['admin', 'zonal_admin', 'subzonal_admin'], true)) {
        mysqli_rollback($conn);
        userActionRedirect('error', 'Choose a valid admin role.');
    }
    $zoneId = 0;
    $subzoneId = 0;
    if ($newRole === 'zonal_admin' || $newRole === 'subzonal_admin') {
        $memberLookup = mysqli_prepare(
            $conn,
            'SELECT z.id AS valid_zone_id, sz.id AS valid_subzone_id
             FROM members m
             LEFT JOIN zones z ON z.id = m.zone_id AND z.org_id = m.org_id
             LEFT JOIN subzones sz ON sz.id = m.subzone_id AND sz.zone_id = m.zone_id
             WHERE m.org_id = ? AND LOWER(m.email) = ? AND m.status <> \'suspended\'
             LIMIT 2'
        );
        if (!$memberLookup) {
            mysqli_rollback($conn);
            error_log('Admin role member lookup could not be prepared: ' . mysqli_error($conn));
            userActionRedirect('error', 'The member area could not be verified. Please try again.');
        }
        $memberEmail = strtolower(trim((string) $target['email']));
        mysqli_stmt_bind_param($memberLookup, 'is', $orgId, $memberEmail);
        if (!mysqli_stmt_execute($memberLookup)) {
            mysqli_rollback($conn);
            error_log('Admin role member lookup failed: ' . mysqli_stmt_error($memberLookup));
            mysqli_stmt_close($memberLookup);
            userActionRedirect('error', 'The member area could not be verified. Please try again.');
        }
        $memberResult = mysqli_stmt_get_result($memberLookup);
        $member = $memberResult && mysqli_num_rows($memberResult) === 1 ? mysqli_fetch_assoc($memberResult) : null;
        mysqli_stmt_close($memberLookup);
        if (!$member || (int) ($member['valid_zone_id'] ?? 0) < 1) {
            mysqli_rollback($conn);
            userActionRedirect('error', 'The account email must match one active member with a valid zone assignment.');
        }
        $zoneId = (int) $member['valid_zone_id'];
        $subzoneId = (int) ($member['valid_subzone_id'] ?? 0);
        if ($newRole === 'subzonal_admin' && $subzoneId < 1) {
            mysqli_rollback($conn);
            userActionRedirect('error', 'The account email must match an active member with a valid sub-zone assignment.');
        }
    }
    $roleUpdate = mysqli_prepare($conn, 'UPDATE `admin-info` SET role = ?, zone_id = NULLIF(?, 0), subzone_id = NULLIF(?, 0) WHERE acc_id = ? AND org_id = ?');
    if (!$roleUpdate) {
        mysqli_rollback($conn);
        error_log('Staff role update could not be prepared: ' . mysqli_error($conn));
        userActionRedirect('error', 'The account role could not be changed. Please try again later.');
    }
    mysqli_stmt_bind_param($roleUpdate, 'siiii', $newRole, $zoneId, $subzoneId, $accountId, $orgId);
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
    userActionRedirect('success', 'Admin role updated. The account receives its new access on the next request.');
}
userActionRedirect('success', $action === 'disable' ? 'Admin account disabled.' : 'Admin account enabled.');
