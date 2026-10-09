<?php
require_once 'inc/auth.php';
require_once '../inc/db.php';

$adminRole = (string) ($_SESSION['admin_role'] ?? '');
if (!in_array($adminRole, ['admin', 'super_admin'], true)) {
    admin_render_access_denied('Only organization administrators can record offline payments.');
}

$orgId = (int) ($_SESSION['org_id'] ?? 0);
$canViewAllOrganizations = $adminRole === 'super_admin' && $orgId < 1;
$memberScope = $canViewAllOrganizations ? '1 = 1' : 'org_id = ' . $orgId;
$members = [];
$notice = $_SESSION['finance_notice'] ?? null;
unset($_SESSION['finance_notice']);

if (empty($_SESSION['finance_csrf'])) {
    $_SESSION['finance_csrf'] = bin2hex(random_bytes(32));
}

$memberResult = mysqli_query(
    $conn,
    "SELECT id, member_code, first_name, last_name
     FROM members
     WHERE $memberScope
     ORDER BY first_name, last_name"
);
if ($memberResult) {
    while ($member = mysqli_fetch_assoc($memberResult)) {
        $members[] = $member;
    }
} else {
    error_log('Finance member list query failed: ' . mysqli_error($conn));
    $notice = ['type' => 'error', 'text' => 'Member list could not be loaded. Please try again later.'];
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Record Offline Payment - Associa8</title>
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
            <button class="header-toggle-btn" id="sidebarToggle" type="button" aria-label="Toggle navigation">
              <i class="fa-solid fa-bars"></i>
            </button>
            <h1 class="page-title">Record Payment</h1>
          </div>
          <div class="header-right">
            <div class="admin-user-profile">
              <div class="avatar-badge">SA</div>
              <div class="user-info">
                <span class="user-name">Administrator</span>
                <span class="user-role">Offline payment entry</span>
              </div>
            </div>
          </div>
        </header>

        <div class="dashboard-content">
          <div class="page-action-header mb-4">
            <div>
              <h2 class="page-title-main">Record an offline payment</h2>
              <p class="page-subtitle">Record a payment <?php admin_info_tip('Use this only after money has already been received outside the online payment flow, such as cash or a bank transfer.', 'Offline payment help'); ?></p>
            </div>
            <a href="financial.php" class="btn-navy-outline">
              <i class="fa-solid fa-arrow-left"></i> Back to finance
            </a>
          </div>

          <?php if (is_array($notice)): ?>
            <div class="dashboard-card" role="alert" style="margin-bottom: 1rem; color: #b91c1c;">
              <?php echo htmlspecialchars((string) ($notice['text'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
            </div>
          <?php endif; ?>

          <section class="dashboard-card structure-form-card">
            <div class="structure-form-heading">
              <div class="structure-form-icon"><i class="fa-solid fa-money-bill-wave"></i></div>
              <div>
                <h3>Payment information</h3>
              </div>
            </div>
            <?php if ($members): ?>
              <form action="proc-record-payment.php" method="post" class="structure-form">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['finance_csrf'], ENT_QUOTES, 'UTF-8'); ?>" />
                <div class="form-row-2col">
                  <div class="form-group">
                    <label for="member_id" class="form-label">Member</label>
                    <select class="form-select" id="member_id" name="member_id" required>
                      <option value="" selected disabled>Select member</option>
                      <?php foreach ($members as $member): ?>
                        <option value="<?php echo (int) $member['id']; ?>"><?php echo htmlspecialchars($member['member_code'] . ' — ' . $member['first_name'] . ' ' . $member['last_name'], ENT_QUOTES, 'UTF-8'); ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="type" class="form-label">Payment type</label>
                    <select class="form-select" id="type" name="type" required>
                      <option value="membership_dues">Membership dues</option>
                      <option value="admission_fee">Admission fee</option>
                      <option value="event">Event</option>
                      <option value="other">Other</option>
                    </select>
                  </div>
                </div>
                <div class="form-row-2col">
                  <div class="form-group">
                    <label for="amount" class="form-label">Amount (NGN)</label>
                    <input class="form-control" type="number" id="amount" name="amount" min="0.01" max="9999999999.99" step="0.01" required />
                  </div>
                  <div class="form-group">
                    <label for="payment_method" class="form-label">How was it received?</label>
                    <select class="form-select" id="payment_method" name="payment_method" required>
                      <option value="cash">Cash</option>
                      <option value="bank_transfer">Bank transfer</option>
                      <option value="other">Other offline method</option>
                    </select>
                  </div>
                </div>
                <div class="form-row-2col">
                  <div class="form-group">
                    <label for="paid_at" class="form-label">Date received (Africa/Lagos)</label>
                    <input class="form-control" type="datetime-local" id="paid_at" name="paid_at" value="<?php echo (new DateTimeImmutable('now', new DateTimeZone('Africa/Lagos')))->format('Y-m-d\TH:i'); ?>" required />
                  </div>
                  <div class="form-group">
                    <label for="notes" class="form-label">Note (optional)</label>
                    <input class="form-control" type="text" id="notes" name="notes" maxlength="100" placeholder="e.g. teller or receipt detail" />
                  </div>
                </div>
                <div class="structure-form-actions">
                  <a href="financial.php" class="btn-action-dark-outline">Cancel</a>
                  <button type="submit" class="btn-navy-filled"><i class="fa-solid fa-check"></i> Save payment</button>
                </div>
              </form>
            <?php else: ?>
              <div class="zone-empty-state">No members are available in this organization. Add a member before recording a payment.</div>
            <?php endif; ?>
          </section>
        </div>
        <?php include('inc/footer.php'); ?>
      </main>
    </div>
    <script src="../js/admin-sidebar.js"></script>
    <script src="../js/preloader.js"></script>
  </body>
</html>
