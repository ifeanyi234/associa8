<?php
require_once "../inc/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: titles-hierarchy.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$level = (int) ($_POST['level'] ?? 0);
$description = trim($_POST['description'] ?? '');

if ($id < 1 || $name === '' || $level < 1 || $level > 99) {
    header('Location: titles-hierarchy.php?status=error&msg=' . urlencode('Enter valid title details.'));
    exit;
}

$statement = mysqli_prepare($conn, 'UPDATE titles SET title = ?, level = ?, description = ? WHERE id = ?');
mysqli_stmt_bind_param($statement, 'sisi', $name, $level, $description, $id);
$success = mysqli_stmt_execute($statement);
$message = $success ? 'Title updated successfully.' : 'Could not update this title. The level may already exist.';

header('Location: titles-hierarchy.php?status=' . ($success ? 'success' : 'error') . '&msg=' . urlencode($message));
exit;
