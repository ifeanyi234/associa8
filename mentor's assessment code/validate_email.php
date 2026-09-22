<?php
ob_start();
session_start();

ob_start();

include('../config.php');
include('../outsourcehr-admin/connection/connect.php');
require_once('../outsourcehr-admin/inc/fns.php');


$email = mysqli_real_escape_string($db, $_POST['email']);


if ($email == '') {
    $error = "Enter your email to proceed";
    include('index.php');
    exit;
}

if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $error = "Invalid email format";
    include('index.php');
    exit;
}
$query = "select * from participant where email = '$email'";
$result = mysqli_query($db, $query);
$num = mysqli_num_rows($result);
$row = mysqli_fetch_array($result);

$_SESSION['firstname'] = $row['firstname'];
$_SESSION['lastname'] = $row['lastname'];
$_SESSION['email'] = $row['email'];
$_SESSION['phone'] = $row['phone'];
$_SESSION['job_applied_id'] = $row['job_applied_id'];
$phone = $row['phone'];

if ($num > 0) {
    $_SESSION['ats_candidate'] = $email;

    header('location: code-page');
    exit;
} else {
    $error = "Sorry this email has not be profiled in our system";
    include('index.php');
    exit;
}
