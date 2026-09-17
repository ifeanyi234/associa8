<?php
require_once "inc/auth.php";
require_once "../inc/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: documents.php');
    exit;
}

$documentId = (int) ($_POST['document_id'] ?? 0);
if ($documentId < 1) {
    header('Location: documents.php?status=error&msg=' . urlencode('Invalid document selected.'));
    exit;
}

$lookupStatement = mysqli_prepare($conn, 'SELECT file_path FROM documents WHERE id = ?');
mysqli_stmt_bind_param($lookupStatement, 'i', $documentId);
mysqli_stmt_execute($lookupStatement);
$lookupResult = mysqli_stmt_get_result($lookupStatement);
$document = $lookupResult ? mysqli_fetch_assoc($lookupResult) : null;

if (!$document) {
    header('Location: documents.php?status=error&msg=' . urlencode('Document not found.'));
    exit;
}

$deleteStatement = mysqli_prepare($conn, 'DELETE FROM documents WHERE id = ?');
mysqli_stmt_bind_param($deleteStatement, 'i', $documentId);
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