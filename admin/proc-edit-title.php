<?php
require_once "inc/auth.php";
require_once "../inc/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: titles-hierarchy.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$scope = $_POST['scope'] ?? '';
$level = (int) ($_POST['level'] ?? 0);
$description = trim($_POST['description'] ?? '');
$adminRole = $_SESSION['admin_role'] ?? 'admin';
$orgId = isset($_SESSION['org_id']) && $_SESSION['org_id'] !== null ? (int) $_SESSION['org_id'] : null;

if ($id < 1 || $name === '' || strlen($name) > 100 || strlen($description) > 255 || !in_array($scope, ['hq', 'zone', 'subzone'], true) || $level < 1 || $level > 99 || ($adminRole !== 'super_admin' && ($orgId === null || $orgId < 1))) {
    header('Location: titles-hierarchy.php?status=error&msg=' . urlencode('Enter valid title details.'));
    exit;
}

$titleSql = 'SELECT scope, org_id FROM titles WHERE id = ?';
if ($adminRole !== 'super_admin') {
    $titleSql .= ' AND org_id = ?';
}
$titleSql .= ' LIMIT 1 FOR UPDATE';
$titleStatement = mysqli_prepare($conn, $titleSql);
if (!$titleStatement) {
    error_log('Title edit lookup prepare failed: ' . mysqli_error($conn));
    header('Location: titles-hierarchy.php?status=error&msg=' . urlencode('The title could not be updated.'));
    exit;
}
if ($adminRole === 'super_admin') {
    mysqli_stmt_bind_param($titleStatement, 'i', $id);
} else {
    mysqli_stmt_bind_param($titleStatement, 'ii', $id, $orgId);
}
if (!mysqli_begin_transaction($conn)) {
    error_log('Title edit transaction could not start: ' . mysqli_error($conn));
    header('Location: titles-hierarchy.php?status=error&msg=' . urlencode('The title could not be updated.'));
    exit;
}
if (!mysqli_stmt_execute($titleStatement)) {
    error_log('Title edit lookup failed: ' . mysqli_stmt_error($titleStatement));
    mysqli_rollback($conn);
    header('Location: titles-hierarchy.php?status=error&msg=' . urlencode('The title could not be updated.'));
    exit;
}
$titleResult = mysqli_stmt_get_result($titleStatement);
$existingTitle = $titleResult ? mysqli_fetch_assoc($titleResult) : null;
if (!$existingTitle) {
    mysqli_rollback($conn);
    header('Location: titles-hierarchy.php?status=error&msg=' . urlencode('The selected title could not be found.'));
    exit;
}
$titleOrgId = (int) $existingTitle['org_id'];
$levelCheck = mysqli_prepare($conn, 'SELECT title FROM titles WHERE org_id = ? AND scope = ? AND level = ? AND id <> ? LIMIT 1 FOR UPDATE');
if (!$levelCheck) {
    error_log('Title hierarchy level check prepare failed: ' . mysqli_error($conn));
    mysqli_rollback($conn);
    header('Location: titles-hierarchy.php?status=error&msg=' . urlencode('Could not check whether this hierarchy level is available.'));
    exit;
}
mysqli_stmt_bind_param($levelCheck, 'isii', $titleOrgId, $scope, $level, $id);
if (!mysqli_stmt_execute($levelCheck)) {
    error_log('Title hierarchy level check failed: ' . mysqli_stmt_error($levelCheck));
    mysqli_rollback($conn);
    header('Location: titles-hierarchy.php?status=error&msg=' . urlencode('Could not check whether this hierarchy level is available.'));
    exit;
}
$levelResult = mysqli_stmt_get_result($levelCheck);
$existingLevel = $levelResult ? mysqli_fetch_assoc($levelResult) : null;
if (!$levelResult) {
    error_log('Title hierarchy level result failed: ' . mysqli_stmt_error($levelCheck));
    mysqli_rollback($conn);
    header('Location: titles-hierarchy.php?status=error&msg=' . urlencode('Could not check whether this hierarchy level is available.'));
    exit;
}
if ($existingLevel) {
    mysqli_rollback($conn);
    $scopeLabel = ['hq' => 'HQ', 'zone' => 'Zone', 'subzone' => 'Sub-zone'][$scope];
    $message = 'Hierarchy level ' . $level . ' is already used by ' . $existingLevel['title'] . ' in ' . $scopeLabel . '. Choose a different level.';
    header('Location: titles-hierarchy.php?status=error&msg=' . urlencode($message));
    exit;
}

if ($scope !== $existingTitle['scope']) {
    require_once 'inc/title-assignment.php';
    $membersStatement = mysqli_prepare($conn, 'SELECT id, zone_id, subzone_id FROM members WHERE org_id = ? AND title_id = ?');
    if (!$membersStatement) {
        error_log('Title edit members prepare failed: ' . mysqli_error($conn));
        mysqli_rollback($conn);
        header('Location: titles-hierarchy.php?status=error&msg=' . urlencode('The title assignments could not be checked.'));
        exit;
    }
    mysqli_stmt_bind_param($membersStatement, 'ii', $titleOrgId, $id);
    if (!mysqli_stmt_execute($membersStatement)) {
        error_log('Title edit members lookup failed: ' . mysqli_stmt_error($membersStatement));
        mysqli_rollback($conn);
        header('Location: titles-hierarchy.php?status=error&msg=' . urlencode('The title assignments could not be checked.'));
        exit;
    }
    $membersResult = mysqli_stmt_get_result($membersStatement);
    while ($membersResult && $member = mysqli_fetch_assoc($membersResult)) {
        $assignment = validate_title_assignment(
            $conn,
            $titleOrgId,
            $id,
            $member['zone_id'] !== null ? (int) $member['zone_id'] : null,
            $member['subzone_id'] !== null ? (int) $member['subzone_id'] : null,
            (int) $member['id'],
            $scope
        );
        if (!$assignment['valid']) {
            mysqli_rollback($conn);
            header('Location: titles-hierarchy.php?status=error&msg=' . urlencode('Cannot change this title level: ' . $assignment['message']));
            exit;
        }
    }
}

$sql = 'UPDATE titles SET scope = ?, title = ?, level = ?, description = ? WHERE id = ?';
if ($adminRole !== 'super_admin') {
    $sql .= ' AND org_id = ?';
}
$statement = mysqli_prepare($conn, $sql);
if (!$statement) {
    error_log('Title edit update prepare failed: ' . mysqli_error($conn));
    mysqli_rollback($conn);
    header('Location: titles-hierarchy.php?status=error&msg=' . urlencode('The title could not be updated.'));
    exit;
}
if ($adminRole === 'super_admin') {
    mysqli_stmt_bind_param($statement, 'ssisi', $scope, $name, $level, $description, $id);
} else {
    mysqli_stmt_bind_param($statement, 'ssisii', $scope, $name, $level, $description, $id, $orgId);
}
$success = mysqli_stmt_execute($statement) && mysqli_commit($conn);
if (!$success) {
    error_log('Title update failed: ' . mysqli_stmt_error($statement));
    mysqli_rollback($conn);
}
$message = $success ? 'Title updated successfully.' : 'Could not update this title. Its name or hierarchy number may already exist at this level.';

header('Location: titles-hierarchy.php?status=' . ($success ? 'success' : 'error') . '&msg=' . urlencode($message));
exit;
