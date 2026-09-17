<?php
require_once "inc/auth.php";
require_once "../inc/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admission-management.php');
    exit;
}

$admissionId = (int) ($_POST['admission_id'] ?? 0);
$newStatus = $_POST['status'] ?? '';
$allowedStatuses = ['under_review', 'approved', 'rejected'];

if ($admissionId < 1 || !in_array($newStatus, $allowedStatuses, true)) {
    header('Location: admission-management.php?status=error&msg=' . urlencode('Choose a valid admission status.'));
    exit;
}

$findStatement = mysqli_prepare($conn, 'SELECT status FROM admissions WHERE id = ? LIMIT 1');
if (!$findStatement) {
    header('Location: admission-management.php?status=error&msg=' . urlencode('The admission status service is unavailable.'));
    exit;
}

mysqli_stmt_bind_param($findStatement, 'i', $admissionId);
mysqli_stmt_execute($findStatement);
$admissionResult = mysqli_stmt_get_result($findStatement);
$admission = $admissionResult ? mysqli_fetch_assoc($admissionResult) : null;

if (!$admission) {
    header('Location: admission-management.php?status=error&msg=' . urlencode('The selected admission could not be found.'));
    exit;
}

$currentStatus = $admission['status'];
$validTransition = ($currentStatus === 'pending' && $newStatus === 'under_review')
    || ($currentStatus === 'under_review' && in_array($newStatus, ['approved', 'rejected'], true));

if (!$validTransition) {
    header('Location: admission-management.php?status=error&msg=' . urlencode('That status transition is not allowed from the current stage.'));
    exit;
}

$updateStatement = mysqli_prepare($conn, 'UPDATE admissions SET status = ? WHERE id = ?');
if (!$updateStatement) {
    header('Location: admission-management.php?status=error&msg=' . urlencode('The admission status service is unavailable.'));
    exit;
}

mysqli_stmt_bind_param($updateStatement, 'si', $newStatus, $admissionId);
$success = mysqli_stmt_execute($updateStatement);
$message = $success ? 'Admission moved to ' . ucwords(str_replace('_', ' ', $newStatus)) . '.' : 'The admission status could not be updated.';

header('Location: admission-management.php?status=' . ($success ? 'success' : 'error') . '&msg=' . urlencode($message));
exit;
