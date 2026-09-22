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

$user = $_SESSION['ats_candidate'];
$exam_code = $_SESSION['exam_code'];

if (check_exam_status($user, $exam_code) == 'finished') {
    header("Location: index");
    exit;
}



if (!isset($_SESSION['end_time'])) {

    $start_time = date('Y-m-d H:i:s');

    $end_time = get_exam_end_time($start_time, $_SESSION['job_id']);
    $_SESSION['end_time'] = $end_time;

    $query = "update exam_result set start_time = '$start_time', end_time = '$end_time' where exam_code = '$exam_code' and email = '$user'";

    $result = mysqli_query($db, $query);
}

//check if session end time it is past current time
if ($_SESSION['end_time'] < date('Y-m-d H:i:s')) {
    $error = 'Your exam session has ended.';
    header("Location: index?error=$error");
}


//get end time from database
$query2 = "select * from exam_result where email = '$user' and exam_code = '$exam_code'";
$result2 = mysqli_query($db, $query2);
$row2 = mysqli_fetch_array($result2);

$time_diff = get_time_left($row2['end_time']);


if (strpos($time_diff, '-') !== false) {
    header("Location: index");
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
    <script src="jquery-3.6.0.min.js"></script>
    <script>
        $(function() {
            document.addEventListener("contextmenu", event => event.preventDefault());
            $('.questions').hide();
            $('#question1').show();

            <?php
            $num_questions = num_questions($_SESSION['job_id']);
            $num_pages = num_questions($_SESSION['job_id']) + 1;

            for ($q = 1; $q <= $num_pages; $q++) {
            ?>

                $('#btn_next<?php echo $q; ?>').click(function() {
                    $('.questions').hide();
                    $('#question<?php echo $q + 1; ?>').show(100);
                    checkInternetConnection();
                })
            <?php
            }
            ?>

            <?php
            for ($q = 2; $q <= $num_pages; $q++) {
            ?>
                $('#btn_prev<?php echo $q; ?>').click(function() {
                    $('.questions').hide();
                    $('#question<?php echo $q - 1; ?>').show(100);
                })
            <?php } ?>

            // My timer starts here
            // let timer2 = "10:01";
            var timer2 = "<?php echo $time_diff; ?>";
            var interval = setInterval(function() {
                var timer = timer2.split(":");
                //by parsing integer, I avoid all extra string processing
                var minutes = parseInt(timer[0], 10);
                var seconds = parseInt(timer[1], 10);

                --seconds;

                minutes = seconds < 0 ? --minutes : minutes;
                if (minutes < 0) clearInterval(interval);
                seconds = seconds < 0 ? 59 : seconds;
                seconds = seconds < 10 ? "0" + seconds : seconds;
                //minutes = (minutes < 10) ?  minutes : minutes;
                $(".countdown").html(minutes + ":" + seconds);
                timer2 = minutes + ":" + seconds;
            }, 1000);

            // my timer ends here

            // check timer and warn if time is less that an agreed time
            setInterval(function() {
                var wtime = $(".countdown").html();

                // if (wseconds == 20) {
                //   alert("You have 20 seconds remaining");

                if (wtime == '5:00') {
                    alert("You have 5 minutes remaining");

                    $(".countdown").css("border-width", "3px");
                    $(".countdown").css("border-color", "red");
                    $(".countdown").css("color", "red");
                    $(".countdown").css("border-style", "solid");
                }

                if (wtime == '0:01') {
                    alert("Your test has ended. We will redirect you to the score page");
                    window.location.href = "end_test";
                }

                // var wtime_split = wtime.split(":");
                // var wseconds = wtime_split[1];

                // if (wseconds == 1) {
                //   alert("You test has ended. We will redirect you to the score page");
                //   window.location.href = "end_test";
                // }
            }, 1000);



            //check internet connection


            setTimeout(function() {
                checkInternetConnection();
            }, 1000);

        });
    </script>

    <script>
        function checkInternetConnection() {
            $('#exampleModal').hide();
            var status = navigator.onLine;
            if (status) {
                console.log('Internet Available !!');
            } else {
                // alert('Your system is currently not connected to the internet.\n\nDO NOT REFRESH OR CLOSE YOUR PAGE. \n\nJust check your internet connection and click OK to continue');
                // return false;
                $('#exampleModal').modal('show');
                return false;
            }

        }
    </script>
</head>

<style>
    body {
        padding: 0px;
        margin: 0px;
        background-image: url('images/test_img.png');
        background-attachment: fixed;
        background-repeat: no-repeat;
        background-size: cover;
        height: 150vh;

    }
</style>

<body>

    <div style="top: 100px;" id="exampleModal" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Sorry there is no internet connection enabled. Kindly check your internet
                        connection before you can proceed. </h5>

                    <img src="images/no internet.png" width="150" class="img-container">
                    <!-- <button type="button" id="checked" class="btn" data-dismiss="modal" type="button"
                    ><i class="fa fa-times"></i> </button> -->
                </div>
                <div class="modal-body">

                    <div class="form-group">
                        <button type="button" id="checked" class="btn btn-success text-center" data-dismiss="modal"
                            type="button">Close</button>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">

            <div class="col-md-2"></div>
            <div class="col-md-8 text-center " id="test_area">

                <div class="row">

                    <div class="col-6"><img src="../outsourcehr-admin/assets/img/logo.png" style="width:40%;"
                            class="img-fluid logo" width="250" alt="" srcset=""></div>
                    <div class="col-6">
                        <div id="timer">
                            <div class="countdown">00:00</div>
                        </div>
                    </div>

                </div>

                <div class="row">
                    <?php

                    //get category of question
                    $query_c = "select * from assessment where assessment_name = '" . $_SESSION['job_title'] . "'";
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
                    // $sql = "select * from questions order by rand() limit 0, $num_questions";
                    $result_1 = mysqli_query($db, $sql);
                    $num_1 = mysqli_num_rows($result_1);
                    for ($i = 1; $i <= $num_1; $i++) {
                        $row_1 = mysqli_fetch_array($result_1);


                    ?>
                        <div class="col-12">

                            <!-- ------------------------------------------------------------- -->
                            <form class="qtn" action="end_test" method="post">
                                <!-- <div id="question1" data-ques="1" class="questions"> -->
                                <div id="question<?php echo $i; ?>" data-ques="<?php echo $i; ?>" class="questions">
                                    <h3 class="">
                                        Question <?php echo $i; ?>
                                    </h3>
                                    <p style="font-size: 15px;" class="question">
                                        <?php echo $row_1['question']; ?>
                                    </p>

                                    <?php
                                    //display image is there is a media file
                                    if ($row_1['media']) {
                                        echo '<img src="../admin/upload/assessment/' . $row_1['media'] . '" style="width:100%; height:150px; margin-bottom:100px;">';
                                    }
                                    ?>

                                    <?php
                                    //if question is textfield question
                                    if ($row_1['type'] == 'space') {

                                    ?>
                                        <div>
                                            <input class="form-control" type="text" name="q<?php echo $i; ?>"
                                                placeholder="Enter your answer" />
                                            <input class="form-control" type="hidden" name="q_no<?php echo $i; ?>"
                                                value="<?php echo $row_1['id']; ?>" />



                                        </div>
                                    <?php
                                    }
                                    ?>

                                    <?php
                                    //if question is a single option question
                                    if ($row_1['type'] == 'single') {

                                    ?>

                                        <div class="opt">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="q<?php echo $i; ?>"
                                                    value="<?php echo $row_1['id']; ?>=A" />
                                                <label class="form-check-label"
                                                    for="flexRadioDefault1"><?php echo $row_1['option_a']; ?></label>


                                            </div>
                                            <div class="form-check">


                                                <input class="form-check-input" type="radio" name="q<?php echo $i; ?>"
                                                    value="<?php echo $row_1['id']; ?>=B" />
                                                <label class="form-check-label"
                                                    for="flexRadioDefault1"><?php echo $row_1['option_b']; ?></label>

                                            </div>
                                            <div class="form-check">
                                                <?php if ($row_1['option_c'] != '') {
                                                ?>
                                                    <input class="form-check-input" type="radio" name="q<?php echo $i; ?>"
                                                        value="<?php echo $row_1['id']; ?>=C" />
                                                    <label class="form-check-label"
                                                        for="flexRadioDefault1"><?php echo $row_1['option_c']; ?></label>
                                                <?php } ?>

                                            </div>
                                            <div class="form-check">
                                                <?php if ($row_1['option_d'] != '') {
                                                ?>
                                                    <input class="form-check-input" type="radio" name="q<?php echo $i; ?>"
                                                        value="<?php echo $row_1['id']; ?>=D" />
                                                    <label class="form-check-label"
                                                        for="flexRadioDefault1"><?php echo $row_1['option_d']; ?></label>
                                                <?php } ?>
                                            </div>
                                            <div class="form-check">

                                                <?php if ($row_1['option_e'] != '') {
                                                ?>
                                                    <input class="form-check-input" type="radio" name="q<?php echo $i; ?>"
                                                        value="<?php echo $row_1['id']; ?>=E" />
                                                    <label class="form-check-label"
                                                        for="flexRadioDefault1"><?php echo $row_1['option_e']; ?></label>
                                                <?php } ?>
                                            </div>
                                        </div>

                                    <?php } ?>




                                    <?php
                                    //if question is a single option question
                                    if ($row_1['type'] == 'multiple') {

                                    ?>

                                        <div class="opt">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="q<?php echo $i; ?>[]"
                                                    value="<?php echo $row_1['id']; ?>=A" />
                                                <label class="form-check-label"
                                                    for="flexRadioDefault1"><?php echo $row_1['option_a']; ?></label>


                                            </div>
                                            <div class="form-check">


                                                <input class="form-check-input" type="checkbox" name="q<?php echo $i; ?>[]"
                                                    value="<?php echo $row_1['id']; ?>=B" />
                                                <label class="form-check-label"
                                                    for="flexRadioDefault1"><?php echo $row_1['option_b']; ?></label>

                                            </div>
                                            <div class="form-check">

                                                <input class="form-check-input" type="checkbox" name="q<?php echo $i; ?>[]"
                                                    value="<?php echo $row_1['id']; ?>=C" />
                                                <label class="form-check-label"
                                                    for="flexRadioDefault1"><?php echo $row_1['option_c']; ?></label>
                                            </div>
                                            <div class="form-check">

                                                <input class="form-check-input" type="checkbox" name="q<?php echo $i; ?>[]"
                                                    value="<?php echo $row_1['id']; ?>=D" />
                                                <label class="form-check-label"
                                                    for="flexRadioDefault1"><?php echo $row_1['option_d']; ?></label>
                                            </div>
                                            <div class="form-check">

                                                <?php if ($row_1['option_e'] != '') {
                                                ?>
                                                    <input class="form-check-input" type="checkbox" name="q<?php echo $i; ?>[]"
                                                        value="<?php echo $row_1['id']; ?>=E" />
                                                    <label class="form-check-label"
                                                        for="flexRadioDefault1"><?php echo $row_1['option_e']; ?></label>
                                                <?php } ?>
                                            </div>
                                        </div>

                                    <?php } ?>

                                    <input type="hidden" name="q_type<?php echo $i; ?>"
                                        value="<?php echo $row_1['type']; ?>" />



                                    <input type="hidden" name="q_category<?php echo $i; ?>"
                                        value="<?php echo $row_1['category']; ?>" />


                                    <br>

                                    <div class="row btt">
                                        <div class="col-md-9"></div>
                                        <div class="col-md-12">

                                            <a href="#" style="float: left;" class="btn btn-primary "
                                                id="btn_prev<?php echo $i; ?>" data-prev-btn="<?php echo $i; ?>">Previous
                                            </a>
                                            <a href="#" style="float: right;" class="btn btn-primary "
                                                id="btn_next<?php echo $i; ?>" data-next-btn="<?php echo $i; ?>">Next
                                            </a>

                                        </div>

                                    </div>
                                </div>
                            <?php } ?>
                            <div id="question<?php echo $num_1 + 1; ?>" data-ques="<?php echo $num_1 + 1; ?>"
                                class="questions">
                                <p class="question">
                                    You have gotten to the last question. Please go back to review your previous
                                    questions.
                                    <span id="internetok">
                                        <br><br>Your internet is OK. Click NEXT if you are ready to end test.
                                        <br><br><b> Please note that you cannot "Go Back" if
                                            you go beyond this page.</b>
                                        <span>
                                            <span id="internetissue" style="color:red;">
                                                <!-- <br><br><b>There is an issue with your internet. Please check your connection before you click NEXT</b> -->
                                                <span>
                                </p>
                                <div class="col-md-12">
                                    <!-- <button class="nxt"> -->
                                    <a href="#" class="btn btn-primary " id="btn_prev<?php echo $num_1 + 1; ?>"
                                        data-prev-btn="<?php echo $num_1 + 1; ?>">Previous
                                    </a>
                                    <input type="submit"
                                        onclick="return confirm('You have gotten to the end of your test.\n\nKindly click the PREVIOUS button to go back and run through all your questions before you proceed beyond this page\nClicking the NEXT button means that you are sure you want to submit your test\n\nYOU CANNOT GO BACK AFTER THIS POINT & YOU MUST NOT REFRESH YOUR PAGE FOR ANY REASON');"
                                        class="btn btn-primary " id="btn_next<?php echo $num_1 + 1; ?>"
                                        data-next-btn="<?php echo $num_1 + 1; ?>" value="Next">

                                    <!-- </button> -->
                                </div>
                            </div>

                            </form>

                        </div>

                </div>
            </div>
            <div class="col-md-2"></div>
        </div>
    </div>




</body>

</html>