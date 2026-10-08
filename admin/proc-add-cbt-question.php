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

$scopeSql = $adminRole === 'super_admin' ? '' : ' AND org_id = ?';
$examCheck = mysqli_prepare($conn, "SELECT id FROM cbt_exams WHERE id = ? AND status = 'draft'" . $scopeSql . ' FOR UPDATE');
if (!$examCheck) {
    error_log('CBT exam check prepare failed: ' . mysqli_error($conn));
    header('Location: add-cbt-question.php?status=error&msg=' . urlencode('The selected draft exam could not be checked.') . '&exam_id=' . $examId);
    exit;
}
if ($adminRole === 'super_admin') {
    mysqli_stmt_bind_param($examCheck, 'i', $examId);
} else {
    mysqli_stmt_bind_param($examCheck, 'ii', $examId, $orgId);
}
mysqli_begin_transaction($conn);
if (!mysqli_stmt_execute($examCheck)) {
    error_log('CBT draft exam check failed: ' . mysqli_stmt_error($examCheck));
    mysqli_rollback($conn);
    header('Location: add-cbt-question.php?status=error&msg=' . urlencode('The selected draft exam could not be checked.') . '&exam_id=' . $examId);
    exit;
}
$examResult = mysqli_stmt_get_result($examCheck);
if (!$examResult) {
    error_log('CBT draft exam result failed: ' . mysqli_stmt_error($examCheck));
    mysqli_rollback($conn);
    header('Location: add-cbt-question.php?status=error&msg=' . urlencode('The selected draft exam could not be checked.') . '&exam_id=' . $examId);
    exit;
}
if (mysqli_num_rows($examResult) !== 1) {
    mysqli_rollback($conn);
    header('Location: add-cbt-question.php?status=error&msg=' . urlencode('Questions can only be added to a draft exam in your organization.') . '&exam_id=' . $examId);
    exit;
}
$statement = mysqli_prepare($conn, 'INSERT INTO cbt_questions (exam_id, question_text, option_a, option_b, option_c, option_d, option_e, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
if (!$statement) {
    mysqli_rollback($conn);
    error_log('CBT question insert prepare failed: ' . mysqli_error($conn));
    header('Location: add-cbt-question.php?status=error&msg=' . urlencode('The CBT questions table is not available.') . '&exam_id=' . $examId);
    exit;
}
mysqli_stmt_bind_param($statement, 'isssssss', $examId, $question, $options['a'], $options['b'], $options['c'], $options['d'], $options['e'], $correct);
$success = mysqli_stmt_execute($statement);
if ($success) {
    mysqli_commit($conn);
} else {
    mysqli_rollback($conn);
    error_log('CBT question insert failed: ' . mysqli_stmt_error($statement));
}
header('Location: add-cbt-question.php?status=' . ($success ? 'success' : 'error') . '&msg=' . urlencode($success ? 'Question added successfully. Add another question below.' : 'Could not save the question.') . '&exam_id=' . $examId);
exit;
