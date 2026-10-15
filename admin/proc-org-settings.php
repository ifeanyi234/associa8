<?php
require_once 'inc/auth.php';
require_once '../inc/db.php';
require_once '../inc/admin-remember-me.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: org-settings.php');
    exit;
}

$csrfToken = $_POST['csrf_token'] ?? '';
$sessionToken = $_SESSION['org_settings_csrf'] ?? '';
if (!is_string($csrfToken) || !is_string($sessionToken) || $sessionToken === ''
    || !hash_equals($sessionToken, $csrfToken)) {
    header('Location: org-settings.php?status=csrf');
    exit;
}

$getPostedText = static function (string $field): string {
    $value = $_POST[$field] ?? '';
    return is_string($value) ? trim($value) : '';
};

$name = $getPostedText('name');
$type = $getPostedText('type');
$email = $getPostedText('email');
$phone = $getPostedText('phone');
$country = $getPostedText('country');
$state = $getPostedText('state');
$firstName = $getPostedText('first_name');
$lastName = $getPostedText('last_name');
$adminEmail = $getPostedText('admin_email');
$adminPhone = $getPostedText('admin_phone');
$jobTitle = $getPostedText('job_title');
$username = $getPostedText('username');
$currentPassword = $_POST['current_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';
$allowedTypes = ['association', 'church', 'ngo', 'club', 'corporate'];
$phoneIsValid = $phone === '' || (strlen($phone) <= 30 && preg_match('/^\+?[0-9(). -]*[0-9][0-9(). -]*$/D', $phone) === 1);
$adminPhoneIsValid = $adminPhone === '' || preg_match('/^[0-9]{1,19}$/D', $adminPhone) === 1;
if ($adminPhoneIsValid && $adminPhone !== '') {
    $normalizedAdminPhone = ltrim($adminPhone, '0');
    $normalizedAdminPhone = $normalizedAdminPhone === '' ? '0' : $normalizedAdminPhone;
    $maxPhone = (string) PHP_INT_MAX;
    $adminPhoneIsValid = strlen($normalizedAdminPhone) < strlen($maxPhone)
        || (strlen($normalizedAdminPhone) === strlen($maxPhone) && strcmp($normalizedAdminPhone, $maxPhone) <= 0);
}
$passwordChangeRequested = $newPassword !== '' || $confirmPassword !== '' || $currentPassword !== '';

if ($name === '' || strlen($name) > 100 || !in_array($type, $allowedTypes, true)
    || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100
    || !$phoneIsValid || $country === '' || strlen($country) > 50
    || $state === '' || strlen($state) > 40
    || $firstName === '' || strlen($firstName) > 100
    || $lastName === '' || strlen($lastName) > 100
    || !filter_var($adminEmail, FILTER_VALIDATE_EMAIL) || strlen($adminEmail) > 150
    || !$adminPhoneIsValid || $jobTitle === '' || strlen($jobTitle) > 200
    || $username === '' || strlen($username) > 100
    || !is_string($currentPassword) || !is_string($newPassword) || !is_string($confirmPassword)
    || ($passwordChangeRequested && (strlen($newPassword) < 12 || strlen($newPassword) > 72
        || $currentPassword === '' || !hash_equals($newPassword, $confirmPassword)))) {
    header('Location: org-settings.php?status=' . ($passwordChangeRequested ? 'password' : 'invalid'));
    exit;
}

$orgId = (int) ($_SESSION['org_id'] ?? 0);
$accountId = (int) ($_SESSION['user_id'] ?? 0);
if ($orgId < 1 || $accountId < 1) {
    http_response_code(403);
    exit('Organization settings are not available for this account.');
}

if (!mysqli_begin_transaction($conn)) {
    error_log('Organization settings transaction could not be started: ' . mysqli_error($conn));
    header('Location: org-settings.php?status=error');
    exit;
}
$rollbackAndRedirect = static function (string $status) use ($conn): void {
    if (!mysqli_rollback($conn)) {
        error_log('Organization settings transaction rollback failed: ' . mysqli_error($conn));
    }
    header('Location: org-settings.php?status=' . rawurlencode($status));
    exit;
};

$identityStmt = mysqli_prepare(
    $conn,
    "SELECT a.password, ai.id AS admin_id
     FROM `acc-info` a
     INNER JOIN `admin-info` ai ON ai.acc_id = a.id AND ai.org_id = a.org_id
     WHERE a.id = ? AND a.org_id = ? AND a.status = 'active'
     ORDER BY ai.id ASC
     LIMIT 1
     FOR UPDATE"
);
if (!$identityStmt) {
    error_log('Administrator settings identity lookup could not be prepared: ' . mysqli_error($conn));
    $rollbackAndRedirect('error');
}
mysqli_stmt_bind_param($identityStmt, 'ii', $accountId, $orgId);
if (!mysqli_stmt_execute($identityStmt)) {
    error_log('Administrator settings identity lookup failed: ' . mysqli_stmt_error($identityStmt));
    mysqli_stmt_close($identityStmt);
    $rollbackAndRedirect('error');
}
$identityResult = mysqli_stmt_get_result($identityStmt);
$identity = $identityResult ? mysqli_fetch_assoc($identityResult) : null;
mysqli_stmt_close($identityStmt);
if (!$identity) {
    $rollbackAndRedirect('error');
}
$adminId = (int) $identity['admin_id'];

if ($passwordChangeRequested && !password_verify($currentPassword, (string) $identity['password'])) {
    $rollbackAndRedirect('password');
}

$duplicateStmt = mysqli_prepare(
    $conn,
    'SELECT 1 FROM `acc-info` WHERE username = ? AND id <> ?
     UNION ALL
     SELECT 1 FROM `admin-info` WHERE email = ? AND NOT (id = ? AND org_id = ?)
     UNION ALL
     SELECT 1 FROM `admin-info` WHERE email = ? AND NOT (id = ? AND org_id = ?)
     UNION ALL
     SELECT 1 FROM `acc-info` WHERE username = ? AND id <> ?
     LIMIT 1'
);
if (!$duplicateStmt) {
    error_log('Administrator settings duplicate check could not be prepared: ' . mysqli_error($conn));
    $rollbackAndRedirect('error');
}
mysqli_stmt_bind_param(
    $duplicateStmt,
    'sisiisiisi',
    $username,
    $accountId,
    $adminEmail,
    $adminId,
    $orgId,
    $username,
    $adminId,
    $orgId,
    $adminEmail,
    $accountId
);
if (!mysqli_stmt_execute($duplicateStmt)) {
    error_log('Administrator settings duplicate check failed: ' . mysqli_stmt_error($duplicateStmt));
    mysqli_stmt_close($duplicateStmt);
    $rollbackAndRedirect('error');
}
$duplicateResult = mysqli_stmt_get_result($duplicateStmt);
$hasDuplicate = $duplicateResult && mysqli_num_rows($duplicateResult) > 0;
mysqli_stmt_close($duplicateStmt);
if ($hasDuplicate) {
    $rollbackAndRedirect('duplicate');
}

$organizationUpdate = mysqli_prepare(
    $conn,
    'UPDATE `org-info` SET name = ?, type = ?, email = ?, phone = ?, country = ?, state = ?
     WHERE id = ?'
);
if (!$organizationUpdate) {
    error_log('Organization settings update could not be prepared: ' . mysqli_error($conn));
    $rollbackAndRedirect('error');
}
mysqli_stmt_bind_param($organizationUpdate, 'ssssssi', $name, $type, $email, $phone, $country, $state, $orgId);
$organizationUpdated = mysqli_stmt_execute($organizationUpdate);
if (!$organizationUpdated) {
    error_log('Organization settings update failed: ' . mysqli_stmt_error($organizationUpdate));
}
mysqli_stmt_close($organizationUpdate);
if (!$organizationUpdated) {
    $rollbackAndRedirect('error');
}

$administratorUpdate = mysqli_prepare(
    $conn,
    "UPDATE `admin-info`
     SET `first-name` = ?, `last-name` = ?, email = ?, phone = NULLIF(?, ''), `job-title` = ?
     WHERE id = ? AND org_id = ? AND acc_id = ?"
);
if (!$administratorUpdate) {
    error_log('Administrator profile update could not be prepared: ' . mysqli_error($conn));
    $rollbackAndRedirect('error');
}
mysqli_stmt_bind_param(
    $administratorUpdate,
    'sssssiii',
    $firstName,
    $lastName,
    $adminEmail,
    $adminPhone,
    $jobTitle,
    $adminId,
    $orgId,
    $accountId
);
$administratorUpdated = mysqli_stmt_execute($administratorUpdate);
if (!$administratorUpdated) {
    error_log('Administrator profile update failed: ' . mysqli_stmt_error($administratorUpdate));
}
mysqli_stmt_close($administratorUpdate);
if (!$administratorUpdated) {
    $rollbackAndRedirect('error');
}

$passwordHash = null;
if ($passwordChangeRequested) {
    $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
    if (!is_string($passwordHash)) {
        error_log('Administrator password hashing failed.');
        $rollbackAndRedirect('error');
    }
    $accountUpdate = mysqli_prepare(
        $conn,
        'UPDATE `acc-info` SET username = ?, password = ? WHERE id = ? AND org_id = ?'
    );
    if (!$accountUpdate) {
        error_log('Administrator login account update could not be prepared: ' . mysqli_error($conn));
        $rollbackAndRedirect('error');
    }
    mysqli_stmt_bind_param($accountUpdate, 'ssii', $username, $passwordHash, $accountId, $orgId);
} else {
    $accountUpdate = mysqli_prepare(
        $conn,
        'UPDATE `acc-info` SET username = ? WHERE id = ? AND org_id = ?'
    );
    if (!$accountUpdate) {
        error_log('Administrator login account update could not be prepared: ' . mysqli_error($conn));
        $rollbackAndRedirect('error');
    }
    mysqli_stmt_bind_param($accountUpdate, 'sii', $username, $accountId, $orgId);
}
$accountUpdated = mysqli_stmt_execute($accountUpdate);
if (!$accountUpdated) {
    error_log('Administrator login account update failed: ' . mysqli_stmt_error($accountUpdate));
}
mysqli_stmt_close($accountUpdate);
if (!$accountUpdated) {
    $rollbackAndRedirect('error');
}

if ($passwordChangeRequested) {
    $revokeTokens = mysqli_prepare($conn, 'DELETE FROM admin_remember_tokens WHERE account_id = ?');
    if (!$revokeTokens) {
        error_log('Administrator remembered sessions could not be prepared for revocation: ' . mysqli_error($conn));
        $rollbackAndRedirect('error');
    }
    mysqli_stmt_bind_param($revokeTokens, 'i', $accountId);
    $tokensRevoked = mysqli_stmt_execute($revokeTokens);
    if (!$tokensRevoked) {
        error_log('Administrator remembered sessions could not be revoked: ' . mysqli_stmt_error($revokeTokens));
    }
    mysqli_stmt_close($revokeTokens);
    if (!$tokensRevoked) {
        $rollbackAndRedirect('error');
    }
}

if (!mysqli_commit($conn)) {
    error_log('Organization and administrator settings transaction could not be committed: ' . mysqli_error($conn));
    $rollbackAndRedirect('error');
}

$_SESSION['username'] = $username;
$_SESSION['admin_name'] = trim($firstName . ' ' . $lastName);
$_SESSION['admin_organization_name'] = $name;
if ($passwordChangeRequested) {
    admin_clear_remember_cookie();
}
unset($_SESSION['org_settings_csrf']);
header('Location: org-settings.php?status=success');
exit;
