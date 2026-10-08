<?php
require_once 'inc/auth.php';
require_once '../inc/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cbt-questions.php');
    exit;
}

$examId = (int) ($_POST['exam_id'] ?? 0);
$adminRole = $_SESSION['admin_role'];
$orgId = isset($_SESSION['org_id']) ? (int) $_SESSION['org_id'] : null;
if ($examId < 1) {
    header('Location: cbt-questions.php?status=error&msg=' . urlencode('Select a valid exam.'));
    exit;
}

$scopeSql = $adminRole === 'super_admin' ? '' : ' AND org_id = ?';
$lock = mysqli_prepare($conn, 'SELECT id, status FROM cbt_exams WHERE id = ?' . $scopeSql . ' FOR UPDATE');
if (!$lock) {
    error_log('CBT exam activation lock prepare failed: ' . mysqli_error($conn));
    header('Location: cbt-questions.php?status=error&msg=' . urlencode('The CBT exam could not be checked.'));
    exit;
}
if ($adminRole === 'super_admin') {
    mysqli_stmt_bind_param($lock, 'i', $examId);
} else {
    mysqli_stmt_bind_param($lock, 'ii', $examId, $orgId);
}

mysqli_begin_transaction($conn);
if (!mysqli_stmt_execute($lock)) {
    error_log('CBT exam activation lock failed: ' . mysqli_stmt_error($lock));
    mysqli_rollback($conn);
    header('Location: cbt-questions.php?status=error&msg=' . urlencode('The CBT exam could not be checked.'));
    exit;
}
$examResult = mysqli_stmt_get_result($lock);
$exam = $examResult ? mysqli_fetch_assoc($examResult) : null;
if (!$exam) {
    if (!$examResult) {
        error_log('CBT exam activation result failed: ' . mysqli_stmt_error($lock));
    }
    mysqli_rollback($conn);
    header('Location: cbt-questions.php?status=error&msg=' . urlencode('The draft exam was not found in your organization.'));
    exit;
}
if ($exam['status'] !== 'draft') {
    mysqli_rollback($conn);
    header('Location: cbt-questions.php?status=error&msg=' . urlencode('Only draft exams can be activated.'));
    exit;
}

$readiness = mysqli_prepare($conn, "SELECT COUNT(*) AS total, SUM(CASE WHEN question_text <> '' AND option_a <> '' AND option_b <> '' AND option_c <> '' AND option_d <> '' AND correct_option IN ('A','B','C','D','E') AND (correct_option <> 'E' OR option_e <> '') THEN 1 ELSE 0 END) AS ready FROM cbt_questions WHERE exam_id = ?");
if (!$readiness) {
    error_log('CBT exam readiness prepare failed: ' . mysqli_error($conn));
    mysqli_rollback($conn);
    header('Location: cbt-questions.php?status=error&msg=' . urlencode('The exam questions could not be checked.'));
    exit;
}
mysqli_stmt_bind_param($readiness, 'i', $examId);
if (!mysqli_stmt_execute($readiness)) {
    error_log('CBT exam readiness query failed: ' . mysqli_stmt_error($readiness));
    mysqli_rollback($conn);
    header('Location: cbt-questions.php?status=error&msg=' . urlencode('The exam questions could not be checked.'));
    exit;
}
$readinessResult = mysqli_stmt_get_result($readiness);
$questionCounts = $readinessResult ? mysqli_fetch_assoc($readinessResult) : null;
if (!$questionCounts) {
    error_log('CBT exam readiness result failed: ' . mysqli_stmt_error($readiness));
    mysqli_rollback($conn);
    header('Location: cbt-questions.php?status=error&msg=' . urlencode('The exam questions could not be checked.'));
    exit;
}
$questionCount = (int) $questionCounts['total'];
if ($questionCount < 1 || (int) $questionCounts['ready'] !== $questionCount) {
    mysqli_rollback($conn);
    header('Location: cbt-questions.php?status=error&msg=' . urlencode('Add at least one complete question before activating this exam.'));
    exit;
}

$usage = mysqli_prepare($conn, 'SELECT EXISTS(SELECT 1 FROM admissions WHERE cbt_exam_id = ?) AS has_applicants, EXISTS(SELECT 1 FROM cbt_results WHERE exam_id = ?) AS has_results');
if (!$usage) {
    error_log('CBT exam usage check prepare failed: ' . mysqli_error($conn));
    mysqli_rollback($conn);
    header('Location: cbt-questions.php?status=error&msg=' . urlencode('The exam usage could not be checked.'));
    exit;
}
mysqli_stmt_bind_param($usage, 'ii', $examId, $examId);
if (!mysqli_stmt_execute($usage)) {
    error_log('CBT exam usage check failed: ' . mysqli_stmt_error($usage));
    mysqli_rollback($conn);
    header('Location: cbt-questions.php?status=error&msg=' . urlencode('The exam usage could not be checked.'));
    exit;
}
$usageResult = mysqli_stmt_get_result($usage);
$usageRow = $usageResult ? mysqli_fetch_assoc($usageResult) : null;
if (!$usageRow || (int) $usageRow['has_applicants'] === 1 || (int) $usageRow['has_results'] === 1) {
    if (!$usageRow) {
        error_log('CBT exam usage result failed: ' . mysqli_stmt_error($usage));
    }
    mysqli_rollback($conn);
    header('Location: cbt-questions.php?status=error&msg=' . urlencode('An exam already assigned to applicants or with results cannot be activated again.'));
    exit;
}

$activate = mysqli_prepare($conn, "UPDATE cbt_exams SET status = 'active' WHERE id = ? AND status = 'draft'");
if (!$activate) {
    error_log('CBT exam activation update prepare failed: ' . mysqli_error($conn));
    mysqli_rollback($conn);
    header('Location: cbt-questions.php?status=error&msg=' . urlencode('The CBT exam could not be activated.'));
    exit;
}
mysqli_stmt_bind_param($activate, 'i', $examId);
if (!mysqli_stmt_execute($activate) || mysqli_stmt_affected_rows($activate) !== 1) {
    error_log('CBT exam activation update failed: ' . mysqli_stmt_error($activate));
    mysqli_rollback($conn);
    header('Location: cbt-questions.php?status=error&msg=' . urlencode('The CBT exam could not be activated.'));
    exit;
}
mysqli_commit($conn);
header('Location: cbt-questions.php?status=success&msg=' . urlencode('Exam activated. Its questions are now locked.'));
exit;
