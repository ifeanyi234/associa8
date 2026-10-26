<?php
require_once "inc/auth.php";
require_once "../inc/db.php";
require_once "../inc/admission-notifications.php";
require_once "../inc/admin-notifications.php";
require_once "inc/admission-number.php";
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: admission-management.php'); exit; }
$name = trim($_POST['applicant_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$guarantorName = trim($_POST['guarantor_name'] ?? '');
$guarantorEmail = trim($_POST['guarantor_email'] ?? '');
$guarantorPhone = trim($_POST['guarantor_phone'] ?? '');
$guarantorRelationship = trim($_POST['guarantor_relationship'] ?? '');
if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $phone === '' || $guarantorName === '' || $guarantorPhone === '' || $guarantorRelationship === '' || ($guarantorEmail !== '' && !filter_var($guarantorEmail, FILTER_VALIDATE_EMAIL))) { header('Location: add-admission.php?status=error&msg=' . urlencode('Complete all applicant and guarantor fields with valid values.')); exit; }
$adminRole = $_SESSION['admin_role'] ?? 'admin';
$orgId = isset($_SESSION['org_id']) && $_SESSION['org_id'] !== null ? (int) $_SESSION['org_id'] : null;
if ($orgId === null || $orgId < 1) {
    header('Location: add-admission.php?status=error&msg=' . urlencode('Your account is not linked to an organization.'));
    exit;
}

$statement = mysqli_prepare($conn, 'INSERT INTO admissions (org_id, application_number, applicant_name, email, phone, guarantor_name, guarantor_email, guarantor_phone, guarantor_relationship) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
if (!$statement) {
	error_log('Admission prepare failed: ' . mysqli_error($conn));
	header('Location: add-admission.php?status=error&msg=' . urlencode('The admissions guarantor fields are not available yet. Import the admissions-guarantor-migration.sql file first.'));
	exit;
}
$success = false;
$admissionId = 0;
for ($attempt = 0; $attempt < 3; $attempt++) {
	try {
		$number = admin_generate_application_number($conn);
	} catch (RuntimeException $exception) {
		error_log('Admission application number generation failed: ' . $exception->getMessage());
		break;
	}
	mysqli_stmt_bind_param($statement, 'issssssss', $orgId, $number, $name, $email, $phone, $guarantorName, $guarantorEmail, $guarantorPhone, $guarantorRelationship);
	if (mysqli_stmt_execute($statement)) {
		$success = true;
		$admissionId = mysqli_insert_id($conn);
		break;
	}
	if (mysqli_stmt_errno($statement) !== 1062) {
		error_log('Admission insert failed: ' . mysqli_stmt_error($statement));
		break;
	}
}
if ($success) {
	notify_admission_status($conn, (int) $admissionId, 'pending', $name, $email);
	admin_notify_organization($conn, $orgId, 'admission', 'New admission application', $name . ' submitted an admission application.', 'admission-management.php');
}
$message = $success ? 'Admission application created successfully. Application number: ' . $number : 'Could not save this application. Please review the details and try again.';
header('Location: admission-management.php?status=' . ($success ? 'success' : 'error') . '&msg=' . urlencode($message));
exit;
