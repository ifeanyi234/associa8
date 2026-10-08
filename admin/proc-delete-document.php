<?php
require_once "inc/auth.php";
require_once "../inc/db.php";
require_once "../inc/document-file.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: documents.php');
    exit;
}

$documentId = (int) ($_POST['document_id'] ?? 0);
$adminRole = $_SESSION['admin_role'] ?? 'admin';
$orgId = isset($_SESSION['org_id']) && $_SESSION['org_id'] !== null ? (int) $_SESSION['org_id'] : null;
$adminZoneId = isset($_SESSION['admin_zone_id']) && $_SESSION['admin_zone_id'] !== null ? (int) $_SESSION['admin_zone_id'] : null;
$adminSubzoneId = isset($_SESSION['admin_subzone_id']) && $_SESSION['admin_subzone_id'] !== null ? (int) $_SESSION['admin_subzone_id'] : null;
if ($documentId < 1 || ($adminRole !== 'super_admin' && $orgId === null)) {
    header('Location: documents.php?status=error&msg=' . urlencode('Invalid document selected.'));
    exit;
}

$lookupSql = 'SELECT file_path FROM documents WHERE id = ?';
if ($adminRole !== 'super_admin') {
    $lookupSql .= ' AND org_id = ?';
    if ($adminZoneId !== null) {
        $lookupSql .= ' AND ((zone_id IS NULL AND subzone_id IS NULL) OR zone_id = ? OR subzone_id = ?)';
    }
}
$lookupStatement = mysqli_prepare($conn, $lookupSql);
if (!$lookupStatement) {
    header('Location: documents.php?status=error&msg=' . urlencode('The document could not be deleted.'));
    exit;
}
if ($adminRole === 'super_admin') {
    mysqli_stmt_bind_param($lookupStatement, 'i', $documentId);
} elseif ($adminZoneId !== null) {
    mysqli_stmt_bind_param($lookupStatement, 'iiii', $documentId, $orgId, $adminZoneId, $adminSubzoneId);
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
    if ($adminZoneId !== null) {
        $deleteSql .= ' AND ((zone_id IS NULL AND subzone_id IS NULL) OR zone_id = ? OR subzone_id = ?)';
    }
}
$deleteStatement = mysqli_prepare($conn, $deleteSql);
if (!$deleteStatement) {
    header('Location: documents.php?status=error&msg=' . urlencode('The document could not be deleted.'));
    exit;
}
if ($adminRole === 'super_admin') {
    mysqli_stmt_bind_param($deleteStatement, 'i', $documentId);
} elseif ($adminZoneId !== null) {
    mysqli_stmt_bind_param($deleteStatement, 'iiii', $documentId, $orgId, $adminZoneId, $adminSubzoneId);
} else {
    mysqli_stmt_bind_param($deleteStatement, 'ii', $documentId, $orgId);
}
$success = mysqli_stmt_execute($deleteStatement) && mysqli_stmt_affected_rows($deleteStatement) === 1;

if ($success) {
    $filePath = associa8_resolve_document_file($document['file_path']);
    if ($filePath !== null && !unlink($filePath)) {
        error_log('Document record deleted but uploaded file cleanup failed.');
        header('Location: documents.php?action=delete&status=error&msg=' . urlencode('The document record was deleted, but its file could not be removed.'));
        exit;
    }
}

$message = $success ? 'Document deleted successfully.' : 'The document could not be deleted.';
header('Location: documents.php?action=delete&status=' . ($success ? 'success' : 'error') . '&msg=' . urlencode($message));
exit;