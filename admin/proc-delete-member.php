<?php
require_once "inc/auth.php";
require_once "../inc/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: member-directory.php');
    exit;
}
$memberId = (int) ($_POST['member_id'] ?? 0);
$adminRole = $_SESSION['admin_role'] ?? 'admin';
$orgId = isset($_SESSION['org_id']) && $_SESSION['org_id'] !== null ? (int) $_SESSION['org_id'] : null;
if ($memberId < 1 || ($adminRole !== 'super_admin' && $orgId === null)) {
    header('Location: member-directory.php?status=error&msg=' . urlencode('The member could not be deleted.'));
    exit;
}
$scope = $adminRole === 'super_admin' ? '' : ' AND org_id = ' . $orgId;
$statement = mysqli_prepare($conn, 'DELETE FROM members WHERE id = ?' . $scope);
if (!$statement) {
    header('Location: member-directory.php?status=error&msg=' . urlencode('The member could not be deleted.'));
    exit;
}
mysqli_stmt_bind_param($statement, 'i', $memberId);
$success = mysqli_stmt_execute($statement) && mysqli_stmt_affected_rows($statement) === 1;
$message = $success ? 'Member deleted successfully.' : 'The member could not be deleted.';
header('Location: member-directory.php?status=' . ($success ? 'success' : 'error') . '&msg=' . urlencode($message));
exit;
