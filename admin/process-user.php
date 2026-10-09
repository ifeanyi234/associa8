<?php
require_once 'inc/auth.php';
require_once '../inc/db.php';
require_once '../inc/mailer.php';

function userControlRedirect(string $type, string $message): void
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
    userControlRedirect('error', 'Your form expired. Reload the page and try again.');
}

$actorRole = (string) $_SESSION['admin_role'];
$orgId = (int) ($_SESSION['org_id'] ?? 0);
$actorId = (int) ($_SESSION['user_id'] ?? 0);
$firstName = trim((string) ($_POST['first_name'] ?? ''));
$lastName = trim((string) ($_POST['last_name'] ?? ''));
$email = strtolower(trim((string) ($_POST['email'] ?? '')));
$phone = trim((string) ($_POST['phone'] ?? ''));
$jobTitle = trim((string) ($_POST['job_title'] ?? ''));
$role = (string) ($_POST['role'] ?? '');
$allowedRoles = $actorRole === 'super_admin' ? ['admin', 'manager', 'staff'] : ['manager', 'staff'];

if (
    $orgId < 1
    || $firstName === ''
    || $lastName === ''
    || strlen($firstName) > 100
    || strlen($lastName) > 100
    || !filter_var($email, FILTER_VALIDATE_EMAIL)
    || strlen($email) > 100
    || !in_array($role, $allowedRoles, true)
    || strlen($jobTitle) > 200
    || ($phone !== '' && !preg_match('/^\+?[0-9]{7,15}$/', $phone))
) {
    userControlRedirect('error', 'Enter a valid name, email, role, and optional phone number.');
}

$organizationStmt = mysqli_prepare($conn, 'SELECT name FROM `org-info` WHERE id = ? LIMIT 1');
if (!$organizationStmt) {
    error_log('Staff invitation organization check could not be prepared: ' . mysqli_error($conn));
    userControlRedirect('error', 'Your organization could not be verified.');
}
mysqli_stmt_bind_param($organizationStmt, 'i', $orgId);
if (!mysqli_stmt_execute($organizationStmt)) {
    error_log('Staff invitation organization check failed: ' . mysqli_stmt_error($organizationStmt));
    mysqli_stmt_close($organizationStmt);
    userControlRedirect('error', 'The selected organization could not be verified.');
}
$organizationResult = mysqli_stmt_get_result($organizationStmt);
$organization = $organizationResult ? mysqli_fetch_assoc($organizationResult) : null;
mysqli_stmt_close($organizationStmt);
if (!$organization) {
    userControlRedirect('error', 'Your organization could not be verified.');
}

$existingAccountStmt = mysqli_prepare(
    $conn,
    'SELECT 1 FROM `acc-info` WHERE username = ?
     UNION ALL
     SELECT 1 FROM `admin-info` WHERE email = ?
     LIMIT 1'
);
if (!$existingAccountStmt) {
    error_log('Staff invitation duplicate check could not be prepared: ' . mysqli_error($conn));
    userControlRedirect('error', 'The invitation could not be checked. Please try again.');
}
mysqli_stmt_bind_param($existingAccountStmt, 'ss', $email, $email);
if (!mysqli_stmt_execute($existingAccountStmt)) {
    error_log('Staff invitation duplicate check failed: ' . mysqli_stmt_error($existingAccountStmt));
    mysqli_stmt_close($existingAccountStmt);
    userControlRedirect('error', 'The invitation could not be checked. Please try again.');
}
$existingAccountResult = mysqli_stmt_get_result($existingAccountStmt);
$accountExists = $existingAccountResult && mysqli_num_rows($existingAccountResult) > 0;
mysqli_stmt_close($existingAccountStmt);
if ($accountExists) {
    userControlRedirect('error', 'That email already has an organization staff profile or login. Resolve the existing profile before sending another invitation.');
}

$expiredInviteStmt = mysqli_prepare(
    $conn,
    'DELETE FROM staff_invitations WHERE email = ? AND accepted_at IS NULL AND expires_at <= UTC_TIMESTAMP()'
);
if (!$expiredInviteStmt) {
    error_log('Expired staff invitation cleanup could not be prepared: ' . mysqli_error($conn));
    userControlRedirect('error', 'The invitation could not be created. Please try again.');
}
mysqli_stmt_bind_param($expiredInviteStmt, 's', $email);
if (!mysqli_stmt_execute($expiredInviteStmt)) {
    error_log('Expired staff invitation cleanup failed: ' . mysqli_stmt_error($expiredInviteStmt));
    mysqli_stmt_close($expiredInviteStmt);
    userControlRedirect('error', 'The invitation could not be created. Please try again.');
}
mysqli_stmt_close($expiredInviteStmt);

$plainToken = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
$tokenHash = hash('sha256', $plainToken);
$inviteStmt = mysqli_prepare(
    $conn,
    'INSERT INTO staff_invitations
        (org_id, invited_by, first_name, last_name, email, phone, job_title, role, token_hash, expires_at)
     VALUES (?, ?, ?, ?, ?, NULLIF(?, \'\'), ?, ?, ?, DATE_ADD(UTC_TIMESTAMP(), INTERVAL 48 HOUR))'
);
if (!$inviteStmt) {
    error_log('Staff invitation could not be prepared: ' . mysqli_error($conn));
    userControlRedirect('error', 'The invitation could not be created. Apply the staff invitation database update and try again.');
}
mysqli_stmt_bind_param($inviteStmt, 'iisssssss', $orgId, $actorId, $firstName, $lastName, $email, $phone, $jobTitle, $role, $tokenHash);
if (!mysqli_stmt_execute($inviteStmt)) {
    $errorCode = mysqli_stmt_errno($inviteStmt);
    error_log('Staff invitation insert failed: ' . mysqli_stmt_error($inviteStmt));
    mysqli_stmt_close($inviteStmt);
    userControlRedirect('error', $errorCode === 1062 ? 'This email already has an account or a pending invitation.' : 'The invitation could not be created. Please try again.');
}
$invitationId = mysqli_insert_id($conn);
mysqli_stmt_close($inviteStmt);

$scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
$host = (string) ($_SERVER['HTTP_HOST'] ?? '');
if ($host === '' || preg_match('/[\r\n\/\\\\]/', $host)) {
    $deleteStmt = mysqli_prepare($conn, 'DELETE FROM staff_invitations WHERE id = ? AND token_hash = ?');
    if ($deleteStmt) {
        mysqli_stmt_bind_param($deleteStmt, 'is', $invitationId, $tokenHash);
        mysqli_stmt_execute($deleteStmt);
        mysqli_stmt_close($deleteStmt);
    }
    error_log('Staff invitation could not build a safe link from the request host.');
    userControlRedirect('error', 'The invitation link could not be created for this site.');
}
$basePath = rtrim(dirname(dirname((string) ($_SERVER['SCRIPT_NAME'] ?? '/admin/process-user.php'))), '/\\');
$inviteUrl = $scheme . '://' . $host . $basePath . '/admin/accept-staff-invitation.php?token=' . rawurlencode($plainToken);
$safeName = htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8');
$safeOrg = htmlspecialchars((string) $organization['name'], ENT_QUOTES, 'UTF-8');
$safeRole = htmlspecialchars(ucfirst(str_replace('_', ' ', $role)), ENT_QUOTES, 'UTF-8');
$safeUrl = htmlspecialchars($inviteUrl, ENT_QUOTES, 'UTF-8');
$body = '<p>Hello ' . $safeName . ',</p>'
    . '<p>You have been invited to access the <strong>' . $safeOrg . '</strong> organization workspace on Associa8.</p>'
    . '<p>Your assigned access role is <strong>' . $safeRole . '</strong>. The organization administrator set this role;</p>'
    . '<p>Accept the invitation and choose your own password using the link below. This link expires in 48 hours.</p>'
    . '<p><a href="' . $safeUrl . '">Accept invitation and set password</a></p>'
    . '<p>If you were not expecting this invitation, you can ignore this email.</p>';
if (!send_app_mail($email, $firstName . ' ' . $lastName, 'Invitation to access ' . (string) $organization['name'] . ' on Associa8', $body)) {
    $deleteStmt = mysqli_prepare($conn, 'DELETE FROM staff_invitations WHERE id = ? AND token_hash = ? AND accepted_at IS NULL');
    if ($deleteStmt) {
        mysqli_stmt_bind_param($deleteStmt, 'is', $invitationId, $tokenHash);
        if (!mysqli_stmt_execute($deleteStmt)) {
            error_log('Failed-delivery staff invitation cleanup failed: ' . mysqli_stmt_error($deleteStmt));
        }
        mysqli_stmt_close($deleteStmt);
    } else {
        error_log('Failed-delivery staff invitation cleanup could not be prepared: ' . mysqli_error($conn));
    }
    userControlRedirect('error', 'The invitation email could not be sent. Check the mail setup and try again.');
}

userControlRedirect('success', 'Invitation sent. They will choose their own password when they accept it.');
