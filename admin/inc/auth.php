<?php
require_once __DIR__ . '/info-tip.php';
require_once __DIR__ . '/access-denied.php';
require_once __DIR__ . '/permissions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../inc/db.php';
require_once __DIR__ . '/../../inc/admin-remember-me.php';
if (!isset($_SESSION['user_id']) && isset($_COOKIE[ADMIN_REMEMBER_COOKIE])) {
    admin_restore_remembered_session($conn);
}

if (!isset($_SESSION['user_id']) || !ctype_digit((string) $_SESSION['user_id']) || (int) $_SESSION['user_id'] < 1) {
    session_unset();
    session_destroy();
    header('Location: index.php?error=unauthorized');
    exit;
}

$accountId = (int) $_SESSION['user_id'];
$accountStmt = mysqli_prepare(
    $conn,
    "SELECT a.org_id, a.username, ai.role, ai.`first-name` AS first_name, ai.`last-name` AS last_name,
            ai.zone_id, ai.subzone_id, o.name AS organization_name
     FROM `acc-info` a
     INNER JOIN `admin-info` ai ON ai.acc_id = a.id AND ai.org_id = a.org_id
     LEFT JOIN `org-info` o ON o.id = a.org_id
     WHERE a.id = ? AND a.status = 'active'
     LIMIT 1"
);
if (!$accountStmt) {
    error_log('Admin session account check could not be prepared: ' . mysqli_error($conn));
    http_response_code(503);
    exit('Your account could not be checked. Please try again later.');
}
mysqli_stmt_bind_param($accountStmt, 'i', $accountId);
if (!mysqli_stmt_execute($accountStmt)) {
    error_log('Admin session account check failed: ' . mysqli_stmt_error($accountStmt));
    mysqli_stmt_close($accountStmt);
    http_response_code(503);
    exit('Your account could not be checked. Please try again later.');
}
$accountResult = mysqli_stmt_get_result($accountStmt);
$account = $accountResult ? mysqli_fetch_assoc($accountResult) : null;
mysqli_stmt_close($accountStmt);
if (!$account) {
    session_unset();
    session_destroy();
    header('Location: index.php?error=unauthorized');
    exit;
}

$validRoles = ['super_admin', 'admin', 'zonal_admin', 'subzonal_admin'];
if (!in_array($account['role'], $validRoles, true) || (int) $account['org_id'] < 1) {
    session_unset();
    session_destroy();
    header('Location: index.php?error=invalid-role');
    exit;
}
$_SESSION['username'] = (string) $account['username'];
$_SESSION['org_id'] = (int) $account['org_id'];
$_SESSION['admin_is_super_admin'] = $account['role'] === 'super_admin';
$_SESSION['admin_display_role'] = (string) $account['role'];
$_SESSION['admin_role'] = $account['role'] === 'super_admin' ? 'admin' : (string) $account['role'];
$_SESSION['admin_name'] = trim((string) $account['first_name'] . ' ' . (string) $account['last_name']);
$_SESSION['admin_organization_name'] = trim((string) ($account['organization_name'] ?? ''));
$_SESSION['admin_zone_id'] = isset($account['zone_id']) ? (int) $account['zone_id'] : null;
$_SESSION['admin_subzone_id'] = isset($account['subzone_id']) ? (int) $account['subzone_id'] : null;
if (in_array($account['role'], ['zonal_admin', 'subzonal_admin'], true)) {
    require_once __DIR__ . '/scope.php';
    $scopeContext = admin_scope_context($conn);
    if (empty($scopeContext['valid'])) {
        session_unset();
        session_destroy();
        header('Location: index.php?error=invalid-role');
        exit;
    }
}

function admin_role_permissions(string $role): array
{
    if (in_array($role, ['zonal_admin', 'subzonal_admin'], true)) {
        return ['dashboard', 'members_view', 'members_manage'];
    }
    if ($role === 'super_admin') {
        return ['admin_only', 'dashboard', 'members_view', 'members_manage', 'membership_structure', 'admission', 'cbt_management', 'document', 'attendance', 'finance', 'events', 'user_control', 'messages'];
    }
    if ($role === 'admin') {
        $permissions = ['admin_only', 'dashboard', 'members_view', 'members_manage', 'membership_structure', 'admission', 'cbt_management', 'document', 'attendance', 'finance', 'events', 'messages'];
        if (!empty($_SESSION['admin_is_super_admin'])) {
            $permissions[] = 'user_control';
        }
        return $permissions;
    }
    return [];
}

function admin_is_super_admin(): bool
{
    return !empty($_SESSION['admin_is_super_admin']);
}

function admin_has_permission(string $permission): bool
{
    return in_array($permission, admin_role_permissions((string) ($_SESSION['admin_role'] ?? '')), true);
}

$adminRoutePermissions = [
    'dashboard.php' => 'dashboard',
    'notification-feed.php' => 'dashboard',
    'user-controls.php' => 'user_control',
    'process-user.php' => 'user_control',
    'proc-user-action.php' => 'user_control',
    'proc-invitation-action.php' => 'user_control',
    'financial.php' => 'finance',
    'add-payment.php' => 'finance',
    'proc-record-payment.php' => 'finance',
    'proc-create-levy.php' => 'finance',
    'admission-management.php' => 'admission',
    'add-admission.php' => 'admission',
    'proc-add-admission.php' => 'admission',
    'proc-update-admission-status.php' => 'admission',
    'org-settings.php' => 'admin_only',
    'proc-org-settings.php' => 'admin_only',
    'messages.php' => 'messages',
    'proc-message-reply.php' => 'messages',
    'portal-settings.php' => 'admin_only',
    'proc-portal-settings.php' => 'admin_only',
    'cbt-applicants.php' => 'cbt_management',
    'admission-applicants-bulk-upload.php' => 'admission',
    'cbt-applicants-bulk-upload.php' => 'admission',
    'cbt-bulk-upload.php' => 'cbt_management',
    'cbt-exam-preview.php' => 'cbt_management',
    'cbt-questions.php' => 'cbt_management',
    'cbt-result-detail.php' => 'cbt_management',
    'cbt-results.php' => 'cbt_management',
    'add-cbt-exam.php' => 'cbt_management',
    'add-cbt-question.php' => 'cbt_management',
    'schedule-cbt.php' => 'cbt_management',
    'proc-activate-cbt-exam.php' => 'cbt_management',
    'proc-add-cbt-exam.php' => 'cbt_management',
    'proc-add-cbt-question.php' => 'cbt_management',
    'proc-bulk-upload-cbt.php' => 'cbt_management',
    'proc-bulk-upload-applicants.php' => 'admission',
    'proc-download-cbt-template.php' => 'cbt_management',
    'proc-download-cbt-applicant-template.php' => 'admission',
    'proc-delete-cbt-question.php' => 'cbt_management',
    'proc-schedule-cbt.php' => 'cbt_management',
    'documents.php' => 'document',
    'upload-document.php' => 'document',
    'proc-upload-document.php' => 'document',
    'proc-delete-document.php' => 'document',
    'attendance.php' => 'attendance',
    'add-attendance.php' => 'attendance',
    'proc-record-attendance.php' => 'attendance',
    'events.php' => 'events',
    'add-event.php' => 'events',
    'proc-save-event.php' => 'events',
    'proc-event-action.php' => 'events',
    'event-attendance.php' => 'events',
    'proc-record-event-attendance.php' => 'events',
    'member-directory.php' => 'members_view',
    'add-member.php' => 'members_manage',
    'edit-member.php' => 'members_manage',
    'proc-add-member.php' => 'members_manage',
    'proc-edit-member.php' => 'members_manage',
    'proc-delete-member.php' => 'members_manage',
    'titles-hierarchy.php' => 'membership_structure',
    'add-title.php' => 'membership_structure',
    'edit-title.php' => 'membership_structure',
    'proc-add-title.php' => 'membership_structure',
    'proc-edit-title.php' => 'membership_structure',
    'proc-delete-title.php' => 'membership_structure',
    'zones.php' => 'membership_structure',
    'add-zone.php' => 'membership_structure',
    'edit-zone.php' => 'membership_structure',
    'edit-subzone.php' => 'membership_structure',
    'proc-add-zone.php' => 'membership_structure',
    'proc-edit-zone.php' => 'membership_structure',
    'proc-edit-subzone.php' => 'membership_structure',
    'suspension.php' => 'membership_structure',
    'add-suspension.php' => 'membership_structure',
    'proc-suspension.php' => 'membership_structure',
];
$routeName = basename((string) ($_SERVER['SCRIPT_FILENAME'] ?? ''));
$requiredPermission = $adminRoutePermissions[$routeName] ?? 'admin_only';
if (!admin_has_permission($requiredPermission)) {
    admin_render_access_denied();
}
