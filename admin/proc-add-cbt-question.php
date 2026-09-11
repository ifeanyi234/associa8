<?php
require_once "../inc/db.php";
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: cbt-questions.php'); exit; }
$examId = (int) ($_POST['exam_id'] ?? 0);
$question = trim($_POST['question_text'] ?? '');
$options = [];
foreach (['a', 'b', 'c', 'd', 'e'] as $option) { $options[$option] = trim($_POST['option_' . $option] ?? ''); }
$correct = $_POST['correct_option'] ?? '';
if ($examId < 1 || $question === '' || $options['a'] === '' || $options['b'] === '' || $options['c'] === '' || $options['d'] === '' || !in_array($correct, ['A', 'B', 'C', 'D', 'E'], true) || ($correct === 'E' && $options['e'] === '')) { header('Location: add-cbt-question.php?status=error&msg=' . urlencode('Complete the question, options, and correct answer.')); exit; }
$statement = mysqli_prepare($conn, 'INSERT INTO cbt_questions (exam_id, question_text, option_a, option_b, option_c, option_d, option_e, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
if (!$statement) { header('Location: add-cbt-question.php?status=error&msg=' . urlencode('The CBT questions table is not available.')); exit; }
mysqli_stmt_bind_param($statement, 'isssssss', $examId, $question, $options['a'], $options['b'], $options['c'], $options['d'], $options['e'], $correct);
$success = mysqli_stmt_execute($statement);
header('Location: cbt-questions.php?status=' . ($success ? 'success' : 'error') . '&msg=' . urlencode($success ? 'Question added successfully.' : 'Could not save the question.'));
exit;
