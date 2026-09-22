<?php
ob_start();
session_start();

include('../config.php');
include('../outsourcehr-admin/connection/connect.php');
require_once('../outsourcehr-admin/inc/fns.php');

if (!isset($_SESSION['ats_candidate'])) {
  include('index.php');
  exit;
}
if (!isset($_SESSION['started'])) {
  include('index.php');
  exit;
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KLINHR Assessment</title>
  <link rel="stylesheet" href="dist/css/bootstrap.min.css">
  <script src="dist/js/bootstrap.min.js"></script>
  <link rel="stylesheet" href="dist/">
  <link rel="stylesheet" href="dist/font-awesome/css/font-awesome.min.css">
  <link rel="icon" href="../outsourcehr-admin/assets/img/fav.png" />
  <!-- <link href="//fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link href="//fonts.googleapis.com/css2?family=Allerta+Stencil&display=swap" rel="stylesheet"> -->
  <link rel="stylesheet" href="dist/css/animate.css">
  <link rel="stylesheet" href="style.css">
  <link rel="icon" type="icon" href="../outsourcehr-admin/assets/img/fav.png">
</head>

<style>
  body {
    padding: 0px;
    margin: 0px;
    background-image: url('images/test_img.png');
    background-repeat: no-repeat;
    background-attachment: fixed;
    background-size: cover;
    background-position: center;
    height: 150vh;

  }
</style>

<body>
  <div class="container-fluid">
    <div class="row">

      <div class="col-md-2"></div>

      <div class="col-md-8 text-center" id="test_area">
        <img src="../outsourcehr-admin/assets/img/logo.png" style="width:40%;" style="width: 25%;" class="img-fluid logo" alt="" srcset="">
        <h3>Please read the instructions below before to get started</h3>
        <p id="txt"> <?php echo get_val('message_template', 'template_name', 'Test Instruction', 'message'); ?>
          <a href="test-question">
            <button class="btn btn-primary form-control ">Start Test</button>
          </a>
        </p>
      </div>
      <div class="col-md-2"></div>

    </div>
  </div>
</body>

</html>