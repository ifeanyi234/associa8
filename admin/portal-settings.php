<?php
require_once "inc/auth.php";
require_once "../inc/db.php";
$portalSettings = ['admission' => ['start_at' => '', 'end_at' => ''], 'cbt' => ['start_at' => '', 'end_at' => '']];
$settingsResult = mysqli_query($conn, "SELECT portal_key, start_at, end_at FROM portal_settings WHERE portal_key IN ('admission', 'cbt')");
if ($settingsResult) { while ($setting = mysqli_fetch_assoc($settingsResult)) { $portalSettings[$setting['portal_key']] = $setting; } }
$portalDateValue = function ($value) {
  return $value ? str_replace(' ', 'T', substr($value, 0, 16)) : '';
};
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Portal Settings - Associa8</title>

    <!-- Google Fonts -->
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

    <!-- Favicon -->
    <link
      rel="shortcut icon"
      href="../images/fav-logo.png"
      type="image/x-icon"
    />

    <!-- Admin Dashboard CSS -->
    <link rel="stylesheet" href="../css/preloader.css" />
    <link rel="stylesheet" href="../css/dashboard.css" />
  </head>
  <body class="admin-body">
    <!-- Preloader -->
    <?php include('inc/preloader.php') ?>

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
            <h1 class="page-title">Portal Settings</h1>
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

        <!-- Dashboard Body Content -->
        <div class="dashboard-content">
          <!-- Page Main Action Header -->
          <div class="page-action-header mb-4">
            <div>
              <h2 class="page-title-main">Portal Settings</h2>
              <p class="page-subtitle">Reset Every Admission Date & Time</p>
            </div>
          </div>

          <!-- Settings Data Table Card -->
          <form action="proc-portal-settings.php" method="POST" class="table-responsive-card">
            <table class="admin-table" data-dashboard-tools="false">
              <thead>
                <tr>
                  <th>Portal</th>
                  <th>Status</th>
                  <th>Current Deadline</th>
                  <th>Start Date / Time</th>
                  <th>Set New Deadline</th>
                  <th style="text-align: right;">Action</th>
                </tr>
              </thead>
              <tbody>
                <!-- Row 1: Admission Portal -->
                <tr>
                  <td>
                    <span class="member-name" style="font-weight: 600;">Admission Portal</span>
                  </td>
                  <td>
                    <span class="badge-pill status-inactive">Closed</span>
                  </td>
                  <td>
                    <div style="display: flex; flex-direction: column;">
                      <span style="font-weight: 500;"><?php echo $portalSettings['admission']['end_at'] ? htmlspecialchars($portalSettings['admission']['end_at']) : 'Not set'; ?></span>
                    </div>
                  </td>
                  <td>
                    <div class="toolbar-search" style="max-width: 210px; background: #f8fafc; border: 1px solid #e2e8f0; padding: 4px 8px;">
                      <i class="fa-solid fa-calendar-days search-icon"></i>
                      <input type="datetime-local" style="font-size: 0.8rem; border: none; background: transparent; color: #334155; width: 100%; outline: none;" name="admission_start" value="<?php echo htmlspecialchars($portalDateValue($portalSettings['admission']['start_at'])); ?>" />
                    </div>
                  </td>
                  <td>
                    <div class="toolbar-search" style="max-width: 210px; background: #f8fafc; border: 1px solid #e2e8f0; padding: 4px 8px;">
                      <i class="fa-solid fa-clock search-icon"></i>
                      <input type="datetime-local" style="font-size: 0.8rem; border: none; background: transparent; color: #334155; width: 100%; outline: none;" name="admission_end" value="<?php echo htmlspecialchars($portalDateValue($portalSettings['admission']['end_at'])); ?>" />
                    </div>
                  </td>
                  <td style="text-align: right;">
                    <button class="btn-outline-primary" type="submit" name="portal_key" value="admission">
                      <span>Update Admission</span>
                    </button>
                  </td>
                </tr>

                <!-- Row 2: CBT Portal -->
                <tr>
                  <td>
                    <span class="member-name" style="font-weight: 600;">CBT Portal</span>
                  </td>
                  <td>
                    <span class="badge-pill status-inactive">Closed</span>
                  </td>
                  <td>
                    <div style="display: flex; flex-direction: column;">
                      <span style="font-weight: 500;"><?php echo $portalSettings['cbt']['end_at'] ? htmlspecialchars($portalSettings['cbt']['end_at']) : 'Not set'; ?></span>
                    </div>
                  </td>
                  <td>
                    <div class="toolbar-search" style="max-width: 210px; background: #f8fafc; border: 1px solid #e2e8f0; padding: 4px 8px;">
                      <i class="fa-solid fa-calendar-days search-icon"></i>
                      <input type="datetime-local" style="font-size: 0.8rem; border: none; background: transparent; color: #334155; width: 100%; outline: none;" name="cbt_start" value="<?php echo htmlspecialchars($portalDateValue($portalSettings['cbt']['start_at'])); ?>" />
                    </div>
                  </td>
                  <td>
                    <div class="toolbar-search" style="max-width: 210px; background: #f8fafc; border: 1px solid #e2e8f0; padding: 4px 8px;">
                      <i class="fa-solid fa-clock search-icon"></i>
                      <input type="datetime-local" style="font-size: 0.8rem; border: none; background: transparent; color: #334155; width: 100%; outline: none;" name="cbt_end" value="<?php echo htmlspecialchars($portalDateValue($portalSettings['cbt']['end_at'])); ?>" />
                    </div>
                  </td>
                  <td style="text-align: right;">
                    <button class="btn-outline-primary" type="submit" name="portal_key" value="cbt">
                      <span>Update CBT</span>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </form>
        </div>

        <!-- Footer -->
        <?php include('inc/footer.php')?>
      </main>
    </div>

    <!-- Interactive Scripts -->
    <script>
      window.addEventListener("DOMContentLoaded", () => {
        const params = new URLSearchParams(window.location.search);
        if (!params.get("status") || !window.AppModal) return;
        const success = params.get("status") === "success";
        window.AppModal.open({
          type: success ? "success" : "error",
          heading: success ? "Portal settings updated" : "Portal settings not saved",
          body: params.get("msg") || "Please check the dates and try again.",
          detail: success ? "The portal dates are now stored in the database." : "The existing settings were left unchanged.",
        });
        window.history.replaceState({}, document.title, window.location.pathname);
      });
    </script>
    <script>
      // 1. Sidebar Dropdown Accordion Toggle Logic
      const dropdownItems = document.querySelectorAll(".sidebar-item.dropdown");

      dropdownItems.forEach((item) => {
        const link = item.querySelector(".sidebar-link");
        const submenu = item.querySelector(".sidebar-submenu");

        link.addEventListener("click", (e) => {
          e.preventDefault();
          const isOpen = item.classList.contains("open");

          dropdownItems.forEach((otherItem) => {
            if (otherItem !== item) {
              otherItem.classList.remove("open");
              const otherSub = otherItem.querySelector(".sidebar-submenu");
              if (otherSub) otherSub.style.maxHeight = null;
            }
          });

          if (!isOpen) {
            item.classList.add("open");
            submenu.style.maxHeight = submenu.scrollHeight + "px";
          } else {
            item.classList.remove("open");
            submenu.style.maxHeight = null;
          }
        });
      });

      // 2. Mobile Sidebar Toggle
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
    </script>
    <script src="../js/preloader.js"></script>
  </body>
</html>