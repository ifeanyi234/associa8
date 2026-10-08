<?php
require_once "inc/auth.php";
require_once "../inc/db.php";
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: cbt-questions.php'); exit; }
$title = trim($_POST['title'] ?? '');
$duration = (int) ($_POST['duration_minutes'] ?? 0);
$passMark = (int) ($_POST['pass_mark'] ?? 0);
if ($title === '' || $duration < 1 || $passMark < 0 || $passMark > 100) { header('Location: add-cbt-exam.php?status=error&msg=' . urlencode('Enter valid exam details.')); exit; }
$adminRole = $_SESSION['admin_role'] ?? 'admin';
$orgId = isset($_SESSION['org_id']) && $_SESSION['org_id'] !== null ? (int) $_SESSION['org_id'] : null;
if ($orgId === null || $orgId < 1) {
    header('Location: add-cbt-exam.php?status=error&msg=' . urlencode('Your account is not linked to an organization.'));
    exit;
}

$statement = mysqli_prepare($conn, 'INSERT INTO cbt_exams (org_id, title, duration_minutes, pass_mark, status) VALUES (?, ?, ?, ?, \'draft\')');
if (!$statement) { header('Location: add-cbt-exam.php?status=error&msg=' . urlencode('The CBT exam table is not available.')); exit; }
mysqli_stmt_bind_param($statement, 'isii', $orgId, $title, $duration, $passMark);
$success = mysqli_stmt_execute($statement);
header('Location: cbt-questions.php?status=' . ($success ? 'success' : 'error') . '&msg=' . urlencode($success ? 'CBT exam created as a draft. Add questions, then activate it when it is ready.' : 'Could not create the CBT exam.'));
exit;
