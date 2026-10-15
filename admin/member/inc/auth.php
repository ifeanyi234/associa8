<?php
require_once __DIR__ . '/../../inc/info-tip.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../inc/db.php';
require_once __DIR__ . '/../../../inc/member-remember-me.php';
if (!isset($_SESSION['member_id'])) {
    member_restore_remembered_session($conn);
}

if (!isset($_SESSION['member_id']) || !isset($_SESSION['member_org_id'])) {
    header('Location: ../../member-login.php');
    exit;
}

$memberAvatarQuery = mysqli_prepare(
    $conn,
    'SELECT profile_photo_path FROM members WHERE id = ? AND org_id = ? LIMIT 1'
);
if (!$memberAvatarQuery) {
    error_log('Member avatar lookup could not be prepared: ' . mysqli_error($conn));
    http_response_code(500);
    exit('Your member profile could not be loaded right now.');
}
$memberId = (int) $_SESSION['member_id'];
$memberOrgId = (int) $_SESSION['member_org_id'];
mysqli_stmt_bind_param($memberAvatarQuery, 'ii', $memberId, $memberOrgId);
if (!mysqli_stmt_execute($memberAvatarQuery)) {
    error_log('Member avatar lookup failed: ' . mysqli_stmt_error($memberAvatarQuery));
    mysqli_stmt_close($memberAvatarQuery);
    http_response_code(500);
    exit('Your member profile could not be loaded right now.');
}
$memberAvatarResult = mysqli_stmt_get_result($memberAvatarQuery);
$memberAvatarRow = $memberAvatarResult ? mysqli_fetch_assoc($memberAvatarResult) : null;
mysqli_stmt_close($memberAvatarQuery);
if (!$memberAvatarRow) {
    http_response_code(404);
    exit('Your member profile could not be found.');
}
$memberPhotoPath = (string) ($memberAvatarRow['profile_photo_path'] ?? '');
$memberAvatarLabel = trim((string) ($_SESSION['member_name'] ?? '')) ?: 'Member';

$memberNameParts = preg_split('/\s+/', trim($_SESSION['member_name'] ?? '')) ?: [];
$memberFirstName = trim($_SESSION['member_first_name'] ?? ($memberNameParts[0] ?? ''));
$memberLastName = trim($_SESSION['member_last_name'] ?? (count($memberNameParts) > 1 ? end($memberNameParts) : ''));
$memberInitials = strtoupper(substr($memberFirstName, 0, 1) . substr($memberLastName, 0, 1));
$memberInitials = $memberInitials !== '' ? $memberInitials : 'M';
?>
