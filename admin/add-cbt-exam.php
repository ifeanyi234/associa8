<?php
require_once "inc/auth.php";
require_once "../inc/db.php";
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Add CBT Exam - Associa8</title>
    <link rel="shortcut icon" href="../images/fav-logo.png" type="image/x-icon" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="../css/preloader.css" />
<link rel="stylesheet" href="../css/dashboard.css" />
  </head>
  <body class="admin-body">
    <?php include('inc/preloader.php'); ?>
    <div class="admin-layout">
      <?php include('inc/sidebar.php'); ?>
      <div class="sidebar-overlay" id="sidebarOverlay">
</div>
      <main class="admin-main">
        <header class="admin-header">
          <div class="header-left">
            <button class="header-toggle-btn" id="sidebarToggle" type="button">
              <i class="fa-solid fa-bars">
</i>
            </button>
            <h1 class="page-title">Add CBT Exam</h1>
          </div>
        </header>
        <div class="dashboard-content">
          <div class="page-action-header mb-4">
            <div>
              <h2 class="page-title-main">Create CBT Exam</h2>
              <p class="page-subtitle">Set the exam rules before adding questions.</p>
            </div>
            <a href="cbt-questions.php" class="btn-outline-primary">
<i class="fa-solid fa-arrow-left">
</i> Back to questions</a>
            </div>
            <section class="dashboard-card structure-form-card">
              <form action="proc-add-cbt-exam.php" method="POST" class="structure-form">
                <div class="form-row-2col">
                  <div class="form-group">
                    <label class="form-label" for="title">Exam title</label>
                    <input class="form-control" id="title" name="title" required />
                  </div>
                  <div class="form-group">
                    <label class="form-label" for="duration">Duration minutes</label>
                    <input class="form-control" type="number" id="duration" name="duration_minutes" min="1" value="60" required />
                  </div>
                </div>
                <div class="form-row-2col">
                  <div class="form-group">
                    <label class="form-label" for="passMark">Pass mark</label>
                    <input class="form-control" type="number" id="passMark" name="pass_mark" min="0" max="100" value="50" required />
                  </div>
                  <div class="form-group">
                    <label class="form-label" for="status">Status</label>
                    <select class="form-select" id="status" name="status">
                      <option value="draft">Draft</option>
                      <option value="active">Active</option>
                    </select>
                  </div>
                </div>
                <div class="structure-form-actions">
                  <a href="cbt-questions.php" class="btn-action-dark-outline">Cancel</a>
                  <button class="btn-navy-filled" type="submit">
<i class="fa-solid fa-check">
</i> Save exam</button>
                </div>
              </form>
            </section>
          </div>
          <?php include('inc/footer.php'); ?>
        </main>
      </div>
      <script src="../js/admin-sidebar.js">
</script>
      <script src="../js/preloader.js">
</script>
  </body>
</html>

