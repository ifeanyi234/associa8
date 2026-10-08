<?php
require_once 'inc/auth.php';
require_once '../inc/db.php';

$resultId = (int) ($_GET['result_id'] ?? 0);
$adminRole = $_SESSION['admin_role'];
$orgId = isset($_SESSION['org_id']) ? (int) $_SESSION['org_id'] : null;
if ($resultId < 1) {
    header('Location: cbt-results.php?status=error&msg=' . urlencode('Select a valid CBT result.'));
    exit;
}

$sql = "SELECT r.id, r.score, r.status, r.taken_at, e.title AS exam_title, e.pass_mark, a.applicant_name, a.application_number, a.email, a.phone, m.first_name, m.last_name, m.email AS member_email, m.phone AS member_phone FROM cbt_results r INNER JOIN cbt_exams e ON e.id = r.exam_id AND e.org_id = r.org_id LEFT JOIN admissions a ON a.id = r.admission_id AND a.org_id = r.org_id LEFT JOIN members m ON m.id = r.member_id AND m.org_id = r.org_id WHERE r.id = ?";
if ($adminRole !== 'super_admin') {
    $sql .= ' AND r.org_id = ?';
}
$sql .= ' LIMIT 1';
$statement = mysqli_prepare($conn, $sql);
if (!$statement) {
    error_log('CBT result detail prepare failed: ' . mysqli_error($conn));
    http_response_code(500);
    exit('The CBT result is temporarily unavailable.');
}
if ($adminRole === 'super_admin') {
    mysqli_stmt_bind_param($statement, 'i', $resultId);
} else {
    mysqli_stmt_bind_param($statement, 'ii', $resultId, $orgId);
}
if (!mysqli_stmt_execute($statement)) {
    error_log('CBT result detail lookup failed: ' . mysqli_stmt_error($statement));
    http_response_code(500);
    exit('The CBT result is temporarily unavailable.');
}
$resultSet = mysqli_stmt_get_result($statement);
$result = $resultSet ? mysqli_fetch_assoc($resultSet) : null;
if (!$result) {
    if (!$resultSet) {
        error_log('CBT result detail result set failed: ' . mysqli_stmt_error($statement));
        http_response_code(500);
        exit('The CBT result is temporarily unavailable.');
    }
    http_response_code(404);
    exit('The CBT result was not found in your organization.');
}

$applicantName = trim((string) ($result['applicant_name'] ?? ''));
$memberName = trim((string) ($result['first_name'] ?? '') . ' ' . (string) ($result['last_name'] ?? ''));
$displayName = $applicantName !== '' ? $applicantName : ($memberName !== '' ? $memberName : 'Unknown');
$email = $result['email'] ?? $result['member_email'] ?? '';
$phone = $result['phone'] ?? $result['member_phone'] ?? '';
$takenAt = (new DateTimeImmutable($result['taken_at'], new DateTimeZone('UTC')))
    ->setTimezone(new DateTimeZone('Africa/Lagos'))
    ->format('Y-m-d H:i:s');
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>CBT Result Details - Associa8</title>
    <link rel="shortcut icon" href="../images/fav-logo.png" type="image/x-icon" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="../css/preloader.css" />
    <link rel="stylesheet" href="../css/dashboard.css?v=20261008-infotips-3" />
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
            <h1 class="page-title">CBT Result Details</h1>
          </div>
        </header>
        <div class="dashboard-content">
          <div class="page-action-header mb-4">
            <div>
              <h2 class="page-title-main"><?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?></h2>
              <p class="page-subtitle"><?php echo htmlspecialchars($result['exam_title'], ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <a href="cbt-results.php" class="btn-outline-primary"><i class="fa-solid fa-arrow-left"></i> Back to results</a>
          </div>

          <section class="dashboard-card">
            <dl class="result-details-list">
              <dt>Application number</dt>
              <dd><?php echo htmlspecialchars($result['application_number'] ?? 'Not available', ENT_QUOTES, 'UTF-8'); ?></dd>
              <dt>Email</dt>
              <dd><?php echo htmlspecialchars($email !== '' ? $email : 'Not provided', ENT_QUOTES, 'UTF-8'); ?></dd>
              <dt>Phone</dt>
              <dd><?php echo htmlspecialchars($phone !== '' ? $phone : 'Not provided', ENT_QUOTES, 'UTF-8'); ?></dd>
              <dt>Score</dt>
              <dd><?php echo (int) $result['score']; ?>%</dd>
              <dt>Pass mark</dt>
              <dd><?php echo (int) $result['pass_mark']; ?>%</dd>
              <dt>Result</dt>
              <dd><span class="badge-pill <?php echo $result['status'] === 'passed' ? 'status-active' : 'status-inactive'; ?>"><?php echo htmlspecialchars(ucfirst($result['status']), ENT_QUOTES, 'UTF-8'); ?></span></dd>
              <dt>Submitted</dt>
              <dd><?php echo htmlspecialchars($takenAt, ENT_QUOTES, 'UTF-8'); ?> (Africa/Lagos)</dd>
            </dl>
            <p class="page-subtitle">Assessment result <?php admin_info_tip('The score and pass/fail result are calculated from the answers and exam pass mark. This review is read-only and does not change the admission status.', 'Assessment result help'); ?></p>
          </section>
        </div>
        <?php include('inc/footer.php'); ?>
      </main>
    </div>
    <style>
      .result-details-list {
        display: grid;
        grid-template-columns: minmax(140px, 220px) 1fr;
        gap: 12px 20px;
        margin: 0 0 24px;
      }
      .result-details-list dt { font-weight: 700; }
      .result-details-list dd { margin: 0; }
      @media (max-width: 560px) {
        .result-details-list { grid-template-columns: 1fr; gap: 6px; }
        .result-details-list dd { margin-bottom: 12px; }
      }
    </style>
    <script src="../js/admin-sidebar.js"></script>
    <script src="../js/preloader.js"></script>
  </body>
</html>
