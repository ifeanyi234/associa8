<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Member Directory - Associa8</title>

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
      <!-- ==========================================
         SIDEBAR NAVIGATION
         ========================================== -->
      <?php include('inc/sidebar.php') ?>

      <!-- ==========================================
         MAIN CONTENT AREA
         ========================================== -->
      <main class="admin-main">
        <!-- Top Navigation Header -->
        <header class="admin-header">
          <div class="header-left">
            <button class="header-toggle-btn" id="sidebarToggle">
              <i class="fa-solid fa-bars"></i>
            </button>
            <h1 class="page-title">Members Directory</h1>
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
          <div class="page-action-header">
            <div>
              <h2 class="page-title-main">Member Directory</h2>
              <p class="page-subtitle">8 total members across all zones</p>
            </div>
            <button class="btn-outline-primary">
              <i class="fa-solid fa-user-plus"></i>
              <span>Add members</span>
            </button>
          </div>

          <!-- Search & Status Filter Toolbar -->
          <div class="directory-toolbar">
            <div class="toolbar-search">
              <i class="fa-solid fa-magnifying-glass search-icon"></i>
              <input
                type="text"
                placeholder="Search by name or member code..."
              />
            </div>

            <div class="filter-pill-group">
              <button class="filter-pill active">All</button>
              <button class="filter-pill">Active</button>
              <button class="filter-pill">Suspended</button>
              <button class="filter-pill">Inactive</button>
            </div>
          </div>

          <!-- Directory Data Table Card -->
          <div class="table-responsive-card">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Member</th>
                  <th>Code</th>
                  <th>Title</th>
                  <th>Zone</th>
                  <th>Status</th>
                  <th>Dues</th>
                  <th>Joined</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <!-- Row 1 -->
                <tr>
                  <td>
                    <div class="member-cell">
                      <div class="member-avatar">CO</div>
                      <div class="member-info">
                        <span class="member-name">Chukwuemeka Obi</span>
                        <span class="member-email">c.obi@gmail.com</span>
                      </div>
                    </div>
                  </td>
                  <td>ASC-001</td>
                  <td>
                    <span class="cell-with-icon">
                      <i class="fa-solid fa-shield-halved"></i> Fellow
                    </span>
                  </td>
                  <td>
                    <span class="cell-with-icon">
                      <i class="fa-solid fa-location-dot"></i> Lagos
                    </span>
                  </td>
                  <td><span class="badge-pill status-active">Active</span></td>
                  <td><span class="badge-pill dues-current">Current</span></td>
                  <td>20-05-2026</td>
                  <td>
                    <button class="btn-action-trigger" aria-label="Options">
                      <i class="fa-solid fa-ellipsis"></i>
                    </button>
                  </td>
                </tr>

                <!-- Row 2 -->
                <tr>
                  <td>
                    <div class="member-cell">
                      <div class="member-avatar">AP</div>
                      <div class="member-info">
                        <span class="member-name">Adamu Philips</span>
                        <span class="member-email">ph_2gmail.com</span>
                      </div>
                    </div>
                  </td>
                  <td>ASC-002</td>
                  <td>
                    <span class="cell-with-icon">
                      <i class="fa-solid fa-shield-halved"></i> Associate
                    </span>
                  </td>
                  <td>
                    <span class="cell-with-icon">
                      <i class="fa-solid fa-location-dot"></i> Kano
                    </span>
                  </td>
                  <td>
                    <span class="badge-pill status-suspension">Suspension</span>
                  </td>
                  <td><span class="badge-pill dues-arrears">Arrears</span></td>
                  <td>20-05-2026</td>
                  <td>
                    <button class="btn-action-trigger" aria-label="Options">
                      <i class="fa-solid fa-ellipsis"></i>
                    </button>
                  </td>
                </tr>

                <!-- Row 3 -->
                <tr>
                  <td>
                    <div class="member-cell">
                      <div class="member-avatar">JK</div>
                      <div class="member-info">
                        <span class="member-name">James Kehinde</span>
                        <span class="member-email">j.kn2@gmail.com</span>
                      </div>
                    </div>
                  </td>
                  <td>ASC-003</td>
                  <td>
                    <span class="cell-with-icon">
                      <i class="fa-solid fa-shield-halved"></i> Sen Fellow
                    </span>
                  </td>
                  <td>
                    <span class="cell-with-icon">
                      <i class="fa-solid fa-location-dot"></i> Abuja
                    </span>
                  </td>
                  <td><span class="badge-pill status-active">Active</span></td>
                  <td><span class="badge-pill dues-current">Current</span></td>
                  <td>20-05-2026</td>
                  <td>
                    <button class="btn-action-trigger" aria-label="Options">
                      <i class="fa-solid fa-ellipsis"></i>
                    </button>
                  </td>
                </tr>

                <!-- Row 4 -->
                <tr>
                  <td>
                    <div class="member-cell">
                      <div class="member-avatar">CO</div>
                      <div class="member-info">
                        <span class="member-name">Chukwuemeka Obi</span>
                        <span class="member-email">c.obi@gmail.com</span>
                      </div>
                    </div>
                  </td>
                  <td>ASC-004</td>
                  <td>
                    <span class="cell-with-icon">
                      <i class="fa-solid fa-shield-halved"></i> Fellow
                    </span>
                  </td>
                  <td>
                    <span class="cell-with-icon">
                      <i class="fa-solid fa-location-dot"></i> Lagos
                    </span>
                  </td>
                  <td><span class="badge-pill status-active">Active</span></td>
                  <td><span class="badge-pill dues-current">Current</span></td>
                  <td>20-05-2026</td>
                  <td>
                    <button class="btn-action-trigger" aria-label="Options">
                      <i class="fa-solid fa-ellipsis"></i>
                    </button>
                  </td>
                </tr>

                <!-- Row 5 -->
                <tr>
                  <td>
                    <div class="member-cell">
                      <div class="member-avatar">CO</div>
                      <div class="member-info">
                        <span class="member-name">Chukwuemeka Obi</span>
                        <span class="member-email">c.obi@gmail.com</span>
                      </div>
                    </div>
                  </td>
                  <td>ASC-005</td>
                  <td>
                    <span class="cell-with-icon">
                      <i class="fa-solid fa-shield-halved"></i> Fellow
                    </span>
                  </td>
                  <td>
                    <span class="cell-with-icon">
                      <i class="fa-solid fa-location-dot"></i> Lagos
                    </span>
                  </td>
                  <td><span class="badge-pill status-active">Active</span></td>
                  <td><span class="badge-pill dues-current">Current</span></td>
                  <td>20-05-2026</td>
                  <td>
                    <button class="btn-action-trigger" aria-label="Options">
                      <i class="fa-solid fa-ellipsis"></i>
                    </button>
                  </td>
                </tr>

                <!-- Row 6 -->
                <tr>
                  <td>
                    <div class="member-cell">
                      <div class="member-avatar">CO</div>
                      <div class="member-info">
                        <span class="member-name">Chukwuemeka Obi</span>
                        <span class="member-email">c.obi@gmail.com</span>
                      </div>
                    </div>
                  </td>
                  <td>ASC-006</td>
                  <td>
                    <span class="cell-with-icon">
                      <i class="fa-solid fa-shield-halved"></i> Fellow
                    </span>
                  </td>
                  <td>
                    <span class="cell-with-icon">
                      <i class="fa-solid fa-location-dot"></i> Lagos
                    </span>
                  </td>
                  <td><span class="badge-pill status-active">Active</span></td>
                  <td><span class="badge-pill dues-current">Current</span></td>
                  <td>20-05-2026</td>
                  <td>
                    <button class="btn-action-trigger" aria-label="Options">
                      <i class="fa-solid fa-ellipsis"></i>
                    </button>
                  </td>
                </tr>

                <!-- Row 7 -->
                <tr>
                  <td>
                    <div class="member-cell">
                      <div class="member-avatar">CO</div>
                      <div class="member-info">
                        <span class="member-name">Chukwuemeka Obi</span>
                        <span class="member-email">c.obi@gmail.com</span>
                      </div>
                    </div>
                  </td>
                  <td>ASC-007</td>
                  <td>
                    <span class="cell-with-icon">
                      <i class="fa-solid fa-shield-halved"></i> Fellow
                    </span>
                  </td>
                  <td>
                    <span class="cell-with-icon">
                      <i class="fa-solid fa-location-dot"></i> Lagos
                    </span>
                  </td>
                  <td><span class="badge-pill status-active">Active</span></td>
                  <td><span class="badge-pill dues-current">Current</span></td>
                  <td>20-05-2026</td>
                  <td>
                    <button class="btn-action-trigger" aria-label="Options">
                      <i class="fa-solid fa-ellipsis"></i>
                    </button>
                  </td>
                </tr>

                <!-- Row 8 -->
                <tr>
                  <td>
                    <div class="member-cell">
                      <div class="member-avatar">JP</div>
                      <div class="member-info">
                        <span class="member-name">Joy Peters</span>
                        <span class="member-email">pj1_2gmail.com</span>
                      </div>
                    </div>
                  </td>
                  <td>ASC-008</td>
                  <td>
                    <span class="cell-with-icon">
                      <i class="fa-solid fa-shield-halved"></i> Member
                    </span>
                  </td>
                  <td>
                    <span class="cell-with-icon">
                      <i class="fa-solid fa-location-dot"></i> Port-Har..
                    </span>
                  </td>
                  <td>
                    <span class="badge-pill status-inactive">Inactive</span>
                  </td>
                  <td><span class="badge-pill dues-current">Current</span></td>
                  <td>20-05-2026</td>
                  <td>
                    <button class="btn-action-trigger" aria-label="Options">
                      <i class="fa-solid fa-ellipsis"></i>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>

            <!-- Table Pagination Footer -->
            <div class="table-pagination-footer">
              <span>Showing 8 of 8</span>
              <div class="pagination-controls">
                <button class="page-btn">Prev</button>
                <button class="page-btn active">1</button>
                <button class="page-btn">2</button>
                <button class="page-btn">Next</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <?php include('inc/footer.php')?>
      </main>
    </div>

    <!-- Interactive Scripts -->
    <script>
      // 1. Sidebar Dropdown Dynamic Accordion Logic
      const dropdownItems = document.querySelectorAll(".sidebar-item.dropdown");

      dropdownItems.forEach((item) => {
        const link = item.querySelector(".sidebar-link");
        const submenu = item.querySelector(".sidebar-submenu");

        link.addEventListener("click", (e) => {
          e.preventDefault();
          const isOpen = item.classList.contains("open");

          // Close other open dropdowns
          dropdownItems.forEach((otherItem) => {
            if (otherItem !== item) {
              otherItem.classList.remove("open");
              const otherSub = otherItem.querySelector(".sidebar-submenu");
              if (otherSub) otherSub.style.maxHeight = null;
            }
          });

          // Toggle target dropdown
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

      // 3. Filter Pill Toggle State
      const filterPills = document.querySelectorAll(".filter-pill");
      filterPills.forEach((pill) => {
        pill.addEventListener("click", () => {
          filterPills.forEach((p) => p.classList.remove("active"));
          pill.classList.add("active");
        });
      });
    </script>
    <script src="../js/preloader.js"></script>
  </body>
</html>
