<?php
require_once 'inc/auth.php';
require_once '../../inc/db.php';

$memberId = (int) $_SESSION['member_id'];

$transactions = [];
$transactionsResult = mysqli_query($conn, "SELECT id, reference, amount, type, status, paid_at, created_at FROM finance_transactions WHERE member_id = $memberId ORDER BY paid_at DESC, created_at DESC");
if ($transactionsResult) {
  while ($row = mysqli_fetch_assoc($transactionsResult)) {
    $transactions[] = $row;
  }
}

$paidThisYear = 0;
$paidThisYearResult = mysqli_query($conn, "SELECT COALESCE(SUM(amount), 0) AS total FROM finance_transactions WHERE member_id = $memberId AND status = 'successful' AND YEAR(paid_at) = YEAR(CURDATE())");
if ($paidThisYearResult) {
  $paidRow = mysqli_fetch_assoc($paidThisYearResult);
  $paidThisYear = (float) ($paidRow['total'] ?? 0);
}

$outstanding = 0;
$outstandingResult = mysqli_query($conn, "SELECT COALESCE(SUM(amount), 0) AS total FROM finance_transactions WHERE member_id = $memberId AND status = 'pending'");
if ($outstandingResult) {
  $outstandingRow = mysqli_fetch_assoc($outstandingResult);
  $outstanding = (float) ($outstandingRow['total'] ?? 0);
}
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
              <div class="avatar-badge"><?php echo htmlspecialchars(substr($_SESSION['member_id'] ?? 'M', 0, 2)); ?></div>
              <span class="badge-pill status-active" style="margin-left: -0.5rem;">Active</span>
            </div>
          </div>
        </header>

        <div class="dashboard-content">
          <!-- Stat Cards -->
          <section class="summary-cards-grid">
            <div class="summary-card">
              <div class="summary-card-header">
                <span class="summary-card-title">Outstanding Dues</span>
                <div class="summary-icon-circle">
                  <i class="fa-solid fa-circle-info"></i>
                </div>
              </div>
              <div class="summary-value">&#8358;<?php echo number_format($outstanding, 0); ?></div>
              <div class="summary-sublabel"><?php echo $outstanding > 0 ? 'Payment still due' : 'No pending dues'; ?></div>
              <button class="btn-summary-action">Pay up</button>
            </div>

            <div class="summary-card">
              <div class="summary-card-header">
                <span class="summary-card-title">Paid This Year</span>
                <div class="summary-icon-circle">
                  <i class="fa-solid fa-check"></i>
                </div>
              </div>
              <div class="summary-value">&#8358;<?php echo number_format($paidThisYear, 0); ?></div>
              <div class="summary-sublabel"><?php echo count($transactions) > 0 ? count($transactions) . ' transactions recorded' : 'No recorded payments'; ?></div>
            </div>

            <div class="summary-card">
              <div class="summary-card-header">
                <span class="summary-card-title">Total All Time</span>
                <div class="summary-icon-circle">
                  <i class="fa-regular fa-credit-card"></i>
                </div>
              </div>
              <div class="summary-value">&#8358;<?php echo number_format(array_reduce($transactions, fn($carry, $item) => $carry + (float) ($item['amount'] ?? 0), 0), 0); ?></div>
              <div class="summary-sublabel">Since first transaction</div>
            </div>
          </section>

          <!-- Payment History -->
          <section class="table-responsive-card">
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
                      <td><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $transaction['type'] ?? 'other'))); ?></td>
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