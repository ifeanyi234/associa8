<?php
require_once 'inc/auth.php';
require_once '../inc/db.php';

$lagosTimezone = new DateTimeZone('Africa/Lagos');
$utcTimezone = new DateTimeZone('UTC');
$orgId = isset($_SESSION['org_id']) ? (int) $_SESSION['org_id'] : 0;
$isPlatformAdmin = ($_SESSION['admin_role'] ?? '') === 'super_admin';
$canViewAllOrganizations = $isPlatformAdmin && $orgId < 1;
$scopeSql = $canViewAllOrganizations ? '1 = 1' : 'm.org_id = ' . $orgId;
$attendanceRows = [];
$attendanceQueryFailed = false;
$notice = $_SESSION['attendance_notice'] ?? null;
unset($_SESSION['attendance_notice']);

$search = trim((string) ($_GET['q'] ?? ''));
$statusFilter = (string) ($_GET['status'] ?? '');
$allowedStatuses = ['present', 'late', 'absent'];
if (!in_array($statusFilter, $allowedStatuses, true)) {
    $statusFilter = '';
}

$whereParts = [$scopeSql];
$params = [];
$paramTypes = '';
if ($search !== '') {
    $whereParts[] = "(m.member_code LIKE ? OR m.first_name LIKE ? OR m.last_name LIKE ? OR z.name LIKE ?)";
    $searchTerm = '%' . $search . '%';
    array_push($params, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    $paramTypes .= 'ssss';
}
if ($statusFilter !== '') {
    $whereParts[] = 'al.status = ?';
    $params[] = $statusFilter;
    $paramTypes .= 's';
}

$attendanceSql = "SELECT al.id, al.check_in, al.check_out, al.status, m.member_code,
                         m.first_name, m.last_name, t.title AS member_title, z.name AS zone_name
                  FROM attendance_logs al
                  INNER JOIN members m ON m.id = al.member_id
                  LEFT JOIN titles t ON t.id = m.title_id AND t.org_id = m.org_id
                  LEFT JOIN zones z ON z.id = m.zone_id AND z.org_id = m.org_id
                  WHERE " . implode(' AND ', $whereParts) . "
                  ORDER BY al.check_in DESC, al.id DESC";
$attendanceStmt = mysqli_prepare($conn, $attendanceSql);
if ($attendanceStmt) {
    if ($params) {
        mysqli_stmt_bind_param($attendanceStmt, $paramTypes, ...$params);
    }
    if (mysqli_stmt_execute($attendanceStmt)) {
        $attendanceResult = mysqli_stmt_get_result($attendanceStmt);
        while ($row = mysqli_fetch_assoc($attendanceResult)) {
            $attendanceRows[] = $row;
        }
    }
    else {
        error_log('Attendance records query failed: ' . mysqli_stmt_error($attendanceStmt));
        $attendanceQueryFailed = true;
    }
    mysqli_stmt_close($attendanceStmt);
} else {
    error_log('Attendance records query could not be prepared: ' . mysqli_error($conn));
    $attendanceQueryFailed = true;
}

?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Attendance Management - Associa8</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="shortcut icon" href="../images/fav-logo.png" type="image/x-icon" />
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
            <button class="header-toggle-btn" id="sidebarToggle" aria-label="Toggle navigation">
              <i class="fa-solid fa-bars"></i>
            </button>
            <h1 class="page-title">Attendance</h1>
          </div>
          <div class="header-right">
            <div class="header-search">
              <i class="fa-solid fa-magnifying-glass header-search-icon"></i>
              <input type="text" placeholder="Search...." />
            </div>
            <button class="notification-btn" type="button" aria-label="Notifications">
              <i class="fa-solid fa-bell"></i>
              <span class="notification-badge"></span>
            </button>
            <div class="admin-user-profile">
              <div class="avatar-badge">SA</div>
              <div class="user-info">
                <span class="user-name">Super Admin</span>
                <span class="user-role">Full Access</span>
              </div>
            </div>
          </div>
        </header>

        <div class="dashboard-content">
          <div class="page-action-header">
            <div>
              <h2 class="page-title-main">Attendance</h2>
              <p class="page-subtitle">Review member attendance records, check-in times, and status.</p>
            </div>
            <div style="display: flex; gap: 0.75rem;">
              <button class="btn-navy-outline" id="attendanceFilterToggle" type="button" aria-expanded="<?php echo $statusFilter !== '' ? 'true' : 'false'; ?>" aria-controls="attendanceStatusFilter">
                <i class="fa-solid fa-sliders"></i> Filter
              </button>
              <button class="btn-navy-outline" type="button" disabled title="Attendance export is not available yet">
                <i class="fa-solid fa-arrow-up-from-bracket"></i> Export
              </button>
              <a class="btn-navy-filled" href="add-attendance.php">
                <i class="fa-solid fa-user-plus"></i> Add Attendance
              </a>
            </div>
          </div>

          <?php if (is_array($notice)): ?>
            <div class="dashboard-card" role="status" style="margin-bottom: 1rem; color: <?php echo ($notice['type'] ?? '') === 'success' ? '#166534' : '#b91c1c'; ?>;">
              <?php echo htmlspecialchars($notice['text'], ENT_QUOTES, 'UTF-8'); ?>
            </div>
          <?php endif; ?>

          <form id="attendanceFilters" method="get" action="attendance.php" style="margin-bottom: 1.25rem; max-width: 360px;">
            <div style="position: relative;">
              <input type="search" name="q" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Search by name or zone" style="width: 100%; padding: 0.65rem 2.5rem 0.65rem 1rem; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.875rem; background-color: #f8fafc; outline: none;" />
              <i class="fa-solid fa-magnifying-glass" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 0.875rem;"></i>
            </div>
            <div id="attendanceStatusFilter" style="display: <?php echo $statusFilter !== '' ? 'block' : 'none'; ?>; margin-top: 0.75rem;">
              <label class="form-label" for="attendance_filter_status">Filter by status</label>
              <select class="form-select" id="attendance_filter_status" name="status" style="min-width: 130px;" onchange="this.form.submit()">
                <option value="">All statuses</option>
                <?php foreach ($allowedStatuses as $allowedStatus): ?>
                  <option value="<?php echo htmlspecialchars($allowedStatus, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $statusFilter === $allowedStatus ? 'selected' : ''; ?>><?php echo htmlspecialchars(ucfirst($allowedStatus), ENT_QUOTES, 'UTF-8'); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </form>

          <div class="dashboard-card" style="padding: 0; overflow: hidden;">
            <div style="overflow-x: auto;">
              <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.875rem;">
                <thead>
                  <tr style="background-color: #0f172a; color: #ffffff;">
                    <th style="padding: 1rem; font-weight: 600;">ID</th>
                    <th style="padding: 1rem; font-weight: 600;">Member</th>
                    <th style="padding: 1rem; font-weight: 600;">Title</th>
                    <th style="padding: 1rem; font-weight: 600;">Zone</th>
                    <th style="padding: 1rem; font-weight: 600;">Status</th>
                    <th style="padding: 1rem; font-weight: 600;">Check-in</th>
                    <th style="padding: 1rem; font-weight: 600;">Date</th>
                    <th style="padding: 1rem; font-weight: 600;">Events</th>
                    <th style="padding: 1rem; font-weight: 600; text-align: center;"></th>
                  </tr>
                </thead>
                <tbody style="color: #334155;">
                  <?php if ($attendanceQueryFailed): ?>
                    <tr><td colspan="9" class="zone-empty-state">Attendance could not be loaded. Please refresh or contact support if the problem continues.</td></tr>
                  <?php elseif ($attendanceRows): ?>
                    <?php foreach ($attendanceRows as $row): ?>
                      <?php
                        $status = strtolower((string) $row['status']);
                        $statusColor = $status === 'present' ? '#166534' : ($status === 'late' ? '#92400e' : '#b91c1c');
                        $statusBackground = $status === 'present' ? '#dcfce7' : ($status === 'late' ? '#fef3c7' : '#fee2e2');
                        $localCheckIn = (new DateTimeImmutable($row['check_in'], $utcTimezone))->setTimezone($lagosTimezone);
                      ?>
                      <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 1rem; color: #64748b;"><?php echo (int) $row['id']; ?></td>
                        <td style="padding: 1rem; font-weight: 600; color: #0f172a;"><?php echo htmlspecialchars($row['member_code'] . ' — ' . $row['first_name'] . ' ' . $row['last_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td style="padding: 1rem;"><?php echo htmlspecialchars($row['member_title'] ?: 'Member', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td style="padding: 1rem;"><?php echo htmlspecialchars($row['zone_name'] ?: '—', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td style="padding: 1rem;"><span style="background-color: <?php echo $statusBackground; ?>; color: <?php echo $statusColor; ?>; padding: 0.35rem 0.75rem; border-radius: 6px; font-weight: 500; font-size: 0.75rem; display: inline-block;"><?php echo htmlspecialchars(ucfirst($status), ENT_QUOTES, 'UTF-8'); ?></span></td>
                        <td style="padding: 1rem; color: #475569;"><?php echo htmlspecialchars($localCheckIn->format('h:i A'), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td style="padding: 1rem; color: #475569;"><?php echo htmlspecialchars($localCheckIn->format('d-m-Y'), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td style="padding: 1rem; color: #475569;">General attendance</td>
                        <td style="padding: 1rem; text-align: center; color: #94a3b8;"><i class="fa-solid fa-ellipsis" aria-hidden="true"></i></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr><td colspan="9" class="zone-empty-state">No attendance records match this organization and filter yet.</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <?php include('inc/footer.php'); ?>
      </main>
    </div>
    <script>
      const sidebarEl = document.getElementById('adminSidebar');
      const sidebarOverlay = document.getElementById('sidebarOverlay');
      const sidebarToggle = document.getElementById('sidebarToggle');
      const attendanceFilterToggle = document.getElementById('attendanceFilterToggle');
      const attendanceStatusFilter = document.getElementById('attendanceStatusFilter');
      function closeSidebar() {
        if (sidebarEl) sidebarEl.classList.remove('open');
        if (sidebarOverlay) sidebarOverlay.classList.remove('active');
      }
      if (sidebarToggle && sidebarEl) {
        sidebarToggle.addEventListener('click', function () {
          sidebarEl.classList.toggle('open');
          if (sidebarOverlay) sidebarOverlay.classList.toggle('active');
        });
      }
      if (sidebarOverlay) sidebarOverlay.addEventListener('click', closeSidebar);
      if (attendanceFilterToggle && attendanceStatusFilter) {
        attendanceFilterToggle.addEventListener('click', function () {
          const isExpanded = attendanceFilterToggle.getAttribute('aria-expanded') === 'true';
          attendanceFilterToggle.setAttribute('aria-expanded', isExpanded ? 'false' : 'true');
          attendanceStatusFilter.style.display = isExpanded ? 'none' : 'block';
        });
      }
      document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') closeSidebar();
      });
    </script>
    <script src="../js/preloader.js"></script>
  </body>
</html>
