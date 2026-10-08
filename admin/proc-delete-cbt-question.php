<?php
require_once "inc/auth.php";
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

$adminRole = $_SESSION['admin_role'];
$orgId = isset($_SESSION['org_id']) ? (int) $_SESSION['org_id'] : null;
$scopeSql = $adminRole === 'super_admin' ? '' : ' AND e.org_id = ?';
$lookup = mysqli_prepare($conn, 'SELECT q.exam_id, e.status FROM cbt_questions q INNER JOIN cbt_exams e ON e.id = q.exam_id WHERE q.id = ?' . $scopeSql . ' FOR UPDATE');
if (!$lookup) {
    error_log('CBT question delete lookup prepare failed: ' . mysqli_error($conn));
    header('Location: cbt-questions.php?status=error&msg=' . urlencode('The CBT question could not be checked.'));
    exit;
}
if ($adminRole === 'super_admin') {
    mysqli_stmt_bind_param($lookup, 'i', $questionId);
} else {
    mysqli_stmt_bind_param($lookup, 'ii', $questionId, $orgId);
}

mysqli_begin_transaction($conn);
if (!mysqli_stmt_execute($lookup)) {
    error_log('CBT question delete lookup failed: ' . mysqli_stmt_error($lookup));
    mysqli_rollback($conn);
    header('Location: cbt-questions.php?status=error&msg=' . urlencode('The CBT question could not be checked.'));
    exit;
}
$lookupResult = mysqli_stmt_get_result($lookup);
$question = $lookupResult ? mysqli_fetch_assoc($lookupResult) : null;
if (!$question) {
    if (!$lookupResult) {
        error_log('CBT question delete lookup result failed: ' . mysqli_stmt_error($lookup));
    }
    mysqli_rollback($conn);
    header('Location: cbt-questions.php?status=error&msg=' . urlencode('Question not found or unavailable in your organization.'));
    exit;
}

$examId = (int) $question['exam_id'];
$usage = mysqli_prepare($conn, 'SELECT EXISTS(SELECT 1 FROM admissions WHERE cbt_exam_id = ?) AS has_applicants, EXISTS(SELECT 1 FROM cbt_results WHERE exam_id = ?) AS has_results');
if (!$usage) {
    error_log('CBT question usage check prepare failed: ' . mysqli_error($conn));
    mysqli_rollback($conn);
    header('Location: cbt-questions.php?status=error&msg=' . urlencode('The CBT question could not be checked.'));
    exit;
}
mysqli_stmt_bind_param($usage, 'ii', $examId, $examId);
if (!mysqli_stmt_execute($usage)) {
    error_log('CBT question usage check failed: ' . mysqli_stmt_error($usage));
    mysqli_rollback($conn);
    header('Location: cbt-questions.php?status=error&msg=' . urlencode('The CBT question could not be checked.'));
    exit;
}
$usageResult = mysqli_stmt_get_result($usage);
$usageRow = $usageResult ? mysqli_fetch_assoc($usageResult) : null;
if (!$usageRow) {
    error_log('CBT question usage result failed: ' . mysqli_stmt_error($usage));
    mysqli_rollback($conn);
    header('Location: cbt-questions.php?status=error&msg=' . urlencode('The CBT question could not be checked.'));
    exit;
}
if ($question['status'] !== 'draft' || (int) $usageRow['has_applicants'] === 1 || (int) $usageRow['has_results'] === 1) {
    mysqli_rollback($conn);
    header('Location: cbt-questions.php?status=error&msg=' . urlencode('Questions can only be removed from draft exams with no applicants or results.'));
    exit;
}

$delete = mysqli_prepare($conn, 'DELETE FROM cbt_questions WHERE id = ? AND exam_id = ?');
if (!$delete) {
    error_log('CBT question delete prepare failed: ' . mysqli_error($conn));
    mysqli_rollback($conn);
    header('Location: cbt-questions.php?status=error&msg=' . urlencode('The CBT question could not be deleted.'));
    exit;
}
mysqli_stmt_bind_param($delete, 'ii', $questionId, $examId);
if (!mysqli_stmt_execute($delete) || mysqli_stmt_affected_rows($delete) !== 1) {
    error_log('CBT question delete failed: ' . mysqli_stmt_error($delete));
    mysqli_rollback($conn);
    header('Location: cbt-questions.php?status=error&msg=' . urlencode('The CBT question could not be deleted.'));
    exit;
}
mysqli_commit($conn);
header('Location: cbt-questions.php?status=success&msg=' . urlencode('Question deleted successfully.'));
exit;
