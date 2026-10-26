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
$historyQueryFailed = false;
$generalHistoryStmt = mysqli_prepare(
  $conn,
  'SELECT id, check_in, status FROM attendance_logs WHERE member_id = ? ORDER BY check_in DESC LIMIT 10'
);
if (!$generalHistoryStmt) {
  error_log('Member general attendance history could not be prepared: ' . mysqli_error($conn));
  $historyQueryFailed = true;
} else {
  mysqli_stmt_bind_param($generalHistoryStmt, 'i', $memberId);
  if (!mysqli_stmt_execute($generalHistoryStmt)) {
    error_log('Member general attendance history query failed: ' . mysqli_stmt_error($generalHistoryStmt));
    $historyQueryFailed = true;
  } else {
    $generalHistoryResult = mysqli_stmt_get_result($generalHistoryStmt);
    while ($generalHistoryResult && ($row = mysqli_fetch_assoc($generalHistoryResult))) {
      $row['attendance_type'] = 'general';
      $row['sort_timestamp'] = (new DateTimeImmutable($row['check_in'], new DateTimeZone('UTC')))->getTimestamp();
      $attendanceHistory[] = $row;
    }
  }
  mysqli_stmt_close($generalHistoryStmt);
}

$eventHistoryStmt = mysqli_prepare(
  $conn,
  "SELECT ea.id, ea.status, e.title, e.event_date, e.event_time
   FROM event_attendance ea
   INNER JOIN events e ON e.id = ea.event_id
   INNER JOIN members m ON m.id = ea.member_id AND m.org_id = e.org_id
   WHERE ea.member_id = ? AND m.org_id = ?
   ORDER BY e.event_date DESC, COALESCE(e.event_time, '23:59:59') DESC, ea.id DESC
   LIMIT 10"
);
if (!$eventHistoryStmt) {
  error_log('Member event attendance history could not be prepared: ' . mysqli_error($conn));
  $historyQueryFailed = true;
} else {
  $memberOrgId = (int) $_SESSION['member_org_id'];
  mysqli_stmt_bind_param($eventHistoryStmt, 'ii', $memberId, $memberOrgId);
  if (!mysqli_stmt_execute($eventHistoryStmt)) {
    error_log('Member event attendance history query failed: ' . mysqli_stmt_error($eventHistoryStmt));
    $historyQueryFailed = true;
  } else {
    $eventHistoryResult = mysqli_stmt_get_result($eventHistoryStmt);
    while ($eventHistoryResult && ($row = mysqli_fetch_assoc($eventHistoryResult))) {
      $eventDateTime = new DateTimeImmutable(
        $row['event_date'] . ' ' . ($row['event_time'] ?: '23:59:59'),
        new DateTimeZone('Africa/Lagos')
      );
      $row['attendance_type'] = 'event';
      $row['sort_timestamp'] = $eventDateTime->getTimestamp();
      $attendanceHistory[] = $row;
    }
  }
  mysqli_stmt_close($eventHistoryStmt);
}
usort($attendanceHistory, static fn($left, $right) => $right['sort_timestamp'] <=> $left['sort_timestamp']);
$attendanceHistory = array_slice($attendanceHistory, 0, 10);

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
    <link rel="stylesheet" href="../../css/dashboard.css?v=20261019-member-history-square-1" />
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
              <div class="avatar-badge member-avatar" aria-label="<?php echo htmlspecialchars($memberAvatarLabel, ENT_QUOTES, 'UTF-8'); ?>">
                <?php if ($memberPhotoPath !== ''): ?>
                  <img src="<?php echo htmlspecialchars($memberPhotoPath, ENT_QUOTES, 'UTF-8'); ?>" alt="" />
                <?php else: ?>
                  <?php echo htmlspecialchars($memberInitials); ?>
                <?php endif; ?>
              </div>
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
                <?php admin_info_tip('The number of attendance records recorded for you. Contact your organization if a record looks incorrect.', 'Attendance records help'); ?>
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
            <div class="progress-note">Summary counts general attendance only. Event attendance is listed separately below.</div>
          </section>

          <!-- Attendance History -->
          <section class="table-responsive-card member-history-table-card">
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
                <?php if ($historyQueryFailed): ?>
                  <tr>
                    <td colspan="5" class="zone-empty-state">Attendance history could not be loaded. Please try again later.</td>
                  </tr>
                <?php elseif ($attendanceHistory): ?>
                  <?php foreach ($attendanceHistory as $record): ?>
                    <?php
                      $isEventAttendance = $record['attendance_type'] === 'event';
                      $statusText = ucfirst($record['status'] ?? 'present');
                      $badgeClass = in_array($record['status'], ['present', 'attended'], true)
                        ? 'status-text-attended'
                        : (in_array($record['status'], ['late', 'excused'], true) ? 'status-text-pending' : 'status-text-missed');
                      $statusIcon = in_array($record['status'], ['present', 'attended'], true)
                        ? 'fa-check'
                        : (in_array($record['status'], ['late', 'excused'], true) ? 'fa-clock' : 'fa-xmark');
                      $recordDate = $isEventAttendance
                        ? new DateTimeImmutable($record['event_date'], new DateTimeZone('Africa/Lagos'))
                        : (new DateTimeImmutable($record['check_in'], new DateTimeZone('UTC')))->setTimezone(new DateTimeZone('Africa/Lagos'));
                    ?>
                    <tr>
                      <td style="font-weight: 600; color: var(--text-primary);"><?php echo htmlspecialchars($isEventAttendance ? $record['title'] : 'General attendance', ENT_QUOTES, 'UTF-8'); ?></td>
                      <td><?php echo htmlspecialchars($recordDate->format('F j, Y'), ENT_QUOTES, 'UTF-8'); ?></td>
                      <td><span class="event-type-pill"><?php echo $isEventAttendance ? 'Event' : 'General'; ?></span></td>
                      <td><span class="<?php echo $badgeClass; ?>"><i class="fa-solid <?php echo $statusIcon; ?>"></i> <?php echo htmlspecialchars($statusText, ENT_QUOTES, 'UTF-8'); ?></span></td>
                      <td></td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="5" class="zone-empty-state">No attendance records are available yet for this member.</td>
                  </tr>
                <?php endif; ?>
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