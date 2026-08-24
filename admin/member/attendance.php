<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Attendance - Associa8</title>
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
              <div class="avatar-badge">JR</div>
              <span class="badge-pill status-active" style="margin-left: -0.5rem;">Active</span>
            </div>
          </div>
        </header>

        <div class="dashboard-content">
          <!-- Stat Cards -->
          <section class="summary-cards-grid" style="grid-template-columns: repeat(4, 1fr);">
            <div class="summary-card">
              <div class="summary-card-header">
                <span class="summary-card-title">Total Events</span>
                <div class="summary-icon-circle">
                  <i class="fa-solid fa-circle-info"></i>
                </div>
              </div>
              <div class="summary-value">8</div>
            </div>

            <div class="summary-card">
              <div class="summary-card-header">
                <span class="summary-card-title">Attended</span>
                <div class="summary-icon-circle">
                  <i class="fa-solid fa-check"></i>
                </div>
              </div>
              <div class="summary-value">6</div>
            </div>

            <div class="summary-card">
              <div class="summary-card-header">
                <span class="summary-card-title">Missed</span>
                <div class="summary-icon-circle">
                  <i class="fa-solid fa-xmark"></i>
                </div>
              </div>
              <div class="summary-value">2</div>
            </div>

            <div class="summary-card">
              <div class="summary-card-header">
                <span class="summary-card-title">Attendance Rate</span>
                <div class="summary-icon-circle">
                  <i class="fa-solid fa-chart-simple"></i>
                </div>
              </div>
              <div class="summary-value">75%</div>
            </div>
          </section>

          <!-- Overall Progress -->
          <section class="dashboard-card">
            <div class="progress-labeled-row">
              <span class="progress-labeled-title">Overall Attendance Rate</span>
              <span class="progress-labeled-percent">75%</span>
            </div>
            <div class="progress-bar-wrapper" style="height: 8px; margin-top: 0;">
              <div class="progress-bar-fill" style="width: 75%; background-color: var(--banner-bg);"></div>
            </div>
            <div class="progress-note">Excellent attendance - you are eligible to member benefits.</div>
          </section>

          <!-- Attendance History -->
          <section class="table-responsive-card">
            <div class="list-card-dark-header">
              <div class="list-card-dark-header-left">
                <span class="list-card-dark-header-icon">
                  <i class="fa-solid fa-arrow-right"></i>
                </span>
                Attendance History
              </div>
            </div>

            <table class="admin-table">
              <thead>
                <tr>
                  <th>Events</th>
                  <th>Date</th>
                  <th>Type</th>
                  <th>Status</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td style="font-weight: 600; color: var(--text-primary);">Q2 General Meeting 2026</td>
                  <td>June 15, 2026</td>
                  <td><span class="event-type-pill">Meetings</span></td>
                  <td><span class="status-text-attended"><i class="fa-solid fa-check"></i> Attended</span></td>
                  <td style="text-align: right;">
                    <button class="btn-action-trigger" aria-label="Options">
                      <i class="fa-solid fa-ellipsis"></i>
                    </button>
                  </td>
                </tr>
                <tr>
                  <td style="font-weight: 600; color: var(--text-primary);">Leadership Workshop</td>
                  <td>May 12,2026</td>
                  <td><span class="event-type-pill">Workshop</span></td>
                  <td><span class="status-text-attended"><i class="fa-solid fa-check"></i> Attended</span></td>
                  <td style="text-align: right;">
                    <button class="btn-action-trigger" aria-label="Options">
                      <i class="fa-solid fa-ellipsis"></i>
                    </button>
                  </td>
                </tr>
                <tr>
                  <td style="font-weight: 600; color: var(--text-primary);">Q1 General Meeting</td>
                  <td>Jan 15, 2026</td>
                  <td><span class="event-type-pill">Meetings</span></td>
                  <td><span class="status-text-missed"><i class="fa-solid fa-xmark"></i> Missed</span></td>
                  <td style="text-align: right;">
                    <button class="btn-action-trigger" aria-label="Options">
                      <i class="fa-solid fa-ellipsis"></i>
                    </button>
                  </td>
                </tr>
                <tr>
                  <td style="font-weight: 600; color: var(--text-primary);">Annual General Meeting 2026</td>
                  <td>Mar 20, 2026</td>
                  <td><span class="event-type-pill">AGM</span></td>
                  <td><span class="status-text-attended"><i class="fa-solid fa-check"></i> Attended</span></td>
                  <td style="text-align: right;">
                    <button class="btn-action-trigger" aria-label="Options">
                      <i class="fa-solid fa-ellipsis"></i>
                    </button>
                  </td>
                </tr>
                <tr>
                  <td style="font-weight: 600; color: var(--text-primary);">Year End Gala 2025</td>
                  <td>Dec 17, 2025</td>
                  <td><span class="event-type-pill">Social</span></td>
                  <td><span class="status-text-attended"><i class="fa-solid fa-check"></i> Attended</span></td>
                  <td style="text-align: right;">
                    <button class="btn-action-trigger" aria-label="Options">
                      <i class="fa-solid fa-ellipsis"></i>
                    </button>
                  </td>
                </tr>
                <tr>
                  <td style="font-weight: 600; color: var(--text-primary);">Q4 General Meeting 2025</td>
                  <td>Oct 15, 2025</td>
                  <td><span class="event-type-pill">Meetings</span></td>
                  <td><span class="status-text-attended"><i class="fa-solid fa-check"></i> Attended</span></td>
                  <td style="text-align: right;">
                    <button class="btn-action-trigger" aria-label="Options">
                      <i class="fa-solid fa-ellipsis"></i>
                    </button>
                  </td>
                </tr>
                <tr>
                  <td style="font-weight: 600; color: var(--text-primary);">Welfare Community Meetings</td>
                  <td>June 15, 2025</td>
                  <td><span class="event-type-pill">Community</span></td>
                  <td><span class="status-text-missed"><i class="fa-solid fa-xmark"></i> Missed</span></td>
                  <td style="text-align: right;">
                    <button class="btn-action-trigger" aria-label="Options">
                      <i class="fa-solid fa-ellipsis"></i>
                    </button>
                  </td>
                </tr>
                <tr>
                  <td style="font-weight: 600; color: var(--text-primary);">Annual General Meeting 2025</td>
                  <td>Jan 15, 2025</td>
                  <td><span class="event-type-pill">Meetings</span></td>
                  <td><span class="status-text-attended"><i class="fa-solid fa-check"></i> Attended</span></td>
                  <td style="text-align: right;">
                    <button class="btn-action-trigger" aria-label="Options">
                      <i class="fa-solid fa-ellipsis"></i>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
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