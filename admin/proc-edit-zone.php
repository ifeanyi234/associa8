<?php
require_once "inc/auth.php";
require_once "../inc/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: zones.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$coordinator = trim($_POST['coordinator_name'] ?? '');
$adminRole = $_SESSION['admin_role'] ?? 'admin';
$orgId = isset($_SESSION['org_id']) && $_SESSION['org_id'] !== null ? (int) $_SESSION['org_id'] : null;

if ($id < 1 || $name === '' || strlen($name) > 100 || strlen($coordinator) > 150 || ($adminRole !== 'super_admin' && ($orgId === null || $orgId < 1))) {
    header('Location: zones.php?status=error&msg=' . urlencode('Enter valid zone details.'));
    exit;
}

$sql = 'UPDATE zones SET name = ?, coordinator_name = ? WHERE id = ?';
if ($adminRole !== 'super_admin') {
    $sql .= ' AND org_id = ?';
}
$statement = mysqli_prepare($conn, $sql);
if ($statement && $adminRole === 'super_admin') {
    mysqli_stmt_bind_param($statement, 'ssi', $name, $coordinator, $id);
} elseif ($statement) {
    mysqli_stmt_bind_param($statement, 'ssii', $name, $coordinator, $id, $orgId);
}
$success = $statement && mysqli_stmt_execute($statement);
$message = $success ? 'Zone updated successfully.' : 'Could not update this zone. The name may already exist.';

header('Location: zones.php?status=' . ($success ? 'success' : 'error') . '&msg=' . urlencode($message));
exit;
