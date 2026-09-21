<?php
require_once 'inc/auth.php';
require_once '../../inc/db.php';

$memberId = (int) $_SESSION['member_id'];
$memberResult = mysqli_query($conn, "SELECT * FROM members WHERE id = $memberId LIMIT 1");
$member = $memberResult && mysqli_num_rows($memberResult) > 0 ? mysqli_fetch_assoc($memberResult) : null;
$memberName = $member ? trim(($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? '')) : 'Member';
$memberCode = $member['member_code'] ?? 'N/A';
$memberStatus = ucfirst($member['status'] ?? 'Active');

$initials = '';
foreach (explode(' ', trim($memberName)) as $part) {
  $part = trim($part);
  if ($part !== '') {
    $initials .= strtoupper(substr($part, 0, 1));
  }
  if (strlen($initials) >= 2) {
    break;
  }
}
$memberInitials = $initials ?: 'M';

$profileFields = ['first_name', 'last_name', 'email', 'phone', 'home_address', 'date_of_birth', 'occupation', 'state_of_origin', 'emergency_contact_name', 'profile_photo_path'];
$filledFields = 0;
foreach ($profileFields as $field) {
  if (!empty($member[$field] ?? null)) {
    $filledFields++;
  }
}
$profileCompletion = count($profileFields) > 0 ? (int) round(($filledFields / count($profileFields)) * 100) : 0;

$memberOrgId = isset($_SESSION['member_org_id']) ? (int) $_SESSION['member_org_id'] : 0;
$documentsCount = 0;
$documentsCountResult = mysqli_query($conn, "SELECT COUNT(*) AS total FROM documents WHERE org_id = $memberOrgId");
if ($documentsCountResult) {
  $documentsCountRow = mysqli_fetch_assoc($documentsCountResult);
  $documentsCount = (int) ($documentsCountRow['total'] ?? 0);
}

$outstandingDues = 0;
$duesResult = mysqli_query($conn, "SELECT COALESCE(SUM(amount), 0) AS total FROM finance_transactions WHERE member_id = $memberId AND status = 'pending'");
if ($duesResult) {
  $duesRow = mysqli_fetch_assoc($duesResult);
  $outstandingDues = (float) ($duesRow['total'] ?? 0);
}

$nextEventQuery = mysqli_query($conn, "SELECT MIN(event_date) AS next_date, COUNT(*) AS total FROM events WHERE event_date >= CURDATE()");
$nextEventDate = null;
$upcomingEventCount = 0;
if ($nextEventQuery) {
  $nextEventRow = mysqli_fetch_assoc($nextEventQuery);
  $upcomingEventCount = (int) ($nextEventRow['total'] ?? 0);
  $nextEventDate = $nextEventRow['next_date'] ?? null;
}

$notifications = [];
$notificationsResult = mysqli_query($conn, "SELECT id, title, message, is_read, created_at FROM notifications WHERE member_id = $memberId ORDER BY created_at DESC LIMIT 4");
if ($notificationsResult) {
  while ($notification = mysqli_fetch_assoc($notificationsResult)) {
    $notifications[] = $notification;
  }
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>My Dashboard - Associa8</title>
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
            <h1 class="page-title">My Dashboard</h1>
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
              <div class="avatar-badge"><?php echo htmlspecialchars($memberInitials); ?></div>
              <span class="badge-pill status-active" style="margin-left: -0.5rem;"><?php echo htmlspecialchars($memberStatus); ?></span>
            </div>
          </div>
        </header>

        <div class="dashboard-content">
          <!-- Welcome Banner -->
          <section class="welcome-banner">
            <h2 class="welcome-title">Good morning, <?php echo htmlspecialchars($memberName); ?> 👋</h2>
            <p class="welcome-subtitle">
              Here's what's happening in the organization today..
            </p>

            <div class="banner-actions">
              <a href="profile.php" style="text-decoration: none;" class="btn-banner-ghost">
                <i class="fa-solid fa-user"></i> Profile
              </a>
              <a href="attendance.php" style="text-decoration: none;" class="btn-banner-ghost">
                <i class="fa-solid fa-user-check"></i> Attendance
              </a>
              <a href="messages.php" style="text-decoration: none;" class="btn-banner-ghost">
                <i class="fa-regular fa-comment"></i> Messages
              </a>
            </div>
          </section>

          <!-- Summary Cards -->
          <section class="summary-cards-grid">
            <div class="summary-card">
              <div class="summary-card-header">
                <span class="summary-card-title">Profile Completion</span>
                <div class="summary-icon-circle">
                  <i class="fa-solid fa-user"></i>
                </div>
              </div>
              <div class="summary-value"><?php echo $profileCompletion; ?>%</div>
              <div class="summary-sublabel"><?php echo $filledFields; ?> of <?php echo count($profileFields); ?> profile fields filled</div>
              <div class="summary-progress-row">
                <div class="progress-bar-wrapper">
                  <div class="progress-bar-fill" style="width: <?php echo $profileCompletion; ?>%"></div>
                </div>
                <span class="summary-progress-percent"><?php echo $profileCompletion; ?>%</span>
              </div>
            </div>

            <div class="summary-card">
              <div class="summary-card-header">
                <span class="summary-card-title">Outstanding Dues</span>
                <div class="summary-icon-circle">
                  <i class="fa-solid fa-circle-info"></i>
                </div>
              </div>
              <div class="summary-value">&#8358;<?php echo number_format($outstandingDues, 0); ?></div>
              <div class="summary-sublabel"><?php echo $outstandingDues > 0 ? 'Payment still due' : 'No pending dues'; ?></div>
              <button class="btn-summary-action" onclick="window.location.href='payment.php'">Pay up</button>
            </div>

            <div class="summary-card">
              <div class="summary-card-header">
                <span class="summary-card-title">Upcoming Events</span>
                <div class="summary-icon-circle">
                  <i class="fa-regular fa-calendar"></i>
                </div>
              </div>
              <div class="summary-value" style="font-size: 1.1rem;"><?php echo $upcomingEventCount; ?> event(s)</div>
              <div class="summary-sublabel"><?php echo $nextEventDate ? date('D, d M Y', strtotime($nextEventDate)) : 'No upcoming events'; ?></div>
              <button class="btn-summary-action" onclick="window.location.href='events.php'">View details</button>
            </div>
          </section>

          <!-- Notifications & Recent Activities -->
          <section class="dashboard-lists-grid">
            <!-- Notifications -->
            <div class="list-card-dark">
              <div class="list-card-dark-header">
                <div class="list-card-dark-header-left">
                  <span class="list-card-dark-header-icon">
                    <i class="fa-solid fa-bell"></i>
                  </span>
                  Notification
                </div>
              </div>
              <div class="list-card-body">
                <?php if ($notifications): ?>
                  <?php foreach ($notifications as $notification): ?>
                    <div class="list-card-row">
                      <div class="list-card-row-left">
                        <div class="list-card-row-icon">
                          <i class="fa-solid fa-bell"></i>
                        </div>
                        <div>
                          <div class="list-card-row-title"><?php echo htmlspecialchars($notification['title']); ?></div>
                          <div class="list-card-row-desc"><?php echo htmlspecialchars($notification['message']); ?></div>
                        </div>
                      </div>
                      <span class="list-card-row-time"><?php echo htmlspecialchars(date('D, d M Y', strtotime($notification['created_at']))); ?></span>
                    </div>
                  <?php endforeach; ?>
                <?php else: ?>
                  <div class="list-card-row">
                    <div class="list-card-row-left">
                      <div class="list-card-row-icon">
                        <i class="fa-solid fa-bell"></i>
                      </div>
                      <div>
                        <div class="list-card-row-title">No notifications</div>
                        <div class="list-card-row-desc">You do not have any updates yet.</div>
                      </div>
                    </div>
                  </div>
                <?php endif; ?>
              </div>
              <a href="messages.php" class="list-card-footer-btn">View All Notifications</a>
            </div>

            <!-- Recent Activities -->
            <div class="list-card-dark">
              <div class="list-card-dark-header">
                <div class="list-card-dark-header-left">
                  <span class="list-card-dark-header-icon">
                    <i class="fa-solid fa-arrow-right"></i>
                  </span>
                  Recent Activities
                </div>
                <span class="list-card-dark-header-meta">Last 30 days</span>
              </div>
              <div class="list-card-body">
                <div class="list-card-row">
                  <div class="list-card-row-left">
                    <div class="list-card-row-icon">
                      <i class="fa-regular fa-calendar-check"></i>
                    </div>
                    <div>
                      <div class="list-card-row-title">RSVP Confirmed</div>
                      <div class="list-card-row-desc">Annual general meeting - 1 Aug</div>
                    </div>
                  </div>
                  <span class="list-card-row-time">Today, 9:14am</span>
                </div>

                <div class="list-card-row">
                  <div class="list-card-row-left">
                    <div class="list-card-row-icon">
                      <i class="fa-regular fa-file-lines"></i>
                    </div>
                    <div>
                      <div class="list-card-row-title">Documents Uploaded</div>
                      <div class="list-card-row-desc">Updated national ID on profile</div>
                    </div>
                  </div>
                  <span class="list-card-row-time">Yesterday, 3:12pm</span>
                </div>

                <div class="list-card-row">
                  <div class="list-card-row-left">
                    <div class="list-card-row-icon">
                      <i class="fa-regular fa-credit-card"></i>
                    </div>
                    <div>
                      <div class="list-card-row-title">Payment Received</div>
                      <div class="list-card-row-desc">&#8358;5,000 partial dues payment</div>
                    </div>
                  </div>
                  <span class="list-card-row-time">Jul 27, 11:00am</span>
                </div>

                <div class="list-card-row">
                  <div class="list-card-row-left">
                    <div class="list-card-row-icon">
                      <i class="fa-solid fa-user"></i>
                    </div>
                    <div>
                      <div class="list-card-row-title">Profile Updated</div>
                      <div class="list-card-row-desc">Updated phone number and address</div>
                    </div>
                  </div>
                  <span class="list-card-row-time">Jun 20, 10:17pm</span>
                </div>

                <div class="list-card-row">
                  <div class="list-card-row-left">
                    <div class="list-card-row-icon">
                      <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <div>
                      <div class="list-card-row-title">Membership Activated</div>
                      <div class="list-card-row-desc">2026 membership successfully activated</div>
                    </div>
                  </div>
                  <span class="list-card-row-time">Jun 20, 12:16pm</span>
                </div>
              </div>
              <a href="#" class="list-card-footer-btn">View Full History</a>
            </div>
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