<?php
require_once "inc/auth.php";
require_once "../inc/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: add-title.php');
    exit;
}

$name = trim($_POST['name'] ?? '');
$scope = $_POST['scope'] ?? '';
$level = (int) ($_POST['level'] ?? 0);
$description = trim($_POST['description'] ?? '');
$orgId = isset($_SESSION['org_id']) && $_SESSION['org_id'] !== null ? (int) $_SESSION['org_id'] : null;

if ($orgId === null || $orgId < 1 || $name === '' || strlen($name) > 100 || strlen($description) > 255 || !in_array($scope, ['hq', 'zone', 'subzone'], true) || $level < 1 || $level > 99) {
    header('Location: add-title.php?status=error&msg=' . urlencode('Enter a title name, level, and valid hierarchy number.'));
    exit;
}

if (!mysqli_begin_transaction($conn)) {
    error_log('Title creation transaction could not start: ' . mysqli_error($conn));
    header('Location: add-title.php?status=error&msg=' . urlencode('Could not save this title.'));
    exit;
}

$levelCheck = mysqli_prepare($conn, 'SELECT title FROM titles WHERE org_id = ? AND scope = ? AND level = ? LIMIT 1 FOR UPDATE');
if (!$levelCheck) {
    error_log('Title hierarchy level check prepare failed: ' . mysqli_error($conn));
    mysqli_rollback($conn);
    header('Location: add-title.php?status=error&msg=' . urlencode('Could not check whether this hierarchy level is available.'));
    exit;
}
mysqli_stmt_bind_param($levelCheck, 'isi', $orgId, $scope, $level);
if (!mysqli_stmt_execute($levelCheck)) {
    error_log('Title hierarchy level check failed: ' . mysqli_stmt_error($levelCheck));
    mysqli_rollback($conn);
    header('Location: add-title.php?status=error&msg=' . urlencode('Could not check whether this hierarchy level is available.'));
    exit;
}
$levelResult = mysqli_stmt_get_result($levelCheck);
$existingLevel = $levelResult ? mysqli_fetch_assoc($levelResult) : null;
if (!$levelResult) {
    error_log('Title hierarchy level result failed: ' . mysqli_stmt_error($levelCheck));
    mysqli_rollback($conn);
    header('Location: add-title.php?status=error&msg=' . urlencode('Could not check whether this hierarchy level is available.'));
    exit;
}
if ($existingLevel) {
    mysqli_rollback($conn);
    $scopeLabel = ['hq' => 'HQ', 'zone' => 'Zone', 'subzone' => 'Sub-zone'][$scope];
    $message = 'Hierarchy level ' . $level . ' is already used by ' . $existingLevel['title'] . ' in ' . $scopeLabel . '. Choose a different level.';
    header('Location: add-title.php?status=error&msg=' . urlencode($message));
    exit;
}

$statement = mysqli_prepare($conn, 'INSERT INTO titles (org_id, scope, title, level, description) VALUES (?, ?, ?, ?, ?)');
if (!$statement) {
    error_log('Title creation prepare failed: ' . mysqli_error($conn));
    mysqli_rollback($conn);
    header('Location: add-title.php?status=error&msg=' . urlencode('Could not save this title.'));
    exit;
}
mysqli_stmt_bind_param($statement, 'issis', $orgId, $scope, $name, $level, $description);
$success = mysqli_stmt_execute($statement);
if (!$success) {
    error_log('Title creation failed: ' . mysqli_stmt_error($statement));
    mysqli_rollback($conn);
} elseif (!mysqli_commit($conn)) {
    error_log('Title creation commit failed: ' . mysqli_error($conn));
    mysqli_rollback($conn);
    $success = false;
}
$message = $success ? 'Title created successfully.' : 'Could not save this title. Its name may already exist at this level.';

header('Location: add-title.php?status=' . ($success ? 'success' : 'error') . '&msg=' . urlencode($message));
exit;
