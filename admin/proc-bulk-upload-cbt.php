<?php
require_once "inc/auth.php";
require_once "../inc/db.php";

function cbt_bulk_redirect(string $status, string $message, array $errors = []): void
{
    if ($errors) {
        $_SESSION['cbt_bulk_upload_errors'] = $errors;
    }
    header('Location: cbt-bulk-upload.php?status=' . urlencode($status) . '&msg=' . urlencode($message));
    exit;
}

function cbt_bulk_remove_images(array $paths): void
{
    foreach ($paths as $path) {
        if (is_file($path) && !unlink($path)) {
            error_log('Failed to remove unreferenced CBT bulk image: ' . $path);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cbt-bulk-upload.php');
    exit;
}

$csrfToken = $_POST['csrf_token'] ?? '';
$sessionToken = $_SESSION['cbt_bulk_upload_csrf'] ?? '';
if (!is_string($csrfToken) || !is_string($sessionToken) || $sessionToken === '' || !hash_equals($sessionToken, $csrfToken)) {
    cbt_bulk_redirect('error', 'Your session expired. Reload the page and try again.');
}

$adminRole = $_SESSION['admin_role'] ?? 'admin';
$orgId = isset($_SESSION['org_id']) && $_SESSION['org_id'] !== null ? (int) $_SESSION['org_id'] : 0;
if ($adminRole !== 'super_admin' && $orgId < 1) {
    cbt_bulk_redirect('error', 'Your account is not linked to an association.');
}
$examId = filter_input(INPUT_POST, 'exam_id', FILTER_VALIDATE_INT);
if (!$examId || $examId < 1) {
    cbt_bulk_redirect('error', 'Select a draft exam.');
}
if (!isset($_FILES['questions_file']) || $_FILES['questions_file']['error'] !== UPLOAD_ERR_OK) {
    cbt_bulk_redirect('error', 'Choose a CSV question template to upload.');
}
if ($_FILES['questions_file']['size'] < 1 || $_FILES['questions_file']['size'] > 2 * 1024 * 1024
    || strtolower(pathinfo($_FILES['questions_file']['name'], PATHINFO_EXTENSION)) !== 'csv'
    || !is_uploaded_file($_FILES['questions_file']['tmp_name'])) {
    cbt_bulk_redirect('error', 'The question file must be a valid CSV smaller than 2 MB.');
}

$csv = fopen($_FILES['questions_file']['tmp_name'], 'rb');
if ($csv === false) {
    error_log('CBT bulk upload CSV could not be opened.');
    cbt_bulk_redirect('error', 'The question file could not be read.');
}
$header = fgetcsv($csv, 0, ',', '"', '\\');
$expectedHeader = [
    'Question',
    'Option A',
    'Option B',
    'Option C',
    'Option D',
    'Option E (optional)',
    'Correct Option (A-E)',
    'Image Filename (optional)',
];
if (is_array($header) && isset($header[0])) {
    $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header[0]);
}
if ($header !== $expectedHeader) {
    fclose($csv);
    cbt_bulk_redirect('error', 'The CSV headers do not match the template. Download a fresh template and keep its header row unchanged.', [
        ['row' => 1, 'message' => 'Expected the eight template headers in their original order.'],
    ]);
}

$rows = [];
$errors = [];
$rowNumber = 1;
while (($record = fgetcsv($csv, 0, ',', '"', '\\')) !== false) {
    $rowNumber++;
    if (count(array_filter($record, static fn($value) => trim((string) $value) !== '')) === 0) {
        continue;
    }
    if (count($record) !== count($expectedHeader)) {
        $errors[] = ['row' => $rowNumber, 'message' => 'This row must contain exactly eight columns.'];
        continue;
    }
    $values = array_map(static fn($value) => trim((string) $value), $record);
    [$question, $optionA, $optionB, $optionC, $optionD, $optionE, $correctOption, $imageFilename] = $values;
    $rowErrors = [];
    if ($question === '') {
        $rowErrors[] = 'Question is required.';
    } elseif (strlen($question) > 65535) {
        $rowErrors[] = 'Question text is too long (maximum 65,535 bytes).';
    }
    foreach (['A' => $optionA, 'B' => $optionB, 'C' => $optionC, 'D' => $optionD] as $label => $optionText) {
        if ($optionText === '') {
            $rowErrors[] = 'Option ' . $label . ' is required.';
        } elseif (strlen($optionText) > 255) {
            $rowErrors[] = 'Option ' . $label . ' is too long (maximum 255 bytes).';
        }
    }
    if (strlen($optionE) > 255) {
        $rowErrors[] = 'Option E is too long (maximum 255 bytes).';
    }
    $correctOption = strtoupper($correctOption);
    if (!in_array($correctOption, ['A', 'B', 'C', 'D', 'E'], true)) {
        $rowErrors[] = 'Correct Option must be A, B, C, D, or E.';
    } else {
        $correctText = [
            'A' => $optionA,
            'B' => $optionB,
            'C' => $optionC,
            'D' => $optionD,
            'E' => $optionE,
        ][$correctOption];
        if ($correctText === '') {
            $rowErrors[] = 'The correct option must have answer text.';
        }
    }
    if ($imageFilename !== '' && (basename($imageFilename) !== $imageFilename || str_contains($imageFilename, '\\') || str_contains($imageFilename, "\0"))) {
        $rowErrors[] = 'Image Filename must be a plain filename, not a path.';
    }
    if ($rowErrors) {
        foreach ($rowErrors as $rowError) {
            $errors[] = ['row' => $rowNumber, 'message' => $rowError];
        }
        continue;
    }
    $rows[] = [
        'row' => $rowNumber,
        'question' => $question,
        'option_a' => $optionA,
        'option_b' => $optionB,
        'option_c' => $optionC,
        'option_d' => $optionD,
        'option_e' => $optionE === '' ? null : $optionE,
        'correct_option' => $correctOption,
        'image_filename' => $imageFilename,
    ];
    if (count($rows) > 500) {
        $errors[] = ['row' => $rowNumber, 'message' => 'A single upload can contain no more than 500 questions.'];
        break;
    }
}
fclose($csv);
if (!$rows && !$errors) {
    $errors[] = ['row' => 2, 'message' => 'Add at least one question below the header row.'];
}

$referencedImageNames = [];
foreach ($rows as $row) {
    if ($row['image_filename'] !== '') {
        $referencedImageNames[$row['image_filename']] = true;
    }
}
$imagesByFilename = [];
$imagesFileProvided = isset($_FILES['images_file']) && $_FILES['images_file']['error'] !== UPLOAD_ERR_NO_FILE;
if ($imagesFileProvided && $_FILES['images_file']['error'] !== UPLOAD_ERR_OK) {
    $errors[] = ['row' => 1, 'message' => 'The ZIP image file could not be uploaded.'];
} elseif ($imagesFileProvided) {
    if (!class_exists('ZipArchive') || !class_exists('finfo')) {
        error_log('CBT bulk image import requires ZipArchive and Fileinfo extensions.');
        $errors[] = ['row' => 1, 'message' => 'Image imports are temporarily unavailable on this server.'];
    } elseif ($_FILES['images_file']['size'] < 1 || $_FILES['images_file']['size'] > 20 * 1024 * 1024
        || strtolower(pathinfo($_FILES['images_file']['name'], PATHINFO_EXTENSION)) !== 'zip'
        || !is_uploaded_file($_FILES['images_file']['tmp_name'])) {
        $errors[] = ['row' => 1, 'message' => 'The image archive must be a valid ZIP no larger than 20 MB.'];
    } else {
        $zip = new ZipArchive();
        $zipOpened = $zip->open($_FILES['images_file']['tmp_name']);
        if ($zipOpened !== true) {
            $errors[] = ['row' => 1, 'message' => 'The image archive is not a valid ZIP file.'];
        } elseif ($zip->numFiles > 100) {
            $zip->close();
            $errors[] = ['row' => 1, 'message' => 'The image archive cannot contain more than 100 files.'];
        } else {
            $totalUncompressedSize = 0;
            $fileInfo = new finfo(FILEINFO_MIME_TYPE);
            $extensionsByMime = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            for ($index = 0; $index < $zip->numFiles; $index++) {
                $entry = $zip->statIndex($index);
                $entryName = is_array($entry) ? (string) $entry['name'] : '';
                $normalizedName = str_replace('\\', '/', $entryName);
                $segments = explode('/', $normalizedName);
                if ($entryName === '' || str_contains($entryName, "\0") || str_starts_with($normalizedName, '/')
                    || preg_match('/^[A-Za-z]:/', $normalizedName)
                    || in_array('..', $segments, true) || in_array('.', $segments, true)
                    || in_array('', $segments, true) && !str_ends_with($normalizedName, '/')) {
                    $errors[] = ['row' => 1, 'message' => 'The ZIP contains an unsafe file path. Images must be stored inside the archive without absolute paths or parent-directory references.'];
                    break;
                }
                if (str_ends_with($normalizedName, '/')) {
                    continue;
                }
                if (count($segments) !== 1) {
                    $errors[] = ['row' => 1, 'message' => 'Place image files at the root of the ZIP; subfolders are not supported.'];
                    break;
                }
                $entrySize = (int) ($entry['size'] ?? -1);
                $totalUncompressedSize += $entrySize;
                if ($entrySize < 1 || $entrySize > 5 * 1024 * 1024 || $totalUncompressedSize > 20 * 1024 * 1024) {
                    $errors[] = ['row' => 1, 'message' => 'Images must be non-empty, no larger than 5 MB each, and total no more than 20 MB.'];
                    break;
                }
                $contents = $zip->getFromIndex($index);
                if (!is_string($contents) || strlen($contents) !== $entrySize) {
                    $errors[] = ['row' => 1, 'message' => 'An image could not be read from the ZIP archive.'];
                    break;
                }
                $mimeType = $fileInfo->buffer($contents);
                if (!isset($extensionsByMime[$mimeType])) {
                    $errors[] = ['row' => 1, 'message' => 'Every file in the ZIP must be a valid JPG, PNG, or WebP image.'];
                    break;
                }
                if (array_key_exists($entryName, $imagesByFilename)) {
                    $errors[] = ['row' => 1, 'message' => 'The ZIP contains duplicate image filenames.'];
                    break;
                }
                if (isset($referencedImageNames[$entryName])) {
                    $imagesByFilename[$entryName] = [
                        'contents' => $contents,
                        'extension' => $extensionsByMime[$mimeType],
                    ];
                }
                unset($contents);
            }
            $zip->close();
        }
    }
}
foreach ($rows as $row) {
    if ($row['image_filename'] !== '' && !$imagesFileProvided) {
        $errors[] = ['row' => $row['row'], 'message' => 'An image filename is provided, but no ZIP of images was uploaded.'];
    } elseif ($row['image_filename'] !== '' && !array_key_exists($row['image_filename'], $imagesByFilename)) {
        $errors[] = ['row' => $row['row'], 'message' => 'Image "' . $row['image_filename'] . '" was not found in the ZIP.'];
    }
}
if ($errors) {
    cbt_bulk_redirect('error', 'No questions were imported. Fix the listed rows and upload again.', $errors);
}

mysqli_begin_transaction($conn);
if ($adminRole === 'super_admin') {
    $examCheck = mysqli_prepare($conn, "SELECT id FROM cbt_exams WHERE id = ? AND status = 'draft' AND org_id IS NOT NULL FOR UPDATE");
} else {
    $examCheck = mysqli_prepare($conn, "SELECT id FROM cbt_exams WHERE id = ? AND status = 'draft' AND org_id = ? FOR UPDATE");
}
if (!$examCheck) {
    mysqli_rollback($conn);
    error_log('CBT bulk upload exam check prepare failed: ' . mysqli_error($conn));
    cbt_bulk_redirect('error', 'The selected draft exam could not be checked.');
}
if ($adminRole === 'super_admin') {
    mysqli_stmt_bind_param($examCheck, 'i', $examId);
} else {
    mysqli_stmt_bind_param($examCheck, 'ii', $examId, $orgId);
}
if (!mysqli_stmt_execute($examCheck)) {
    mysqli_rollback($conn);
    error_log('CBT bulk upload exam check failed: ' . mysqli_stmt_error($examCheck));
    cbt_bulk_redirect('error', 'The selected draft exam could not be checked.');
}
$examResult = mysqli_stmt_get_result($examCheck);
if (!$examResult || mysqli_num_rows($examResult) !== 1) {
    mysqli_rollback($conn);
    cbt_bulk_redirect('error', 'Questions can only be uploaded to a draft exam in your association.');
}

$uploadDirectory = __DIR__ . '/uploads/cbt-questions/';
$createdImagePaths = [];
$relativeImagePaths = [];
if ($imagesByFilename && !is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true) && !is_dir($uploadDirectory)) {
    mysqli_rollback($conn);
    error_log('CBT bulk image upload directory could not be created.');
    cbt_bulk_redirect('error', 'The question images could not be saved.');
}
foreach ($imagesByFilename as $filename => $image) {
    try {
        $storedFilename = bin2hex(random_bytes(16)) . '.' . $image['extension'];
    } catch (\Throwable $exception) {
        mysqli_rollback($conn);
        error_log('CBT bulk image filename generation failed: ' . $exception->getMessage());
        cbt_bulk_remove_images($createdImagePaths);
        cbt_bulk_redirect('error', 'The question images could not be saved.');
    }
    $absolutePath = $uploadDirectory . $storedFilename;
    if (file_put_contents($absolutePath, $image['contents'], LOCK_EX) !== strlen($image['contents'])) {
        cbt_bulk_remove_images(array_merge($createdImagePaths, [$absolutePath]));
        mysqli_rollback($conn);
        error_log('CBT bulk question image could not be written.');
        cbt_bulk_redirect('error', 'The question images could not be saved.');
    }
    $createdImagePaths[] = $absolutePath;
    $relativeImagePaths[$filename] = 'uploads/cbt-questions/' . $storedFilename;
}

$insert = mysqli_prepare($conn, 'INSERT INTO cbt_questions (exam_id, question_text, option_a, option_b, option_c, option_d, option_e, correct_option, image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
if (!$insert) {
    cbt_bulk_remove_images($createdImagePaths);
    mysqli_rollback($conn);
    error_log('CBT bulk question insert prepare failed: ' . mysqli_error($conn));
    cbt_bulk_redirect('error', 'The questions could not be imported. Check that the database migration is up to date.');
}

$insertFailed = false;
foreach ($rows as $row) {
    $imagePath = $row['image_filename'] !== '' ? $relativeImagePaths[$row['image_filename']] : null;
    mysqli_stmt_bind_param(
        $insert,
        'issssssss',
        $examId,
        $row['question'],
        $row['option_a'],
        $row['option_b'],
        $row['option_c'],
        $row['option_d'],
        $row['option_e'],
        $row['correct_option'],
        $imagePath
    );
    if (!mysqli_stmt_execute($insert)) {
        error_log('CBT bulk question insert failed on CSV row ' . $row['row'] . ': ' . mysqli_stmt_error($insert));
        $errors[] = ['row' => $row['row'], 'message' => 'The database could not save this question. No questions were imported.'];
        $insertFailed = true;
        break;
    }
}
if ($insertFailed || !mysqli_commit($conn)) {
    if (!$insertFailed) {
        error_log('CBT bulk question transaction commit failed: ' . mysqli_error($conn));
        $errors[] = ['row' => 1, 'message' => 'The import could not be committed. No questions were imported.'];
    }
    mysqli_rollback($conn);
    cbt_bulk_remove_images($createdImagePaths);
    cbt_bulk_redirect('error', 'No questions were imported. Please review the error and try again.', $errors);
}

cbt_bulk_redirect('success', count($rows) . ' questions imported.');
