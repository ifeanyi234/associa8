<?php
require_once "../inc/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cbt-questions.php');
    exit;
}

$questionId = (int) ($_POST['question_id'] ?? 0);
if ($questionId < 1) {
    header('Location: cbt-questions.php?status=error&msg=' . urlencode('Invalid question selected.'));
    exit;
}

$statement = mysqli_prepare($conn, 'DELETE FROM cbt_questions WHERE id = ?');
if (!$statement) {
    header('Location: cbt-questions.php?status=error&msg=' . urlencode('The CBT questions table is not available.'));
    exit;
}

mysqli_stmt_bind_param($statement, 'i', $questionId);
$success = mysqli_stmt_execute($statement) && mysqli_stmt_affected_rows($statement) === 1;
$message = $success ? 'Question deleted successfully.' : 'The question could not be deleted.';
header('Location: cbt-questions.php?status=' . ($success ? 'success' : 'error') . '&msg=' . urlencode($message));
exit;
