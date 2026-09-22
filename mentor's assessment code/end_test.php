<?php
ob_start();
ob_start();
session_start();

include('../config.php');
include('../outsourcehr-admin/connection/connect.php');
require_once('../outsourcehr-admin/inc/fns.php');

require('../outsourcehr-admin/PHPMailer/PHPMailerAutoload.php');

if (!isset($_SESSION['ats_candidate'])) {
    include('index.php');
    exit;
}

$user = $_SESSION['ats_candidate'];
$exam_code = $_SESSION['exam_code'];

$question_count = num_questions($_SESSION['job_id']);

for ($i = 1; $i <= $question_count; $i++) {
    $ques[$i] = $_POST['q' . $i];
    $q_type[$i] = $_POST['q_type' . $i]; //get the question type
    $q_no[$i] = $_POST['q_no' . $i]; //get the question no
    $q_category[$i] = $_POST['q_category' . $i]; //get the question category

    if (!$ques[$i]) {
        $no_answer[] = 'Question ' . $i;
    }

    if ($q_type[$i] == 'space') {
        //if question is a textfield question, get the question id and merge with answer
        $ques[$i] = $q_no[$i] . '=' . $ques[$i];
    } elseif ($q_type[$i] == 'multiple') {
        for ($y = 0; $y < count($ques[$i]); $y++) {
            $ques[$y] = implode(',', $ques[$i]);
        }
        $ques[$i] =  $ques[0];
    } else {
        $ques[$i] =  $ques[$i];
    }



    $query = "update exam_result set ans_" . $i . " = '" . $ques[$i] . "' where email = '$user' and exam_code = '$exam_code'";
    $result = mysqli_query($db, $query);

    //function to score each  qestions.
    $points[$i] = score_question($user, $ques[$i], $q_type[$i], $q_category[$i]);
}

$total_score = array_sum($points);
$average_point = round($total_score / $question_count * 100);

$_SESSION['average_point'] = $average_point;


$remark = get_remark($_SESSION['job_title'], $_SESSION['average_point']);
$cut_off = get_off($_SESSION['job_title']);


$query = "update exam_result set total_score = '$total_score', average = '" . $average_point . "', remark = '$remark', status = 'finished' where email = '$user'";
$result = mysqli_query($db, $query);

assessment_log($_SESSION['ats_candidate'], 'Completed Assessment');

$subject = 'Your test has ended';

$content =  'Hello ' . $_SESSION['firstname'] . ",<br><br>Your test has ended<br><br>You will ONLY be contacted if you have passed this test.<br><br>";


send_email($_SESSION['email'], $_SESSION['firstname'], org(), $subject, $content);

if ($average_point >= cutoff($cut_off)) {
    push_for_nextstage('First Level Interview', $_SESSION['job_applied_id']);
}


$final_page = "thanks-page?p=" . base64_encode($average_point) . "&r=" . base64_encode($remark) . "&c=" . base64_encode($cut_off);

if (empty($no_answer)) {
    header("Location: $final_page");
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
    <link rel="icon" href="images/favicon.png" />
    <!-- <link href="//fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link href="//fonts.googleapis.com/css2?family=Allerta+Stencil&display=swap" rel="stylesheet"> -->
    <link rel="stylesheet" href="dist/css/animate.css">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="icon" href="images/icon.png">
    <script src="jquery-3.6.0.min.js"></script>

</head>

<style>
    body {
        padding: 0px;
        margin: 0px;
        background-image: url('images/test_img.png');
        background-repeat: no-repeat;
        background-size: cover;
        height: 150vh;

    }
</style>

<body>
    <div class="container-fluid">
        <div class="row">

            <div class="col-md-2"></div>
            <div class="col-md-8 text-center " id="test_area">

                <div class="row">

                    <div class="col-6"><img src="images/logo.svg" class="img-fluid logo" alt="" srcset=""></div>
                    <div class="col-6">
                        <div id="timer">
                            <div class="countdown">00:00</div>
                        </div>
                    </div>

                </div>

                <div class="row">

                    <div class="col-md-12">

                        <!-- ------------------------------------------------------------- -->


                        <h3 style="text-align: center;">
                            Your session has ended
                        </h3>
                        <p id="txt" style="font-size: 20px; margin-bottom:-15px;" class="danger">
                            We noticed you did not answer a few quesions. This will definitely affect your scores.
                        </p>
                        <div id="txt" class="">
                            <div style="margin-top: -15px;" class="">
                                <?php
                                for ($u = 0; $u < count($no_answer); $u++) {
                                ?>
                                    <span style="color: red; font-size: 17px;"
                                        class="no_ques_badge"><br><br><?php echo $no_answer[$u]; ?></span>
                                <?php } ?>


                            </div>

                        </div>
                        <br><b>However, you cannot go back</b></p>


                    </div>
                    <div class="col-md-12">
                        <div>
                            <a href="<?php echo $final_page; ?>" style="margin-bottom: 15px;"
                                class="btn btn-primary ">Finish
                            </a> <br>

                        </div>
                    </div>
                </div>


            </div>

        </div>
    </div>
    <div class="col-md-2"></div>
    </div>
    </div>
</body>

</html>