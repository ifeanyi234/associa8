<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Attendance Management - Associa8</title>
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
    <link rel="stylesheet" href="../css/dashboard.css" />
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
            <h1 class="page-title">Attendance</h1>
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
              <h2 class="page-title-main">Attendance</h2>
              <p class="page-subtitle">Manage attendance seamlessly with real-time tracking, event check-ins and detailed reporting.</p>
            </div>
            <div style="display: flex; gap: 0.75rem;">
              <button class="btn-navy-outline">
                <i class="fa-solid fa-sliders"></i> Filter
              </button>
              <button class="btn-navy-outline">
                <i class="fa-solid fa-arrow-up-from-bracket"></i> Export
              </button>
              <button class="btn-navy-filled">
                <i class="fa-solid fa-user-plus"></i> Add Attendance
              </button>
            </div>
          </div>

          <!-- Table Search Bar -->
          <div style="margin-bottom: 1.25rem; position: relative; max-width: 360px;">
            <input type="text" placeholder="Search by name or zone" style="width: 100%; padding: 0.65rem 2.5rem 0.65rem 1rem; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.875rem; background-color: #f8fafc; outline: none;" />
            <i class="fa-solid fa-magnifying-glass" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 0.875rem;"></i>
          </div>

          <!-- Attendance Data Table Card -->
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
                  <!-- Row 1 -->
                  <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 1rem; color: #64748b;">AS-001</td>
                    <td style="padding: 1rem; font-weight: 600; color: #0f172a;">Charles Monday</td>
                    <td style="padding: 1rem;"><i class="fa-solid fa-shield-halved" style="color: #0f172a; margin-right: 6px;"></i> Member</td>
                    <td style="padding: 1rem;"><i class="fa-solid fa-location-dot" style="color: #0f172a; margin-right: 6px;"></i> Lagos</td>
                    <td style="padding: 1rem;">
                      <span style="background-color: #e0e7ff; color: #4338ca; padding: 0.35rem 0.75rem; border-radius: 6px; font-weight: 500; font-size: 0.75rem; display: inline-block;">Present</span>
                    </td>
                    <td style="padding: 1rem; color: #475569;">08:00AM</td>
                    <td style="padding: 1rem; color: #475569;">28-06-2026</td>
                    <td style="padding: 1rem; color: #475569;">AGM-2026</td>
                    <td style="padding: 1rem; text-align: center; color: #94a3b8; cursor: pointer;"><i class="fa-solid fa-ellipsis"></i></td>
                  </tr>

                  <!-- Row 2 -->
                  <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 1rem; color: #64748b;">AS-002</td>
                    <td style="padding: 1rem; font-weight: 600; color: #0f172a;">Joseph Raymond</td>
                    <td style="padding: 1rem;"><i class="fa-solid fa-shield-halved" style="color: #0f172a; margin-right: 6px;"></i> Member</td>
                    <td style="padding: 1rem;"><i class="fa-solid fa-location-dot" style="color: #0f172a; margin-right: 6px;"></i> Lagos</td>
                    <td style="padding: 1rem;">
                      <span style="background-color: #e0e7ff; color: #4338ca; padding: 0.35rem 0.75rem; border-radius: 6px; font-weight: 500; font-size: 0.75rem; display: inline-block;">Present</span>
                    </td>
                    <td style="padding: 1rem; color: #475569;">08:00AM</td>
                    <td style="padding: 1rem; color: #475569;">28-06-2026</td>
                    <td style="padding: 1rem; color: #475569;">ZM-2026</td>
                    <td style="padding: 1rem; text-align: center; color: #94a3b8; cursor: pointer;"><i class="fa-solid fa-ellipsis"></i></td>
                  </tr>

                  <!-- Row 3 -->
                  <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 1rem; color: #64748b;">AS-003</td>
                    <td style="padding: 1rem; font-weight: 600; color: #0f172a;">Adamu Philips</td>
                    <td style="padding: 1rem;"><i class="fa-solid fa-shield-halved" style="color: #0f172a; margin-right: 6px;"></i> Sen fellow</td>
                    <td style="padding: 1rem;"><i class="fa-solid fa-location-dot" style="color: #0f172a; margin-right: 6px;"></i> Kano</td>
                    <td style="padding: 1rem;">
                      <span style="background-color: #fee2e2; color: #dc2626; padding: 0.35rem 0.75rem; border-radius: 6px; font-weight: 500; font-size: 0.75rem; display: inline-block;">Absent</span>
                    </td>
                    <td style="padding: 1rem; color: #475569;">10:00AM</td>
                    <td style="padding: 1rem; color: #475569;">29-06-2026</td>
                    <td style="padding: 1rem; color: #475569;">AGM-2026</td>
                    <td style="padding: 1rem; text-align: center; color: #94a3b8; cursor: pointer;"><i class="fa-solid fa-ellipsis"></i></td>
                  </tr>

                  <!-- Row 4 -->
                  <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 1rem; color: #64748b;">AS-004</td>
                    <td style="padding: 1rem; font-weight: 600; color: #0f172a;">Ken Mordi</td>
                    <td style="padding: 1rem;"><i class="fa-solid fa-shield-halved" style="color: #0f172a; margin-right: 6px;"></i> Member</td>
                    <td style="padding: 1rem;"><i class="fa-solid fa-location-dot" style="color: #0f172a; margin-right: 6px;"></i> Lagos</td>
                    <td style="padding: 1rem;">
                      <span style="background-color: #e0e7ff; color: #4338ca; padding: 0.35rem 0.75rem; border-radius: 6px; font-weight: 500; font-size: 0.75rem; display: inline-block;">Present</span>
                    </td>
                    <td style="padding: 1rem; color: #475569;">08:00AM</td>
                    <td style="padding: 1rem; color: #475569;">28-06-2026</td>
                    <td style="padding: 1rem; color: #475569;">AGM-2026</td>
                    <td style="padding: 1rem; text-align: center; color: #94a3b8; cursor: pointer;"><i class="fa-solid fa-ellipsis"></i></td>
                  </tr>

                  <!-- Row 5 -->
                  <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 1rem; color: #64748b;">AS-005</td>
                    <td style="padding: 1rem; font-weight: 600; color: #0f172a;">Fatai John</td>
                    <td style="padding: 1rem;"><i class="fa-solid fa-shield-halved" style="color: #0f172a; margin-right: 6px;"></i> Member</td>
                    <td style="padding: 1rem;"><i class="fa-solid fa-location-dot" style="color: #0f172a; margin-right: 6px;"></i> Lagos</td>
                    <td style="padding: 1rem;">
                      <span style="background-color: #e0e7ff; color: #4338ca; padding: 0.35rem 0.75rem; border-radius: 6px; font-weight: 500; font-size: 0.75rem; display: inline-block;">Present</span>
                    </td>
                    <td style="padding: 1rem; color: #475569;">08:00AM</td>
                    <td style="padding: 1rem; color: #475569;">28-06-2026</td>
                    <td style="padding: 1rem; color: #475569;">AGM-2026</td>
                    <td style="padding: 1rem; text-align: center; color: #94a3b8; cursor: pointer;"><i class="fa-solid fa-ellipsis"></i></td>
                  </tr>

                  <!-- Row 6 -->
                  <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 1rem; color: #64748b;">AS-006</td>
                    <td style="padding: 1rem; font-weight: 600; color: #0f172a;">Joseph Odigi</td>
                    <td style="padding: 1rem;"><i class="fa-solid fa-shield-halved" style="color: #0f172a; margin-right: 6px;"></i> Member</td>
                    <td style="padding: 1rem;"><i class="fa-solid fa-location-dot" style="color: #0f172a; margin-right: 6px;"></i> Lagos</td>
                    <td style="padding: 1rem;">
                      <span style="background-color: #e0e7ff; color: #4338ca; padding: 0.35rem 0.75rem; border-radius: 6px; font-weight: 500; font-size: 0.75rem; display: inline-block;">Present</span>
                    </td>
                    <td style="padding: 1rem; color: #475569;">08:00AM</td>
                    <td style="padding: 1rem; color: #475569;">28-06-2026</td>
                    <td style="padding: 1rem; color: #475569;">AGM-2026</td>
                    <td style="padding: 1rem; text-align: center; color: #94a3b8; cursor: pointer;"><i class="fa-solid fa-ellipsis"></i></td>
                  </tr>

                  <!-- Row 7 -->
                  <tr>
                    <td style="padding: 1rem; color: #64748b;">AS-007</td>
                    <td style="padding: 1rem; font-weight: 600; color: #0f172a;">Joy Peters</td>
                    <td style="padding: 1rem;"><i class="fa-solid fa-shield-halved" style="color: #0f172a; margin-right: 6px;"></i> Member</td>
                    <td style="padding: 1rem;"><i class="fa-solid fa-location-dot" style="color: #0f172a; margin-right: 6px;"></i> Port Ha..</td>
                    <td style="padding: 1rem;">
                      <span style="background-color: #fee2e2; color: #dc2626; padding: 0.35rem 0.75rem; border-radius: 6px; font-weight: 500; font-size: 0.75rem; display: inline-block;">Absent</span>
                    </td>
                    <td style="padding: 1rem; color: #475569;">08:00AM</td>
                    <td style="padding: 1rem; color: #475569;">24-06-2026</td>
                    <td style="padding: 1rem; color: #475569;">ZM-2026</td>
                    <td style="padding: 1rem; text-align: center; color: #94a3b8; cursor: pointer;"><i class="fa-solid fa-ellipsis"></i></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <?php include('inc/footer.php') ?>
      </main>
    </div>

    <!-- Script Block -->
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
    </script>
    <script src="../js/preloader.js"></script>
  </body>
</html>