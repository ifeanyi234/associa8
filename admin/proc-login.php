<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/admin-remember-me.php';

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
$validRoles = ['super_admin', 'admin', 'zonal_admin', 'subzonal_admin'];

if (!$account || !password_verify($password, (string) $account['password']) || !in_array($account['role'], $validRoles, true)) {
    header('Location: index.php?error=1');
    exit;
}
if ((int) $account['org_id'] < 1) {
    header('Location: index.php?error=1');
    exit;
}

if (!session_regenerate_id(true)) {
    error_log('Could not regenerate the PHP session after admin login.');
    header('Location: index.php?error=2');
    exit;
}
$_SESSION['user_id'] = (int) $account['id'];
$_SESSION['username'] = (string) $account['username'];
$_SESSION['org_id'] = (int) $account['org_id'];
$_SESSION['admin_is_super_admin'] = $account['role'] === 'super_admin';
$_SESSION['admin_display_role'] = (string) $account['role'];
$_SESSION['admin_role'] = $account['role'] === 'super_admin' ? 'admin' : (string) $account['role'];
$_SESSION['admin_zone_id'] = isset($account['zone_id']) ? (int) $account['zone_id'] : null;
$_SESSION['admin_subzone_id'] = isset($account['subzone_id']) ? (int) $account['subzone_id'] : null;
if (isset($_POST['remember'])) {
    if (!admin_create_remember_token($conn, (int) $account['id'])) {
        $_SESSION = [];
        session_regenerate_id(true);
        header('Location: index.php?error=2');
        exit;
    }
} elseif (isset($_COOKIE[ADMIN_REMEMBER_COOKIE]) && !admin_revoke_remember_token($conn)) {
    $_SESSION = [];
    session_regenerate_id(true);
    header('Location: index.php?error=2');
    exit;
}
header('Location: dashboard.php');
exit;
