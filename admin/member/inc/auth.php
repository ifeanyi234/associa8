<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['member_id']) || !isset($_SESSION['member_org_id'])) {
    header('Location: ../../member-login.php');
    exit;
}

$memberNameParts = preg_split('/\s+/', trim($_SESSION['member_name'] ?? '')) ?: [];
$memberFirstName = trim($_SESSION['member_first_name'] ?? ($memberNameParts[0] ?? ''));
$memberLastName = trim($_SESSION['member_last_name'] ?? (count($memberNameParts) > 1 ? end($memberNameParts) : ''));
$memberInitials = strtoupper(substr($memberFirstName, 0, 1) . substr($memberLastName, 0, 1));
$memberInitials = $memberInitials !== '' ? $memberInitials : 'M';
?>
