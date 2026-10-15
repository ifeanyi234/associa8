<?php
require_once 'inc/auth.php';
require_once '../../inc/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: profile.php');
    exit;
}

$csrfToken = $_POST['csrf_token'] ?? '';
$sessionToken = $_SESSION['member_profile_csrf'] ?? '';
if (!is_string($csrfToken) || !is_string($sessionToken) || $sessionToken === '' || !hash_equals($sessionToken, $csrfToken)) {
    header('Location: profile.php?status=photo-error');
    exit;
}

$file = $_FILES['profile_photo'] ?? null;
if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK
    || !isset($file['tmp_name'], $file['size']) || !is_uploaded_file($file['tmp_name'])
    || (int) $file['size'] < 1 || (int) $file['size'] > 3 * 1024 * 1024) {
    header('Location: profile.php?status=photo-error');
    exit;
}

if (!class_exists('finfo') || !function_exists('imagecreatefromstring') || !function_exists('imagejpeg')) {
    error_log('Member profile photo upload requires fileinfo and GD support.');
    header('Location: profile.php?status=photo-error');
    exit;
}

$fileInfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $fileInfo->file($file['tmp_name']);
$allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
$imageInfo = getimagesize($file['tmp_name']);
if (!in_array($mimeType, $allowedTypes, true) || $imageInfo === false
    || !in_array($imageInfo['mime'] ?? '', $allowedTypes, true)
    || (int) $imageInfo[0] < 1 || (int) $imageInfo[1] < 1
    || (int) $imageInfo[0] * (int) $imageInfo[1] > 12000000) {
    header('Location: profile.php?status=photo-error');
    exit;
}

$image = imagecreatefromstring(file_get_contents($file['tmp_name']));
if ($image === false) {
    header('Location: profile.php?status=photo-error');
    exit;
}

$sourceWidth = imagesx($image);
$sourceHeight = imagesy($image);
$scale = min(1, 1200 / max($sourceWidth, $sourceHeight));
$targetWidth = max(1, (int) round($sourceWidth * $scale));
$targetHeight = max(1, (int) round($sourceHeight * $scale));
$normalizedImage = imagecreatetruecolor($targetWidth, $targetHeight);
$white = imagecolorallocate($normalizedImage, 255, 255, 255);
imagefill($normalizedImage, 0, 0, $white);
$resampled = imagecopyresampled(
    $normalizedImage,
    $image,
    0,
    0,
    0,
    0,
    $targetWidth,
    $targetHeight,
    $sourceWidth,
    $sourceHeight
);
imagedestroy($image);
if (!$resampled) {
    imagedestroy($normalizedImage);
    header('Location: profile.php?status=photo-error');
    exit;
}

$uploadDirectory = __DIR__ . '/uploads/profile-photos';
if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true) && !is_dir($uploadDirectory)) {
    imagedestroy($normalizedImage);
    error_log('Member profile photo directory could not be created.');
    header('Location: profile.php?status=photo-error');
    exit;
}

try {
    $fileName = bin2hex(random_bytes(16)) . '.jpg';
} catch (Throwable $exception) {
    imagedestroy($normalizedImage);
    error_log('Member profile photo filename could not be generated: ' . $exception->getMessage());
    header('Location: profile.php?status=photo-error');
    exit;
}

$destination = $uploadDirectory . DIRECTORY_SEPARATOR . $fileName;
if (!imagejpeg($normalizedImage, $destination, 85)) {
    imagedestroy($normalizedImage);
    error_log('Member profile photo could not be written.');
    header('Location: profile.php?status=photo-error');
    exit;
}
imagedestroy($normalizedImage);

$photoPath = 'uploads/profile-photos/' . $fileName;
$memberId = (int) $_SESSION['member_id'];
$memberOrgId = (int) $_SESSION['member_org_id'];
$update = mysqli_prepare($conn, 'UPDATE members SET profile_photo_path = ? WHERE id = ? AND org_id = ?');
if (!$update) {
    unlink($destination);
    error_log('Member profile photo update could not be prepared: ' . mysqli_error($conn));
    header('Location: profile.php?status=photo-error');
    exit;
}

mysqli_stmt_bind_param($update, 'sii', $photoPath, $memberId, $memberOrgId);
$updated = mysqli_stmt_execute($update);
if (!$updated) {
    error_log('Member profile photo update failed: ' . mysqli_stmt_error($update));
}
$affectedRows = mysqli_stmt_affected_rows($update);
mysqli_stmt_close($update);
if (!$updated || $affectedRows !== 1) {
    unlink($destination);
    header('Location: profile.php?status=photo-error');
    exit;
}

unset($_SESSION['member_profile_csrf']);
header('Location: profile.php?status=photo-success');
exit;
