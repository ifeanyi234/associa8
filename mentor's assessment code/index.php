<?php 
include('../config.php');
include('../outsourcehr-admin/connection/connect.php');
include_once('../outsourcehr-admin/inc/fns.php');
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
  <link rel="icon" href="assets/img/fav.png" />
  <link rel="icon" type="icon" href="../outsourcehr-admin/assets/img/fav.png">
  <!-- <link href="//fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link href="//fonts.googleapis.com/css2?family=Allerta+Stencil&display=swap" rel="stylesheet"> -->
  <link rel="stylesheet" href="dist/css/animate.css">
  <link rel="stylesheet" href="style.css">
</head>

<body>
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-6" id="home_img">

      </div>
      <div class="col-md-1"></div>

      <div class="col-md-4 text-center" id="login_area">
        <div id="logo"><img src="../outsourcehr-admin/assets/img/logo.png" style="width:40%;" class="img-fluid"></div>
        <form action="validate_email " method="post">
          <label>Enter your email to start test</label>
          <?php if ($error || $_GET['error']) echo '<div class="alert alert-danger">' . $error . $_GET['error'] . '</div>'; ?>
          <input type="text" name="email" placeholder="Your email address" class="form-control" required>
          <input type="submit" value="Login" class="btn btn-primary form-control">
        </form>
      </div>

      <div class="col-md-1"></div>

    </div>
  </div>
</body>

</html>