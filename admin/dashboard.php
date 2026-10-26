<?php
require_once "inc/auth.php";
require_once "../inc/db.php";

$dashboardRole = (string) ($_SESSION['admin_role'] ?? '');
if (in_array($dashboardRole, ['zonal_admin', 'subzonal_admin'], true)) {
    require_once __DIR__ . '/inc/scope.php';
    $scopeContext = admin_scope_context($conn);
    if (empty($scopeContext['valid'])) {
        admin_render_access_denied('Your account does not have a valid zone or sub-zone assignment.');
    }
    require __DIR__ . '/inc/scoped-dashboard.php';
    exit;
}

$dashboardOrgId = (int) ($_SESSION['org_id'] ?? 0);
$dashboardMemberScope = 'org_id = ' . $dashboardOrgId;
$dashboardMemberJoinScope = 'm.org_id = ' . $dashboardOrgId;
$dashboardErrors = [];
$dashboardTimezone = new DateTimeZone('Africa/Lagos');
$dashboardUtc = new DateTimeZone('UTC');
$dashboardNow = new DateTimeImmutable('now', $dashboardTimezone);
$dashboardMonthStart = $dashboardNow->modify('first day of this month')->setTime(0, 0);
$dashboardMonthEnd = $dashboardMonthStart->modify('+1 month');
$dashboardRangeStart = $dashboardMonthStart->modify('-11 months');
$dashboardYearStart = $dashboardNow->setDate((int) $dashboardNow->format('Y'), 1, 1)->setTime(0, 0);
$dashboardYearEnd = $dashboardYearStart->modify('+1 year');
$dashboardMonthStartUtc = $dashboardMonthStart->setTimezone($dashboardUtc)->format('Y-m-d H:i:s');
$dashboardMonthEndUtc = $dashboardMonthEnd->setTimezone($dashboardUtc)->format('Y-m-d H:i:s');
$dashboardRangeStartUtc = $dashboardRangeStart->setTimezone($dashboardUtc)->format('Y-m-d H:i:s');
$dashboardRangeEndUtc = $dashboardMonthEnd->setTimezone($dashboardUtc)->format('Y-m-d H:i:s');
$dashboardYearStartUtc = $dashboardYearStart->setTimezone($dashboardUtc)->format('Y-m-d H:i:s');
$dashboardYearEndUtc = $dashboardYearEnd->setTimezone($dashboardUtc)->format('Y-m-d H:i:s');

$dashboardMemberTotal = null;
$dashboardMemberStatuses = ['active' => 0, 'pending' => 0, 'suspended' => 0];
$dashboardGrowthLabels = [];
$dashboardGrowthValues = [];
$dashboardRevenueTotal = null;
$dashboardRevenueMonths = [];
$dashboardRevenueTypes = [];
$dashboardAttendanceRate = null;
$dashboardAttendanceRecords = 0;
$dashboardPendingApplications = null;
$dashboardDuesCompliance = null;
$dashboardDuesAssessed = 0;
$dashboardCbtPending = null;
$dashboardUpcomingEvents = null;
$dashboardNextEvent = null;
$dashboardActivities = [];

$memberCountResult = mysqli_query($conn, "SELECT COUNT(*) AS total FROM members WHERE $dashboardMemberScope");
if ($memberCountResult) {
    $dashboardMemberTotal = (int) mysqli_fetch_assoc($memberCountResult)['total'];
} else {
    error_log('Dashboard member count query failed: ' . mysqli_error($conn));
    $dashboardErrors['members'] = true;
}

$memberStatusResult = mysqli_query($conn, "SELECT status, COUNT(*) AS total FROM members WHERE $dashboardMemberScope GROUP BY status");
if ($memberStatusResult) {
    while ($statusRow = mysqli_fetch_assoc($memberStatusResult)) {
        if (array_key_exists($statusRow['status'], $dashboardMemberStatuses)) {
            $dashboardMemberStatuses[$statusRow['status']] = (int) $statusRow['total'];
        }
    }
} else {
    error_log('Dashboard member status query failed: ' . mysqli_error($conn));
    $dashboardErrors['member_status'] = true;
}

$growthStartDate = $dashboardRangeStart->format('Y-m-d');
$growthEndDate = $dashboardMonthEnd->format('Y-m-d');
$growthBaselineResult = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM members
     WHERE $dashboardMemberScope
       AND COALESCE(joined_date, DATE(created_at)) < '$growthStartDate'"
);
$growthBaseline = 0;
if ($growthBaselineResult) {
    $growthBaseline = (int) mysqli_fetch_assoc($growthBaselineResult)['total'];
} else {
    error_log('Dashboard member growth baseline query failed: ' . mysqli_error($conn));
    $dashboardErrors['growth'] = true;
}
$growthByMonthResult = mysqli_query(
    $conn,
    "SELECT DATE_FORMAT(COALESCE(joined_date, DATE(created_at)), '%Y-%m') AS month_key, COUNT(*) AS total
     FROM members
     WHERE $dashboardMemberScope
       AND COALESCE(joined_date, DATE(created_at)) >= '$growthStartDate'
       AND COALESCE(joined_date, DATE(created_at)) < '$growthEndDate'
     GROUP BY month_key"
);
$growthByMonth = [];
if ($growthByMonthResult) {
    while ($growthRow = mysqli_fetch_assoc($growthByMonthResult)) {
        $growthByMonth[$growthRow['month_key']] = (int) $growthRow['total'];
    }
} else {
    error_log('Dashboard member growth query failed: ' . mysqli_error($conn));
    $dashboardErrors['growth'] = true;
}
$growthCumulative = $growthBaseline;
for ($growthOffset = 0; $growthOffset < 12; $growthOffset++) {
    $growthMonth = $dashboardRangeStart->modify("+$growthOffset months");
    $growthKey = $growthMonth->format('Y-m');
    $growthCumulative += $growthByMonth[$growthKey] ?? 0;
    $dashboardGrowthLabels[] = $growthMonth->format('M Y');
    $dashboardGrowthValues[] = $growthCumulative;
}

if (admin_has_permission('finance')) {
    $financeSql = "SELECT ft.type, ft.amount, ft.paid_at
                   FROM finance_transactions ft
                   INNER JOIN members m ON m.id = ft.member_id
                   WHERE $dashboardMemberJoinScope
                     AND ft.status = 'successful'
                     AND ft.paid_at >= ?
                     AND ft.paid_at < ?";
    $financeStmt = mysqli_prepare($conn, $financeSql);
    if (!$financeStmt) {
        error_log('Dashboard finance query could not be prepared: ' . mysqli_error($conn));
        $dashboardErrors['finance'] = true;
    } else {
        mysqli_stmt_bind_param($financeStmt, 'ss', $dashboardRangeStartUtc, $dashboardRangeEndUtc);
        if (!mysqli_stmt_execute($financeStmt)) {
            error_log('Dashboard finance query failed: ' . mysqli_stmt_error($financeStmt));
            $dashboardErrors['finance'] = true;
        } else {
            $financeResult = mysqli_stmt_get_result($financeStmt);
            $dashboardRevenueTotal = 0.0;
            while ($financeResult && ($financeRow = mysqli_fetch_assoc($financeResult))) {
                $paidAtLocal = (new DateTimeImmutable($financeRow['paid_at'], $dashboardUtc))->setTimezone($dashboardTimezone);
                $monthKey = $paidAtLocal->format('Y-m');
                $type = (string) $financeRow['type'];
                $amount = (float) $financeRow['amount'];
                if (!isset($dashboardRevenueMonths[$monthKey])) {
                    $dashboardRevenueMonths[$monthKey] = [];
                }
                $dashboardRevenueMonths[$monthKey][$type] = ($dashboardRevenueMonths[$monthKey][$type] ?? 0) + $amount;
                $dashboardRevenueTotal += $amount;
                $dashboardRevenueTypes[$type] = true;
            }
        }
        mysqli_stmt_close($financeStmt);
    }
}

if (admin_has_permission('attendance')) {
    $attendanceSql = "SELECT COUNT(*) AS total,
                             SUM(CASE WHEN al.status IN ('present', 'late') THEN 1 ELSE 0 END) AS attended
                      FROM attendance_logs al
                      INNER JOIN members m ON m.id = al.member_id
                      WHERE $dashboardMemberJoinScope
                        AND al.check_in >= ?
                        AND al.check_in < ?";
    $attendanceStmt = mysqli_prepare($conn, $attendanceSql);
    if (!$attendanceStmt) {
        error_log('Dashboard attendance query could not be prepared: ' . mysqli_error($conn));
        $dashboardErrors['attendance'] = true;
    } else {
        mysqli_stmt_bind_param($attendanceStmt, 'ss', $dashboardMonthStartUtc, $dashboardMonthEndUtc);
        if (!mysqli_stmt_execute($attendanceStmt)) {
            error_log('Dashboard attendance query failed: ' . mysqli_stmt_error($attendanceStmt));
            $dashboardErrors['attendance'] = true;
        } else {
            $attendanceResult = mysqli_stmt_get_result($attendanceStmt);
            $attendanceSummary = $attendanceResult ? mysqli_fetch_assoc($attendanceResult) : null;
            if ($attendanceSummary) {
                $dashboardAttendanceRecords = (int) $attendanceSummary['total'];
                $dashboardAttendanceRate = $dashboardAttendanceRecords > 0
                    ? (int) round(((int) $attendanceSummary['attended'] / $dashboardAttendanceRecords) * 100)
                    : 0;
            }
        }
        mysqli_stmt_close($attendanceStmt);
    }
}

if (admin_has_permission('admission')) {
    $admissionResult = mysqli_query(
        $conn,
        "SELECT
            SUM(CASE WHEN status IN ('pending', 'under_review') THEN 1 ELSE 0 END) AS in_progress,
            SUM(CASE WHEN status = 'under_review' AND cbt_exam_id IS NOT NULL THEN 1 ELSE 0 END) AS awaiting_cbt
         FROM admissions
         WHERE org_id = " . $dashboardOrgId
    );
    if ($admissionResult) {
        $admissionSummary = mysqli_fetch_assoc($admissionResult);
        $dashboardPendingApplications = (int) ($admissionSummary['in_progress'] ?? 0);
        $dashboardCbtPending = (int) ($admissionSummary['awaiting_cbt'] ?? 0);
    } else {
        error_log('Dashboard admissions query failed: ' . mysqli_error($conn));
        $dashboardErrors['admissions'] = true;
    }
}

if (admin_has_permission('finance')) {
    $duesComplianceSql = "SELECT SUM(CASE WHEN dues.total_due > 0 THEN 1 ELSE 0 END) AS assessed,
                                 SUM(CASE WHEN dues.total_due > 0 AND dues.paid_amount >= dues.total_due THEN 1 ELSE 0 END) AS current_members
                          FROM (
                              SELECT m.id,
                                     COALESCE(z.annual_dues, 0) + COALESCE(sz.annual_dues, 0) AS total_due,
                                     COALESCE(SUM(ft.amount), 0) AS paid_amount
                              FROM members m
                              LEFT JOIN zones z ON z.id = m.zone_id AND z.org_id = m.org_id
                              LEFT JOIN subzones sz ON sz.id = m.subzone_id AND sz.zone_id = m.zone_id
                              LEFT JOIN finance_transactions ft
                                ON ft.member_id = m.id AND ft.type = 'membership_dues'
                                AND ft.status = 'successful' AND ft.paid_at >= ? AND ft.paid_at < ?
                              WHERE m.org_id = " . $dashboardOrgId . " AND m.status = 'active'
                              GROUP BY m.id, z.annual_dues, sz.annual_dues
                          ) dues";
    $duesStmt = mysqli_prepare($conn, $duesComplianceSql);
    if (!$duesStmt) {
        error_log('Dashboard dues compliance query could not be prepared: ' . mysqli_error($conn));
        $dashboardErrors['dues'] = true;
    } else {
        mysqli_stmt_bind_param($duesStmt, 'ss', $dashboardYearStartUtc, $dashboardYearEndUtc);
        if (!mysqli_stmt_execute($duesStmt)) {
            error_log('Dashboard dues compliance query failed: ' . mysqli_stmt_error($duesStmt));
            $dashboardErrors['dues'] = true;
        } else {
            $duesResult = mysqli_stmt_get_result($duesStmt);
            $duesSummary = $duesResult ? mysqli_fetch_assoc($duesResult) : null;
            if ($duesSummary) {
                $dashboardDuesAssessed = (int) $duesSummary['assessed'];
                $dashboardDuesCompliance = $dashboardDuesAssessed > 0
                    ? (int) round(((int) $duesSummary['current_members'] / $dashboardDuesAssessed) * 100)
                    : 0;
            }
        }
        mysqli_stmt_close($duesStmt);
    }
}

if (admin_has_permission('events')) {
    $today = $dashboardNow->format('Y-m-d');
    $upcomingEnd = $dashboardNow->modify('+30 days')->format('Y-m-d');
    $eventResult = mysqli_query(
        $conn,
        "SELECT title, event_date, event_time
         FROM events
         WHERE org_id = " . $dashboardOrgId . "
           AND status = 'scheduled'
           AND event_date >= '$today'
           AND event_date <= '$upcomingEnd'
         ORDER BY event_date, event_time"
    );
    if ($eventResult) {
        $dashboardUpcomingEvents = 0;
        while ($eventRow = mysqli_fetch_assoc($eventResult)) {
            $dashboardUpcomingEvents++;
            if ($dashboardNextEvent === null) {
                $eventDate = new DateTimeImmutable($eventRow['event_date'], $dashboardTimezone);
                $dashboardNextEvent = [
                    'title' => (string) $eventRow['title'],
                    'date' => $eventDate->format('M j'),
                ];
            }
        }
    } else {
        error_log('Dashboard upcoming events query failed: ' . mysqli_error($conn));
        $dashboardErrors['events'] = true;
    }
}

$activityUnion = [];
$activityUnion[] = "SELECT CONCAT('New member joined: ', m.first_name, ' ', m.last_name) AS activity_text,
                           'fa-user-plus' AS activity_icon, 'icon-blue' AS activity_color,
                           COALESCE(m.created_at, CONCAT(m.joined_date, ' 00:00:00')) AS activity_time
                    FROM members m WHERE $dashboardMemberJoinScope";
if (admin_has_permission('admission')) {
    $activityUnion[] = "SELECT CONCAT('New application from ', a.applicant_name) AS activity_text,
                               'fa-graduation-cap' AS activity_icon, 'icon-red' AS activity_color,
                               a.applied_at AS activity_time
                        FROM admissions a
                        WHERE a.org_id = " . $dashboardOrgId;
}
if (admin_has_permission('finance')) {
    $activityUnion[] = "SELECT CONCAT('Payment received - NGN ', FORMAT(ft.amount, 2)) AS activity_text,
                               'fa-naira-sign' AS activity_icon, 'icon-green' AS activity_color,
                               COALESCE(ft.paid_at, ft.created_at) AS activity_time
                        FROM finance_transactions ft
                        INNER JOIN members m ON m.id = ft.member_id
                        WHERE $dashboardMemberJoinScope AND ft.status = 'successful'";
}
$activitiesSql = 'SELECT activity_text, activity_icon, activity_color, activity_time FROM ('
    . implode(' UNION ALL ', $activityUnion)
    . ') dashboard_activities WHERE activity_time IS NOT NULL ORDER BY activity_time DESC LIMIT 5';
$activitiesResult = mysqli_query($conn, $activitiesSql);
if ($activitiesResult) {
    while ($activity = mysqli_fetch_assoc($activitiesResult)) {
        $activityDate = new DateTimeImmutable($activity['activity_time'], $dashboardUtc);
        $activityDate = $activityDate->setTimezone($dashboardTimezone);
        $activity['time_label'] = $activityDate->format('M j, g:i a');
        $dashboardActivities[] = $activity;
    }
} else {
    error_log('Dashboard recent activity query failed: ' . mysqli_error($conn));
    $dashboardErrors['activities'] = true;
}

$dashboardMonthLabels = [];
$dashboardRevenueDatasets = [];
$revenueTypeLabels = [
    'membership_dues' => 'Membership dues',
    'levy' => 'Levies',
    'admission_fee' => 'Admission fees',
    'event' => 'Events',
    'other' => 'Other',
];
$revenueTypeColors = [
    'membership_dues' => '#3b82f6',
    'levy' => '#22c55e',
    'admission_fee' => '#a855f7',
    'event' => '#f59e0b',
    'other' => '#64748b',
];
foreach ($dashboardRevenueTypes as $revenueType => $_) {
    $dashboardRevenueDatasets[] = [
        'type' => $revenueType,
        'label' => $revenueTypeLabels[$revenueType] ?? ucfirst(str_replace('_', ' ', $revenueType)),
        'data' => [],
        'backgroundColor' => $revenueTypeColors[$revenueType] ?? '#64748b',
        'borderRadius' => 2,
    ];
}
for ($monthOffset = 0; $monthOffset < 12; $monthOffset++) {
    $revenueMonth = $dashboardRangeStart->modify("+$monthOffset months");
    $monthKey = $revenueMonth->format('Y-m');
    $dashboardMonthLabels[] = $revenueMonth->format('M Y');
    foreach ($dashboardRevenueDatasets as &$dataset) {
        $dataset['data'][] = (float) ($dashboardRevenueMonths[$monthKey][$dataset['type']] ?? 0);
    }
    unset($dataset);
}
$dashboardRevenueDatasets = array_map(static function (array $dataset): array {
    unset($dataset['type']);
    return $dataset;
}, $dashboardRevenueDatasets);
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Super Admin Dashboard - Associa8</title>
    <!-- Google font -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap"
      rel="stylesheet"
    />

    <!-- Font Awesome Icons -->
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    />
    <!-- Fav icon -->
    <link
      rel="shortcut icon"
      href="../images/fav-logo.png"
      type="image/x-icon"
    />

    <!-- Admin Dashboard CSS -->
    <link rel="stylesheet" href="../css/preloader.css" />
    <link rel="stylesheet" href="../css/dashboard.css?v=20261016-responsive-tables-1" />
  </head>
  <body class="admin-body">
    <!-- ====================
    PRELOADER
    ==================== -->
    <?php include('inc/preloader.php')?>
    <div class="admin-layout">
      <!-- ==========================================
         SIDEBAR NAVIGATION
         ========================================== -->
        <?php include('inc/sidebar.php') ?>

      <!-- Mobile sidebar backdrop: tap it to close the sidebar -->
      <div class="sidebar-overlay" id="sidebarOverlay"></div>

      <!-- ==========================================
         MAIN CONTENT AREA
         ========================================== -->
      <main class="admin-main">
        <!-- Top Navigation Header -->
        <header class="admin-header">
          <div class="header-left">
            <button class="header-toggle-btn" id="sidebarToggle">
              <i class="fa-solid fa-bars"></i>
            </button>
            <h1 class="page-title">Dashboard Overview</h1>
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
              <?php
                $dashboardAdminName = trim((string) ($_SESSION['admin_name'] ?? '')) ?: (string) ($_SESSION['username'] ?? 'Administrator');
                $dashboardInitials = strtoupper(substr($dashboardAdminName, 0, 1));
                if (strpos($dashboardAdminName, ' ') !== false) {
                    $dashboardInitials .= strtoupper(substr(substr($dashboardAdminName, (int) strrpos($dashboardAdminName, ' ') + 1), 0, 1));
                }
                $dashboardGreeting = (int) $dashboardNow->format('G') < 12
                    ? 'Good morning'
                    : ((int) $dashboardNow->format('G') < 17 ? 'Good afternoon' : 'Good evening');
              ?>
              <div class="avatar-badge"><?php echo htmlspecialchars($dashboardInitials, ENT_QUOTES, 'UTF-8'); ?></div>
              <div class="user-info">
                <span class="user-name"><?php echo htmlspecialchars($dashboardAdminName, ENT_QUOTES, 'UTF-8'); ?></span>
                <span class="user-role"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', (string) ($_SESSION['admin_role'] ?? 'admin'))), ENT_QUOTES, 'UTF-8'); ?></span>
              </div>
            </div>
          </div>
        </header>

        <!-- Dashboard Body Content -->
        <div class="dashboard-content">
          <!-- Welcome Banner -->
          <section class="welcome-banner">
            <h2 class="welcome-title"><?php echo $dashboardGreeting; ?>, <?php echo htmlspecialchars($dashboardAdminName, ENT_QUOTES, 'UTF-8'); ?> 👋</h2>
            <p class="welcome-subtitle">
              Here's what's happening in your association today.
            </p>

            <div class="banner-actions">
              <?php if (admin_has_permission('admission')): ?>
                <a href="admission-management.php" style="text-decoration: none;" class="btn-banner-action">
                  <i class="fa-solid fa-graduation-cap"></i> Review Applications
                </a>
              <?php endif; ?>
              <?php if (admin_has_permission('finance')): ?>
                <a href="financial.php" style="text-decoration: none;" class="btn-banner-action">
                  <i class="fa-solid fa-wallet"></i> View Financials
                </a>
              <?php endif; ?>
              <?php if (admin_has_permission('attendance')): ?>
                <a href="attendance.php" style="text-decoration: none;" class="btn-banner-action">
                  <i class="fa-regular fa-calendar-days"></i> Attendance Reports
                </a>
              <?php endif; ?>
            </div>
          </section>

          <!-- Stats Grid (4 Cards) -->
          <section class="stats-grid">
            <div class="stat-card">
              <div class="stat-card-header">
                <div class="stat-icon-wrapper icon-blue">
                  <i class="fa-solid fa-users"></i>
                </div>
              </div>
              <div class="stat-value"><?php echo $dashboardMemberTotal === null ? '—' : number_format($dashboardMemberTotal); ?></div>
              <div>
                <div class="stat-label">Total Members</div>
                <div class="stat-subtext">In this Association</div>
              </div>
            </div>

            <?php if (admin_has_permission('finance')): ?>
            <div class="stat-card">
              <div class="stat-card-header">
                <div class="stat-icon-wrapper icon-green">
                  <i class="fa-solid fa-naira-sign"></i>
                </div>
              </div>
              <div class="stat-value"><?php echo $dashboardRevenueTotal === null ? '—' : '&#8358;' . number_format($dashboardRevenueTotal, 0); ?></div>
              <div>
                <div class="stat-label">Revenue</div>
                <div class="stat-subtext">Successful payments in the past 12 months</div>
              </div>
            </div>
            <?php endif; ?>

            <?php if (admin_has_permission('attendance')): ?>
            <div class="stat-card">
              <div class="stat-card-header">
                <div class="stat-icon-wrapper icon-dark">
                  <i class="fa-solid fa-chart-simple"></i>
                </div>
              </div>
              <div class="stat-value"><?php echo $dashboardAttendanceRate === null || $dashboardAttendanceRecords === 0 ? '—' : $dashboardAttendanceRate . '%'; ?></div>
              <div>
                <div class="stat-label">Attendance this month</div>
                <div class="stat-subtext"><?php echo $dashboardAttendanceRecords ? number_format($dashboardAttendanceRecords) . ' records' : 'No attendance records this month'; ?></div>
              </div>
            </div>
            <?php endif; ?>

            <?php if (admin_has_permission('admission')): ?>
            <div class="stat-card">
              <div class="stat-card-header">
                <div class="stat-icon-wrapper icon-red">
                  <i class="fa-solid fa-graduation-cap"></i>
                </div>
              </div>
              <div class="stat-value"><?php echo $dashboardPendingApplications === null ? '—' : number_format($dashboardPendingApplications); ?></div>
              <div>
                <div class="stat-label">Applications in progress</div>
                <div class="stat-subtext">Pending or under review</div>
              </div>
            </div>
            <?php endif; ?>
          </section>

          <?php if ($dashboardErrors): ?>
            <div class="dashboard-card" role="status" style="margin-bottom: 1rem;">
              Some dashboard figures could not be loaded. Other available figures are shown below.
            </div>
          <?php endif; ?>

          <!-- Charts Row 1 -->
          <section class="dashboard-split-grid">
            <div class="dashboard-card">
              <div class="dashboard-card-header">
                <div>
                  <h3 class="dashboard-card-title">Member Growth</h3>
                  <p class="dashboard-card-subtitle">
                    Cumulative membership count
                  </p>
                </div>
              </div>
              <?php if (!empty($dashboardErrors['growth'])): ?>
                <div class="zone-empty-state">Member growth could not be loaded.</div>
              <?php elseif ($dashboardMemberTotal === 0): ?>
                <div class="zone-empty-state">Member growth will appear after members are added.</div>
              <?php else: ?>
                <div style="height: 250px; position: relative">
                  <canvas id="memberGrowthChart"></canvas>
                </div>
              <?php endif; ?>
            </div>

            <div class="dashboard-card">
              <div class="dashboard-card-header">
                <div>
                  <h3 class="dashboard-card-title">Member Status</h3>
                  <p class="dashboard-card-subtitle">Current distribution</p>
                </div>
              </div>
              <div
                style="
                  height: 180px;
                  position: relative;
                  display: flex;
                  justify-content: center;
                "
              >
                <?php if (!empty($dashboardErrors['member_status'])): ?>
                  <div class="zone-empty-state">Member status could not be loaded.</div>
                <?php else: ?>
                  <canvas id="memberStatusChart"></canvas>
                <?php endif; ?>
              </div>
              <div class="legend-list">
                <div class="legend-item">
                  <div class="legend-info">
                    <span class="legend-dot active"></span> Active
                  </div>
                  <span class="legend-value"><?php echo number_format($dashboardMemberStatuses['active']); ?></span>
                </div>
                <div class="legend-item">
                  <div class="legend-info">
                    <span class="legend-dot suspended"></span> Suspended
                  </div>
                  <span class="legend-value"><?php echo number_format($dashboardMemberStatuses['suspended']); ?></span>
                </div>
                <div class="legend-item">
                  <div class="legend-info">
                    <span class="legend-dot pending"></span> Pending
                  </div>
                  <span class="legend-value"><?php echo number_format($dashboardMemberStatuses['pending']); ?></span>
                </div>
              </div>
            </div>
          </section>

          <!-- Charts Row 2 -->
          <section class="dashboard-split-grid">
            <?php if (admin_has_permission('finance')): ?>
            <div class="dashboard-card">
              <div class="dashboard-card-header">
                <div>
                  <h3 class="dashboard-card-title">Revenue by payment type</h3>
                  <p class="dashboard-card-subtitle">
                    Successful payments over the past 12 months
                  </p>
                </div>
              </div>
              <?php if (!empty($dashboardErrors['finance'])): ?>
                <div class="zone-empty-state">Payment data could not be loaded.</div>
              <?php elseif (!$dashboardRevenueTypes): ?>
                <div class="zone-empty-state">No successful payments were recorded in the past 12 months.</div>
              <?php else: ?>
                <div style="height: 250px; position: relative">
                  <canvas id="revenueChart"></canvas>
                </div>
              <?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="dashboard-card">
              <div class="dashboard-card-header">
                <h3 class="dashboard-card-title">Recent Activities</h3>
              </div>
              <div class="activity-list">
                <?php if (!empty($dashboardErrors['activities'])): ?>
                  <div class="zone-empty-state">Recent activity could not be loaded.</div>
                <?php elseif ($dashboardActivities): ?>
                  <?php foreach ($dashboardActivities as $activity): ?>
                    <div class="activity-item">
                      <div class="activity-icon <?php echo htmlspecialchars($activity['activity_color'], ENT_QUOTES, 'UTF-8'); ?>">
                        <i class="fa-solid <?php echo htmlspecialchars($activity['activity_icon'], ENT_QUOTES, 'UTF-8'); ?>"></i>
                      </div>
                      <div class="activity-details">
                        <span class="activity-text"><?php echo htmlspecialchars($activity['activity_text'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="activity-time"><?php echo htmlspecialchars($activity['time_label'], ENT_QUOTES, 'UTF-8'); ?></span>
                      </div>
                    </div>
                  <?php endforeach; ?>
                <?php else: ?>
                  <div class="zone-empty-state">No recent activity.</div>
                <?php endif; ?>
              </div>
            </div>
          </section>

          <!-- Bottom Row -->
          <section class="bottom-widgets-grid">
            <?php if (admin_has_permission('finance')): ?>
            <div class="dashboard-card">
              <div class="mini-stat-header success">
                <i class="fa-solid fa-comment-dots"></i> Annual Dues Compliance
              </div>
              <div class="mini-stat-value">
                <?php echo $dashboardDuesCompliance === null ? '—' : $dashboardDuesCompliance . '%'; ?>
                <span class="mini-stat-value-sub"><?php echo $dashboardDuesAssessed ? 'of members with dues assessed' : 'members with dues assessed'; ?></span>
              </div>
              <?php if ($dashboardDuesAssessed > 0): ?>
                <div class="progress-bar-wrapper">
                  <div class="progress-bar-fill" style="width: <?php echo $dashboardDuesCompliance ?? 0; ?>%"></div>
                </div>
              <?php elseif ($dashboardDuesCompliance !== null): ?>
                <div class="mini-stat-footnote">No annual dues are configured.</div>
              <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if (admin_has_permission('admission')): ?>
            <div class="dashboard-card">
              <div class="mini-stat-header danger">
                <i class="fa-regular fa-clock"></i> CBT Pending
              </div>
              <div class="mini-stat-value">
                <?php echo $dashboardCbtPending === null ? '—' : number_format($dashboardCbtPending); ?>
                <span class="mini-stat-value-sub">applicants assigned an exam and not yet completed</span>
              </div>
              <div class="mini-stat-footnote danger">Currently under review and assigned an exam</div>
            </div>
            <?php endif; ?>

            <?php if (admin_has_permission('events')): ?>
            <div class="dashboard-card">
              <div class="mini-stat-header purple">
                <i class="fa-regular fa-calendar-days"></i> Upcoming Events
              </div>
              <div class="mini-stat-value">
                <?php echo $dashboardUpcomingEvents === null ? '—' : number_format($dashboardUpcomingEvents); ?>
                <span class="mini-stat-value-sub">in the next 30 days</span>
              </div>
              <div class="mini-stat-footnote purple"><?php echo $dashboardNextEvent ? 'Next: ' . htmlspecialchars($dashboardNextEvent['title'], ENT_QUOTES, 'UTF-8') . ' · ' . htmlspecialchars($dashboardNextEvent['date'], ENT_QUOTES, 'UTF-8') : 'No events scheduled in the next 30 days'; ?></div>
            </div>
            <?php endif; ?>
          </section>
        </div>

        <!-- Footer -->
        <?php include('inc/footer.php') ?>
      </main>
    </div>

    <!-- Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Interactive Dashboard Script -->
    <script>
      // 1. Sidebar Dropdown Accordion Toggle Logic (Dynamic Height Precision)
      const dropdownItems = document.querySelectorAll(".sidebar-item.dropdown");

      dropdownItems.forEach((item) => {
        const link = item.querySelector(".sidebar-link");
        const submenu = item.querySelector(".sidebar-submenu");

        link.addEventListener("click", (e) => {
          e.preventDefault();
          const isOpen = item.classList.contains("open");

          // Close all other open dropdowns
          dropdownItems.forEach((otherItem) => {
            otherItem.classList.remove("open");
            const otherSub = otherItem.querySelector(".sidebar-submenu");
            if (otherSub) otherSub.style.maxHeight = null;
          });

          // Toggle clicked dropdown
          if (!isOpen) {
            item.classList.add("open");
            submenu.style.maxHeight = submenu.scrollHeight + "px";
          } else {
            item.classList.remove("open");
            submenu.style.maxHeight = null;
          }
        });
      });

      // 2. Mobile Sidebar Toggle (with backdrop + outside-tap/Escape to close)
      const sidebarEl = document.getElementById("adminSidebar");
      const sidebarOverlay = document.getElementById("sidebarOverlay");

      function openSidebar() {
        sidebarEl.classList.add("open");
        sidebarOverlay.classList.add("active");
      }

      function closeSidebar() {
        sidebarEl.classList.remove("open");
        sidebarOverlay.classList.remove("active");
      }

      document.getElementById("sidebarToggle").addEventListener("click", () => {
        sidebarEl.classList.contains("open") ? closeSidebar() : openSidebar();
      });

      // Tap the backdrop to close
      sidebarOverlay.addEventListener("click", closeSidebar);

      // Escape key to close
      document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") closeSidebar();
      });

      // Close as soon as a real nav link (not a dropdown toggle) is tapped,
      // so it doesn't stay open while the new page loads
      sidebarEl.querySelectorAll(".sidebar-link").forEach((link) => {
        const parentItem = link.closest(".sidebar-item");
        const isDropdownToggle = parentItem && parentItem.classList.contains("dropdown") && link === parentItem.querySelector(":scope > .sidebar-link");
        if (!isDropdownToggle) {
          link.addEventListener("click", closeSidebar);
        }
      });

      // 3. Member Growth Line Chart
      const growthCanvas = document.getElementById("memberGrowthChart");
      if (growthCanvas) {
        new Chart(growthCanvas.getContext("2d"), {
          type: "line",
          data: {
            labels: <?php echo json_encode($dashboardGrowthLabels, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
            datasets: [{
              label: "Members",
              data: <?php echo json_encode($dashboardGrowthValues); ?>,
              borderColor: "#3b82f6",
              borderWidth: 3,
              pointRadius: 2,
              tension: 0.1,
            }],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
              y: { beginAtZero: true, ticks: { precision: 0 } },
              x: { grid: { display: false } },
            },
          },
        });
      }

      // 4. Member Status Donut Chart
      const statusCanvas = document.getElementById("memberStatusChart");
      if (statusCanvas) {
        new Chart(statusCanvas.getContext("2d"), {
          type: "doughnut",
          data: {
            labels: ["Active", "Pending", "Suspended"],
            datasets: [{
              data: [
                <?php echo (int) $dashboardMemberStatuses['active']; ?>,
                <?php echo (int) $dashboardMemberStatuses['pending']; ?>,
                <?php echo (int) $dashboardMemberStatuses['suspended']; ?>
              ],
              backgroundColor: ["#3b82f6", "#eab308", "#ef4444"],
              borderWidth: 2,
            }],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: "75%",
            plugins: { legend: { display: false } },
          },
        });
      }

      // 5. Revenue by payment type
      const revenueCanvas = document.getElementById("revenueChart");
      if (revenueCanvas) {
        new Chart(revenueCanvas.getContext("2d"), {
          type: "bar",
          data: {
            labels: <?php echo json_encode($dashboardMonthLabels, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
            datasets: <?php echo json_encode($dashboardRevenueDatasets, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                position: "bottom",
                labels: { boxWidth: 12, usePointStyle: false },
              },
              tooltip: {
                callbacks: {
                  label: (context) => context.dataset.label + ": ₦" + Number(context.raw).toLocaleString(),
                },
              },
            },
            scales: {
              y: { beginAtZero: true, ticks: { callback: (value) => "₦" + Number(value).toLocaleString() } },
              x: { grid: { display: false } },
            },
          },
        });
      }
    </script>
    <script src="../js/preloader.js"></script>
  </body>
</html>