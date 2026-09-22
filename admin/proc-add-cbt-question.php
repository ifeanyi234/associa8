<?php
require_once "inc/auth.php";
require_once "../inc/db.php";
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: add-cbt-question.php'); exit; }
$examId = (int) ($_POST['exam_id'] ?? 0);
$question = trim($_POST['question_text'] ?? '');
$options = [];
foreach (['a', 'b', 'c', 'd', 'e'] as $option) { $options[$option] = trim($_POST['option_' . $option] ?? ''); }
$correct = $_POST['correct_option'] ?? '';
if ($examId < 1 || $question === '' || $options['a'] === '' || $options['b'] === '' || $options['c'] === '' || $options['d'] === '' || !in_array($correct, ['A', 'B', 'C', 'D', 'E'], true) || ($correct === 'E' && $options['e'] === '')) { header('Location: add-cbt-question.php?status=error&msg=' . urlencode('Complete the question, options, and correct answer.') . '&exam_id=' . $examId); exit; }
$adminRole = $_SESSION['admin_role'] ?? 'admin';
$orgId = isset($_SESSION['org_id']) && $_SESSION['org_id'] !== null ? (int) $_SESSION['org_id'] : null;
if ($adminRole !== 'super_admin' && $orgId === null) {
    header('Location: add-cbt-question.php?status=error&msg=' . urlencode('Your account is not linked to an organization.') . '&exam_id=' . $examId);
    exit;
}

$examSql = 'SELECT id FROM cbt_exams WHERE id = ' . $examId . " AND status <> 'closed'";
if ($adminRole !== 'super_admin') {
    $examSql .= ' AND org_id = ' . (int) $orgId;
}
$examSql .= ' LIMIT 1';
$examCheck = mysqli_query($conn, $examSql);
if (!$examCheck || mysqli_num_rows($examCheck) !== 1) {
    header('Location: add-cbt-question.php?status=error&msg=' . urlencode('The selected exam is not available in your organization.') . '&exam_id=' . $examId);
    exit;
}

$statement = mysqli_prepare($conn, 'INSERT INTO cbt_questions (exam_id, question_text, option_a, option_b, option_c, option_d, option_e, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
if (!$statement) { header('Location: add-cbt-question.php?status=error&msg=' . urlencode('The CBT questions table is not available.') . '&exam_id=' . $examId); exit; }
mysqli_stmt_bind_param($statement, 'isssssss', $examId, $question, $options['a'], $options['b'], $options['c'], $options['d'], $options['e'], $correct);
$success = mysqli_stmt_execute($statement);
header('Location: add-cbt-question.php?status=' . ($success ? 'success' : 'error') . '&msg=' . urlencode($success ? 'Question added successfully. Add another question below.' : 'Could not save the question.') . '&exam_id=' . $examId);
exit;
