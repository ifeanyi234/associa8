<?php
session_start();
require_once ("../inc/db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, trim($_POST["username"] ?? ""));
    $password = $_POST["password"] ?? "";

    $sql = "SELECT * FROM `acc-info` WHERE `username` = '$username' LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            header("Location: dashboard.php");
            exit;
        }
    }

    header("Location: index.php?error=1");
    exit;
}
?>