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
$imagePath = null;
$imageTemporaryPath = null;
if (isset($_FILES['question_image']) && $_FILES['question_image']['error'] !== UPLOAD_ERR_NO_FILE) {
    if ($_FILES['question_image']['error'] !== UPLOAD_ERR_OK) {
        header('Location: add-cbt-question.php?status=error&msg=' . urlencode('The question image could not be uploaded. Try again.') . '&exam_id=' . $examId);
        exit;
    }
    if (!class_exists('finfo')) {
        error_log('Question image upload requires PHP Fileinfo.');
        header('Location: add-cbt-question.php?status=error&msg=' . urlencode('Image uploads are temporarily unavailable.') . '&exam_id=' . $examId);
        exit;
    }
    $temporaryPath = $_FILES['question_image']['tmp_name'];
    $fileSize = is_uploaded_file($temporaryPath) ? filesize($temporaryPath) : false;
    if ($fileSize === false || $fileSize < 1 || $fileSize > 5 * 1024 * 1024) {
        header('Location: add-cbt-question.php?status=error&msg=' . urlencode('Choose an image smaller than 5 MB.') . '&exam_id=' . $examId);
        exit;
    }
    $extensionsByMime = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    $mimeType = (new finfo(FILEINFO_MIME_TYPE))->file($temporaryPath);
    if (!isset($extensionsByMime[$mimeType])) {
        header('Location: add-cbt-question.php?status=error&msg=' . urlencode('Choose a valid JPG, PNG, or WebP image.') . '&exam_id=' . $examId);
        exit;
    }
    $imageTemporaryPath = $temporaryPath;
    $imageExtension = $extensionsByMime[$mimeType];
}
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
if ($imageTemporaryPath !== null) {
    $uploadDirectory = __DIR__ . '/uploads/cbt-questions/';
    if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true) && !is_dir($uploadDirectory)) {
        mysqli_rollback($conn);
        error_log('CBT question image directory could not be created.');
        header('Location: add-cbt-question.php?status=error&msg=' . urlencode('The question image could not be saved.') . '&exam_id=' . $examId);
        exit;
    }
    try {
        $imageFileName = bin2hex(random_bytes(16)) . '.' . $imageExtension;
    } catch (\Random\RandomException $exception) {
        mysqli_rollback($conn);
        error_log('CBT question image filename generation failed: ' . $exception->getMessage());
        header('Location: add-cbt-question.php?status=error&msg=' . urlencode('The question image could not be saved.') . '&exam_id=' . $examId);
        exit;
    }
    $imagePath = 'uploads/cbt-questions/' . $imageFileName;
    $imageAbsolutePath = $uploadDirectory . $imageFileName;
    if (!move_uploaded_file($imageTemporaryPath, $imageAbsolutePath)) {
        mysqli_rollback($conn);
        error_log('CBT question image could not be moved into storage.');
        header('Location: add-cbt-question.php?status=error&msg=' . urlencode('The question image could not be saved.') . '&exam_id=' . $examId);
        exit;
    }
}
$statement = mysqli_prepare($conn, 'INSERT INTO cbt_questions (exam_id, question_text, option_a, option_b, option_c, option_d, option_e, correct_option, image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
if (!$statement) {
    if ($imagePath !== null && !unlink($imageAbsolutePath)) {
        error_log('Failed to remove unreferenced CBT question image: ' . $imageAbsolutePath);
    }
    mysqli_rollback($conn);
    error_log('CBT question insert prepare failed: ' . mysqli_error($conn));
    header('Location: add-cbt-question.php?status=error&msg=' . urlencode('The CBT questions table is not available.') . '&exam_id=' . $examId);
    exit;
}
mysqli_stmt_bind_param($statement, 'issssssss', $examId, $question, $options['a'], $options['b'], $options['c'], $options['d'], $options['e'], $correct, $imagePath);
$success = mysqli_stmt_execute($statement);
if ($success && mysqli_commit($conn)) {
    $saved = true;
} else {
    $saved = false;
    mysqli_rollback($conn);
    error_log('CBT question insert failed: ' . mysqli_stmt_error($statement));
    if ($imagePath !== null && !unlink($imageAbsolutePath)) {
        error_log('Failed to remove unreferenced CBT question image: ' . $imageAbsolutePath);
    }
}
header('Location: add-cbt-question.php?status=' . ($saved ? 'success' : 'error') . '&msg=' . urlencode($saved ? 'Question added successfully. Add another question below.' : 'Could not save the question.') . '&exam_id=' . $examId);
exit;
