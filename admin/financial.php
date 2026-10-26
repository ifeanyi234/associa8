<?php
require_once "inc/auth.php";
require_once "../inc/db.php";
require_once __DIR__ . '/inc/finance-levies.php';

$orgId = (int) ($_SESSION['org_id'] ?? 0);
$canRecordOfflinePayments = in_array((string) ($_SESSION['admin_role'] ?? ''), ['admin', 'super_admin'], true);
$memberScope = 'm.org_id = ' . $orgId;
$transactions = [];
$totalCollected = 0.0;
$pendingAmount = 0.0;
$successfulCount = 0;
$pendingCount = 0;
$transactionQueryFailed = false;
$financeNotice = $_SESSION['finance_notice'] ?? null;
unset($_SESSION['finance_notice']);
$levyNotice = $_SESSION['levy_notice'] ?? null;
unset($_SESSION['levy_notice']);
if (empty($_SESSION['finance_csrf'])) {
    $_SESSION['finance_csrf'] = bin2hex(random_bytes(32));
}
$levySchemaAvailable = admin_finance_levies_available($conn);

$levyTransactionFields = $levySchemaAvailable ? ', l.title AS levy_title' : '';
$levyTransactionJoin = $levySchemaAvailable ? ' LEFT JOIN levies l ON l.id = ft.levy_id' : '';
$sql = "SELECT ft.reference, CONCAT(COALESCE(m.first_name, ''), ' ', COALESCE(m.last_name, '')) AS member_name,
               ft.type $levyTransactionFields, ft.amount, ft.status, ft.paid_at, ft.created_at
        FROM finance_transactions ft
        LEFT JOIN members m ON m.id = ft.member_id
        $levyTransactionJoin
        WHERE $memberScope
        ORDER BY COALESCE(ft.paid_at, ft.created_at) DESC, ft.reference DESC";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $transactions[] = $row;
        $status = strtolower((string) $row['status']);
        $amount = (float) $row['amount'];
        if ($status === 'successful') {
            $totalCollected += $amount;
            $successfulCount++;
        } elseif ($status === 'pending') {
            $pendingAmount += $amount;
            $pendingCount++;
        }
    }
} else {
    error_log('Finance transaction query failed: ' . mysqli_error($conn));
    $transactionQueryFailed = true;
}

$financeTimezone = new DateTimeZone('Africa/Lagos');
$financeNow = new DateTimeImmutable('now', $financeTimezone);
$financeYearStart = $financeNow
    ->setDate((int) $financeNow->format('Y'), 1, 1)
    ->setTime(0, 0);
$financeYearEnd = $financeYearStart->modify('+1 year');
$financeUtc = new DateTimeZone('UTC');
$financeYearStartUtc = $financeYearStart->setTimezone($financeUtc)->format('Y-m-d H:i:s');
$financeYearEndUtc = $financeYearEnd->setTimezone($financeUtc)->format('Y-m-d H:i:s');
$duesMembers = [];
$duesTotalDue = 0.0;
$duesTotalPaid = 0.0;
$duesTotalOutstanding = 0.0;
$duesMembersAssessed = 0;
$duesQueryFailed = false;
$duesSql = "SELECT m.id, m.first_name, m.last_name, m.member_code,
                   COALESCE(z.annual_dues, 0) + COALESCE(sz.annual_dues, 0) AS total_due,
                   COALESCE((
                       SELECT SUM(ft.amount)
                       FROM finance_transactions ft
                       WHERE ft.member_id = m.id
                         AND ft.type = 'membership_dues'
                         AND ft.status = 'successful'
                         AND ft.paid_at >= ?
                         AND ft.paid_at < ?
                   ), 0) AS paid_this_year
            FROM members m
            LEFT JOIN zones z ON z.id = m.zone_id AND z.org_id = m.org_id
            LEFT JOIN subzones sz ON sz.id = m.subzone_id AND sz.zone_id = m.zone_id
            WHERE $memberScope
            ORDER BY m.first_name, m.last_name, m.id";
$duesStmt = mysqli_prepare($conn, $duesSql);
if (!$duesStmt) {
    error_log('Finance annual dues query could not be prepared: ' . mysqli_error($conn));
    $duesQueryFailed = true;
} else {
    mysqli_stmt_bind_param($duesStmt, 'ss', $financeYearStartUtc, $financeYearEndUtc);
    if (!mysqli_stmt_execute($duesStmt)) {
        error_log('Finance annual dues query failed: ' . mysqli_stmt_error($duesStmt));
        $duesQueryFailed = true;
    } else {
        $duesResult = mysqli_stmt_get_result($duesStmt);
        while ($duesResult && ($row = mysqli_fetch_assoc($duesResult))) {
            $row['total_due'] = (float) $row['total_due'];
            $row['paid_this_year'] = (float) $row['paid_this_year'];
            $row['applied_paid'] = min($row['total_due'], $row['paid_this_year']);
            $row['outstanding'] = max(0.0, $row['total_due'] - $row['applied_paid']);
            $row['paid_percent'] = $row['total_due'] > 0
                ? (int) round(($row['applied_paid'] / $row['total_due']) * 100)
                : 0;
            $duesTotalDue += $row['total_due'];
            $duesTotalPaid += $row['applied_paid'];
            $duesTotalOutstanding += $row['outstanding'];
            if ($row['total_due'] > 0) {
                $duesMembersAssessed++;
            }
            $duesMembers[] = $row;
        }
    }
    mysqli_stmt_close($duesStmt);
}
$duesPaidPercent = $duesTotalDue > 0 ? (int) round(($duesTotalPaid / $duesTotalDue) * 100) : 0;
$duesScopeLabel = 'Association-wide total';

$levyScope = 'l.org_id = ' . $orgId;
$levies = [];
$levyTotals = ['assessed' => 0.0, 'paid' => 0.0, 'outstanding' => 0.0];
$leviesQueryFailed = false;
$leviesSql = "SELECT l.id, l.title, l.amount, l.due_date, l.scope_type, l.scope_id,
                     COALESCE(z.name, CONCAT(subz.name, ' / ', sz.name), 'Entire organization') AS target_name,
                     COUNT(ml.member_id) AS member_count,
                     COALESCE(SUM(ml.amount), 0) AS assessed_total,
                     COALESCE(SUM(LEAST(ml.amount, COALESCE(lp.paid_amount, 0))), 0) AS paid_total
              FROM levies l
              LEFT JOIN zones z ON l.scope_type = 'zone' AND z.id = l.scope_id AND z.org_id = l.org_id
              LEFT JOIN subzones sz ON l.scope_type = 'subzone' AND sz.id = l.scope_id
              LEFT JOIN zones subz ON l.scope_type = 'subzone' AND subz.id = sz.zone_id AND subz.org_id = l.org_id
              LEFT JOIN member_levies ml ON ml.levy_id = l.id
              LEFT JOIN (
                  SELECT levy_id, member_id, SUM(amount) AS paid_amount
                  FROM finance_transactions
                  WHERE type = 'levy' AND status = 'successful'
                  GROUP BY levy_id, member_id
              ) lp ON lp.levy_id = ml.levy_id AND lp.member_id = ml.member_id
              WHERE $levyScope
              GROUP BY l.id, l.title, l.amount, l.due_date, l.scope_type, l.scope_id, z.name, subz.name, sz.name
              ORDER BY l.due_date DESC, l.id DESC";
if ($levySchemaAvailable) {
    $leviesResult = mysqli_query($conn, $leviesSql);
    if (!$leviesResult) {
        error_log('Finance levy query failed: ' . mysqli_error($conn));
        $leviesQueryFailed = true;
    } else {
        while ($levy = mysqli_fetch_assoc($leviesResult)) {
            $levy['amount'] = (float) $levy['amount'];
            $levy['assessed_total'] = (float) $levy['assessed_total'];
            $levy['paid_total'] = (float) $levy['paid_total'];
            $levy['outstanding_total'] = max(0.0, $levy['assessed_total'] - $levy['paid_total']);
            $levy['due_date_label'] = (new DateTimeImmutable($levy['due_date'], $financeTimezone))->format('M j, Y');
            $levies[] = $levy;
            $levyTotals['assessed'] += $levy['assessed_total'];
            $levyTotals['paid'] += min($levy['assessed_total'], $levy['paid_total']);
            $levyTotals['outstanding'] += $levy['outstanding_total'];
        }
    }
}

$levyTargets = [];
$levyTargetQueryFailed = false;
if ($levySchemaAvailable && $orgId > 0) {
    $zoneTargetsResult = mysqli_query($conn, "SELECT id, name FROM zones WHERE org_id = $orgId ORDER BY name");
    $subzoneTargetsResult = mysqli_query(
        $conn,
        "SELECT sz.id, sz.name, z.name AS zone_name
         FROM subzones sz INNER JOIN zones z ON z.id = sz.zone_id
         WHERE z.org_id = $orgId
         ORDER BY z.name, sz.name"
    );
    if (!$zoneTargetsResult || !$subzoneTargetsResult) {
        error_log('Levy target list query failed: ' . mysqli_error($conn));
        $levyTargetQueryFailed = true;
    } else {
        while ($zone = mysqli_fetch_assoc($zoneTargetsResult)) {
            $levyTargets[] = [
                'value' => 'zone:' . (int) $zone['id'],
                'label' => 'Zone: ' . $zone['name'],
            ];
        }
        while ($subzone = mysqli_fetch_assoc($subzoneTargetsResult)) {
            $levyTargets[] = [
                'value' => 'subzone:' . (int) $subzone['id'],
                'label' => 'Sub-zone: ' . $subzone['zone_name'] . ' / ' . $subzone['name'],
            ];
        }
    }
}
$canCreateLevy = $levySchemaAvailable
    && in_array((string) ($_SESSION['admin_role'] ?? ''), ['admin', 'super_admin'], true)
    && $orgId > 0;

$monthLabels = [];
$monthKeys = [];
$monthlyCollected = [];
for ($offset = 5; $offset >= 0; $offset--) {
    $month = (new DateTimeImmutable('first day of this month'))->modify("-$offset month");
    $monthLabels[] = $month->format('M Y');
    $monthKeys[] = $month->format('Y-m');
    $monthlyCollected[] = 0;
}
foreach ($transactions as $transaction) {
    if (strtolower((string) $transaction['status']) !== 'successful' || empty($transaction['paid_at'])) {
        continue;
    }
    $monthIndex = array_search(substr($transaction['paid_at'], 0, 7), $monthKeys, true);
    if ($monthIndex !== false) {
        $monthlyCollected[$monthIndex] += (float) $transaction['amount'];
    }
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Financial Management - Associa8</title>
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
    <!-- PRELOADER -->
    <?php include('inc/preloader.php')?>
    
    <div class="admin-layout">
      <!-- SIDEBAR NAVIGATION -->
      <?php include('inc/sidebar.php') ?>

      <!-- Mobile sidebar backdrop: tap it to close the sidebar -->
      <div class="sidebar-overlay" id="sidebarOverlay"></div>

      <!-- MAIN CONTENT AREA -->
      <main class="admin-main">
        <!-- Top Navigation Header -->
        <header class="admin-header">
          <div class="header-left">
            <button class="header-toggle-btn" id="sidebarToggle">
              <i class="fa-solid fa-bars"></i>
            </button>
            <h1 class="page-title">Financial Management</h1>
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
              <div class="avatar-badge">SA</div>
              <div class="user-info">
                <span class="user-name">Super Admin</span>
                <span class="user-role">Full Access</span>
              </div>
            </div>
          </div>
        </header>

        <!-- Page Body Content -->
        <div class="dashboard-content">
          <!-- Page Action Header -->
          <div class="page-action-header">
            <div>
              <h2 class="page-title-main">Financial Management</h2>
              <p class="page-subtitle">Review payment activity recorded for your organization.</p>
            </div>
            <div style="display: flex; gap: 0.75rem;">
              <button class="btn-navy-outline" type="button" disabled title="Export is not available yet">
                <i class="fa-solid fa-arrow-up-from-bracket"></i> Export
              </button>
              <?php if ($canRecordOfflinePayments): ?>
                <a class="btn-navy-filled" href="add-payment.php">
                  <i class="fa-solid fa-circle-plus"></i> Record New Payment
                </a>
              <?php else: ?>
                <button class="btn-navy-filled" type="button" disabled title="Only organization administrators can record offline payments">
                  <i class="fa-solid fa-circle-plus"></i> Record New Payment
                </button>
              <?php endif; ?>
            </div>
          </div>

          <?php if (is_array($financeNotice) && ($financeNotice['type'] ?? '') !== 'success'): ?>
            <div class="dashboard-card" role="status" style="margin-bottom: 1rem; color: #b91c1c;">
              <?php echo htmlspecialchars((string) ($financeNotice['text'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
            </div>
          <?php endif; ?>

          <!-- Financial Overview Stats Grid (4 Cards) -->
          <section class="stats-grid-4">
            <div class="stat-card-simple">
              <div class="stat-card-icon">
                <i class="fa-solid fa-naira-sign"></i>
              </div>
              <div class="stat-card-number"><?php echo $transactionQueryFailed ? '—' : '&#8358;' . number_format($totalCollected, 0); ?></div>
              <div class="stat-card-label">Total Collected</div>
              <div class="stat-subtext">Successful transactions</div>
            </div>

            <div class="stat-card-simple">
              <div class="stat-card-icon">
                <i class="fa-regular fa-clock"></i>
              </div>
              <div class="stat-card-number"><?php echo $transactionQueryFailed ? '—' : '&#8358;' . number_format($pendingAmount, 0); ?></div>
              <div class="stat-card-label">Pending Payments</div>
              <div class="stat-subtext"><?php echo $transactionQueryFailed ? 'Data unavailable' : $pendingCount . ' pending transaction' . ($pendingCount === 1 ? '' : 's'); ?></div>
            </div>

            <div class="stat-card-simple">
              <div class="stat-card-icon">
                <i class="fa-solid fa-chart-column"></i>
              </div>
              <div class="stat-card-number"><?php echo $transactionQueryFailed ? '—' : $successfulCount; ?></div>
              <div class="stat-card-label">Successful Payments</div>
              <div class="stat-subtext">Verified records in the transaction table</div>
            </div>

            <div class="stat-card-simple">
              <div class="stat-card-icon">
                <i class="fa-regular fa-receipt"></i>
              </div>
              <div class="stat-card-number"><?php echo $transactionQueryFailed ? '—' : count($transactions); ?></div>
              <div class="stat-card-label">Transaction Records</div>
              <div class="stat-subtext">All recorded statuses</div>
            </div>
          </section>

                    <!-- Module Navigation Tabs -->
          <div class="filter-pill-group" style="margin-bottom: 0.5rem;">
            <button class="filter-pill <?php echo ($_GET['tab'] ?? '') === 'dues-levies' ? '' : 'active'; ?>" data-tab="overview">Overview</button>
            <button class="filter-pill <?php echo ($_GET['tab'] ?? '') === 'dues-levies' ? 'active' : ''; ?>" data-tab="dues-levies">Dues & Levies</button>
            <button class="filter-pill" data-tab="payment">Payment</button>
            <button class="filter-pill" data-tab="budget-tracking">Budget Tracking</button>
          </div>

          <div class="tab-content">

            <!-- ============ OVERVIEW TAB ============ -->
            <div class="tab-pane <?php echo ($_GET['tab'] ?? '') === 'dues-levies' ? '' : 'active'; ?>" id="tab-overview">
              <section class="dashboard-split-grid">
                <!-- Left Card: Monthly Collection -->
                <div class="dashboard-card">
                  <div class="dashboard-card-header">
                    <div>
                      <h3 class="dashboard-card-title">Monthly Collection</h3>
                      <p class="dashboard-card-subtitle">Successful payments in naira, last 6 months</p>
                    </div>
                  </div>
                  <?php if ($transactionQueryFailed): ?>
                    <div class="zone-empty-state">Financial records could not be loaded. Please refresh or contact support if the problem continues.</div>
                  <?php elseif (array_sum($monthlyCollected) > 0): ?>
                    <div style="height: 280px; position: relative">
                      <canvas id="monthlyCollectionChart"></canvas>
                    </div>
                  <?php else: ?>
                    <div class="zone-empty-state">No successful payments in the last six months.</div>
                  <?php endif; ?>
                </div>

                <!-- Budget figures are not available without a budget data model. -->
                <div class="dashboard-card">
                  <div class="dashboard-card-header">
                    <h3 class="dashboard-card-title">Budget Allocations</h3>
                  </div>
                  <div class="zone-empty-state">Budget tracking is not configured. No budget values are shown until budgets can be entered and verified.</div>
                </div>
              </section>
            </div>

            <!-- ============ DUES & LEVIES TAB ============ -->
            <div class="tab-pane <?php echo ($_GET['tab'] ?? '') === 'dues-levies' ? 'active' : ''; ?>" id="tab-dues-levies">
              <?php if (is_array($levyNotice)): ?>
                <div class="dashboard-card" role="<?php echo ($levyNotice['type'] ?? '') === 'success' ? 'status' : 'alert'; ?>" style="margin-bottom: 1rem;">
                  <?php echo htmlspecialchars((string) ($levyNotice['text'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                </div>
              <?php endif; ?>
              <?php if ($duesQueryFailed): ?>
                <div class="dashboard-card" role="alert">
                  Annual dues could not be loaded. Please refresh or contact support if the problem continues.
                </div>
              <?php else: ?>
                <section class="stats-grid-4">
                  <div class="stat-card-simple">
                    <div class="stat-card-icon"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                    <div class="stat-card-number">&#8358;<?php echo number_format($duesTotalDue, 2); ?></div>
                    <div class="stat-card-label">Annual dues assessed</div>
                    <div class="stat-subtext"><?php echo (int) $financeYearStart->format('Y'); ?> <?php echo $duesScopeLabel; ?></div>
                  </div>
                  <div class="stat-card-simple">
                    <div class="stat-card-icon"><i class="fa-solid fa-circle-check"></i></div>
                    <div class="stat-card-number">&#8358;<?php echo number_format($duesTotalPaid, 2); ?></div>
                    <div class="stat-card-label">Applied payments</div>
                    <div class="stat-subtext"><?php echo $duesPaidPercent; ?>% of assessed dues</div>
                  </div>
                  <div class="stat-card-simple">
                    <div class="stat-card-icon"><i class="fa-solid fa-hourglass-half"></i></div>
                    <div class="stat-card-number">&#8358;<?php echo number_format($duesTotalOutstanding, 2); ?></div>
                    <div class="stat-card-label">Outstanding dues</div>
                    <div class="stat-subtext">Remaining for <?php echo (int) $financeYearStart->format('Y'); ?></div>
                  </div>
                  <div class="stat-card-simple">
                    <div class="stat-card-icon"><i class="fa-solid fa-users"></i></div>
                    <div class="stat-card-number"><?php echo $duesMembersAssessed; ?></div>
                    <div class="stat-card-label">Members assessed</div>
                    <div class="stat-subtext">Based on assigned zone and sub-zone amounts</div>
                  </div>
                </section>

                <div class="dashboard-card">
                  <div class="dashboard-card-header">
                    <div>
                      <h3 class="dashboard-card-title">Annual membership dues</h3>
                      <p class="dashboard-card-subtitle">Successful membership-dues payments in the current Africa/Lagos calendar year are applied to each member’s assessed amount.</p>
                    </div>
                    <?php if (admin_has_permission('members_manage')): ?>
                      <a class="btn-navy-outline" href="zones.php">Configure zone dues</a>
                    <?php endif; ?>
                  </div>
                  <div class="table-responsive-card">
                    <table class="custom-admin-table">
                      <thead>
                        <tr>
                          <th>Member</th>
                          <th>Member code</th>
                          <th>Annual dues</th>
                          <th>Paid this year</th>
                          <th>Outstanding</th>
                          <th>Progress</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php if ($duesMembers): ?>
                          <?php foreach ($duesMembers as $duesMember): ?>
                            <?php $duesMemberName = trim($duesMember['first_name'] . ' ' . $duesMember['last_name']); ?>
                            <tr>
                              <td class="fw-semibold"><?php echo htmlspecialchars($duesMemberName, ENT_QUOTES, 'UTF-8'); ?></td>
                              <td><?php echo htmlspecialchars($duesMember['member_code'], ENT_QUOTES, 'UTF-8'); ?></td>
                              <td class="amount-cell amount-neutral"><?php echo $duesMember['total_due'] > 0 ? '&#8358;' . number_format($duesMember['total_due'], 2) : 'Not set'; ?></td>
                              <td class="amount-cell amount-neutral"><?php echo $duesMember['total_due'] > 0 ? '&#8358;' . number_format($duesMember['applied_paid'], 2) : '—'; ?></td>
                              <td class="amount-cell amount-neutral"><?php echo $duesMember['total_due'] > 0 ? '&#8358;' . number_format($duesMember['outstanding'], 2) : '—'; ?></td>
                              <td>
                                <?php if ($duesMember['total_due'] > 0): ?>
                                  <?php echo $duesMember['paid_percent']; ?>%
                                <?php else: ?>
                                  <span class="text-muted">No dues set</span>
                                <?php endif; ?>
                              </td>
                            </tr>
                          <?php endforeach; ?>
                        <?php else: ?>
                          <tr><td colspan="6" class="zone-empty-state">No members are available in this organization.</td></tr>
                        <?php endif; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              <?php endif; ?>

              <?php if (!$levySchemaAvailable): ?>
                <div class="dashboard-card" style="margin-top: 1.25rem;">
                  <h3 class="dashboard-card-title">Levies</h3>
                  <div class="zone-empty-state">No levies have been set up yet.</div>
                </div>
              <?php elseif ($leviesQueryFailed): ?>
                <div class="dashboard-card" role="alert" style="margin-top: 1.25rem;">Levy records could not be loaded. Please refresh or contact support if the problem continues.</div>
              <?php else: ?>
              <section class="stats-grid-4" style="margin-top: 1.25rem;">
                <div class="stat-card-simple">
                  <div class="stat-card-icon"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                  <div class="stat-card-number">&#8358;<?php echo number_format($levyTotals['assessed'], 2); ?></div>
                  <div class="stat-card-label">Levies assessed</div>
                  <div class="stat-subtext">Assigned member obligations</div>
                </div>
                <div class="stat-card-simple">
                  <div class="stat-card-icon"><i class="fa-solid fa-circle-check"></i></div>
                  <div class="stat-card-number">&#8358;<?php echo number_format($levyTotals['paid'], 2); ?></div>
                  <div class="stat-card-label">Levies paid</div>
                  <div class="stat-subtext">Successful levy payments</div>
                </div>
                <div class="stat-card-simple">
                  <div class="stat-card-icon"><i class="fa-solid fa-hourglass-half"></i></div>
                  <div class="stat-card-number">&#8358;<?php echo number_format($levyTotals['outstanding'], 2); ?></div>
                  <div class="stat-card-label">Levies outstanding</div>
                  <div class="stat-subtext">Remaining amount due</div>
                </div>
                <div class="stat-card-simple">
                  <div class="stat-card-icon"><i class="fa-solid fa-list-check"></i></div>
                  <div class="stat-card-number"><?php echo count($levies); ?></div>
                  <div class="stat-card-label">One-time levies</div>
                  <div class="stat-subtext">Current organization scope</div>
                </div>
              </section>

              <?php if ($canCreateLevy): ?>
                <div class="dashboard-card" style="margin-top: 1.25rem;">
                  <div class="dashboard-card-header">
                    <div>
                      <h3 class="dashboard-card-title">Create a one-time levy</h3>
                      <p class="dashboard-card-subtitle">Eligible active members are assigned this amount when the levy is created.</p>
                    </div>
                  </div>
                  <?php if ($levyTargetQueryFailed): ?>
                    <div class="zone-empty-state" role="alert">Levy targets could not be loaded, so a levy cannot be created right now.</div>
                  <?php else: ?>
                    <form action="proc-create-levy.php" method="post" class="structure-form">
                      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['finance_csrf'], ENT_QUOTES, 'UTF-8'); ?>" />
                      <div class="form-row-2col">
                        <div class="form-group">
                          <label for="levy_title" class="form-label">Levy name</label>
                          <input class="form-control" type="text" id="levy_title" name="title" maxlength="150" required />
                        </div>
                        <div class="form-group">
                          <label for="levy_amount" class="form-label">Amount per member (NGN)</label>
                          <input class="form-control" type="number" id="levy_amount" name="amount" min="0.01" max="9999999999.99" step="0.01" required />
                        </div>
                      </div>
                      <div class="form-row-2col">
                        <div class="form-group">
                          <label for="levy_due_date" class="form-label">Due date</label>
                          <input class="form-control" type="date" id="levy_due_date" name="due_date" required />
                        </div>
                        <div class="form-group">
                          <label for="levy_scope_target" class="form-label">Applies to</label>
                          <select class="form-select" id="levy_scope_target" name="scope_target" required>
                            <option value="organization">Entire organization</option>
                            <?php foreach ($levyTargets as $levyTarget): ?>
                              <option value="<?php echo htmlspecialchars($levyTarget['value'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($levyTarget['label'], ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                      </div>
                      <div class="structure-form-actions">
                        <button type="submit" class="btn-navy-filled"><i class="fa-solid fa-plus"></i> Create levy</button>
                      </div>
                    </form>
                  <?php endif; ?>
                </div>
              <?php endif; ?>

              <div class="dashboard-card" style="margin-top: 1.25rem;">
                <div class="dashboard-card-header">
                  <div>
                    <h3 class="dashboard-card-title">One-time levies</h3>
                    <p class="dashboard-card-subtitle">Member assignments are a snapshot taken when each levy is created.</p>
                  </div>
                </div>
                <div class="table-responsive-card">
                  <table class="custom-admin-table">
                    <thead>
                      <tr><th>Levy</th><th>Target</th><th>Due date</th><th>Members</th><th>Assessed</th><th>Paid</th><th>Outstanding</th></tr>
                    </thead>
                    <tbody>
                      <?php if ($levies): ?>
                        <?php foreach ($levies as $levy): ?>
                          <tr>
                            <td class="fw-semibold"><?php echo htmlspecialchars($levy['title'], ENT_QUOTES, 'UTF-8'); ?><br /><span class="text-muted">&#8358;<?php echo number_format($levy['amount'], 2); ?> per member</span></td>
                            <td><?php echo htmlspecialchars($levy['target_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($levy['due_date_label'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo (int) $levy['member_count']; ?></td>
                            <td class="amount-cell amount-neutral">&#8358;<?php echo number_format($levy['assessed_total'], 2); ?></td>
                            <td class="amount-cell amount-neutral">&#8358;<?php echo number_format(min($levy['assessed_total'], $levy['paid_total']), 2); ?></td>
                            <td class="amount-cell amount-neutral">&#8358;<?php echo number_format($levy['outstanding_total'], 2); ?></td>
                          </tr>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <tr><td colspan="7" class="zone-empty-state">No one-time levies have been created.</td></tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
              <?php endif; ?>
            </div>

            <!-- ============ PAYMENT TAB ============ -->
            <div class="tab-pane" id="tab-payment">
              <div class="table-responsive-card">
                <table class="custom-admin-table">
                  <thead>
                    <tr>
                      <th>Reference</th>
                      <th>Member</th>
                      <th>Type</th>
                      <th>Amount</th>
                      <th>Date</th>
                      <th>Status</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if ($transactionQueryFailed): ?>
                      <tr><td colspan="7" class="zone-empty-state">Financial records could not be loaded. Please refresh or contact support if the problem continues.</td></tr>
                    <?php elseif ($transactions): ?>
                      <?php foreach ($transactions as $transaction): ?>
                        <?php
                          $status = strtolower((string) $transaction['status']);
                          $statusLabel = $status === 'successful' ? 'Confirmed' : ucfirst($status);
                          $statusClass = $status === 'successful' ? 'badge-finance-paid' : ($status === 'pending' ? 'badge-finance-pending' : 'badge-finance-failed');
                          $paymentDate = $transaction['paid_at'] ?: $transaction['created_at'];
                        ?>
                        <tr>
                          <td><span class="tx-ref-code"><?php echo htmlspecialchars($transaction['reference'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                          <td class="fw-semibold"><?php echo htmlspecialchars(trim((string) $transaction['member_name']) ?: 'Unknown member', ENT_QUOTES, 'UTF-8'); ?></td>
                          <td><?php echo htmlspecialchars($transaction['type'] === 'levy' && !empty($transaction['levy_title']) ? 'Levy: ' . $transaction['levy_title'] : ucfirst(str_replace('_', ' ', (string) $transaction['type'])), ENT_QUOTES, 'UTF-8'); ?></td>
                          <td class="amount-cell amount-neutral">&#8358;<?php echo number_format((float) $transaction['amount'], 0); ?></td>
                          <td><?php echo htmlspecialchars(date('Y - m - d', strtotime($paymentDate)), ENT_QUOTES, 'UTF-8'); ?></td>
                          <td><span class="badge-finance <?php echo htmlspecialchars($statusClass, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8'); ?></span></td>
                          <td style="text-align: right;"><span class="text-muted">—</span></td>
                        </tr>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <tr><td colspan="7" class="zone-empty-state">No transactions have been recorded for this organization yet.</td></tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- ============ BUDGET TRACKING TAB ============ -->
            <div class="tab-pane" id="tab-budget-tracking">
              <div class="dashboard-card">
                <div class="dashboard-card-header">
                  <h3 class="dashboard-card-title">Budget Tracking</h3>
                </div>
                <div class="zone-empty-state">No budget has been entered for this organization. Budget totals and utilization will appear here when budget management is available.</div>
              </div>
            </div>

          </div>
        </div>

        <!-- Footer -->
        <?php include('inc/footer.php') ?>
      </main>
    </div>

    <?php if (is_array($financeNotice) && ($financeNotice['type'] ?? '') === 'success'): ?>
      <script>
        window.addEventListener("DOMContentLoaded", () => {
          window.AppModal?.open({
            type: "success",
            heading: "Payment recorded",
            body: <?php echo json_encode((string) ($financeNotice['text'] ?? 'Payment recorded.'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>,
          });
        });
      </script>
    <?php endif; ?>

    <!-- Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
// Financial page tab switching
const filterPills = document.querySelectorAll(".filter-pill-group .filter-pill[data-tab]");
const tabPanes = document.querySelectorAll(".tab-pane");

filterPills.forEach((pill) => {
  pill.addEventListener("click", () => {
    filterPills.forEach((p) => p.classList.remove("active"));
    pill.classList.add("active");

    const targetId = "tab-" + pill.dataset.tab;
    tabPanes.forEach((pane) => {
      pane.classList.toggle("active", pane.id === targetId);
    });
  });
});
    </script>

    <script>
      // Sidebar Dropdown Accordion Toggle Logic
      const dropdownItems = document.querySelectorAll(".sidebar-item.dropdown");

      dropdownItems.forEach((item) => {
        const link = item.querySelector(".sidebar-link");
        const submenu = item.querySelector(".sidebar-submenu");

        if (link && submenu) {
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
        }
      });

      // Mobile Sidebar Toggle
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

      sidebarOverlay.addEventListener("click", closeSidebar);

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

      // Chart uses actual successful transaction totals; expected amounts are not tracked yet.
      const collectionCanvas = document.getElementById("monthlyCollectionChart");
      if (collectionCanvas) {
        new Chart(collectionCanvas.getContext("2d"), {
          type: "bar",
          data: {
            labels: <?php echo json_encode($monthLabels, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
            datasets: [{
              label: "Collected",
              data: <?php echo json_encode($monthlyCollected); ?>,
              backgroundColor: "#22c55e",
              borderRadius: 3,
              barThickness: 16
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: { position: "bottom", labels: { usePointStyle: true, boxWidth: 8 } },
              tooltip: { callbacks: { label: (context) => "₦" + Number(context.raw).toLocaleString() } }
            },
            scales: {
              y: {
                beginAtZero: true,
                ticks: { callback: (value) => "₦" + Number(value).toLocaleString() },
                grid: { borderDash: [4, 4] }
              },
              x: { grid: { display: false } }
            }
          }
        });
      }
    </script>
    <script src="../js/preloader.js"></script>
  </body>
</html>