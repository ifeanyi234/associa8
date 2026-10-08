<?php
require_once 'inc/auth.php';
require_once '../inc/db.php';
require_once '../inc/mailer.php';

function invitationActionRedirect(string $type, string $message): void
{
    $_SESSION['user_control_notice'] = ['type' => $type, 'message' => $message];
    header('Location: user-controls.php');
    exit;
}

if (!in_array($_SESSION['admin_role'] ?? '', ['super_admin', 'admin'], true)) {
    http_response_code(403);
    exit('Only organization administrators can manage invitations.');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: user-controls.php');
    exit;
}
$token = $_POST['csrf_token'] ?? '';
$sessionToken = $_SESSION['user_control_csrf'] ?? '';
if (!is_string($token) || !is_string($sessionToken) || $sessionToken === '' || !hash_equals($sessionToken, $token)) {
    invitationActionRedirect('error', 'Your form expired. Reload the page and try again.');
}
$invitationId = filter_var($_POST['invitation_id'] ?? null, FILTER_VALIDATE_INT);
$actorRole = (string) $_SESSION['admin_role'];
$orgId = (int) ($_SESSION['org_id'] ?? 0);
$action = (string) ($_POST['action'] ?? '');
if (!$invitationId || $orgId < 1 || !in_array($action, ['revoke', 'resend'], true)) {
    invitationActionRedirect('error', 'Choose a valid invitation action.');
}

$lookup = mysqli_prepare(
    $conn,
    'SELECT i.id, i.org_id, i.first_name, i.last_name, i.email, i.role, i.token_hash, o.name AS organization_name
     FROM staff_invitations i
     INNER JOIN `org-info` o ON o.id = i.org_id
     WHERE i.id = ? AND i.org_id = ? AND i.accepted_at IS NULL
     LIMIT 1 FOR UPDATE'
);
if (!$lookup || !mysqli_begin_transaction($conn)) {
    if ($lookup) {
        mysqli_stmt_close($lookup);
    }
    error_log('Staff invitation action transaction could not start: ' . mysqli_error($conn));
    invitationActionRedirect('error', 'The invitation could not be changed. Please try again.');
}
mysqli_stmt_bind_param($lookup, 'ii', $invitationId, $orgId);
if (!mysqli_stmt_execute($lookup)) {
    mysqli_rollback($conn);
    error_log('Staff invitation action lookup failed: ' . mysqli_stmt_error($lookup));
    mysqli_stmt_close($lookup);
    invitationActionRedirect('error', 'The invitation could not be changed. Please try again.');
}
$result = mysqli_stmt_get_result($lookup);
$invitation = $result ? mysqli_fetch_assoc($result) : null;
mysqli_stmt_close($lookup);
if (!$invitation) {
    mysqli_rollback($conn);
    invitationActionRedirect('error', 'That invitation is no longer pending.');
}

if ($action === 'revoke') {
    $delete = mysqli_prepare($conn, 'DELETE FROM staff_invitations WHERE id = ? AND org_id = ? AND accepted_at IS NULL');
    if (!$delete) {
        mysqli_rollback($conn);
        error_log('Staff invitation revoke could not be prepared: ' . mysqli_error($conn));
        invitationActionRedirect('error', 'The invitation could not be cancelled. Please try again.');
    }
    mysqli_stmt_bind_param($delete, 'ii', $invitationId, $orgId);
    $revoked = mysqli_stmt_execute($delete) && mysqli_stmt_affected_rows($delete) === 1;
    mysqli_stmt_close($delete);
    if (!$revoked || !mysqli_commit($conn)) {
        mysqli_rollback($conn);
        error_log('Staff invitation revoke failed: ' . mysqli_error($conn));
        invitationActionRedirect('error', 'The invitation could not be cancelled. Please try again.');
    }
    invitationActionRedirect('success', 'Invitation cancelled.');
}

$plainToken = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
$tokenHash = hash('sha256', $plainToken);
$update = mysqli_prepare(
    $conn,
    'UPDATE staff_invitations
     SET token_hash = ?, expires_at = DATE_ADD(UTC_TIMESTAMP(), INTERVAL 48 HOUR)
     WHERE id = ? AND org_id = ? AND accepted_at IS NULL'
);
if (!$update) {
    mysqli_rollback($conn);
    error_log('Staff invitation resend could not be prepared: ' . mysqli_error($conn));
    invitationActionRedirect('error', 'The invitation could not be resent. Please try again.');
}
mysqli_stmt_bind_param($update, 'sii', $tokenHash, $invitationId, $orgId);
if (!mysqli_stmt_execute($update) || mysqli_stmt_affected_rows($update) !== 1) {
    mysqli_rollback($conn);
    error_log('Staff invitation resend update failed: ' . mysqli_stmt_error($update));
    mysqli_stmt_close($update);
    invitationActionRedirect('error', 'The invitation could not be resent. Please try again.');
}
mysqli_stmt_close($update);

$host = (string) ($_SERVER['HTTP_HOST'] ?? '');
if ($host === '' || preg_match('/[\r\n\/\\\\]/', $host)) {
    mysqli_rollback($conn);
    error_log('Staff invitation resend could not build a safe link from the request host.');
    invitationActionRedirect('error', 'The invitation link could not be created for this site.');
}
$scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
$basePath = rtrim(dirname(dirname((string) ($_SERVER['SCRIPT_NAME'] ?? '/admin/proc-invitation-action.php'))), '/\\');
$inviteUrl = $scheme . '://' . $host . $basePath . '/admin/accept-staff-invitation.php?token=' . rawurlencode($plainToken);
$body = '<p>Hello ' . htmlspecialchars((string) $invitation['first_name'], ENT_QUOTES, 'UTF-8') . ',</p>'
    . '<p>You have been invited to access the <strong>' . htmlspecialchars((string) $invitation['organization_name'], ENT_QUOTES, 'UTF-8') . '</strong> organization workspace on Associa8.</p>'
    . '<p>Your assigned access role is <strong>' . htmlspecialchars(ucfirst((string) $invitation['role']), ENT_QUOTES, 'UTF-8') . '</strong>. The organization administrator set this role; you cannot change it from the invitation.</p>'
    . '<p>Accept the invitation and choose your own password. This link expires in 48 hours.</p>'
    . '<p><a href="' . htmlspecialchars($inviteUrl, ENT_QUOTES, 'UTF-8') . '">Accept invitation and set password</a></p>'
    . '<p>If you were not expecting this invitation, you can ignore this email.</p>';
if (!send_app_mail(
    (string) $invitation['email'],
    (string) $invitation['first_name'] . ' ' . (string) $invitation['last_name'],
    'Invitation to access ' . (string) $invitation['organization_name'] . ' on Associa8',
    $body
)) {
    mysqli_rollback($conn);
    invitationActionRedirect('error', 'The invitation email could not be sent. The previous invitation link is still valid.');
}
if (!mysqli_commit($conn)) {
    mysqli_rollback($conn);
    error_log('Resent staff invitation could not commit: ' . mysqli_error($conn));
    invitationActionRedirect('error', 'The invitation could not be resent. Please try again.');
}
invitationActionRedirect('success', 'Invitation resent. The new link expires after 48 hours.');
