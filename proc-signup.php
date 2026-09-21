<?php
require_once "inc/db.php";

function field($key) {
    return isset($_POST[$key]) ? trim($_POST[$key]) : '';
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Organization Data
    $orgName = mysqli_real_escape_string($conn, field('orgName'));
    $orgType = mysqli_real_escape_string($conn, field('orgType'));
    $orgEmail = mysqli_real_escape_string($conn, field('orgEmail'));
    $orgPhone = mysqli_real_escape_string($conn, field('orgPhone'));
    $orgCountry = mysqli_real_escape_string($conn, field('orgCountry'));
    $orgState = mysqli_real_escape_string($conn, field('orgState'));
    $orgPricing = mysqli_real_escape_string($conn, field('orgPricing'));
    $orgMembers = (int)field('orgMembers');

    // 2. Admin Data
    $adminFirstName = mysqli_real_escape_string($conn, field('adminFirstName'));
    $adminLastName = mysqli_real_escape_string($conn, field('adminLastName'));
    $adminEmail = mysqli_real_escape_string($conn, field('adminEmail'));
    $adminPhone = mysqli_real_escape_string($conn, field('adminPhone'));
    $adminJobTitle = mysqli_real_escape_string($conn, field('adminJobTitle'));
    $adminRole = mysqli_real_escape_string($conn, field('adminRole'));

    // 3. Account Data
    $accUsername = mysqli_real_escape_string($conn, field('accUsername'));
    $accPassword = field('accPassword');
    $accConfirm = field('accConfirmPassword');
    $accOtp = mysqli_real_escape_string($conn, field('accOtp'));

    // Validate Password Match
    if ($accPassword !== $accConfirm) {
        header("Location: signup.php?status=error&msg=" . urlencode("Error: Passwords do not match."));
        exit;
    }

    // Check duplicates before inserting anything
    $orgEmail_check = "SELECT * FROM `org-info` WHERE email = '$orgEmail' LIMIT 1";
    $result_org = mysqli_query($conn, $orgEmail_check);
    if ($result_org && mysqli_num_rows($result_org) > 0) {
        header("Location: signup.php?status=error&msg=" . urlencode("An Organization with this email already exists."));
        exit;
    }

    $adminEmail_check = "SELECT * FROM `admin-info` WHERE email = '$adminEmail' LIMIT 1";
    $result_admin = mysqli_query($conn, $adminEmail_check);
    if ($result_admin && mysqli_num_rows($result_admin) > 0) {
        header("Location: signup.php?status=error&msg=" . urlencode("An admin with this email already exists."));
        exit;
    }

    $username_check = "SELECT * FROM `acc-info` WHERE username = '$accUsername' LIMIT 1";
    $result_username = mysqli_query($conn, $username_check);
    if ($result_username && mysqli_num_rows($result_username) > 0) {
        header("Location: signup.php?status=error&msg=" . urlencode("Username already exists."));
        exit;
    }

    // Securely Hash Password
    $hashedPassword = password_hash($accPassword, PASSWORD_DEFAULT);

    mysqli_begin_transaction($conn);

    // Insert into `org-info`
    $sql_org = "INSERT INTO `org-info` (`name`, `type`, `email`, `phone`, `country`, `state`, `pricing`, `total-members`) 
                VALUES ('$orgName', '$orgType', '$orgEmail', '$orgPhone', '$orgCountry', '$orgState', '$orgPricing', '$orgMembers')";
    $query_org = mysqli_query($conn, $sql_org);
    if (!$query_org) {
        mysqli_rollback($conn);
        header("Location: signup.php?status=error&msg=" . urlencode("Something went wrong while creating your organization."));
        exit;
    }
    $orgId = (int) mysqli_insert_id($conn);

    // Insert into `acc-info`
    $sql_acc = "INSERT INTO `acc-info` (`org_id`, `username`, `password`, `otp`) 
                VALUES ('$orgId', '$accUsername', '$hashedPassword', '$accOtp')";
    $query_acc = mysqli_query($conn, $sql_acc);
    if (!$query_acc) {
        mysqli_rollback($conn);
        header("Location: signup.php?status=error&msg=" . urlencode("Something went wrong while creating your account."));
        exit;
    }
    $accId = (int) mysqli_insert_id($conn);

    // Insert into `admin-info`
    $sql_admin = "INSERT INTO `admin-info` (`org_id`, `acc_id`, `first-name`, `last-name`, `email`, `phone`, `job-title`, `role`) 
                  VALUES ('$orgId', '$accId', '$adminFirstName', '$adminLastName', '$adminEmail', '$adminPhone', '$adminJobTitle', '$adminRole')";
    $query_admin = mysqli_query($conn, $sql_admin);

    if ($query_admin) {
        mysqli_commit($conn);
        header("Location: signup.php?status=success");
        exit;
    }

    mysqli_rollback($conn);
    header("Location: signup.php?status=error&msg=" . urlencode("Something went wrong. Please try again."));
    exit;
}
?>