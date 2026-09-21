<?php
require_once 'inc/auth.php';
require_once '../../inc/db.php';

$memberId = (int) $_SESSION['member_id'];

$attendanceSummary = [
  'total' => 0,
  'present' => 0,
  'late' => 0,
  'absent' => 0,
];
$attendanceSummaryResult = mysqli_query($conn, "SELECT status, COUNT(*) AS total FROM attendance_logs WHERE member_id = $memberId GROUP BY status");
if ($attendanceSummaryResult) {
  while ($row = mysqli_fetch_assoc($attendanceSummaryResult)) {
    $status = $row['status'] ?? 'present';
    $attendanceSummary['total'] += (int) $row['total'];
    if (isset($attendanceSummary[$status])) {
      $attendanceSummary[$status] = (int) $row['total'];
    }
  }
}

$attendanceHistory = [];
$attendanceHistoryResult = mysqli_query($conn, "SELECT id, check_in, check_out, status, created_at FROM attendance_logs WHERE member_id = $memberId ORDER BY check_in DESC LIMIT 10");
if ($attendanceHistoryResult) {
  while ($row = mysqli_fetch_assoc($attendanceHistoryResult)) {
    $attendanceHistory[] = $row;
  }
}

$attendanceRate = $attendanceSummary['total'] > 0 ? round(($attendanceSummary['present'] / $attendanceSummary['total']) * 100) : 0;
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Attendance - Associa8</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap"
      rel="stylesheet"
    />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    />
    <link
      rel="shortcut icon"
      href="../../images/fav-logo.png"
      type="image/x-icon"
    />
    <link rel="stylesheet" href="../../css/preloader.css" />
    <link rel="stylesheet" href="../../css/dashboard.css" />
  </head>
  <body class="admin-body">
    <?php include('../inc/preloader.php') ?>
    <div class="admin-layout">
      <!-- ==========================================
         MEMBER SIDEBAR NAVIGATION
         ========================================== -->
      <?php include('inc/sidebar.php') ?>

      <!-- Mobile sidebar backdrop: tap it to close the sidebar -->
      <div class="sidebar-overlay" id="sidebarOverlay"></div>

      <!-- MAIN CONTENT AREA -->
      <main class="admin-main">
        <header class="admin-header">
          <div class="header-left">
            <button class="header-toggle-btn" id="sidebarToggle">
              <i class="fa-solid fa-bars"></i>
            </button>
            <h1 class="page-title">Attendance</h1>
          </div>

          <div class="header-right">
            <div class="header-search">
              <i class="fa-solid fa-magnifying-glass header-search-icon"></i>
              <input type="text" placeholder="Search...." />
            </div>

            <button class="notification-btn" aria-label="Notifications">
              <i class="fa-solid fa-bell"></i>
              <span class="notification-badge"></span>
            </button>

            <div class="admin-user-profile">
              <div class="avatar-badge"><?php echo htmlspecialchars(substr($_SESSION['member_id'] ?? 'M', 0, 2)); ?></div>
              <span class="badge-pill status-active" style="margin-left: -0.5rem;">Active</span>
            </div>
          </div>
        </header>

        <div class="dashboard-content">
          <!-- Stat Cards -->
          <section class="summary-cards-grid" style="grid-template-columns: repeat(4, 1fr);">
            <div class="summary-card">
              <div class="summary-card-header">
                <span class="summary-card-title">Total Records</span>
                <div class="summary-icon-circle">
                  <i class="fa-solid fa-circle-info"></i>
                </div>
              </div>
              <div class="summary-value"><?php echo (int) $attendanceSummary['total']; ?></div>
            </div>

            <div class="summary-card">
              <div class="summary-card-header">
                <span class="summary-card-title">Present</span>
                <div class="summary-icon-circle">
                  <i class="fa-solid fa-check"></i>
                </div>
              </div>
              <div class="summary-value"><?php echo (int) $attendanceSummary['present']; ?></div>
            </div>

            <div class="summary-card">
              <div class="summary-card-header">
                <span class="summary-card-title">Late</span>
                <div class="summary-icon-circle">
                  <i class="fa-solid fa-xmark"></i>
                </div>
              </div>
              <div class="summary-value"><?php echo (int) $attendanceSummary['late']; ?></div>
            </div>

            <div class="summary-card">
              <div class="summary-card-header">
                <span class="summary-card-title">Attendance Rate</span>
                <div class="summary-icon-circle">
                  <i class="fa-solid fa-chart-simple"></i>
                </div>
              </div>
              <div class="summary-value"><?php echo $attendanceRate; ?>%</div>
            </div>
          </section>

          <!-- Overall Progress -->
          <section class="dashboard-card">
            <div class="progress-labeled-row">
              <span class="progress-labeled-title">Overall Attendance Rate</span>
              <span class="progress-labeled-percent"><?php echo $attendanceRate; ?>%</span>
            </div>
            <div class="progress-bar-wrapper" style="height: 8px; margin-top: 0;">
              <div class="progress-bar-fill" style="width: <?php echo $attendanceRate; ?>%; background-color: var(--banner-bg);"></div>
            </div>
            <div class="progress-note"><?php echo $attendanceRate >= 75 ? 'Excellent attendance - you are eligible to member benefits.' : 'Your attendance is being tracked. Keep up the momentum.'; ?></div>
          </section>

          <!-- Attendance History -->
          <section class="table-responsive-card">
            <div class="list-card-dark-header">
              <div class="list-card-dark-header-left">
                <span class="list-card-dark-header-icon">
                  <i class="fa-solid fa-arrow-right"></i>
                </span>
                Attendance History
              </div>
            </div>

            <table class="admin-table">
              <thead>
                <tr>
                  <th>Events</th>
                  <th>Date</th>
                  <th>Type</th>
                  <th>Status</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <?php if ($attendanceHistory): ?>
                  <?php foreach ($attendanceHistory as $record): ?>
                    <?php
                      $statusText = ucfirst($record['status'] ?? 'present');
                      $badgeClass = $record['status'] === 'present' ? 'status-text-attended' : ($record['status'] === 'late' ? 'status-text-pending' : 'status-text-missed');
                    ?>
                    <tr>
                      <td style="font-weight: 600; color: var(--text-primary);">Attendance Record</td>
                      <td><?php echo htmlspecialchars(date('F j, Y', strtotime($record['check_in']))); ?></td>
                      <td><span class="event-type-pill"><?php echo htmlspecialchars($statusText); ?></span></td>
                      <td><span class="<?php echo $badgeClass; ?>"><?php echo $record['status'] === 'present' ? '<i class="fa-solid fa-check"></i>' : ($record['status'] === 'late' ? '<i class="fa-solid fa-clock"></i>' : '<i class="fa-solid fa-xmark"></i>'); ?> <?php echo htmlspecialchars($statusText); ?></span></td>
                      <td style="text-align: right;">
                        <button class="btn-action-trigger" aria-label="Options">
                          <i class="fa-solid fa-ellipsis"></i>
                        </button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="5" class="zone-empty-state">No attendance records are available yet for this member.</td>
                  </tr>
                <?php endif; ?>

                  <td style="font-weight: 600; color: var(--text-primary);">Annual General Meeting 2025</td>
                  <td>Jan 15, 2025</td>
                  <td><span class="event-type-pill">Meetings</span></td>
                  <td><span class="status-text-attended"><i class="fa-solid fa-check"></i> Attended</span></td>
                  <td style="text-align: right;">
                    <button class="btn-action-trigger" aria-label="Options">
                      <i class="fa-solid fa-ellipsis"></i>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </section>
        </div>

        <?php include('../inc/footer.php') ?>
      </main>
    </div>

    <script>
      const sidebarEl = document.getElementById("adminSidebar");
      const sidebarOverlay = document.getElementById("sidebarOverlay");
      const sidebarToggle = document.getElementById("sidebarToggle");
      const sidebarCloseBtn = document.getElementById("sidebarCloseBtn");

      function openSidebar() {
        sidebarEl.classList.add("open");
        if (sidebarOverlay) sidebarOverlay.classList.add("active");
      }

      function closeSidebar() {
        sidebarEl.classList.remove("open");
        if (sidebarOverlay) sidebarOverlay.classList.remove("active");
      }

      if (sidebarToggle) {
        sidebarToggle.addEventListener("click", () => {
          sidebarEl.classList.contains("open") ? closeSidebar() : openSidebar();
        });
      }

      if (sidebarCloseBtn) {
        sidebarCloseBtn.addEventListener("click", closeSidebar);
      }

      if (sidebarOverlay) {
        sidebarOverlay.addEventListener("click", closeSidebar);
      }

      document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") closeSidebar();
      });

      sidebarEl.querySelectorAll(".sidebar-link").forEach((link) => {
        const parentItem = link.closest(".sidebar-item");
        const isDropdownToggle = parentItem && parentItem.classList.contains("dropdown") && link === parentItem.querySelector(":scope > .sidebar-link");
        if (!isDropdownToggle) {
          link.addEventListener("click", closeSidebar);
        }
      });
    </script>
    <script src="../../js/preloader.js"></script>
  </body>
</html>