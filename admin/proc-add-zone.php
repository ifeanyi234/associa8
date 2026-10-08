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
$orgId = isset($_SESSION['org_id']) && $_SESSION['org_id'] !== null ? (int) $_SESSION['org_id'] : null;

if ($orgId === null || $orgId < 1 || $name === '' || strlen($name) > 100 || strlen($coordinator) > 150) {
    header('Location: add-zone.php?status=error&msg=' . urlencode('Enter a name before saving.'));
    exit;
}

if ($type === 'zone') {
    $statement = mysqli_prepare($conn, 'INSERT INTO zones (org_id, name, coordinator_name) VALUES (?, ?, ?)');
    if ($statement) {
        mysqli_stmt_bind_param($statement, 'iss', $orgId, $name, $coordinator);
    }
} elseif ($type === 'subzone' && $zoneId > 0) {
    $zoneCheck = mysqli_query($conn, "SELECT id FROM zones WHERE id = $zoneId AND org_id = $orgId LIMIT 1");
    if (!$zoneCheck || mysqli_num_rows($zoneCheck) === 0) {
        header('Location: add-zone.php?status=error&type=subzone&msg=' . urlencode('The selected parent zone does not belong to your organization.'));
        exit;
    }
    $statement = mysqli_prepare($conn, 'INSERT INTO subzones (zone_id, name, coordinator_name) VALUES (?, ?, ?)');
    if ($statement) {
        mysqli_stmt_bind_param($statement, 'iss', $zoneId, $name, $coordinator);
    }
} else {
    header('Location: add-zone.php?status=error&msg=' . urlencode('Choose a valid zone action.'));
    exit;
}

$success = isset($statement) && $statement && mysqli_stmt_execute($statement);
$message = $success ? ucfirst($type) . ' created successfully.' : 'Could not save this record. It may already exist.';

header('Location: add-zone.php?status=' . ($success ? 'success' : 'error') . '&type=' . urlencode($type) . '&msg=' . urlencode($message));
exit;
