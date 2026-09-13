<?php
require_once "inc/auth.php";
require_once "../inc/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: upload-document.php');
    exit;
}

$title = trim($_POST['document_title'] ?? '');
$ownerName = trim($_POST['document_owner'] ?? '');
$fileType = trim($_POST['file_type'] ?? '');

// Basic field validation before we even look at the uploaded file
if ($title === '' || $ownerName === '' || !in_array($fileType, ['JPEG', 'PNG', 'PDF'], true)) {
    header('Location: upload-document.php?status=error&msg=' . urlencode('Complete all fields and choose a valid file type.'));
    exit;
}

// Make sure a file was actually uploaded and there were no upload errors
if (!isset($_FILES['document_file']) || $_FILES['document_file']['error'] !== UPLOAD_ERR_OK) {
    header('Location: upload-document.php?status=error&msg=' . urlencode('Please choose a file to upload.'));
    exit;
}

$allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
$originalName = $_FILES['document_file']['name'];
$extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

if (!in_array($extension, $allowedExtensions, true)) {
    header('Location: upload-document.php?status=error&msg=' . urlencode('Only JPEG, PNG, or PDF files are allowed.'));
    exit;
}

// Build a safe, unique filename so uploads never overwrite each other
$safeFileName = uniqid('doc_', true) . '.' . $extension;
$uploadDir = __DIR__ . '/uploads/documents/';

// Create the folder the first time this runs, if it doesn't exist yet
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$destinationPath = $uploadDir . $safeFileName;

if (!move_uploaded_file($_FILES['document_file']['tmp_name'], $destinationPath)) {
    header('Location: upload-document.php?status=error&msg=' . urlencode('The file could not be saved on the server.'));
    exit;
}

// Store the relative path (not the full server path) so it can be linked to later
$relativeFilePath = 'uploads/documents/' . $safeFileName;
$fileSize = round($_FILES['document_file']['size'] / 1024) . ' KB';

$statement = mysqli_prepare($conn, 'INSERT INTO documents (title, owner_name, file_path, file_type, file_size) VALUES (?, ?, ?, ?, ?)');
mysqli_stmt_bind_param($statement, 'sssss', $title, $ownerName, $relativeFilePath, $fileType, $fileSize);
$success = mysqli_stmt_execute($statement);

$message = $success ? 'Document uploaded successfully.' : 'Could not save the document record.';
header('Location: documents.php?action=upload&status=' . ($success ? 'success' : 'error') . '&msg=' . urlencode($message));
exit;