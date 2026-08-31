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

    // Securely Hash Password
    $hashedPassword = password_hash($accPassword, PASSWORD_DEFAULT);

    $orgEmail_check = "SELECT * FROM `org-info` WHERE email = '$orgEmail'";
    $result_org = mysqli_query($conn, $orgEmail_check);

    $adminEmail_check = "SELECT * FROM `admin-info` WHERE email = '$adminEmail'";
    $result_admin = mysqli_query($conn, $adminEmail_check);

    $username_check = "SELECT * FROM `acc-info` WHERE username = '$accUsername'";
    $result_username = mysqli_query($conn, $username_check);

    // Insert into `org-info`
    $sql_org = "INSERT INTO `org-info` (`name`, `type`, `email`, `phone`, `country`, `state`, `pricing`, `total-members`) 
                VALUES ('$orgName', '$orgType', '$orgEmail', '$orgPhone', '$orgCountry', '$orgState', '$orgPricing', '$orgMembers')";
    
    mysqli_begin_transaction($conn);
    $query_org = mysqli_query($conn, $sql_org);
    $org_error = $query_org ? '' : mysqli_error($conn);

    // Insert into `admin-info`
    $sql_admin = "INSERT INTO `admin-info` (`first-name`, `last-name`, `email`, `phone`, `job-title`, `role`) 
                  VALUES ('$adminFirstName', '$adminLastName', '$adminEmail', '$adminPhone', '$adminJobTitle', '$adminRole')";
    
    $query_admin = mysqli_query($conn, $sql_admin);
    $admin_error = $query_admin ? '' : mysqli_error($conn);

    // Insert into `acc-info`
    $sql_acc = "INSERT INTO `acc-info` (`username`, `password`, `otp`) 
                VALUES ('$accUsername', '$hashedPassword', '$accOtp')";
    
    $query_acc = mysqli_query($conn, $sql_acc);
    $acc_error = $query_acc ? '' : mysqli_error($conn);

    // Checks overall success
    if ($query_org && $query_admin && $query_acc) {
        mysqli_commit($conn);
        header("Location: signup.php?status=success");
        exit;
    }elseif(mysqli_num_rows($result_org) > 0) {
        mysqli_rollback($conn);
        error_log(implode('; ', array_filter([$org_error, $admin_error, $acc_error])));
        header("Location: signup.php?status=error&msg=" . urlencode("An Organization with this email already exists."));
    }elseif(mysqli_num_rows($result_admin) > 0) {
        mysqli_rollback($conn);
        error_log(implode('; ', array_filter([$org_error, $admin_error, $acc_error])));
        header("Location: signup.php?status=error&msg=" . urlencode("An admin with this email already exists."));
    }elseif(mysqli_num_rows($result_username) > 0) {
        mysqli_rollback($conn);
        error_log(implode('; ', array_filter([$org_error, $admin_error, $acc_error])));
        header("Location: signup.php?status=error&msg=" . urlencode("Username already exists."));
    } else {
        mysqli_rollback($conn);
        error_log(implode('; ', array_filter([$org_error, $admin_error, $acc_error])));
        header("Location: signup.php?status=error&msg=" . urlencode("Something went wrong. Please try again."));
        exit;
    }
}
?>