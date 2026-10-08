<?php
require_once "inc/auth.php";
require_once "../inc/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: titles-hierarchy.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$level = (int) ($_POST['level'] ?? 0);
$description = trim($_POST['description'] ?? '');
$adminRole = $_SESSION['admin_role'] ?? 'admin';
$orgId = isset($_SESSION['org_id']) && $_SESSION['org_id'] !== null ? (int) $_SESSION['org_id'] : null;

if ($id < 1 || $name === '' || strlen($name) > 100 || strlen($description) > 255 || $level < 1 || $level > 99 || ($adminRole !== 'super_admin' && ($orgId === null || $orgId < 1))) {
    header('Location: titles-hierarchy.php?status=error&msg=' . urlencode('Enter valid title details.'));
    exit;
}

$sql = 'UPDATE titles SET title = ?, level = ?, description = ? WHERE id = ?';
if ($adminRole !== 'super_admin') {
    $sql .= ' AND org_id = ?';
}
$statement = mysqli_prepare($conn, $sql);
if ($statement && $adminRole === 'super_admin') {
    mysqli_stmt_bind_param($statement, 'sisi', $name, $level, $description, $id);
} elseif ($statement) {
    mysqli_stmt_bind_param($statement, 'sisii', $name, $level, $description, $id, $orgId);
}
$success = $statement && mysqli_stmt_execute($statement);
$message = $success ? 'Title updated successfully.' : 'Could not update this title. The level may already exist.';

header('Location: titles-hierarchy.php?status=' . ($success ? 'success' : 'error') . '&msg=' . urlencode($message));
exit;
