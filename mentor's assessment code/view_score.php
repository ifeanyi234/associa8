<?php

ob_start();
session_start();
if (!isset($_SESSION['admin_user'])) {
    include('index.php');
    exit;
}

include('../config.php');
include('connection/connect.php');
require_once('include/fns.php');


?>
<!DOCTYPE HTML>
<html>

<head>
    <title>Participants Scores | Phillips ATS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- <script type="application/x-javascript"> addEventListener("load", function() { setTimeout(hideURLbar, 0); }, false); function hideURLbar(){ window.scrollTo(0,1); } </script> -->
    <!-- Bootstrap Core CSS -->
    <link href="css/bootstrap.css" rel='stylesheet' type='text/css' />
    <!-- Custom CSS -->
    <link href="css/style.css" rel='stylesheet' type='text/css' />
    <!-- font CSS -->
    <!-- font-awesome icons -->
    <link href="css/font-awesome.css" rel="stylesheet">
    <link href="icons/font-awesome/css/fontawesome-all.css" rel="stylesheet">
    <!-- //font-awesome icons -->
    <!-- js-->
    <script src="js/jquery-1.11.1.min.js"></script>
    <script src="js/modernizr.custom.js"></script>
    <script src="js/my-js.js"></script>
    <!--//js-->
    <!--webfonts-->
    <link href='//fonts.googleapis.com/css?family=Roboto+Condensed:400,300,300italic,400italic,700,700italic'
        rel='stylesheet' type='text/css'>
    <!--//webfonts-->
    <!--animate-->
    <link href="css/animate.css" rel="stylesheet" type="text/css" media="all">
    <script src="js/wow.min.js"></script>
    <script>
        new WOW().init();
    </script>
    <!--//end-animate-->
    <!-- Metis Menu -->
    <script src="js/metisMenu.min.js"></script>
    <script src="js/custom.js"></script>
    <link href="css/custom.css" rel="stylesheet">
    <link rel="icon" type="icon" href="images/icon.png">

    <!--//Metis Menu -->
    <!-- Datatables -->
    <!-- smart table -->
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap5.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/dataTables.bootstrap5.min.css">

    <script>
        $(document).ready(function() {
            $('.table').DataTable();
        });
    </script>
    <!-- smart table -->
    <script src="ckeditor/ckeditor.js"></script>
</head>

<body class="cbp-spmenu-push">
    <div class="main-content">
        <!--left-fixed -navigation-->
        <?php include('include/side_nav.php'); ?>
        <!--left-fixed -navigation-->
        <!-- header-starts -->
        <?php include('include/header.php'); ?>
        <!-- //header-ends -->
        <!-- main content start-->
        <div id="page-wrapper">
            <div class="main-page">
                <div class="forms validation">


                    <form action="process-exam?cat=<?php echo base64_encode($cat); ?>" method="post">



                        <div class="tables">
                            <div class="table-responsive bs-example widget-shadow">
                                <div class="header">
                                    <p id="errorCheck"></p>

                                    <?php if (privilege() == 'Super Admin' || 'Admin') {
                                    ?>
                                        <ul class="header-dropdown" style="float: right; display: flex;
                                justify-content: space-between;  list-style-type: none;">

                                            <div style=" justify-content: center; display: flex;" class="col-md-9">
                                                <span class="col-md-12">Move Applicant To</span>
                                                <select name="stage" class="form-control">
                                                    <option value="">Select</option>

                                                    <option>First Level Interview</option>
                                                    <option>Second Level Interview</option>
                                                    <option>Successful</option>
                                                    <option>Unsuccessful</option>
                                                    <option>Assigned to Client</option>
                                                    <option>Resumed</option>
                                                </select>

                                                <input id="move" style="margin-left: 5px;" type="submit" name="btn_move" value="Move" class="btn btn-success" onclick="return confirm('Moving candidate using this option does not send any notification to the candidates.\n\n If you intend to send notification to candidates, please use the email icon on the right. \n\n Do you want to continue?');">
                                            </div>







                                            <!-- <button style="text-align: center;" type="button" class="btn btn-primary disabled "
                                        data-toggle="modal" data-target="#exampleModal" data-whatever="@mdo">Click here
                                        to complete the recruitment plan -->
                                            </button>

                                            <li style="margin-right: 10px;"><a href="export-results"><button data-toggle="tooltip" data-placement="top"
                                                        data-original-title="Export All Applicants" type="button" id="exportSelectall"
                                                        class="btn btn-sm btn-success action_btn" name="btn_export_all"><i
                                                            class="fas fa-file-excel" style="color: #fff;"></i></button></a></li>

                                            <!-- <li style="margin-right: 10px;"><button class="btn btn-sm btn-danger"
                                            data-toggle="tooltip"
                                            onClick="window.location.href='export-record?export=applicantsPDF'"
                                            data-placement="top" data-original-title="Export as pdf"><i
                                                class="fas fa-file-pdf" style="color: #fff"></i></button></li> -->

                                            <li style="margin-right: 10px;"><button data-toggle="tooltip" data-placement="top"
                                                    data-original-title="Export Selected Applicant" type="submit" id="exportSelect"
                                                    class="btn btn-sm btn-info action_btn" name="btn_export"><i
                                                        class="fas fa-user" style="color: white"></i></button></li>

                                            <li style="margin-right: 10px;"><button data-toggle="tooltip" data-placement="top"
                                                    data-original-title="Delete Applicant" type="submit" id="deleteSelect"
                                                    class="btn btn-sm btn-danger action_btn" name="btn_delete" onclick="return confirm('Are you sure you want to delete the selected records?');"><i
                                                        class="fas fa-times" style="color: white"></i></button></li>



                                        </ul>

                                    <?php } ?>
                                    <script>
                                        $(function() {
                                            $('[data-toggle="tooltip"]').tooltip()
                                        })
                                    </script>

                                    <h4>Applicants List</h4>
                                    <?php if ($_GET['id'] == 'required') echo '<div class="alert alert-danger">No ID was selected </div>'; ?>

                                    <?php

                                    if ($success || $_GET['success']) {
                                        echo '<div class="alert alert-success">' . $success . $_GET['success'] . '</div>';
                                    }
                                    if ($error || $_GET['error']) {
                                        echo '<div class="alert alert-danger">' . $error . $_GET['error'] . '</div>';
                                    }

                                    ?>
                                    <?php if ($_GET['del'] == 'success') echo '<div class="alert alert-success">Applicant successfully deleted</div>'; ?>


                                </div>

                                <div class="tables">

                                    <table class="table table-stripe table-hover">
                                        <!-- <h4>Current Users:</h4> -->
                                        <thead>
                                            <tr>
                                                <th>
                                                    <label class="fancy-checkbox">
                                                        <input class="select-all" type="checkbox" name="checkbox"
                                                            id="checkAll">
                                                        <span></span>
                                                    </label>
                                                </th>



                                                <th>Fullname</th>
                                                <!-- <th>Email</th> -->

                                                <th>Email</th>
                                                <th>Position</th>
                                                <th>Scores</th>
                                                <th>Remark</th>
                                                <th>Status</th>

                                                <th style="width:100px;">Action</th>

                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            // $color = array('active','info','warning','active','danger','light blue');


                                            $query = "select * from exam_result where archieved IS NULL order by id desc";
                                            $result = mysqli_query($db, $query);
                                            $num = mysqli_num_rows($result);
                                            for ($i = 0; $i < $num; $i++) {
                                                $row = mysqli_fetch_array($result);
                                            ?>
                                                <tr>
                                                    <td class="v-align-middle">
                                                        <label class="fancy-checkbox">
                                                            <input class="checkbox-tick" type="checkbox"
                                                                value="<?php echo $row['job_applied_id']; ?>" name="id[]">
                                                            <span></span>
                                                        </label>



                                                    </td>


                                                    <td><?php echo $row['fname']; ?> <?php echo $row['lname']; ?><br>
                                                    </td>

                                                    <td><?php echo $row['email']; ?></td>
                                                    <td><?php echo $row['job_title']; ?></td>
                                                    <td><?php echo $row['average']; ?>%</td>
                                                    <td><?php echo $row['remark']; ?></td>
                                                    <td><?php echo $row['status']; ?></td>

                                                    <td>


                                                        <a data-toggle="tooltip" data-placement="top"
                                                            data-original-title="Delete Applicant" class="btn btn-sm btn-outline-secondary"
                                                            onclick="return confirm('Are you sure you want to delete <?php echo $row['firstname']; ?>'s 'record?')"
                                                            href="delete-record?id=<?php echo $row['id']; ?>&tab=exam_result&return=view_score"><i
                                                                class="fas fa-trash"></i></a>
                                                    </td>


                                                </tr>
                                                <script>
                                                    $(function() {
                                                        $('[data-toggle="tooltip"]').tooltip()
                                                    })
                                                </script>
                                            <?php } ?>
                                            <!-- <div class="col-12 profile_results" id="credentials_result"> </div>-->


                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </form>


                </div>

                <div class="clearfix"> </div>
                <!-- </div> -->
            </div>
        </div>
    </div>
    <!-- modsal -->
    <div class="main-page general">
        <div class="col-md-4 modal-grids">

            <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
                <div class="modal-dialog" role="document">
                    <form method="post"
                        action="<?php echo $_SERVER['PHP_SELF']; ?>?cat=<?php echo base64_encode($cat); ?>">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                        aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title" id="exampleModalLabel">Filter Applicants
                                </h4>
                            </div>


                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6 form-group ">
                                        <span>Age From</span>
                                        <select name="ageFrom" class="form-control">
                                            <option value="">Select</option>
                                            <?php list_val_distinct('jobs_applied', 'age'); ?>
                                        </select>
                                    </div>

                                    <div class="col-md-6 form-group ">
                                        <span class="">Age To</span>
                                        <select name="ageTo" class="form-control">
                                            <option value="">Select</option>
                                            <?php list_val_distinct('jobs_applied', 'age'); ?>
                                        </select>
                                    </div>

                                    <div class="col-md-12 form-group input">
                                        <span class=" ">Position</span>
                                        <select name="job_title" class="form-control">
                                            <option value="">Select</option>
                                            <?php list_val_distinct('jobs_applied', 'job_title'); ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 form-group ">
                                        <span class=" ">Gender</span>
                                        <select name="gender" class="form-control">
                                            <option value="">Select</option>
                                            <?php list_val_distinct('jobs_applied', 'gender'); ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 form-group ">
                                        <span class=" ">Qualification </span>
                                        <select name="qualification" class="form-control">
                                            <option value="">Select</option>
                                            <?php list_val_distinct('jobs_applied', 'qualification'); ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 form-group ">
                                        <span class=" ">Class of Degree </span>
                                        <select name="class_degree" class="form-control">
                                            <option value="">Select</option>
                                            <?php list_val_distinct('jobs_applied', 'class_degree'); ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 form-group ">
                                        <span class=" ">Location </span>
                                        <select name="location" class="form-control">
                                            <option value="">Select</option>
                                            <?php list_val_distinct('jobs_applied', 'state'); ?>
                                        </select>
                                    </div>



                                </div>
                            </div>

                            <div class="modal-footer">
                                <input type="hidden" name="id" value="<?php echo $id; ?>">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                <input name="btn_filter" type="submit" class="btn btn-danger" value="Filter">
                            </div>


                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>
    <!-- Email modal -->
    <div class="main-page general">
        <div class="col-md-4 modal-grids">

            <div class="modal fade" id="emailInvite" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
                <div class="modal-dialog" role="document">
                    <form method="post"
                        action="proc-message?cat=<?php echo base64_encode($cat); ?>">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                        aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title" id="email_modal">Send Message and Move Canidate
                                </h4>
                            </div>


                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6 form-group ">
                                        <span>Email(s)</span>
                                        <input type="text" name="email" id="emailInviteCheck" class="form-control" readonly>
                                    </div>

                                    <div class="col-md-6 form-group ">
                                        <span class="">Phone Number(s)</span>
                                        <input type="text" name="phone" id="smsInviteCheck" class="form-control" readonly>
                                    </div>
                                    <div class="col-md-6 form-group ">
                                        <span class=" ">Template </span>
                                        <select name="template_id" class="form-control" id="template_id">
                                            <option value="">Select</option>
                                            <?php list_val('message_template', 'template_name', 'id'); ?>
                                        </select>
                                    </div>

                                    <div class="col-md-6 form-group ">
                                        <span class=" ">Subject </span>
                                        <input type="text" name="subject" id="subject" class="form-control">
                                    </div>
                                    <div class="col-md-12 form-group ">
                                        <!-- <span class=" ">Template </span> -->
                                        <textarea name="body_msg" id="body_msg">

                                        </textarea>
                                        <!-- <script>
                                            CKEDITOR.replace('body_msg');
                                        </script> -->
                                    </div>

                                    <div class="col-md-12 form-group ">
                                        <span class=" ">Sms Content </span>
                                        <textarea name="sms" id="sms" class="form-control">

                                   </textarea>
                                    </div>



                                </div>
                            </div>

                            <div class="modal-footer">
                                <input type="hidden" name="id" value="<?php echo $id; ?>">
                                <input type="hidden" name="jobs_applied_id" id="idList">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                <input name="btn_filter" type="submit" class="btn btn-danger" value="Send Invite">
                            </div>


                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>
    <p id="error"></p>

    <!-- Email modal end -->
    <!--footer-->
    <?php include('include/footer.php'); ?>
    <!--//footer-->
    </div>
    <!-- Classie -->
    <script src="js/classie.js"></script>
    <script src="dist/js/toastr.js"></script>
    <script>
        var menuLeft = document.getElementById('cbp-spmenu-s1'),
            showLeftPush = document.getElementById('showLeftPush'),
            body = document.body;

        showLeftPush.onclick = function() {
            classie.toggle(this, 'active');
            classie.toggle(body, 'cbp-spmenu-push-toright');
            classie.toggle(menuLeft, 'cbp-spmenu-open');
            disableOther('showLeftPush');
        };

        function disableOther(button) {
            if (button !== 'showLeftPush') {
                classie.toggle(showLeftPush, 'disabled');
            }
        }
    </script>

    <script src="dist/js/toastr.js"></script>

    <script type="text/javascript">
        $(document).ready(function() {

            $("#sendEmailInvite").click('click', (function() {


                var emailCheck = 'yes';
                var checkID = $.map($("input[name='id[]']:checked"), function(e, i) {
                    return +e.value;
                });
                if (checkID == "") {

                    // toastr.options.closeButton = true;
                    // toastr.options.positionClass = 'toast-bottom-right';
                    // toastr['#error']('You need to check at least one applicant');
                    alert('You need to check at least one applicant');
                    return false;



                } else {
                    $.ajax({
                        url: "get_email.php",
                        type: "POST",
                        data: {
                            checkID: checkID,
                            emailCheck: emailCheck
                        },
                        dataType: "json",
                        success: function(response) {
                            $('#emailInvite').modal('show');
                            $('#emailInviteCheck').val(response[0]);
                            $('#smsInviteCheck').val(response[1]);
                            $('#idList').val(response[2]);
                        }
                    });
                }
            }));
        });
    </script>

    <script type="text/javascript">
        $(document).ready(function() {
            $("#move").on('click', (function() {
                var id = $.map($("input[name='id[]']:checked"), function(e, i) {
                    return +e.value;
                });
                if (id == "") {
                    // toastr.options.closeButton = true;
                    // toastr.options.positionClass = 'toast-bottom-right';
                    // toastr['error']('You need to check at least one applicant');
                    alert('You need to check at least one applicant');
                    return false;

                }
            }));
        });
    </script>


    <script type="text/javascript">
        $(document).ready(function() {

            $("#template_id").change(function() {


                var template_id = $(this).val();
                $.ajax({
                    url: "get_message.php",
                    type: "POST",
                    data: {
                        template_id: template_id
                    },
                    dataType: "json",
                    success: function(response) {
                        $('#subject').val(response[0]);
                        $('#body_msg').val(response[1]);
                        $('#sms').html(response[2]);
                    }
                });
            });
        });
    </script>



    <script type="text/javascript">
        $(document).ready(function() {
            $("#exportSelect").on('click', (function() {
                var checkID = $.map($("input[name='id[]']:checked"), function(e, i) {
                    return +e.value;
                });
                if (checkID == "") {
                    // toastr.options.closeButton = true;
                    // toastr.options.positionClass = 'toast-bottom-right';
                    // toastr['error']('You need to check at least one applicant');
                    alert('You need to check at least one applicant');
                    return false;

                }
            }));
        });
    </script>


    <!--scrolling js-->
    <script src="js/jquery.nicescroll.js"></script>
    <script src="js/scripts.js"></script>
    <!--//scrolling js-->
    <!-- Bootstrap Core JavaScript -->
    <script src="js/bootstrap.js"> </script>
    <!--validator js-->
    <script src="js/validator.min.js"></script>
    <!--//validator js-->


</body>

</html>