<?php
require_once "inc/auth.php";
require_once "../inc/db.php";
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: admission-management.php'); exit; }
$number = trim($_POST['application_number'] ?? '');
$name = trim($_POST['applicant_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$guarantorName = trim($_POST['guarantor_name'] ?? '');
$guarantorEmail = trim($_POST['guarantor_email'] ?? '');
$guarantorPhone = trim($_POST['guarantor_phone'] ?? '');
$guarantorRelationship = trim($_POST['guarantor_relationship'] ?? '');
if ($number === '' || $name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $phone === '' || $guarantorName === '' || $guarantorPhone === '' || $guarantorRelationship === '' || ($guarantorEmail !== '' && !filter_var($guarantorEmail, FILTER_VALIDATE_EMAIL))) { header('Location: add-admission.php?status=error&msg=' . urlencode('Complete all applicant and guarantor fields with valid values.')); exit; }
$adminRole = $_SESSION['admin_role'] ?? 'admin';
$orgId = isset($_SESSION['org_id']) && $_SESSION['org_id'] !== null ? (int) $_SESSION['org_id'] : null;
if ($adminRole !== 'super_admin' && $orgId === null) {
    header('Location: add-admission.php?status=error&msg=' . urlencode('Your account is not linked to an organization.'));
    exit;
}

$statement = mysqli_prepare($conn, 'INSERT INTO admissions (org_id, application_number, applicant_name, email, phone, guarantor_name, guarantor_email, guarantor_phone, guarantor_relationship) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
if (!$statement) {
	error_log('Admission prepare failed: ' . mysqli_error($conn));
	header('Location: add-admission.php?status=error&msg=' . urlencode('The admissions guarantor fields are not available yet. Import the admissions-guarantor-migration.sql file first.'));
	exit;
}
mysqli_stmt_bind_param($statement, 'issssssss', $orgId, $number, $name, $email, $phone, $guarantorName, $guarantorEmail, $guarantorPhone, $guarantorRelationship);
$success = mysqli_stmt_execute($statement);
$message = $success ? 'Admission application created successfully.' : 'Could not save this application. The application number may already exist.';
header('Location: admission-management.php?status=' . ($success ? 'success' : 'error') . '&msg=' . urlencode($message));
exit;
