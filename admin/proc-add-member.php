<?php
require_once "../inc/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: member-directory.php');
    exit;
}

$firstName = trim($_POST['first_name'] ?? '');
$lastName = trim($_POST['last_name'] ?? '');
$memberCode = trim($_POST['member_code'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$joinedDate = $_POST['joined_date'] ?? '';
$titleId = (int) ($_POST['title_id'] ?? 0);
$zoneId = (int) ($_POST['zone_id'] ?? 0);

if ($firstName === '' || $lastName === '' || $memberCode === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $titleId < 1 || $zoneId < 1 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $joinedDate)) {
    header('Location: add-member.php?status=error&msg=' . urlencode('Complete all member fields with valid values.'));
    exit;
}

$statement = mysqli_prepare($conn, 'INSERT INTO members (member_code, first_name, last_name, email, phone, code, zone_id, title_id, joined_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
mysqli_stmt_bind_param($statement, 'ssssssiis', $memberCode, $firstName, $lastName, $email, $phone, $memberCode, $zoneId, $titleId, $joinedDate);
$success = mysqli_stmt_execute($statement);
$message = $success ? 'Member added successfully.' : 'Could not save this member. The email or member code may already exist.';

header('Location: add-member.php?status=' . ($success ? 'success' : 'error') . '&msg=' . urlencode($message));
exit;
