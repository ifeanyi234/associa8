<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['member_id']) || !isset($_SESSION['member_org_id'])) {
    header('Location: ../../member-login.php');
    exit;
}
?>
