<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Suspension & Reinstatement - Associa8</title>
    
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
    
    <!-- Fav Icon -->
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
    <?php include('inc/preloader.php'); ?>

    <div class="admin-layout">
      
      <!-- SIDEBAR NAVIGATION -->
      <?php include('inc/sidebar.php'); ?>

      <!-- MAIN CONTENT AREA -->
      <main class="admin-main">
        
        <!-- Top Navigation Header -->
        <header class="admin-header">
          <div class="header-left">
            <button class="header-toggle-btn" id="sidebarToggle">
              <i class="fa-solid fa-bars"></i>
            </button>
            <h1 class="page-title">Suspension & Reinstatement</h1>
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

          <!-- Page Header & Actions -->
          <div class="page-action-header mb-4">
            <div>
              <h2 class="page-title-main">Suspension & Reinstatement</h2>
              <p class="page-subtitle">Manage member disciplinary actions and appeals.</p>
            </div>
            <div class="d-flex gap-2">
              <button class="btn-action-danger-outline">
                <i class="fa-ban"></i>
                <span>Suspended</span>
              </button>
              <button class="btn-action-dark-outline">
                <i class="fa-solid fa-user-plus"></i>
                <span>Reinstate</span>
              </button>
            </div>
          </div>

          <!-- Top Summary Stat Cards Grid -->
          <section class="suspension-stats-grid">
            <div class="suspension-stat-card">
              <div class="suspension-stat-value text-danger">2</div>
              <div class="suspension-stat-label">Active Suspended</div>
            </div>
            <div class="suspension-stat-card">
              <div class="suspension-stat-value text-primary">1</div>
              <div class="suspension-stat-label">Under-Review</div>
            </div>
            <div class="suspension-stat-card">
              <div class="suspension-stat-value text-dark">2</div>
              <div class="suspension-stat-label">Reinstate</div>
            </div>
          </section>

          <!-- Suspension Records Table -->
          <section class="table-responsive-card">
            <table class="custom-admin-table">
              <thead>
                <tr>
                  <th>Member</th>
                  <th>Reason</th>
                  <th>Type</th>
                  <th>Date</th>
                  <th>Status</th>
                  <th style="width: 40px;"></th>
                </tr>
              </thead>
              <tbody>
                
                <!-- Row 1 -->
                <tr>
                  <td>
                    <div class="table-member-profile">
                      <div class="member-avatar">CO</div>
                      <div class="member-meta">
                        <span class="member-name">Chukwuemeka Obi</span>
                        <span class="member-id">ASC-001</span>
                      </div>
                    </div>
                  </td>
                  <td>Paid all outstanding dues</td>
                  <td><span class="badge-pill type-reinstatement">Reinstatement</span></td>
                  <td>20-05-2026</td>
                  <td><span class="badge-pill status-completed">Completed</span></td>
                  <td>
                    <button class="btn-row-action" aria-label="More Actions"><i class="fa-solid fa-ellipsis"></i></button>
                  </td>
                </tr>

                <!-- Row 2 -->
                <tr>
                  <td>
                    <div class="table-member-profile">
                      <div class="member-avatar">CO</div>
                      <div class="member-meta">
                        <span class="member-name">Chukwuemeka Obi</span>
                        <span class="member-id">ASC-001</span>
                      </div>
                    </div>
                  </td>
                  <td>Gross misconduct at meeting</td>
                  <td><span class="badge-pill type-suspension">Suspension</span></td>
                  <td>20-05-2026</td>
                  <td><span class="badge-pill status-active">Active</span></td>
                  <td>
                    <button class="btn-row-action" aria-label="More Actions"><i class="fa-solid fa-ellipsis"></i></button>
                  </td>
                </tr>

                <!-- Row 3 -->
                <tr>
                  <td>
                    <div class="table-member-profile">
                      <div class="member-avatar">CO</div>
                      <div class="member-meta">
                        <span class="member-name">Chukwuemeka Obi</span>
                        <span class="member-id">ASC-001</span>
                      </div>
                    </div>
                  </td>
                  <td>Paid all outstanding dues</td>
                  <td><span class="badge-pill type-reinstatement">Reinstatement</span></td>
                  <td>20-05-2026</td>
                  <td><span class="badge-pill status-completed">Completed</span></td>
                  <td>
                    <button class="btn-row-action" aria-label="More Actions"><i class="fa-solid fa-ellipsis"></i></button>
                  </td>
                </tr>

                <!-- Row 4 -->
                <tr>
                  <td>
                    <div class="table-member-profile">
                      <div class="member-avatar">CO</div>
                      <div class="member-meta">
                        <span class="member-name">Chukwuemeka Obi</span>
                        <span class="member-id">ASC-001</span>
                      </div>
                    </div>
                  </td>
                  <td>Non-payment of dues(2yrs)</td>
                  <td><span class="badge-pill type-suspension">Suspension</span></td>
                  <td>20-05-2026</td>
                  <td><span class="badge-pill status-active">Active</span></td>
                  <td>
                    <button class="btn-row-action" aria-label="More Actions"><i class="fa-solid fa-ellipsis"></i></button>
                  </td>
                </tr>

                <!-- Row 5 -->
                <tr>
                  <td>
                    <div class="table-member-profile">
                      <div class="member-avatar">CO</div>
                      <div class="member-meta">
                        <span class="member-name">Chukwuemeka Obi</span>
                        <span class="member-id">ASC-001</span>
                      </div>
                    </div>
                  </td>
                  <td>Violate code of conduct</td>
                  <td><span class="badge-pill type-suspension">Suspension</span></td>
                  <td>20-05-2026</td>
                  <td><span class="badge-pill status-under-review">Under review</span></td>
                  <td>
                    <button class="btn-row-action" aria-label="More Actions"><i class="fa-solid fa-ellipsis"></i></button>
                  </td>
                </tr>

              </tbody>
            </table>

            <!-- Table Pagination Footer -->
            <div class="table-footer">
              <span class="table-footer-info">Showing 8 of 8</span>
              <div class="pagination-wrapper">
                <button class="page-btn">Prev</button>
                <button class="page-btn active">1</button>
                <button class="page-btn">2</button>
                <button class="page-btn">Next</button>
              </div>
            </div>
          </section>

        </div>

        <!-- FOOTER -->
        <?php include('inc/footer.php'); ?>

      </main>
    </div>

    <!-- Interactive Scripts -->
    <script>
      // 1. Sidebar Dropdown Accordion Toggle Logic
      const dropdownItems = document.querySelectorAll(".sidebar-item.dropdown");

      dropdownItems.forEach((item) => {
        const link = item.querySelector(".sidebar-link");
        const submenu = item.querySelector(".sidebar-submenu");

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
      });

      // 2. Mobile Sidebar Toggle
      document.getElementById("sidebarToggle").addEventListener("click", () => {
        document.getElementById("adminSidebar").classList.toggle("open");
      });
    </script>
    <script src="../js/preloader.js"></script>
  </body>
</html>