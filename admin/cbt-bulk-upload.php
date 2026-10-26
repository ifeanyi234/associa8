<?php
require_once "inc/auth.php";
require_once "../inc/db.php";

$adminRole = $_SESSION['admin_role'] ?? 'admin';
$orgId = isset($_SESSION['org_id']) && $_SESSION['org_id'] !== null ? (int) $_SESSION['org_id'] : 0;
if ($adminRole !== 'super_admin' && $orgId < 1) {
    http_response_code(403);
    exit('Your account is not linked to an association.');
}

if (empty($_SESSION['cbt_bulk_upload_csrf'])) {
    $_SESSION['cbt_bulk_upload_csrf'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['cbt_bulk_upload_csrf'];
$errors = $_SESSION['cbt_bulk_upload_errors'] ?? [];
unset($_SESSION['cbt_bulk_upload_errors']);
$status = $_GET['status'] ?? '';
$message = $_GET['msg'] ?? '';
$errorDetails = [];
foreach (array_slice($errors, 0, 8) as $error) {
    $errorDetails[] = 'Row ' . (int) $error['row'] . ': ' . (string) $error['message'];
}
if (count($errors) > 8) {
    $errorDetails[] = 'And ' . (count($errors) - 8) . ' more row errors.';
}

if ($adminRole === 'super_admin') {
    $examStatement = mysqli_prepare($conn, "SELECT id, title FROM cbt_exams WHERE status = 'draft' AND org_id IS NOT NULL ORDER BY created_at DESC");
} else {
    $examStatement = mysqli_prepare($conn, "SELECT id, title FROM cbt_exams WHERE status = 'draft' AND org_id = ? ORDER BY created_at DESC");
}
if (!$examStatement) {
    error_log('CBT bulk upload exam list prepare failed: ' . mysqli_error($conn));
    http_response_code(500);
    exit('Draft exams are temporarily unavailable.');
}
if ($adminRole !== 'super_admin') {
    mysqli_stmt_bind_param($examStatement, 'i', $orgId);
}
if (!mysqli_stmt_execute($examStatement)) {
    error_log('CBT bulk upload exam list failed: ' . mysqli_stmt_error($examStatement));
    http_response_code(500);
    exit('Draft exams are temporarily unavailable.');
}
$examResult = mysqli_stmt_get_result($examStatement);
if (!$examResult) {
    error_log('CBT bulk upload exam list result failed: ' . mysqli_stmt_error($examStatement));
    http_response_code(500);
    exit('Draft exams are temporarily unavailable.');
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Bulk Upload CBT Questions - Associa8</title>
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
            <h1 class="page-title">Bulk Upload CBT Questions</h1>
          </div>
        </header>
        <div class="dashboard-content">
          <div class="page-action-header mb-4">
            <div>
              <h2 class="page-title-main">Upload questions</h2>
              <p class="page-subtitle">Import questions into a draft exam using the CSV template.</p>
            </div>
            <a href="cbt-questions.php" class="btn-outline-primary"><i class="fa-solid fa-arrow-left"></i> Back to questions</a>
          </div>
          <section class="dashboard-card structure-form-card">
            <?php if (mysqli_num_rows($examResult) === 0): ?>
              <p class="inline-form-message error">Create a draft exam before uploading questions.</p>
            <?php else: ?>
              <form action="proc-bulk-upload-cbt.php" method="POST" enctype="multipart/form-data" class="structure-form">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>" />
                <div class="form-group">
                  <label class="form-label" for="examId">Draft exam</label>
                  <select class="form-select" id="examId" name="exam_id" required>
                    <option value="" selected disabled>Select exam</option>
                    <?php while ($exam = mysqli_fetch_assoc($examResult)): ?>
                      <option value="<?php echo (int) $exam['id']; ?>"><?php echo htmlspecialchars($exam['title'], ENT_QUOTES, 'UTF-8'); ?></option>
                    <?php endwhile; ?>
                  </select>
                </div>
                <div class="form-group">
                  <label class="form-label" for="questionsFile">Question template (CSV)</label>
                  <input class="form-control" id="questionsFile" name="questions_file" type="file" accept=".csv,text/csv" required />
                  <small>Use the <a href="proc-download-cbt-template.php">downloadable template</a>. In the “Image Filename (optional)” column, enter the filename, for example <strong>FILENAME.JPG</strong> (replace it with your actual image filename). Leave it blank if the question has no image. Do not paste or insert the image into the spreadsheet. Maximum CSV size: 2 MB; maximum 500 questions.</small>
                </div>
                <div class="form-group">
                  <label class="form-label" for="imagesFile">Question images (optional ZIP)</label>
                  <input class="form-control" id="imagesFile" name="images_file" type="file" accept=".zip,application/zip" />
                  <small>ZIP the image files separately, then upload that ZIP here. Put the files directly inside the ZIP (not inside a folder) and make each filename match the CSV exactly. For example, if the CSV says <strong>FILENAME.JPG</strong>, the ZIP must contain a file named <strong>FILENAME.JPG</strong>. Each image can be up to 5 MB; the total uncompressed image size is limited to 20 MB.</small>
                </div>
                <div class="structure-form-actions">
                  <a href="cbt-questions.php" class="btn-action-dark-outline">Cancel</a>
                  <button class="btn-navy-filled" type="submit"><i class="fa-solid fa-upload"></i> Import questions</button>
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
    <?php if ($status !== '' || $errors): ?>
      <script>
        window.addEventListener("DOMContentLoaded", () => {
          if (!window.AppModal) {
            console.error("The bulk-upload result dialog is unavailable.");
            return;
          }
          const succeeded = <?php echo json_encode($status === 'success'); ?>;
          window.AppModal.open({
            type: succeeded ? "success" : "error",
            heading: succeeded ? "Questions imported" : "Upload not completed",
            body: <?php echo json_encode($message !== '' ? $message : 'No questions were imported. Please review the errors and try again.', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>,
            detail: <?php echo json_encode(implode("\n", $errorDetails), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>,
          });
          window.history.replaceState({}, document.title, window.location.pathname);
        });
      </script>
    <?php endif; ?>
  </body>
</html>
