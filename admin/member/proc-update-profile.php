<?php
require_once 'inc/auth.php';
require_once '../../inc/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: profile.php');
    exit;
}

$csrfToken = $_POST['csrf_token'] ?? '';
$sessionToken = $_SESSION['member_profile_csrf'] ?? '';
if (!is_string($csrfToken) || !is_string($sessionToken) || !hash_equals($sessionToken, $csrfToken)) {
    header('Location: profile.php?status=error');
    exit;
}

$getPostedText = static function (string $field): string {
    $value = $_POST[$field] ?? '';
    return is_string($value) ? trim($value) : '';
};

$memberId = (int) $_SESSION['member_id'];
$firstName = $getPostedText('first_name');
$lastName = $getPostedText('last_name');
$email = $getPostedText('email');
$phone = $getPostedText('phone');
$homeAddress = $getPostedText('home_address');
$dateOfBirth = $getPostedText('date_of_birth');
$occupation = $getPostedText('occupation');
$stateOfOrigin = $getPostedText('state_of_origin');
$emergencyContactName = $getPostedText('emergency_contact_name');
$emergencyContactPhone = $getPostedText('emergency_contact_phone');

$dateIsValid = $dateOfBirth === '' || (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateOfBirth) && checkdate((int) substr($dateOfBirth, 5, 2), (int) substr($dateOfBirth, 8, 2), (int) substr($dateOfBirth, 0, 4)));
$fieldsAreValid = $firstName !== '' && strlen($firstName) <= 50 && $lastName !== '' && strlen($lastName) <= 50
    && filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($email) <= 100
    && strlen($phone) <= 20 && strlen($occupation) <= 100 && strlen($stateOfOrigin) <= 100
    && strlen($emergencyContactName) <= 150 && strlen($emergencyContactPhone) <= 30 && $dateIsValid;

if (!$fieldsAreValid) {
    header('Location: profile.php?status=error');
    exit;
}

$duplicateEmail = mysqli_prepare($conn, 'SELECT id FROM members WHERE email = ? AND id <> ? LIMIT 1');
if (!$duplicateEmail) {
    header('Location: profile.php?status=error');
    exit;
}
mysqli_stmt_bind_param($duplicateEmail, 'si', $email, $memberId);
mysqli_stmt_execute($duplicateEmail);
$duplicateResult = mysqli_stmt_get_result($duplicateEmail);
$hasDuplicateEmail = $duplicateResult && mysqli_num_rows($duplicateResult) > 0;
mysqli_stmt_close($duplicateEmail);

if ($hasDuplicateEmail) {
    header('Location: profile.php?status=error');
    exit;
}

$update = mysqli_prepare($conn, 'UPDATE members SET first_name = ?, last_name = ?, email = ?, phone = ?, home_address = ?, date_of_birth = ?, occupation = ?, state_of_origin = ?, emergency_contact_name = ?, emergency_contact_phone = ? WHERE id = ? AND org_id = ?');
if (!$update) {
    header('Location: profile.php?status=error');
    exit;
}

$dateOfBirth = $dateOfBirth !== '' ? $dateOfBirth : null;
$memberOrgId = (int) $_SESSION['member_org_id'];
mysqli_stmt_bind_param($update, 'ssssssssssii', $firstName, $lastName, $email, $phone, $homeAddress, $dateOfBirth, $occupation, $stateOfOrigin, $emergencyContactName, $emergencyContactPhone, $memberId, $memberOrgId);
$updated = mysqli_stmt_execute($update);
mysqli_stmt_close($update);

if (!$updated) {
    header('Location: profile.php?status=error');
    exit;
}

$_SESSION['member_first_name'] = $firstName;
$_SESSION['member_last_name'] = $lastName;
$_SESSION['member_name'] = $firstName . ' ' . $lastName;
unset($_SESSION['member_profile_csrf']);

header('Location: profile.php?status=success');
exit;