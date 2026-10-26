<?php
require_once 'inc/auth.php';
require_once '../../inc/db.php';
require_once __DIR__ . '/../inc/finance-levies.php';
require_once __DIR__ . '/inc/annual-dues.php';
require_once '../../inc/paystack.php';

$memberId = (int) $_SESSION['member_id'];
$memberOrgId = (int) $_SESSION['member_org_id'];
$paymentNotice = $_SESSION['member_payment_notice'] ?? null;
unset($_SESSION['member_payment_notice']);
if (empty($_SESSION['member_payment_csrf'])) {
  $_SESSION['member_payment_csrf'] = bin2hex(random_bytes(32));
}
$onlinePaymentsEnabled = associa8_paystack_enabled();
$annualDues = member_load_annual_dues($conn, $memberId, $memberOrgId);
if ($annualDues === null) {
  http_response_code(500);
  exit('Your annual dues could not be loaded right now.');
}

$transactions = [];
$levySchemaAvailable = admin_finance_levies_available($conn);
$transactionSql = $levySchemaAvailable
  ? "SELECT ft.id, ft.reference, ft.amount, ft.type, l.title AS levy_title, ft.status, ft.paid_at, ft.created_at FROM finance_transactions ft LEFT JOIN levies l ON l.id = ft.levy_id WHERE ft.member_id = $memberId ORDER BY ft.paid_at DESC, ft.created_at DESC"
  : "SELECT id, reference, amount, type, status, paid_at, created_at FROM finance_transactions WHERE member_id = $memberId ORDER BY paid_at DESC, created_at DESC";
$transactionsResult = mysqli_query($conn, $transactionSql);
if ($transactionsResult) {
  while ($row = mysqli_fetch_assoc($transactionsResult)) {
    $transactions[] = $row;
  }
} else {
  error_log('Member payment history could not be loaded: ' . mysqli_error($conn));
  http_response_code(500);
  exit('Your payment history could not be loaded right now.');
}

$memberLevies = [];
$memberLevyTotal = 0.0;
$memberLevyPaid = 0.0;
$memberLevyOutstanding = 0.0;
$memberLevySql = "SELECT l.id, l.title, l.due_date, ml.amount,
                         COALESCE(SUM(ft.amount), 0) AS paid_amount
                  FROM member_levies ml
                  INNER JOIN levies l ON l.id = ml.levy_id AND l.org_id = ?
                  LEFT JOIN finance_transactions ft
                    ON ft.levy_id = ml.levy_id AND ft.member_id = ml.member_id
                    AND ft.type = 'levy' AND ft.status = 'successful'
                  WHERE ml.member_id = ?
                  GROUP BY l.id, l.title, l.due_date, ml.amount
                  ORDER BY l.due_date DESC, l.id DESC";
$memberLevyStmt = $levySchemaAvailable ? mysqli_prepare($conn, $memberLevySql) : false;
if ($levySchemaAvailable && !$memberLevyStmt) {
  error_log('Member levy obligations could not be prepared: ' . mysqli_error($conn));
  http_response_code(500);
  exit('Your levy balances could not be loaded right now.');
} elseif ($levySchemaAvailable) {
mysqli_stmt_bind_param($memberLevyStmt, 'ii', $memberOrgId, $memberId);
if (!mysqli_stmt_execute($memberLevyStmt)) {
  error_log('Member levy obligations could not be loaded: ' . mysqli_stmt_error($memberLevyStmt));
  mysqli_stmt_close($memberLevyStmt);
  http_response_code(500);
  exit('Your levy balances could not be loaded right now.');
}
$memberLevyResult = mysqli_stmt_get_result($memberLevyStmt);
while ($memberLevyResult && ($levy = mysqli_fetch_assoc($memberLevyResult))) {
  $levy['amount'] = (float) $levy['amount'];
  $levy['paid_amount'] = min($levy['amount'], (float) $levy['paid_amount']);
  $levy['outstanding'] = max(0.0, $levy['amount'] - $levy['paid_amount']);
  $levy['due_date_label'] = date('M j, Y', strtotime($levy['due_date']));
  $memberLevyTotal += $levy['amount'];
  $memberLevyPaid += $levy['paid_amount'];
  $memberLevyOutstanding += $levy['outstanding'];
  $memberLevies[] = $levy;
}
mysqli_stmt_close($memberLevyStmt);
}

$paidThisYear = $annualDues['paid'];
$outstanding = $annualDues['outstanding'];
$annualTotal = $annualDues['total_due'];
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Payment - Associa8</title>
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
            <h1 class="page-title">Payment</h1>
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
          <?php if (is_array($paymentNotice)): ?>
            <div class="dashboard-card" role="<?php echo ($paymentNotice['type'] ?? '') === 'success' ? 'status' : 'alert'; ?>" style="margin-bottom: 1rem; color: <?php echo ($paymentNotice['type'] ?? '') === 'success' ? '#166534' : '#b91c1c'; ?>;">
              <?php echo htmlspecialchars((string) ($paymentNotice['text'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
            </div>
          <?php endif; ?>
          <!-- Stat Cards -->
          <section class="summary-cards-grid">
            <div class="summary-card">
              <div class="summary-card-header">
                <span class="summary-card-title">Outstanding Dues</span>
                <?php admin_info_tip('Outstanding is the annual zone dues plus any annual sub-zone dues, minus successful membership-dues payments received this year.', 'Outstanding dues help'); ?>
              </div>
              <div class="summary-value">&#8358;<?php echo number_format($outstanding, 2); ?></div>
              <div class="summary-sublabel"><?php echo $annualTotal > 0 ? '&#8358;' . number_format($paidThisYear, 2) . ' paid of &#8358;' . number_format($annualTotal, 2) . ' annual dues in ' . htmlspecialchars($annualDues['year'], ENT_QUOTES, 'UTF-8') : 'No annual dues set for your zone or sub-zone'; ?></div>
              <?php if ($onlinePaymentsEnabled && $outstanding > 0): ?>
                <form action="proc-initiate-payment.php" method="post">
                  <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['member_payment_csrf'], ENT_QUOTES, 'UTF-8'); ?>" />
                  <input type="hidden" name="type" value="membership_dues" />
                  <button class="btn-summary-action" type="submit">Pay annual dues</button>
                </form>
              <?php elseif ($outstanding > 0): ?>
                <span class="summary-sublabel">Online payments are unavailable. Contact your organization to pay offline.</span>
              <?php endif; ?>
            </div>

            <div class="summary-card">
              <div class="summary-card-header">
                <span class="summary-card-title">Annual Dues Paid</span>
                <?php admin_info_tip('Only successful membership-dues payments received in the current Africa/Lagos calendar year count toward this amount.', 'Payments this year help'); ?>
                <div class="summary-icon-circle">
                  <i class="fa-solid fa-check"></i>
                </div>
              </div>
              <div class="summary-value">&#8358;<?php echo number_format($paidThisYear, 2); ?></div>
              <div class="summary-sublabel"><?php echo $annualTotal > 0 ? 'of &#8358;' . number_format($annualTotal, 2) . ' due in ' . htmlspecialchars($annualDues['year'], ENT_QUOTES, 'UTF-8') : 'No annual dues set for your zone or sub-zone'; ?></div>
            </div>

            <div class="summary-card">
              <div class="summary-card-header">
                <span class="summary-card-title">Total All Time</span>
                <div class="summary-icon-circle">
                  <i class="fa-regular fa-credit-card"></i>
                </div>
              </div>
              <div class="summary-value">&#8358;<?php echo number_format(array_reduce($transactions, fn($carry, $item) => $carry + (($item['status'] ?? '') === 'successful' ? (float) ($item['amount'] ?? 0) : 0), 0), 0); ?></div>
              <div class="summary-sublabel">Since first transaction</div>
            </div>
          </section>

          <?php if ($levySchemaAvailable): ?>
          <section class="dashboard-card" style="margin-bottom: 1.5rem;">
            <div class="dashboard-card-header">
              <div>
                <h3 class="dashboard-card-title">One-time levies</h3>
                <p class="dashboard-card-subtitle">Levies assigned to you by your organization.</p>
                <?php if (!$onlinePaymentsEnabled && $memberLevyOutstanding > 0): ?>
                  <p class="dashboard-card-subtitle">Online payments are unavailable. Contact your organization to pay offline.</p>
                <?php endif; ?>
              </div>
              <strong>Outstanding: &#8358;<?php echo number_format($memberLevyOutstanding, 2); ?></strong>
            </div>
            <div class="table-responsive-card">
              <table class="custom-admin-table">
                <thead><tr><th>Levy</th><th>Due date</th><th>Assessed</th><th>Paid</th><th>Outstanding</th></tr></thead>
                <tbody>
                  <?php if ($memberLevies): ?>
                    <?php foreach ($memberLevies as $levy): ?>
                      <tr>
                        <td class="fw-semibold"><?php echo htmlspecialchars($levy['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($levy['due_date_label'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="amount-cell amount-neutral">&#8358;<?php echo number_format($levy['amount'], 2); ?></td>
                        <td class="amount-cell amount-neutral">&#8358;<?php echo number_format($levy['paid_amount'], 2); ?></td>
                        <td class="amount-cell amount-neutral">
                          &#8358;<?php echo number_format($levy['outstanding'], 2); ?>
                          <?php if ($onlinePaymentsEnabled && $levy['outstanding'] > 0): ?>
                            <form action="proc-initiate-payment.php" method="post" style="margin-top: .4rem;">
                              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['member_payment_csrf'], ENT_QUOTES, 'UTF-8'); ?>" />
                              <input type="hidden" name="type" value="levy" />
                              <input type="hidden" name="levy_id" value="<?php echo (int) $levy['id']; ?>" />
                              <button type="submit" class="btn-navy-outline">Pay levy</button>
                            </form>
                          <?php endif; ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                    <tr>
                      <td colspan="2" class="fw-semibold">Total</td>
                      <td class="amount-cell amount-neutral fw-semibold">&#8358;<?php echo number_format($memberLevyTotal, 2); ?></td>
                      <td class="amount-cell amount-neutral fw-semibold">&#8358;<?php echo number_format($memberLevyPaid, 2); ?></td>
                      <td class="amount-cell amount-neutral fw-semibold">&#8358;<?php echo number_format($memberLevyOutstanding, 2); ?></td>
                    </tr>
                  <?php else: ?>
                    <tr><td colspan="5" class="zone-empty-state">No one-time levies have been assigned to you.</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </section>
          <?php endif; ?>

          <!-- Payment History -->
          <section class="table-responsive-card member-history-table-card">
            <div class="list-card-dark-header">
              <div class="list-card-dark-header-left">
                <span class="list-card-dark-header-icon">
                  <i class="fa-solid fa-arrow-right"></i>
                </span>
                Payment History
              </div>
              <a href="#" class="list-card-dark-header-action">
                <i class="fa-solid fa-file-export"></i> Export
              </a>
            </div>

            <table class="admin-table">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Description</th>
                  <th>Reference</th>
                  <th>Amount</th>
                  <th>Status</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <?php if ($transactions): ?>
                  <?php foreach ($transactions as $transaction): ?>
                    <?php
                      $date = $transaction['paid_at'] ?: $transaction['created_at'];
                      $status = ucfirst($transaction['status'] ?? 'pending');
                      $statusClass = $transaction['status'] === 'successful' ? 'status-text-attended' : ($transaction['status'] === 'pending' ? 'status-text-pending' : 'status-text-refunded');
                    ?>
                    <tr>
                      <td style="font-weight: 600; color: var(--text-primary);"><?php echo htmlspecialchars(date('M j, Y', strtotime($date))); ?></td>
                      <td><?php echo htmlspecialchars(($transaction['type'] ?? '') === 'levy' && !empty($transaction['levy_title']) ? 'Levy: ' . $transaction['levy_title'] : ucfirst(str_replace('_', ' ', $transaction['type'] ?? 'other')), ENT_QUOTES, 'UTF-8'); ?></td>
                      <td><span class="tx-ref-code"><?php echo htmlspecialchars($transaction['reference'] ?? 'N/A'); ?></span></td>
                      <td style="font-weight: 600; color: var(--text-primary);">&#8358;<?php echo number_format((float) ($transaction['amount'] ?? 0), 0); ?></td>
                      <td><span class="<?php echo $statusClass; ?>"><?php echo htmlspecialchars($status); ?></span></td>
                      <td style="text-align: right;">
                        <button class="btn-action-trigger" aria-label="Options">
                          <i class="fa-solid fa-ellipsis"></i>
                        </button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="6" class="zone-empty-state">No financial transactions have been recorded for this member yet.</td>
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
      const sidebar = document.getElementById("adminSidebar");
      const sidebarToggle = document.getElementById("sidebarToggle");
      const sidebarCloseBtn = document.getElementById("sidebarCloseBtn");

      if (sidebarToggle) {
        sidebarToggle.addEventListener("click", () => {
          sidebar.classList.toggle("open");
          console.log("Sidebar opened")
        });
      }

      if (sidebarCloseBtn) {
        sidebarCloseBtn.addEventListener("click", () => {
          sidebar.classList.remove("open");
          console.log("Sidebar closed");
        });
      }
    </script>
    <script src="../../js/preloader.js"></script>
  </body>
</html>