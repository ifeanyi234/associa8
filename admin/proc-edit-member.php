<?php
require_once "inc/auth.php";
require_once "../inc/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: member-directory.php');
    exit;
}

$memberId = (int) ($_POST['member_id'] ?? 0);
$firstName = trim($_POST['first_name'] ?? '');
$lastName = trim($_POST['last_name'] ?? '');
$email = strtolower(trim($_POST['email'] ?? ''));
$phone = trim($_POST['phone'] ?? '');
$titleId = (int) ($_POST['title_id'] ?? 0);
$zoneId = (int) ($_POST['zone_id'] ?? 0);
$subzoneId = (int) ($_POST['subzone_id'] ?? 0);
$joinedDate = $_POST['joined_date'] ?? '';
$adminRole = $_SESSION['admin_role'] ?? 'admin';
$orgId = isset($_SESSION['org_id']) && $_SESSION['org_id'] !== null ? (int) $_SESSION['org_id'] : null;

if ($memberId < 1 || $firstName === '' || $lastName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $joinedDate)) {
    header('Location: edit-member.php?id=' . $memberId . '&status=error&msg=' . urlencode('Enter valid member details.'));
    exit;
}
if ($adminRole !== 'super_admin' && $orgId === null) {
    header('Location: member-directory.php?status=error&msg=' . urlencode('Your account is not linked to an organization.'));
    exit;
}

$scope = $adminRole === 'super_admin' ? '' : ' AND org_id = ' . $orgId;
$memberCheck = mysqli_query($conn, 'SELECT org_id FROM members WHERE id = ' . $memberId . $scope . ' LIMIT 1');
$member = $memberCheck ? mysqli_fetch_assoc($memberCheck) : null;
if (!$member) {
    header('Location: member-directory.php?status=error&msg=' . urlencode('The member could not be found.'));
    exit;
}
$memberOrgId = (int) $member['org_id'];

if ($zoneId > 0) {
    $zoneCheck = mysqli_query($conn, 'SELECT id FROM zones WHERE id = ' . $zoneId . ' AND org_id = ' . $memberOrgId . ' LIMIT 1');
    if (!$zoneCheck || mysqli_num_rows($zoneCheck) === 0) {
        header('Location: edit-member.php?id=' . $memberId . '&status=error&msg=' . urlencode('The selected zone is invalid.'));
        exit;
    }
}
if ($subzoneId > 0) {
    if ($zoneId < 1) {
        header('Location: edit-member.php?id=' . $memberId . '&status=error&msg=' . urlencode('Choose a zone before assigning a subzone.'));
        exit;
    }
    $subzoneCheck = mysqli_query($conn, 'SELECT sz.id FROM subzones sz INNER JOIN zones z ON z.id = sz.zone_id WHERE sz.id = ' . $subzoneId . ' AND sz.zone_id = ' . $zoneId . ' AND z.org_id = ' . $memberOrgId . ' LIMIT 1');
    if (!$subzoneCheck || mysqli_num_rows($subzoneCheck) === 0) {
        header('Location: edit-member.php?id=' . $memberId . '&status=error&msg=' . urlencode('The selected subzone is invalid.'));
        exit;
    }
}
if ($titleId > 0) {
    $titleCheck = mysqli_query($conn, 'SELECT id FROM titles WHERE id = ' . $titleId . ' AND org_id = ' . $memberOrgId . ' LIMIT 1');
    if (!$titleCheck || mysqli_num_rows($titleCheck) === 0) {
        header('Location: edit-member.php?id=' . $memberId . '&status=error&msg=' . urlencode('The selected title is invalid.'));
        exit;
    }
}

$zoneValue = $zoneId > 0 ? $zoneId : null;
$subzoneValue = $subzoneId > 0 ? $subzoneId : null;
$titleValue = $titleId > 0 ? $titleId : null;
$statement = mysqli_prepare($conn, 'UPDATE members SET first_name = ?, last_name = ?, email = ?, phone = ?, title_id = ?, zone_id = ?, subzone_id = ?, joined_date = ? WHERE id = ?' . $scope);
if (!$statement) {
    header('Location: edit-member.php?id=' . $memberId . '&status=error&msg=' . urlencode('The member could not be updated.'));
    exit;
}
if ($adminRole === 'super_admin') {
    mysqli_stmt_bind_param($statement, 'ssssiiisi', $firstName, $lastName, $email, $phone, $titleValue, $zoneValue, $subzoneValue, $joinedDate, $memberId);
} else {
    mysqli_stmt_bind_param($statement, 'ssssiiisi', $firstName, $lastName, $email, $phone, $titleValue, $zoneValue, $subzoneValue, $joinedDate, $memberId);
}
$success = mysqli_stmt_execute($statement);
$message = $success ? 'Member updated successfully.' : 'Could not update the member. The email may already exist.';
header('Location: edit-member.php?id=' . $memberId . '&status=' . ($success ? 'success' : 'error') . '&msg=' . urlencode($message));
exit;
