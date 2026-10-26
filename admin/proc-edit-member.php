<?php
require_once "inc/auth.php";
require_once "../inc/db.php";
require_once "inc/title-assignment.php";
require_once "inc/scope.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: member-directory.php');
    exit;
}

$csrfToken = $_POST['csrf_token'] ?? '';
$sessionToken = $_SESSION['member_edit_csrf'] ?? '';
if (!is_string($csrfToken) || !is_string($sessionToken) || $sessionToken === '' || !hash_equals($sessionToken, $csrfToken)) {
    header('Location: member-directory.php?status=error&msg=' . urlencode('The edit request expired. Please try again.'));
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
$scopeContext = admin_scope_context($conn);
if (empty($scopeContext['valid'])) {
    admin_render_access_denied('Your account does not have a valid zone or sub-zone assignment.');
}

if ($memberId < 1 || $firstName === '' || strlen($firstName) > 50 || $lastName === '' || strlen($lastName) > 50 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100 || strlen($phone) > 20 || $titleId < 0 || $zoneId < 0 || $subzoneId < 0 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $joinedDate) || !checkdate((int) substr($joinedDate, 5, 2), (int) substr($joinedDate, 8, 2), (int) substr($joinedDate, 0, 4))) {
    header('Location: edit-member.php?id=' . $memberId . '&status=error&msg=' . urlencode('Enter valid member details.'));
    exit;
}
if ($adminRole !== 'super_admin' && ($orgId === null || $orgId < 1)) {
    header('Location: member-directory.php?status=error&msg=' . urlencode('Your account is not linked to an organization.'));
    exit;
}

$memberScope = admin_scope_sql('m', $scopeContext);
$memberCheck = mysqli_query($conn, 'SELECT m.org_id, m.zone_id, m.subzone_id FROM members m WHERE m.id = ' . $memberId . ($adminRole === 'super_admin' ? '' : ' AND m.org_id = ' . (int) $orgId) . $memberScope . ' LIMIT 1');
$member = $memberCheck ? mysqli_fetch_assoc($memberCheck) : null;
if (!$member) {
    header('Location: member-directory.php?status=error&msg=' . urlencode('The member could not be found.'));
    exit;
}
$memberOrgId = (int) $member['org_id'];
if (!admin_scope_allows_member($scopeContext, $member)) {
    admin_render_access_denied('You can only edit members inside your assigned area.');
}

$duplicateEmail = mysqli_prepare($conn, 'SELECT id FROM members WHERE email = ? AND id <> ? LIMIT 1');
if (!$duplicateEmail) {
    header('Location: edit-member.php?id=' . $memberId . '&status=error&msg=' . urlencode('The member could not be updated.'));
    exit;
}
mysqli_stmt_bind_param($duplicateEmail, 'si', $email, $memberId);
mysqli_stmt_execute($duplicateEmail);
$duplicateEmailResult = mysqli_stmt_get_result($duplicateEmail);
if ($duplicateEmailResult && mysqli_num_rows($duplicateEmailResult) > 0) {
    header('Location: edit-member.php?id=' . $memberId . '&status=error&msg=' . urlencode('That email address is already assigned to another member.'));
    exit;
}

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
if (
    ($scopeContext['role'] === 'zonal_admin'
        && ($zoneId !== (int) $scopeContext['zone_id']
            || ($subzoneId > 0 && !in_array($subzoneId, $scopeContext['subzone_ids'], true))))
    || ($scopeContext['role'] === 'subzonal_admin'
        && ($zoneId !== (int) $scopeContext['zone_id'] || $subzoneId !== (int) $scopeContext['subzone_id']))
) {
    admin_render_access_denied('You can only assign members inside your assigned area.');
}
$zoneValue = $zoneId > 0 ? $zoneId : null;
$subzoneValue = $subzoneId > 0 ? $subzoneId : null;
$titleValue = $titleId > 0 ? $titleId : null;
if (!mysqli_begin_transaction($conn)) {
    error_log('Member update transaction could not start: ' . mysqli_error($conn));
    header('Location: edit-member.php?id=' . $memberId . '&status=error&msg=' . urlencode('The member could not be updated.'));
    exit;
}
$assignment = validate_title_assignment(
    $conn,
    $memberOrgId,
    $titleValue ?? 0,
    $zoneValue,
    $subzoneValue,
    $memberId
);
if (!$assignment['valid']) {
    mysqli_rollback($conn);
    header('Location: edit-member.php?id=' . $memberId . '&status=error&msg=' . urlencode($assignment['message']));
    exit;
}
$updateScope = admin_scope_sql('members', $scopeContext);
$updateOrg = $adminRole === 'super_admin' ? '' : ' AND members.org_id = ' . $memberOrgId;
$statement = mysqli_prepare($conn, 'UPDATE members SET first_name = ?, last_name = ?, email = ?, phone = ?, title_id = ?, zone_id = ?, subzone_id = ?, joined_date = ? WHERE id = ?' . $updateOrg . $updateScope);
if (!$statement) {
    mysqli_rollback($conn);
    error_log('Member update prepare failed: ' . mysqli_error($conn));
    header('Location: edit-member.php?id=' . $memberId . '&status=error&msg=' . urlencode('The member could not be updated.'));
    exit;
}
if ($adminRole === 'super_admin') {
    mysqli_stmt_bind_param($statement, 'ssssiiisi', $firstName, $lastName, $email, $phone, $titleValue, $zoneValue, $subzoneValue, $joinedDate, $memberId);
} else {
    mysqli_stmt_bind_param($statement, 'ssssiiisi', $firstName, $lastName, $email, $phone, $titleValue, $zoneValue, $subzoneValue, $joinedDate, $memberId);
}
$success = mysqli_stmt_execute($statement) && mysqli_commit($conn);
if (!$success) {
    mysqli_rollback($conn);
    error_log('Member update failed: ' . mysqli_stmt_error($statement));
}
$message = $success ? 'Member updated successfully.' : 'Could not update the member. The email may already exist.';
if ($success) {
    unset($_SESSION['member_edit_csrf']);
}
header($success
    ? 'Location: member-directory.php?notice=member_updated&msg=' . urlencode($message)
    : 'Location: edit-member.php?id=' . $memberId . '&status=error&msg=' . urlencode($message));
exit;
