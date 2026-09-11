<?php
require_once "../inc/db.php";
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: cbt-questions.php'); exit; }
$title = trim($_POST['title'] ?? '');
$duration = (int) ($_POST['duration_minutes'] ?? 0);
$passMark = (int) ($_POST['pass_mark'] ?? 0);
$status = $_POST['status'] ?? 'draft';
if ($title === '' || $duration < 1 || $passMark < 0 || $passMark > 100 || !in_array($status, ['draft', 'active'], true)) { header('Location: add-cbt-exam.php?status=error&msg=' . urlencode('Enter valid exam details.')); exit; }
$statement = mysqli_prepare($conn, 'INSERT INTO cbt_exams (title, duration_minutes, pass_mark, status) VALUES (?, ?, ?, ?)');
if (!$statement) { header('Location: add-cbt-exam.php?status=error&msg=' . urlencode('The CBT exam table is not available.')); exit; }
mysqli_stmt_bind_param($statement, 'siis', $title, $duration, $passMark, $status);
$success = mysqli_stmt_execute($statement);
header('Location: cbt-questions.php?status=' . ($success ? 'success' : 'error') . '&msg=' . urlencode($success ? 'CBT exam created successfully.' : 'Could not create the CBT exam.'));
exit;
