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
$annualDuesInput = trim((string) ($_POST['annual_dues'] ?? ''));
$annualDues = $annualDuesInput === '' ? '0.00' : $annualDuesInput;
$adminRole = $_SESSION['admin_role'] ?? 'admin';
$orgId = isset($_SESSION['org_id']) && $_SESSION['org_id'] !== null ? (int) $_SESSION['org_id'] : null;

if (
    $id < 1 || $name === '' || strlen($name) > 100 || strlen($coordinator) > 150
    || ($annualDuesInput !== '' && (!preg_match('/^\d{1,10}(?:\.\d{1,2})?$/', $annualDuesInput) || (float) $annualDuesInput > 9999999999.99))
    || ($adminRole !== 'super_admin' && ($orgId === null || $orgId < 1))
) {
    header('Location: zones.php?status=error&msg=' . urlencode('Enter valid zone details.'));
    exit;
}

$sql = 'UPDATE zones SET name = ?, coordinator_name = ?, annual_dues = ? WHERE id = ?';
if ($adminRole !== 'super_admin') {
    $sql .= ' AND org_id = ?';
}
$statement = mysqli_prepare($conn, $sql);
if ($statement && $adminRole === 'super_admin') {
    mysqli_stmt_bind_param($statement, 'sssi', $name, $coordinator, $annualDues, $id);
} elseif ($statement) {
    mysqli_stmt_bind_param($statement, 'sssii', $name, $coordinator, $annualDues, $id, $orgId);
}
$success = $statement && mysqli_stmt_execute($statement);
$message = $success ? 'Zone updated successfully.' : 'Could not update this zone. The name may already exist.';

header('Location: zones.php?status=' . ($success ? 'success' : 'error') . '&msg=' . urlencode($message));
exit;
