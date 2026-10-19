<?php
require_once 'inc/auth.php';
require_once '../inc/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: titles-hierarchy.php');
    exit;
}

$token = $_POST['csrf_token'] ?? '';
$sessionToken = $_SESSION['title_delete_csrf'] ?? '';
if (!is_string($token) || !is_string($sessionToken) || $sessionToken === '' || !hash_equals($sessionToken, $token)) {
    header('Location: titles-hierarchy.php?status=error&msg=' . urlencode('Your request expired. Reload the page and try again.'));
    exit;
}

$titleId = filter_var($_POST['title_id'] ?? null, FILTER_VALIDATE_INT);
$adminRole = (string) ($_SESSION['admin_role'] ?? 'admin');
$orgId = isset($_SESSION['org_id']) ? (int) $_SESSION['org_id'] : 0;
if (!$titleId || $titleId < 1 || ($adminRole !== 'super_admin' && $orgId < 1)) {
    header('Location: titles-hierarchy.php?status=error&msg=' . urlencode('Choose a valid title.'));
    exit;
}

$sql = 'DELETE FROM titles WHERE id = ?';
if ($adminRole !== 'super_admin') {
    $sql .= ' AND org_id = ?';
}
$statement = mysqli_prepare($conn, $sql);
if (!$statement) {
    error_log('Title deletion could not be prepared: ' . mysqli_error($conn));
    header('Location: titles-hierarchy.php?status=error&msg=' . urlencode('The title could not be deleted. Please try again.'));
    exit;
}
if ($adminRole === 'super_admin') {
    mysqli_stmt_bind_param($statement, 'i', $titleId);
} else {
    mysqli_stmt_bind_param($statement, 'ii', $titleId, $orgId);
}

$deleted = mysqli_stmt_execute($statement) && mysqli_stmt_affected_rows($statement) === 1;
if (!$deleted) {
    error_log('Title deletion failed: ' . mysqli_stmt_error($statement));
}
mysqli_stmt_close($statement);

if (!$deleted) {
    header('Location: titles-hierarchy.php?status=error&msg=' . urlencode('The title could not be deleted. It may no longer be available.'));
    exit;
}

header('Location: titles-hierarchy.php?status=success&action=deleted');
exit;
