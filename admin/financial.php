<?php
require_once "inc/auth.php";
require_once "../inc/db.php";

$orgId = (int) ($_SESSION['org_id'] ?? 0);
$canRecordOfflinePayments = in_array((string) ($_SESSION['admin_role'] ?? ''), ['admin', 'super_admin'], true);
$canViewAllOrganizations = ($_SESSION['admin_role'] ?? '') === 'super_admin' && $orgId < 1;
$memberScope = $canViewAllOrganizations ? '1 = 1' : 'm.org_id = ' . $orgId;
$transactions = [];
$totalCollected = 0.0;
$pendingAmount = 0.0;
$successfulCount = 0;
$pendingCount = 0;
$transactionQueryFailed = false;
$financeNotice = $_SESSION['finance_notice'] ?? null;
unset($_SESSION['finance_notice']);

$sql = "SELECT ft.reference, CONCAT(COALESCE(m.first_name, ''), ' ', COALESCE(m.last_name, '')) AS member_name,
               ft.type, ft.amount, ft.status, ft.paid_at, ft.created_at
        FROM finance_transactions ft
        LEFT JOIN members m ON m.id = ft.member_id
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
    <link rel="stylesheet" href="../css/dashboard.css?v=20261008-infotips-3" />
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

          <?php if (is_array($financeNotice)): ?>
            <div class="dashboard-card" role="status" style="margin-bottom: 1rem; color: <?php echo ($financeNotice['type'] ?? '') === 'success' ? '#166534' : '#b91c1c'; ?>;">
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
            <button class="filter-pill active" data-tab="overview">Overview</button>
            <button class="filter-pill" data-tab="dues-levies">Dues & Levies</button>
            <button class="filter-pill" data-tab="payment">Payment</button>
            <button class="filter-pill" data-tab="budget-tracking">Budget Tracking</button>
          </div>

          <div class="tab-content">

            <!-- ============ OVERVIEW TAB ============ -->
            <div class="tab-pane active" id="tab-overview">
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
            <div class="tab-pane" id="tab-dues-levies">
              <div class="dashboard-card">
                <div class="dashboard-card-header">
                  <h3 class="dashboard-card-title">Dues & Levies</h3>
                </div>
                <div class="zone-empty-state">Dues and levy obligations are not configured yet. The current transaction table records payments only, so member balances and paid percentages cannot be calculated accurately.</div>
              </div>
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
                          <td><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', (string) $transaction['type'])), ENT_QUOTES, 'UTF-8'); ?></td>
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