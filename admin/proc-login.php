<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../inc/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}
$username = trim((string) ($_POST['username'] ?? ''));
$password = (string) ($_POST['password'] ?? '');
if ($username === '' || $password === '') {
    header('Location: index.php?error=1');
    exit;
}

$statement = mysqli_prepare(
    $conn,
    "SELECT a.id, a.org_id, a.username, a.password, ai.role, ai.zone_id, ai.subzone_id
     FROM `acc-info` a
     INNER JOIN `admin-info` ai ON ai.acc_id = a.id AND ai.org_id = a.org_id
     WHERE (a.username = ? OR ai.email = ?) AND a.status = 'active'
     ORDER BY (a.username = ?) DESC
     LIMIT 1"
);
if (!$statement) {
    error_log('Admin login lookup could not be prepared: ' . mysqli_error($conn));
    header('Location: index.php?error=1');
    exit;
}
mysqli_stmt_bind_param($statement, 'sss', $username, $username, $username);
if (!mysqli_stmt_execute($statement)) {
    error_log('Admin login lookup failed: ' . mysqli_stmt_error($statement));
    mysqli_stmt_close($statement);
    header('Location: index.php?error=1');
    exit;
}
$result = mysqli_stmt_get_result($statement);
$account = $result ? mysqli_fetch_assoc($result) : null;
mysqli_stmt_close($statement);
$validRoles = ['super_admin', 'admin', 'manager', 'staff'];

if (!$account || !password_verify($password, (string) $account['password']) || !in_array($account['role'], $validRoles, true)) {
    header('Location: index.php?error=1');
    exit;
}
if ($account['role'] !== 'super_admin' && (int) $account['org_id'] < 1) {
    header('Location: index.php?error=1');
    exit;
}

session_regenerate_id(true);
$_SESSION['user_id'] = (int) $account['id'];
$_SESSION['username'] = (string) $account['username'];
$_SESSION['org_id'] = (int) $account['org_id'];
$_SESSION['admin_role'] = (string) $account['role'];
$_SESSION['admin_zone_id'] = isset($account['zone_id']) ? (int) $account['zone_id'] : null;
$_SESSION['admin_subzone_id'] = isset($account['subzone_id']) ? (int) $account['subzone_id'] : null;
header('Location: dashboard.php');
exit;
