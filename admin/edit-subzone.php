<?php
require_once 'inc/auth.php';
require_once '../inc/db.php';

$orgId = (int) ($_SESSION['org_id'] ?? 0);
$subzoneId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($orgId < 1 || !$subzoneId || $subzoneId < 1) {
    header('Location: zones.php?status=error&msg=' . urlencode('The selected sub-zone could not be found.'));
    exit;
}

$statement = mysqli_prepare(
    $conn,
    'SELECT sz.id, sz.name, sz.coordinator_name, sz.annual_dues, z.name AS zone_name
     FROM subzones sz
     INNER JOIN zones z ON z.id = sz.zone_id
     WHERE sz.id = ? AND z.org_id = ?
     LIMIT 1'
);
if (!$statement) {
    error_log('Sub-zone edit lookup could not be prepared: ' . mysqli_error($conn));
    http_response_code(500);
    exit('The sub-zone could not be loaded right now.');
}
mysqli_stmt_bind_param($statement, 'ii', $subzoneId, $orgId);
if (!mysqli_stmt_execute($statement)) {
    error_log('Sub-zone edit lookup failed: ' . mysqli_stmt_error($statement));
    mysqli_stmt_close($statement);
    http_response_code(500);
    exit('The sub-zone could not be loaded right now.');
}
$result = mysqli_stmt_get_result($statement);
$subzone = $result ? mysqli_fetch_assoc($result) : null;
mysqli_stmt_close($statement);
if (!$subzone) {
    header('Location: zones.php?status=error&msg=' . urlencode('The selected sub-zone could not be found.'));
    exit;
}

if (empty($_SESSION['subzone_edit_csrf'])) {
    $_SESSION['subzone_edit_csrf'] = bin2hex(random_bytes(32));
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Edit Sub-zone - Associa8</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="shortcut icon" href="../images/fav-logo.png" type="image/x-icon" />
    <link rel="stylesheet" href="../css/preloader.css" />
    <link rel="stylesheet" href="../css/dashboard.css?v=20261016-edit-subzone-1" />
  </head>
  <body class="admin-body">
    <?php include('inc/preloader.php'); ?>
    <div class="admin-layout">
      <?php include('inc/sidebar.php'); ?>
      <div class="sidebar-overlay" id="sidebarOverlay"></div>
      <main class="admin-main">
        <header class="admin-header">
          <div class="header-left">
            <button class="header-toggle-btn" id="sidebarToggle" type="button" aria-label="Open navigation"><i class="fa-solid fa-bars"></i></button>
            <h1 class="page-title">Edit Sub-zone</h1>
          </div>
        </header>
        <div class="dashboard-content">
          <div class="page-action-header mb-4">
            <div>
              <h2 class="page-title-main">Edit Sub-zone</h2>
              <p class="page-subtitle">Update details for <?php echo htmlspecialchars((string) $subzone['zone_name'], ENT_QUOTES, 'UTF-8'); ?>.</p>
            </div>
            <a href="zones.php" class="btn-outline-primary"><i class="fa-solid fa-arrow-left"></i><span>Back to zones</span></a>
          </div>
          <section class="dashboard-card structure-form-card">
            <div class="structure-form-heading">
              <div class="structure-form-icon"><i class="fa-solid fa-pen"></i></div>
              <div><h3>Sub-zone information</h3></div>
            </div>
            <form action="proc-edit-subzone.php" method="post" class="structure-form">
              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['subzone_edit_csrf'], ENT_QUOTES, 'UTF-8'); ?>" />
              <input type="hidden" name="id" value="<?php echo (int) $subzone['id']; ?>" />
              <div class="form-row-2col">
                <div class="form-group">
                  <label for="subzoneName" class="form-label">Sub-zone name</label>
                  <input class="form-control" type="text" id="subzoneName" name="name" maxlength="100" value="<?php echo htmlspecialchars((string) $subzone['name'], ENT_QUOTES, 'UTF-8'); ?>" required />
                </div>
                <div class="form-group">
                  <label for="subzoneCoordinator" class="form-label">Coordinator name</label>
                  <input class="form-control" type="text" id="subzoneCoordinator" name="coordinator_name" maxlength="150" value="<?php echo htmlspecialchars((string) ($subzone['coordinator_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" />
                </div>
              </div>
              <div class="form-group">
                <label for="subzoneAnnualDues" class="form-label">Annual sub-zone dues (NGN, optional)</label>
                <input class="form-control" type="number" id="subzoneAnnualDues" name="annual_dues" min="0" max="9999999999.99" step="0.01" value="<?php echo htmlspecialchars((string) $subzone['annual_dues'], ENT_QUOTES, 'UTF-8'); ?>" />
              </div>
              <div class="structure-form-actions">
                <a href="zones.php" class="btn-action-dark-outline">Cancel</a>
                <button type="submit" class="btn-navy-filled"><i class="fa-solid fa-check"></i> Save changes</button>
              </div>
            </form>
          </section>
        </div>
        <?php include('inc/footer.php'); ?>
      </main>
    </div>
    <script src="../js/admin-sidebar.js"></script>
    <script src="../js/preloader.js"></script>
  </body>
</html>
