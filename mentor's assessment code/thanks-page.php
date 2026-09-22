<?php
ob_start();
session_start();

$p = base64_decode($_GET['p']);
$r = base64_decode($_GET['r']);
$c = base64_decode($_GET['c']);

if ($r == 'Passed') {
  $r_color = "text-success";
} else {
  $r_color = "text-danger";
}


include('../config.php');
include('../outsourcehr-admin/connection/connect.php');
require_once('../outsourcehr-admin/inc/fns.php');


if (!isset($_SESSION['ats_candidate'])) {
  include('index.php');
  exit;
}
unset($_SESSION['ats_candidate']);
unset($_SESSION['started']);
unset($_SESSION['exam_code']);
unset($_SESSION['end_time']);
session_destroy();


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
  <link rel="icon" href="images/favicon.png" />
  <!-- <link href="//fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link href="//fonts.googleapis.com/css2?family=Allerta+Stencil&display=swap" rel="stylesheet"> -->
  <link rel="stylesheet" href="dist/css/animate.css">
  <link rel="icon" type="icon" href="images/icon.png">
  <link rel="stylesheet" href="style.css">
</head>

<style>
  body {
    padding: 0px;
    margin: 0px;
    background-image: url('images/test_img.png');
    background-repeat: no-repeat;
    background-size: cover;
    height: 100vh;

  }
</style>

<body>
  <div class="container-fluid">
    <div class="row">

      <div class="col-md-2"></div>

      <div class="col-md-8 text-center" id="test_area">
        <img src="../outsourcehr-admin/assets/img/logo.png" style="width:40%;" class="img-fluid nxxt" alt="" srcset="">
        <h3>Thank you for taking your test</h3>

        You <span class="<?php echo $r_color; ?>"> <b> <?php echo strtoupper($r); ?></b></span> this test</h4>
        <br> <br>
        <h4>Your result is <span class="<?php echo $r_color; ?>"><b><?php echo $p; ?>%</b></span> | The cut-off mark is : <?php echo $c; ?>%



          <p id="txt"><?php echo get_val('message_template', 'template_name', 'Test End', 'message'); ?></p>
          <a href="index">
            <button class="btn btn-primary ">Home</button>
          </a>
      </div>
      <div class="col-md-2"></div>

    </div>
  </div>
</body>

</html>