<?php
require_once "inc/auth.php";
require_once "../inc/db.php";

$adminRole = $_SESSION['admin_role'] ?? 'admin';
$orgId = isset($_SESSION['org_id']) && $_SESSION['org_id'] !== null ? (int) $_SESSION['org_id'] : 0;
if ($adminRole !== 'super_admin' && $orgId < 1) {
    http_response_code(403);
    exit('Your account is not linked to an association.');
}

if (empty($_SESSION['cbt_applicants_bulk_upload_csrf'])) {
    $_SESSION['cbt_applicants_bulk_upload_csrf'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['cbt_applicants_bulk_upload_csrf'];
$status = $_GET['status'] ?? '';
$message = $_GET['msg'] ?? '';
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Bulk Upload Applicants - Associa8</title>
    <link rel="shortcut icon" href="../images/fav-logo.png" type="image/x-icon" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="../css/preloader.css" />
    <link rel="stylesheet" href="../css/dashboard.css?v=20261016-responsive-tables-1" />
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
            <h1 class="page-title">Bulk Upload Applicants</h1>
          </div>
        </header>
        <div class="dashboard-content">
          <div class="page-action-header mb-4">
            <div>
              <h2 class="page-title-main">Import applicants</h2>
              <p class="page-subtitle">Upload a CSV file with your applicant details using the template below.</p>
            </div>
            <a href="admission-management.php" class="btn-outline-primary"><i class="fa-solid fa-arrow-left"></i> Back to admissions</a>
          </div>

          <section class="dashboard-card structure-form-card">
            <form action="proc-bulk-upload-applicants.php" method="POST" enctype="multipart/form-data" class="structure-form">
              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>" />

              <div class="form-group">
                <label class="form-label" for="applicantsFile">Applicant template (CSV)</label>
                <input class="form-control" id="applicantsFile" name="applicants_file" type="file" accept=".csv,text/csv" required />
                <small>Use the <a href="proc-download-cbt-applicant-template.php">downloadable applicant template</a>. Required header columns: <strong>applicant_name,email,phone,guarantor_name,guarantor_email,guarantor_phone,guarantor_relationship</strong>. Keep the exact header names and use one row per applicant.</small>
              </div>

              <div class="structure-form-actions">
                <a href="admission-management.php" class="btn-action-dark-outline">Cancel</a>
                <button class="btn-navy-filled" type="submit"><i class="fa-solid fa-upload"></i> Import applicants</button>
              </div>
            </form>
          </section>
        </div>
        <?php include('inc/footer.php'); ?>
      </main>
    </div>
    <script src="../js/admin-sidebar.js"></script>
    <script src="../js/preloader.js"></script>
    <?php if ($status !== ''): ?>
      <script>
        window.addEventListener("DOMContentLoaded", () => {
          if (!window.AppModal) {
            console.error("The applicant upload result dialog is unavailable.");
            return;
          }
          const succeeded = <?php echo json_encode($status === 'success'); ?>;
          window.AppModal.open({
            type: succeeded ? "success" : "error",
            heading: succeeded ? "Applicants imported" : "Upload not completed",
            body: <?php echo json_encode($message !== '' ? $message : 'No applicants were imported. Review the CSV and try again.', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>,
          });
          window.history.replaceState({}, document.title, window.location.pathname);
        });
      </script>
    <?php endif; ?>
  </body>
</html>
