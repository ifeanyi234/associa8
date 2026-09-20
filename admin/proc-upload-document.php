<?php
require_once "inc/auth.php";
require_once "../inc/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: upload-document.php');
    exit;
}

$visibility = $_POST['visibility'] ?? 'org';
$organizationWide = !empty($_POST['organization_wide']);
$zoneId = isset($_POST['zone_id']) ? (int) $_POST['zone_id'] : 0;
$subzoneId = isset($_POST['subzone_id']) ? (int) $_POST['subzone_id'] : 0;
$title = trim($_POST['document_title'] ?? '');
$fileType = trim($_POST['file_type'] ?? '');

if ($organizationWide || $visibility === 'org') {
    $zoneId = 0;
    $subzoneId = 0;
}

if ($visibility === 'zone' && $zoneId < 1) {
    header('Location: upload-document.php?status=error&msg=' . urlencode('Please select a zone for this document visibility.'));
    exit;
}

if ($visibility === 'subzone') {
    if ($zoneId < 1 || $subzoneId < 1) {
        header('Location: upload-document.php?status=error&msg=' . urlencode('Please choose both a zone and a sub-zone for scoped visibility.'));
        exit;
    }

    $subzoneCheck = mysqli_query($conn, "SELECT id FROM subzones WHERE id = $subzoneId AND zone_id = $zoneId LIMIT 1");
    if (!$subzoneCheck || mysqli_num_rows($subzoneCheck) === 0) {
        header('Location: upload-document.php?status=error&msg=' . urlencode('The selected sub-zone does not belong to the chosen zone.'));
        exit;
    }
}

if ($title === '' || !in_array($fileType, ['JPEG', 'PNG', 'PDF'], true)) {
    header('Location: upload-document.php?status=error&msg=' . urlencode('Complete all fields and choose a valid file type.'));
    exit;
}

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

$safeFileName = uniqid('doc_', true) . '.' . $extension;
$uploadDir = __DIR__ . '/uploads/documents/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$destinationPath = $uploadDir . $safeFileName;
if (!move_uploaded_file($_FILES['document_file']['tmp_name'], $destinationPath)) {
    header('Location: upload-document.php?status=error&msg=' . urlencode('The file could not be saved on the server.'));
    exit;
}

$relativeFilePath = 'uploads/documents/' . $safeFileName;
$fileSize = round($_FILES['document_file']['size'] / 1024) . ' KB';
$uploadedBy = null;
if (!empty($_SESSION['user_id'])) {
    $userCheck = mysqli_query($conn, 'SELECT id FROM users WHERE id = ' . (int) $_SESSION['user_id'] . ' LIMIT 1');
    if ($userCheck && mysqli_num_rows($userCheck) > 0) {
        $uploadedBy = (int) $_SESSION['user_id'];
    }
}
$category = 'General';

$zoneColumnCheck = mysqli_query($conn, "SHOW COLUMNS FROM documents LIKE 'zone_id'");
$subzoneColumnCheck = mysqli_query($conn, "SHOW COLUMNS FROM documents LIKE 'subzone_id'");
if (!$zoneColumnCheck || mysqli_num_rows($zoneColumnCheck) === 0) {
    mysqli_query($conn, "ALTER TABLE documents ADD COLUMN zone_id INT UNSIGNED NULL AFTER uploaded_by");
}
if (!$subzoneColumnCheck || mysqli_num_rows($subzoneColumnCheck) === 0) {
    mysqli_query($conn, "ALTER TABLE documents ADD COLUMN subzone_id INT UNSIGNED NULL AFTER zone_id");
}

if ($zoneId > 0 && $subzoneId > 0) {
    if ($uploadedBy !== null) {
        $statement = mysqli_prepare($conn, 'INSERT INTO documents (title, file_path, file_type, file_size, category, uploaded_by, zone_id, subzone_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        mysqli_stmt_bind_param($statement, 'sssssiii', $title, $relativeFilePath, $fileType, $fileSize, $category, $uploadedBy, $zoneId, $subzoneId);
    } else {
        $statement = mysqli_prepare($conn, 'INSERT INTO documents (title, file_path, file_type, file_size, category, zone_id, subzone_id) VALUES (?, ?, ?, ?, ?, ?, ?)');
        mysqli_stmt_bind_param($statement, 'sssssii', $title, $relativeFilePath, $fileType, $fileSize, $category, $zoneId, $subzoneId);
    }
} elseif ($zoneId > 0) {
    if ($uploadedBy !== null) {
        $statement = mysqli_prepare($conn, 'INSERT INTO documents (title, file_path, file_type, file_size, category, uploaded_by, zone_id) VALUES (?, ?, ?, ?, ?, ?, ?)');
        mysqli_stmt_bind_param($statement, 'sssssi', $title, $relativeFilePath, $fileType, $fileSize, $category, $uploadedBy, $zoneId);
    } else {
        $statement = mysqli_prepare($conn, 'INSERT INTO documents (title, file_path, file_type, file_size, category, zone_id) VALUES (?, ?, ?, ?, ?, ?)');
        mysqli_stmt_bind_param($statement, 'sssssi', $title, $relativeFilePath, $fileType, $fileSize, $category, $zoneId);
    }
} else {
    if ($uploadedBy !== null) {
        $statement = mysqli_prepare($conn, 'INSERT INTO documents (title, file_path, file_type, file_size, category, uploaded_by) VALUES (?, ?, ?, ?, ?, ?)');
        mysqli_stmt_bind_param($statement, 'sssssi', $title, $relativeFilePath, $fileType, $fileSize, $category, $uploadedBy);
    } else {
        $statement = mysqli_prepare($conn, 'INSERT INTO documents (title, file_path, file_type, file_size, category) VALUES (?, ?, ?, ?, ?)');
        mysqli_stmt_bind_param($statement, 'sssss', $title, $relativeFilePath, $fileType, $fileSize, $category);
    }
}

$success = mysqli_stmt_execute($statement);
if (!$success) {
    error_log('Document insert failed: ' . mysqli_error($conn));
}
$message = $success ? 'Document uploaded successfully.' : 'Could not save the document record.';
header('Location: documents.php?action=upload&status=' . ($success ? 'success' : 'error') . '&msg=' . urlencode($message));
exit;