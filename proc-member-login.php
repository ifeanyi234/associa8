<?php
session_start();
require_once __DIR__ . '/inc/db.php';

$email = trim($_POST['email'] ?? '');
$password = (string) ($_POST['password'] ?? '');

if ($email === '' || $password === '') {
    header('Location: member-login.php?error=1');
    exit;
}

$escapedEmail = mysqli_real_escape_string($conn, $email);
$result = mysqli_query($conn, "SELECT id, org_id, zone_id, subzone_id, password, status, first_name, last_name FROM members WHERE email = '$escapedEmail' LIMIT 1");

if (!$result || mysqli_num_rows($result) !== 1) {
    header('Location: member-login.php?error=0');
    exit;
}

$member = mysqli_fetch_assoc($result);
$storedHash = $member['password'] ?? null;

if ($storedHash === null || $storedHash === '' || !is_string($storedHash)) {
    header('Location: member-login.php?error=2');
    exit;
}

if (($member['status'] ?? '') === 'suspended') {
    header('Location: member-login.php?error=3');
    exit;
}

if (!password_verify($password, $storedHash)) {
    header('Location: member-login.php?error=0');
    exit;
}

$_SESSION['member_id'] = (int) $member['id'];
$_SESSION['member_org_id'] = isset($member['org_id']) && $member['org_id'] !== null ? (int) $member['org_id'] : null;
$_SESSION['member_zone_id'] = isset($member['zone_id']) && $member['zone_id'] !== null ? (int) $member['zone_id'] : null;
$_SESSION['member_subzone_id'] = isset($member['subzone_id']) && $member['subzone_id'] !== null ? (int) $member['subzone_id'] : null;
$_SESSION['member_name'] = trim(($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? ''));

header('Location: admin/member/dashboard.php');
exit;
