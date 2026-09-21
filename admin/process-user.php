<?php
require_once 'inc/auth.php';
require_once '../inc/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: user-controls.php');
    exit;
}

$firstName = trim($_POST['first_name'] ?? '');
$lastName = trim($_POST['last_name'] ?? '');
$email = strtolower(trim($_POST['email'] ?? ''));
$role = $_POST['role'] ?? 'staff';
$allowedRoles = ['super_admin', 'admin', 'manager', 'staff'];
if (!in_array($role, $allowedRoles, true)) {
    $role = 'staff';
}

if ($firstName === '' || $lastName === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: user-controls.php?status=error&msg=' . urlencode('Please enter a valid name and email address.'));
    exit;
}

$modules = $_POST['modules'] ?? [];
$allowedModules = ['cbt_management', 'members', 'admission', 'document', 'attendance', 'finance'];
$moduleList = [];
foreach ((array) $modules as $module) {
    if (in_array($module, $allowedModules, true)) {
        $moduleList[] = $module;
    }
}
$moduleList = array_values(array_unique($moduleList));

$escapedEmail = mysqli_real_escape_string($conn, $email);
$existingUser = mysqli_query($conn, "SELECT id FROM users WHERE email = '$escapedEmail' LIMIT 1");
if ($existingUser && mysqli_num_rows($existingUser) > 0) {
    header('Location: user-controls.php?status=error&msg=' . urlencode('This email is already assigned to another user.'));
    exit;
}

$defaultPassword = 'Welcome123!';
$hashedPassword = password_hash($defaultPassword, PASSWORD_DEFAULT);

mysqli_begin_transaction($conn);

$insertUser = mysqli_prepare($conn, 'INSERT INTO users (first_name, last_name, email, password, role, status) VALUES (?, ?, ?, ?, ?, "active")');
if (!$insertUser) {
    mysqli_rollback($conn);
    header('Location: user-controls.php?status=error&msg=' . urlencode('User table is not ready yet. Please check the users schema.'));
    exit;
}

mysqli_stmt_bind_param($insertUser, 'sssss', $firstName, $lastName, $email, $hashedPassword, $role);
if (!mysqli_stmt_execute($insertUser)) {
    mysqli_rollback($conn);
    header('Location: user-controls.php?status=error&msg=' . urlencode('The user record could not be created.'));
    exit;
}

$userId = mysqli_insert_id($conn);
if ($moduleList) {
    $permissionSql = 'INSERT INTO user_module_permissions (user_id, module_key) VALUES (?, ?)';
    $permissionStmt = mysqli_prepare($conn, $permissionSql);
    if (!$permissionStmt) {
        mysqli_rollback($conn);
        header('Location: user-controls.php?status=error&msg=' . urlencode('The permission table is missing or invalid.'));
        exit;
    }

    foreach ($moduleList as $moduleKey) {
        mysqli_stmt_bind_param($permissionStmt, 'is', $userId, $moduleKey);
        if (!mysqli_stmt_execute($permissionStmt)) {
            mysqli_rollback($conn);
            header('Location: user-controls.php?status=error&msg=' . urlencode('Could not save user permissions.'));
            exit;
        }
    }
}

mysqli_commit($conn);
header('Location: user-controls.php?status=success&msg=' . urlencode('User added successfully. Default password: Welcome123!'));
exit;
