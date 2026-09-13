<?php
require_once "inc/auth.php";
require_once "../inc/db.php";
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>New Admission - Associa8</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" /><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="shortcut icon" href="../images/fav-logo.png" type="image/x-icon" /><link rel="stylesheet" href="../css/preloader.css" /><link rel="stylesheet" href="../css/dashboard.css" />
  </head>
  <body class="admin-body">
    <?php include('inc/preloader.php'); ?><div class="admin-layout"><?php include('inc/sidebar.php'); ?><div class="sidebar-overlay" id="sidebarOverlay"></div><main class="admin-main">
      <header class="admin-header"><div class="header-left"><button class="header-toggle-btn" id="sidebarToggle" type="button"><i class="fa-solid fa-bars"></i></button><h1 class="page-title">New Admission</h1></div><div class="header-right"><div class="header-search"><i class="fa-solid fa-magnifying-glass header-search-icon"></i><input type="text" placeholder="Search...." /></div><div class="admin-user-profile"><div class="avatar-badge">SA</div><div class="user-info"><span class="user-name">Super Admin</span><span class="user-role">Full Access</span></div></div></div></header>
      <div class="dashboard-content"><div class="page-action-header mb-4"><div><h2 class="page-title-main">Create Admission Application</h2><p class="page-subtitle">Add an applicant to the admission review queue.</p></div><a href="admission-management.php" class="btn-outline-primary"><i class="fa-solid fa-arrow-left"></i> Back to admissions</a></div>
        <section class="dashboard-card structure-form-card"><div class="structure-form-heading"><div class="structure-form-icon"><i class="fa-solid fa-user-plus"></i></div><div><h3>Applicant information</h3><p>The application starts in pending review.</p></div></div>
          <form action="proc-add-admission.php" method="POST" class="structure-form"><div class="form-row-2col"><div class="form-group"><label class="form-label" for="applicantName">Applicant name</label><input class="form-control" id="applicantName" name="applicant_name" required /></div><div class="form-group"><label class="form-label" for="applicationNumber">Application number</label><input class="form-control" id="applicationNumber" name="application_number" placeholder="e.g. APP-2026-001" required /></div></div><div class="form-row-2col"><div class="form-group"><label class="form-label" for="email">Email</label><input class="form-control" type="email" id="email" name="email" required /></div><div class="form-group"><label class="form-label" for="phone">Phone</label><input class="form-control" id="phone" name="phone" required /></div></div><div class="form-row-2col"><div class="form-group"><label class="form-label" for="guarantorName">Guarantor name</label><input class="form-control" id="guarantorName" name="guarantor_name" required /></div><div class="form-group"><label class="form-label" for="guarantorRelationship">Relationship</label><input class="form-control" id="guarantorRelationship" name="guarantor_relationship" placeholder="e.g. Employer" required /></div></div><div class="form-row-2col"><div class="form-group"><label class="form-label" for="guarantorEmail">Guarantor email</label><input class="form-control" type="email" id="guarantorEmail" name="guarantor_email" /></div><div class="form-group"><label class="form-label" for="guarantorPhone">Guarantor phone</label><input class="form-control" id="guarantorPhone" name="guarantor_phone" required /></div></div><div class="structure-form-actions"><a href="admission-management.php" class="btn-action-dark-outline">Cancel</a><button class="btn-navy-filled" type="submit"><i class="fa-solid fa-check"></i> Save application</button></div></form>
        </section>
      </div><?php include('inc/footer.php'); ?></main></div>
    <script src="../js/admin-sidebar.js"></script><script src="../js/preloader.js"></script>
  </body>
</html>
