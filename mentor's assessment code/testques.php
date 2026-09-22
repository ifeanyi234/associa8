<?php
include('../config.php');
include('../outsourcehr-admin/connection/connect.php');
require_once('../outsourcehr-admin/inc/fns.php');

$query_c = "select * from assessment where assessment_name = 'HR Manager'";
$result_c = mysqli_query($db, $query_c);
$row_c = mysqli_fetch_array($result_c);
$ques_categories = $row_c['category'];
$cat = explode(',', $ques_categories);

if (count($cat) > 1) {
    $clause = implode("' or category = '", $cat) . "'";
    $sql = "select * from questions where category = '$clause order by rand() limit 0, $num_questions";
} else {
    $clause = '';
    $sql = "select * from questions where category = '" . $cat[0] . "' order by rand() limit 0, $num_questions";
}


echo  $sql;
