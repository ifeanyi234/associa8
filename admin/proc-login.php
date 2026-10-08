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
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            $admin = mysqli_query($conn, "SELECT org_id, role, zone_id, subzone_id FROM `admin-info` WHERE acc_id = " . (int) $user['id'] . " LIMIT 1");
            if ($admin && $adminRow = mysqli_fetch_assoc($admin)) {
                $validRoles = ['super_admin', 'admin', 'manager', 'staff'];
                if (!isset($adminRow['role']) || !in_array($adminRow['role'], $validRoles, true)) {
                    session_unset();
                    session_destroy();
                    header("Location: index.php?error=2");
                    exit;
                }

                $_SESSION['org_id'] = isset($adminRow['org_id']) && $adminRow['org_id'] !== null ? (int) $adminRow['org_id'] : null;
                $_SESSION['admin_role'] = $adminRow['role'];
                $_SESSION['admin_zone_id'] = isset($adminRow['zone_id']) && $adminRow['zone_id'] !== null ? (int) $adminRow['zone_id'] : null;
                $_SESSION['admin_subzone_id'] = isset($adminRow['subzone_id']) && $adminRow['subzone_id'] !== null ? (int) $adminRow['subzone_id'] : null;

                if ($_SESSION['admin_role'] !== 'super_admin' && (!is_numeric($_SESSION['org_id']) || (int) $_SESSION['org_id'] <= 0)) {
                    session_unset();
                    session_destroy();
                    header("Location: index.php?error=2");
                    exit;
                }

                header("Location: dashboard.php");
                exit;
            }

            session_unset();
            session_destroy();
            header("Location: index.php?error=2");
            exit;
        }
    }

    header("Location: index.php?error=1");
    exit;
}
?>