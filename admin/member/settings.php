<?php
require_once 'inc/auth.php';
require_once '../../inc/db.php';

$memberId = (int) $_SESSION['member_id'];
$memberResult = mysqli_query($conn, "SELECT * FROM members WHERE id = $memberId LIMIT 1");
$member = $memberResult && mysqli_num_rows($memberResult) > 0 ? mysqli_fetch_assoc($memberResult) : null;
$memberName = $member ? trim(($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? '')) : 'Member';
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Settings - Associa8</title>
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
            <h1 class="page-title">Settings</h1>
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
              <div class="avatar-badge"><?php echo htmlspecialchars(substr($memberName ?: 'M', 0, 2)); ?></div>
              <span class="badge-pill status-active" style="margin-left: -0.5rem;"><?php echo htmlspecialchars(ucfirst($member['status'] ?? 'active')); ?></span>
            </div>
          </div>
        </header>

        <div class="dashboard-content">
          <div class="section-label">Preference</div>

          <section class="settings-grid">
            <!-- Account -->
            <div class="settings-card">
              <div class="settings-card-header">
                <i class="fa-regular fa-user"></i> Account
              </div>
              <div class="settings-card-body">
                <div class="settings-row">
                  <div>
                    <div class="settings-row-label">Full Name</div>
                    <div class="settings-row-value"><?php echo htmlspecialchars($memberName); ?></div>
                  </div>
                  <button class="btn-settings-action">Edit</button>
                </div>
                <div class="settings-row">
                  <div>
                    <div class="settings-row-label">Email Address</div>
                    <div class="settings-row-value"><?php echo htmlspecialchars($member['email'] ?? 'Not set'); ?></div>
                  </div>
                  <button class="btn-settings-action">Edit</button>
                </div>
                <div class="settings-row">
                  <div>
                    <div class="settings-row-label">Phone Number</div>
                    <div class="settings-row-value"><?php echo htmlspecialchars($member['phone'] ?? 'Not set'); ?></div>
                  </div>
                  <button class="btn-settings-action">Edit</button>
                </div>
                <div class="settings-row">
                  <div>
                    <div class="settings-row-label">Member ID</div>
                    <div class="settings-row-value"><?php echo htmlspecialchars($member['member_code'] ?? 'N/A'); ?></div>
                  </div>
                  <button class="btn-settings-action">Edit</button>
                </div>
              </div>
            </div>

            <!-- Security -->
            <div class="settings-card">
              <div class="settings-card-header">
                <i class="fa-solid fa-lock"></i> Security
              </div>
              <div class="settings-card-body">
                <div class="settings-row">
                  <div>
                    <div class="settings-row-label">Password</div>
                    <div class="settings-row-value">Last changed three months ago</div>
                  </div>
                  <button class="btn-settings-action">Change</button>
                </div>
                <div class="settings-row">
                  <div>
                    <div class="settings-row-label">Two-Factor Authentication</div>
                    <div class="settings-row-value"><?php echo !empty($member['two_factor_enabled']) ? 'Enabled' : 'Not enabled'; ?></div>
                  </div>
                  <label class="form-switch">
                    <input type="checkbox" class="form-switch-input" <?php echo !empty($member['two_factor_enabled']) ? 'checked' : ''; ?> />
                  </label>
                </div>
                <div class="settings-row">
                  <div>
                    <div class="settings-row-label">Active Session</div>
                    <div class="settings-row-value">1 device logged in</div>
                  </div>
                  <button class="btn-settings-action danger">Revoke All</button>
                </div>
              </div>
            </div>

            <!-- Notification Preference -->
            <div class="settings-card">
              <div class="settings-card-header">
                <i class="fa-solid fa-bell"></i> Notification Preference
              </div>
              <div class="settings-card-body">
                <div class="settings-row">
                  <div>
                    <div class="settings-row-label">Emails - Due Reminder</div>
                    <div class="settings-row-value" style="font-weight: 400; color: var(--text-secondary); font-size: 0.85rem;">Get notified when dues is approching</div>
                  </div>
                  <label class="form-switch">
                    <input type="checkbox" class="form-switch-input" checked />
                  </label>
                </div>
                <div class="settings-row">
                  <div>
                    <div class="settings-row-label">Emails- Event Reminder</div>
                    <div class="settings-row-value" style="font-weight: 400; color: var(--text-secondary); font-size: 0.85rem;">Receive event annocement</div>
                  </div>
                  <label class="form-switch">
                    <input type="checkbox" class="form-switch-input" checked />
                  </label>
                </div>
                <div class="settings-row">
                  <div>
                    <div class="settings-row-label">Email - General Announcement</div>
                    <div class="settings-row-value" style="font-weight: 400; color: var(--text-secondary); font-size: 0.85rem;">Receive newsletter &amp; admin update</div>
                  </div>
                  <label class="form-switch">
                    <input type="checkbox" class="form-switch-input" checked />
                  </label>
                </div>
                <div class="settings-row">
                  <div>
                    <div class="settings-row-label">SMS Reminder</div>
                    <div class="settings-row-value" style="font-weight: 400; color: var(--text-secondary); font-size: 0.85rem;">Text message reminders for key updates</div>
                  </div>
                  <label class="form-switch">
                    <input type="checkbox" class="form-switch-input" checked />
                  </label>
                </div>
              </div>
            </div>

            <!-- Regional & Display -->
            <div class="settings-card">
              <div class="settings-card-header">
                <i class="fa-solid fa-globe"></i> Regional &amp; Display
              </div>
              <div class="settings-card-body">
                <a href="#" class="settings-row" style="text-decoration: none; cursor: pointer;">
                  <div>
                    <div class="settings-row-label">Language</div>
                    <div class="settings-row-value"><?php echo htmlspecialchars($member['language'] ?? 'English (Nigeria)'); ?></div>
                  </div>
                  <i class="fa-solid fa-chevron-right settings-row-chevron"></i>
                </a>
                <a href="#" class="settings-row" style="text-decoration: none; cursor: pointer;">
                  <div>
                    <div class="settings-row-label">Timezone</div>
                    <div class="settings-row-value"><?php echo htmlspecialchars($member['timezone'] ?? 'Africa/Lagos'); ?></div>
                  </div>
                  <i class="fa-solid fa-chevron-right settings-row-chevron"></i>
                </a>
                <a href="#" class="settings-row" style="text-decoration: none; cursor: pointer;">
                  <div>
                    <div class="settings-row-label">Date / Format</div>
                    <div class="settings-row-value"><?php echo htmlspecialchars($member['date_format'] ?? 'DD/MM/YYYY'); ?></div>
                  </div>
                  <i class="fa-solid fa-chevron-right settings-row-chevron"></i>
                </a>
                <a href="#" class="settings-row" style="text-decoration: none; cursor: pointer;">
                  <div>
                    <div class="settings-row-label">Currency Display</div>
                    <div class="settings-row-value"><?php echo htmlspecialchars($member['currency_display'] ?? 'Nigeria/Naira'); ?></div>
                  </div>
                  <i class="fa-solid fa-chevron-right settings-row-chevron"></i>
                </a>
              </div>
            </div>
          </section>

          <!-- Danger Zone -->
          <div class="danger-zone-card">
            <div>
              <div class="danger-zone-title">Danger Zone</div>
              <div class="danger-zone-desc">Deactivate Account</div>
              <div class="danger-zone-sub">Temporarily suspend your membership portal</div>
            </div>
            <button class="btn-settings-action danger">Deactivate</button>
          </div>
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