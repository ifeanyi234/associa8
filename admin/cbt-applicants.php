<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>CBT Applicants - Associa8</title>

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
            <h1 class="page-title">CBT Management</h1>
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
              <h2 class="page-title-main">CBT Applicant</h2>
              <p class="page-subtitle">CBT Schedule & Onboarding</p>
            </div>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
              <button class="btn-outline-primary">
                <i class="fa-solid fa-user-plus"></i>
                <span>Add Applicants</span>
              </button>
              <button class="btn-outline-primary">
                <i class="fa-solid fa-file-export"></i>
                <span>Export Participant</span>
              </button>
              <button class="btn-outline-primary">
                <i class="fa-solid fa-upload"></i>
                <span>Bulk Upload</span>
              </button>
            </div>
          </div>

          <!-- Summary Stat Cards Grid -->
          <section class="stats-grid mb-4">
  <div class="suspension-stat-card">
    <div class="suspension-stat-value">18</div>
    <div class="suspension-stat-label">Application Review</div>
  </div>
  <div class="suspension-stat-card">
    <div class="suspension-stat-value">23</div>
    <div class="suspension-stat-label">CBT Review</div>
  </div>
  <div class="suspension-stat-card">
    <div class="suspension-stat-value">14</div>
    <div class="suspension-stat-label">Onboarding</div>
  </div>
  <div class="suspension-stat-card">
    <div class="suspension-stat-value text-primary">9</div>
    <div class="suspension-stat-label">Approved</div>
  </div>
</section>

          <!-- Data Table Card -->
          <div class="table-responsive-card">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>First Name</th>
                  <th>Last Name</th>
                  <th>Email</th>
                  <th>Phone</th>
                  <th>Exam Code</th>
                  <th>Expire Date</th>
                  <th style="text-align: right;">Action</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>01</td>
                  <td>Joseph</td>
                  <td>Raymond</td>
                  <td>j-ray@gmail.com</td>
                  <td>081234565789</td>
                  <td>102321</td>
                  <td>10-06-26</td>
                  <td style="text-align: right;">
                    <a href="#" style="color: #64748b; text-decoration: none; font-weight: 500;">View all</a>
                  </td>
                </tr>
                <tr>
                  <td>02</td>
                  <td>Joseph</td>
                  <td>Raymond</td>
                  <td>j-ray@gmail.com</td>
                  <td>081234565789</td>
                  <td>102321</td>
                  <td>10-06-26</td>
                  <td style="text-align: right;">
                    <a href="#" style="color: #64748b; text-decoration: none; font-weight: 500;">View all</a>
                  </td>
                </tr>
                <tr>
                  <td>03</td>
                  <td>Joseph</td>
                  <td>Raymond</td>
                  <td>j-ray@gmail.com</td>
                  <td>081234565789</td>
                  <td>102321</td>
                  <td>10-06-26</td>
                  <td style="text-align: right;">
                    <a href="#" style="color: #64748b; text-decoration: none; font-weight: 500;">View all</a>
                  </td>
                </tr>
                <tr>
                  <td>04</td>
                  <td>Joseph</td>
                  <td>Raymond</td>
                  <td>j-ray@gmail.com</td>
                  <td>081234565789</td>
                  <td>102321</td>
                  <td>10-06-26</td>
                  <td style="text-align: right;">
                    <a href="#" style="color: #64748b; text-decoration: none; font-weight: 500;">View all</a>
                  </td>
                </tr>
                <tr>
                  <td>05</td>
                  <td>Joseph</td>
                  <td>Raymond</td>
                  <td>j-ray@gmail.com</td>
                  <td>081234565789</td>
                  <td>102321</td>
                  <td>10-06-26</td>
                  <td style="text-align: right;">
                    <a href="#" style="color: #64748b; text-decoration: none; font-weight: 500;">View all</a>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Footer -->
        <?php include('inc/footer.php')?>
      </main>
    </div>

    <!-- Interactive Scripts -->
    <script>
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