<?php
require_once 'inc/auth.php';
require_once '../inc/db.php';

$adminRole = $_SESSION['admin_role'] ?? 'admin';
$orgId = isset($_SESSION['org_id']) && $_SESSION['org_id'] !== null ? (int) $_SESSION['org_id'] : null;

if ($adminRole !== 'super_admin' && $orgId === null) {
  header('Location: cbt-applicants.php?status=error&msg=' . urlencode('Your account is not linked to an organization.'));
  exit;
}

$applicantsSql = "SELECT id, applicant_name, email, application_number FROM admissions WHERE status = 'under_review'";
$examsSql = "SELECT id, title, duration_minutes, pass_mark FROM cbt_exams WHERE status = 'active'";
if ($adminRole !== 'super_admin') {
    $applicantsSql .= ' AND org_id = ' . (int) $orgId;
    $examsSql .= ' AND org_id = ' . (int) $orgId;
}
$applicantsSql .= ' ORDER BY applied_at DESC';
$examsSql .= ' ORDER BY title';

$applicantsResult = mysqli_query($conn, $applicantsSql);
$examsResult = mysqli_query($conn, $examsSql);
$applicants = [];
$exams = [];

if ($applicantsResult) {
    while ($applicant = mysqli_fetch_assoc($applicantsResult)) {
        $applicants[] = $applicant;
    }
}
if ($examsResult) {
    while ($exam = mysqli_fetch_assoc($examsResult)) {
        $exams[] = $exam;
    }
}

$status = $_GET['status'] ?? '';
$message = $_GET['msg'] ?? '';
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Schedule CBT - Associa8</title>
    <link rel="shortcut icon" href="../images/fav-logo.png" type="image/x-icon" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="../css/preloader.css" />
    <link rel="stylesheet" href="../css/dashboard.css" />
  </head>
  <body class="admin-body">
    <?php include('inc/preloader.php'); ?>
    <div class="admin-layout">
      <?php include('inc/sidebar.php'); ?>
      <div class="sidebar-overlay" id="sidebarOverlay"></div>
      <main class="admin-main">
        <header class="admin-header">
          <div class="header-left">
            <button class="header-toggle-btn" id="sidebarToggle" type="button"><i class="fa-solid fa-bars"></i></button>
            <h1 class="page-title">Schedule CBT</h1>
          </div>
        </header>
        <div class="dashboard-content">
          <div class="page-action-header mb-4">
            <div>
              <h2 class="page-title-main">Schedule applicant assessment</h2>
              <p class="page-subtitle">Assign an active exam and send a time-bound access code.</p>
            </div>
            <a href="cbt-applicants.php" class="btn-outline-primary"><i class="fa-solid fa-arrow-left"></i> Applicants</a>
          </div>

          <section class="dashboard-card structure-form-card">
            <?php if (!$applicants): ?>
              <p class="zone-empty-state">There are no applicants currently under review.</p>
            <?php elseif (!$exams): ?>
              <p class="zone-empty-state">Create and activate an exam before scheduling an applicant.</p>
            <?php else: ?>
              <form action="proc-schedule-cbt.php" method="POST" class="structure-form">
                <div class="form-group">
                  <label class="form-label" for="admissionId">Applicant</label>
                  <select class="form-select" id="admissionId" name="admission_id" required>
                    <option value="">Select applicant</option>
                    <?php foreach ($applicants as $applicant): ?>
                      <option value="<?php echo (int) $applicant['id']; ?>">
                        <?php echo htmlspecialchars($applicant['applicant_name'] . ' - ' . $applicant['application_number'] . ' (' . $applicant['email'] . ')'); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="form-group">
                  <label class="form-label" for="examId">Active exam</label>
                  <select class="form-select" id="examId" name="exam_id" required>
                    <option value="">Select exam</option>
                    <?php foreach ($exams as $exam): ?>
                      <option value="<?php echo (int) $exam['id']; ?>">
                        <?php echo htmlspecialchars($exam['title'] . ' - ' . $exam['duration_minutes'] . ' minutes, pass mark ' . $exam['pass_mark'] . '%'); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="form-group">
                  <label class="form-label" for="scheduledAt">Scheduled time</label>
                  <input class="form-control" type="datetime-local" id="scheduledAt" name="scheduled_at" min="<?php echo date('Y-m-d\\TH:i'); ?>" required />
                  <small class="form-help-text">The access code expires after the selected exam duration.</small>
                </div>
                <div class="structure-form-actions">
                  <a href="cbt-applicants.php" class="btn-action-dark-outline">Cancel</a>
                  <button class="btn-navy-filled" type="submit"><i class="fa-solid fa-calendar-check"></i> Schedule and notify</button>
                </div>
              </form>
            <?php endif; ?>
          </section>
        </div>
        <?php include('inc/footer.php'); ?>
      </main>
    </div>
    <script src="../js/admin-sidebar.js"></script>
    <script src="../js/preloader.js"></script>
    <script>
      window.addEventListener("DOMContentLoaded", () => {
        const params = new URLSearchParams(window.location.search);
        const status = params.get("status");
        if (!status || !window.AppModal) return;

        const success = status === "success";
        window.AppModal.open({
          type: success ? "success" : "error",
          heading: success ? "CBT scheduled" : "CBT not scheduled",
          body: params.get("msg") || "Please try again.",
          detail: success
            ? "The applicant was assigned an exam and notified."
            : "No CBT schedule was saved.",
        });
        window.history.replaceState({}, document.title, window.location.pathname);
      });
    </script>
  </body>
</html>
