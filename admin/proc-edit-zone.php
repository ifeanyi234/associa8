<?php
require_once "../inc/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: zones.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$coordinator = trim($_POST['coordinator_name'] ?? '');

if ($id < 1 || $name === '') {
    header('Location: zones.php?status=error&msg=' . urlencode('Enter valid zone details.'));
    exit;
}

$statement = mysqli_prepare($conn, 'UPDATE zones SET name = ?, coordinator_name = ? WHERE id = ?');
mysqli_stmt_bind_param($statement, 'ssi', $name, $coordinator, $id);
$success = mysqli_stmt_execute($statement);
$message = $success ? 'Zone updated successfully.' : 'Could not update this zone. The name may already exist.';

header('Location: zones.php?status=' . ($success ? 'success' : 'error') . '&msg=' . urlencode($message));
exit;
