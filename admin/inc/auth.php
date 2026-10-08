<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'], $_SESSION['admin_role']) || !ctype_digit((string) $_SESSION['user_id']) || (int) $_SESSION['user_id'] < 1) {
    session_unset();
    session_destroy();
    header('Location: index.php?error=unauthorized');
    exit;
}

$adminRole = $_SESSION['admin_role'];
if (!is_string($adminRole) || !in_array($adminRole, ['super_admin', 'admin', 'manager', 'staff'], true)) {
    session_unset();
    session_destroy();
    header('Location: index.php?error=invalid-role');
    exit;
}

if ($adminRole !== 'super_admin' && (!isset($_SESSION['org_id']) || !ctype_digit((string) $_SESSION['org_id']) || (int) $_SESSION['org_id'] < 1)) {
    session_unset();
    session_destroy();
    header('Location: index.php?error=invalid-org');
    exit;
}