<?php
require_once "inc/auth.php";
require_once "../inc/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: documents.php');
    exit;
}

$documentId = (int) ($_POST['document_id'] ?? 0);
$adminRole = $_SESSION['admin_role'] ?? 'admin';
$orgId = isset($_SESSION['org_id']) && $_SESSION['org_id'] !== null ? (int) $_SESSION['org_id'] : null;
if ($documentId < 1 || ($adminRole !== 'super_admin' && $orgId === null)) {
    header('Location: documents.php?status=error&msg=' . urlencode('Invalid document selected.'));
    exit;
}

$lookupSql = 'SELECT file_path FROM documents WHERE id = ?';
if ($adminRole !== 'super_admin') {
    $lookupSql .= ' AND org_id = ?';
}
$lookupStatement = mysqli_prepare($conn, $lookupSql);
if (!$lookupStatement) {
    header('Location: documents.php?status=error&msg=' . urlencode('The document could not be deleted.'));
    exit;
}
if ($adminRole === 'super_admin') {
    mysqli_stmt_bind_param($lookupStatement, 'i', $documentId);
} else {
    mysqli_stmt_bind_param($lookupStatement, 'ii', $documentId, $orgId);
}
if (!mysqli_stmt_execute($lookupStatement)) {
    header('Location: documents.php?status=error&msg=' . urlencode('The document could not be deleted.'));
    exit;
}
$lookupResult = mysqli_stmt_get_result($lookupStatement);
$document = $lookupResult ? mysqli_fetch_assoc($lookupResult) : null;

if (!$document) {
    header('Location: documents.php?status=error&msg=' . urlencode('Document not found.'));
    exit;
}

$deleteSql = 'DELETE FROM documents WHERE id = ?';
if ($adminRole !== 'super_admin') {
    $deleteSql .= ' AND org_id = ?';
}
$deleteStatement = mysqli_prepare($conn, $deleteSql);
if (!$deleteStatement) {
    header('Location: documents.php?status=error&msg=' . urlencode('The document could not be deleted.'));
    exit;
}
if ($adminRole === 'super_admin') {
    mysqli_stmt_bind_param($deleteStatement, 'i', $documentId);
} else {
    mysqli_stmt_bind_param($deleteStatement, 'ii', $documentId, $orgId);
}
$success = mysqli_stmt_execute($deleteStatement) && mysqli_stmt_affected_rows($deleteStatement) === 1;

if ($success) {
    $filePath = __DIR__ . '/' . $document['file_path'];
    if (is_file($filePath)) {
        unlink($filePath);
    }
}

$message = $success ? 'Document deleted successfully.' : 'The document could not be deleted.';
header('Location: documents.php?action=delete&status=' . ($success ? 'success' : 'error') . '&msg=' . urlencode($message));
exit;