<?php
require_once "inc/auth.php";
require_once "../inc/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: add-zone.php');
    exit;
}

$type = $_POST['type'] ?? '';
$name = trim($_POST['name'] ?? '');
$coordinator = trim($_POST['coordinator_name'] ?? '');
$zoneId = (int) ($_POST['zone_id'] ?? 0);

if ($name === '') {
    header('Location: add-zone.php?status=error&msg=' . urlencode('Enter a name before saving.'));
    exit;
}

$adminRole = $_SESSION['admin_role'] ?? 'admin';
$orgId = isset($_SESSION['org_id']) && $_SESSION['org_id'] !== null ? (int) $_SESSION['org_id'] : null;
if ($adminRole !== 'super_admin' && $orgId === null) {
    header('Location: add-zone.php?status=error&msg=' . urlencode('Your account is not linked to an organization.'));
    exit;
}

if ($type === 'zone') {
    $statement = mysqli_prepare($conn, 'INSERT INTO zones (org_id, name, coordinator_name) VALUES (?, ?, ?)');
    mysqli_stmt_bind_param($statement, 'iss', $orgId, $name, $coordinator);
} elseif ($type === 'subzone' && $zoneId > 0) {
    $statement = mysqli_prepare($conn, 'INSERT INTO subzones (zone_id, name, coordinator_name) VALUES (?, ?, ?)');
    mysqli_stmt_bind_param($statement, 'iss', $zoneId, $name, $coordinator);
} else {
    header('Location: add-zone.php?status=error&msg=' . urlencode('Choose a valid zone action.'));
    exit;
}

$success = mysqli_stmt_execute($statement);
$message = $success ? ucfirst($type) . ' created successfully.' : 'Could not save this record. It may already exist.';

header('Location: add-zone.php?status=' . ($success ? 'success' : 'error') . '&type=' . urlencode($type) . '&msg=' . urlencode($message));
exit;
