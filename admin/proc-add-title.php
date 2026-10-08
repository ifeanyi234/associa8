<?php
require_once "inc/auth.php";
require_once "../inc/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: add-title.php');
    exit;
}

$name = trim($_POST['name'] ?? '');
$level = (int) ($_POST['level'] ?? 0);
$description = trim($_POST['description'] ?? '');
$orgId = isset($_SESSION['org_id']) && $_SESSION['org_id'] !== null ? (int) $_SESSION['org_id'] : null;

if ($orgId === null || $orgId < 1 || $name === '' || strlen($name) > 100 || strlen($description) > 255 || $level < 1 || $level > 99) {
    header('Location: add-title.php?status=error&msg=' . urlencode('Enter a title name and a valid hierarchy level.'));
    exit;
}

$statement = mysqli_prepare($conn, 'INSERT INTO titles (org_id, title, level, description) VALUES (?, ?, ?, ?)');
if ($statement) {
    mysqli_stmt_bind_param($statement, 'isis', $orgId, $name, $level, $description);
}
$success = $statement && mysqli_stmt_execute($statement);
$message = $success ? 'Title created successfully.' : 'Could not save this title. The level may already exist.';

header('Location: add-title.php?status=' . ($success ? 'success' : 'error') . '&msg=' . urlencode($message));
exit;
