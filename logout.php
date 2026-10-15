<?php
session_start();

if (isset($_COOKIE['associa8_member_remember'])) {
    require_once __DIR__ . '/inc/db.php';
    require_once __DIR__ . '/inc/member-remember-me.php';
    member_revoke_remember_token($conn);
}

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

session_unset();
session_destroy();

header('Location: index.php');
exit;
