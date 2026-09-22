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


$code = ($_POST['code']);
$thistime = mytime();


$exam_code = $code[0] . $code[1] . $code[2] . $code[3] . $code[4] . $code[5];

$user = $_SESSION['ats_candidate'];

$query = "select * from participant where email = '$user' && exam_code = '$exam_code' and expire_date > DATE('$thistime')";
$result = mysqli_query($db, $query);
$num = mysqli_num_rows($result);
$row = mysqli_fetch_array($result);

$job_id = $row['job_id'];
$job_title = $row['job_title'];
$candidate_id = $row['candidate_id'];
$job_applied_id = $row['job_applied_id'];

if ($num > 0) {
    $query2 = "select * from exam_result where email = '$user' and exam_code = '$exam_code' and status = 'finished'";
    $result2 = mysqli_query($db, $query2);
    $num_rows2 = mysqli_num_rows($result2);

    if ($num_rows2 > 0) {
        $error = 'It appears you have taken this exam. If you have any challenge with this, please contact testadministrator@Klinhr.com ';
        include('index.php');
        exit;
    } else {

        $_SESSION['started'] = $exam_code;
        $_SESSION['exam_code'] = $exam_code;
        $_SESSION['job_id'] = $job_id;
        $_SESSION['job_title'] = $job_title;
        $no_questions = get_val('assessment', 'job_id', $job_id, 'no_of_question');


        //get total question

        $query_ck = "select * from exam_result where email = '$user' and exam_code = '$exam_code' and status = 'started'";
        $result_ck = mysqli_query($db, $query_ck);
        $num_rows_ck = mysqli_num_rows($result_ck);
        if ($num_rows_ck == 0) {
            $query3 = "insert into exam_result set candidate_id = '$candidate_id', job_applied_id = '$job_applied_id', fname= '" . $_SESSION['firstname'] . "', lname= '" . $_SESSION['lastname'] . "', email= '" . $_SESSION['email'] . "', phone= '" . $_SESSION['phone'] . "', exam_code = '$exam_code', job_id = '$job_id', job_title = '$job_title', no_questions = '$no_questions', status = 'started'";
            $result3 = mysqli_query($db, $query3);
        }
        header('Location: test-page');
        // include('test-page.php');
        exit;
    }
} else {
    $error = "Sorry this code is incorrect or has expired.";
    include('code-page.php');
    exit;
}
