<?php
  $host = "localhost";
  $db_name = "associa8";
  $db_user = "associa8";
  $db_password = "Associa8_234";

  $conn = mysqli_connect($host, $db_user, $db_password, $db_name);

  if(!$conn){
    die("Database connection failed:" . mysqli_connect_error());
  }

  // live server
  // $db_name = "aledoypr_associa8"
  // $db_user = "aledoypr_associa8"

?>