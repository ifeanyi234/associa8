<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Zones & Sub-Zones - Associa8</title>
    
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
            <h1 class="page-title">Zones & Sub-Zones</h1>
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

          <!-- Page Header & Action -->
          <div class="page-action-header mb-4">
            <div>
              <h2 class="page-title-main">Zones & Sub-Zones</h2>
              <p class="page-subtitle">Manage geographic divisions and coordinators</p>
            </div>
            <button class="btn-outline-primary">
              <i class="fa-solid fa-plus"></i>
              <span>New Zone</span>
            </button>
          </div>

          <!-- Top Summary Stat Cards Grid -->
          <section class="zone-stats-grid">
            <div class="zone-stat-card">
              <div class="zone-stat-value">4</div>
              <div class="zone-stat-label">Total Zones</div>
            </div>
            <div class="zone-stat-card">
              <div class="zone-stat-value">8</div>
              <div class="zone-stat-label">Total Sub-Zones</div>
            </div>
            <div class="zone-stat-card">
              <div class="zone-stat-value">562</div>
              <div class="zone-stat-label">Total Members</div>
            </div>
            <div class="zone-stat-card">
              <div class="zone-stat-value">12</div>
              <div class="zone-stat-label">Active Coordinators</div>
            </div>
          </section>

          <!-- Zone Cards List -->
          <section class="zone-list">
            
            <!-- Lagos Zone -->
            <div class="zone-group">
              <div class="zone-card">
                <div class="zone-info-group">
                  <div class="zone-icon-box">
                    <i class="fa-solid fa-location-dot"></i>
                  </div>
                  <div class="zone-details">
                    <span class="zone-title">Lagos Zone</span>
                    <span class="zone-coordinator">Coordinator: Joseph Raymond</span>
                  </div>
                </div>

                <div class="zone-meta-group">
                  <div class="zone-stat-unit">
                    <span class="zone-stat-number">218</span>
                    <span class="zone-stat-text">members</span>
                  </div>
                  <div class="zone-stat-unit">
                    <span class="zone-stat-number">4</span>
                    <span class="zone-stat-text">sub-zones</span>
                  </div>
                  <div class="zone-actions">
                    <a href="#" class="btn-zone-action" aria-label="Edit Zone"><i class="fa-solid fa-pen"></i></a>
                    <button type="button" class="btn-zone-action btn-zone-toggle" aria-label="Toggle sub-zones" aria-expanded="false">
                      <i class="fa-solid fa-chevron-right"></i>
                    </button>
                  </div>
                </div>
              </div>

              <div class="subzone-panel">
                <div class="subzone-panel-inner">
                  <div class="subzone-panel-header">
                    <span class="subzone-panel-title">Sub-Zones</span>
                    <a href="#" class="subzone-add-link"><i class="fa-solid fa-plus"></i> Add sub-zones</a>
                  </div>

                  <div class="subzone-grid">
                    <div class="subzone-card">
                      <div class="subzone-info">
                        <div class="subzone-icon-box"><i class="fa-solid fa-location-dot"></i></div>
                        <div class="subzone-details">
                          <span class="subzone-title">Lagos Island</span>
                          <span class="subzone-coordinator">Amaka Giwa</span>
                        </div>
                      </div>
                      <div class="subzone-count"><i class="fa-solid fa-users"></i><span>94</span></div>
                    </div>

                    <div class="subzone-card">
                      <div class="subzone-info">
                        <div class="subzone-icon-box"><i class="fa-solid fa-location-dot"></i></div>
                        <div class="subzone-details">
                          <span class="subzone-title">Surulere</span>
                          <span class="subzone-coordinator">Bode Gantos</span>
                        </div>
                      </div>
                      <div class="subzone-count"><i class="fa-solid fa-users"></i><span>82</span></div>
                    </div>

                    <div class="subzone-card">
                      <div class="subzone-info">
                        <div class="subzone-icon-box"><i class="fa-solid fa-location-dot"></i></div>
                        <div class="subzone-details">
                          <span class="subzone-title">Lagos Mainland</span>
                          <span class="subzone-coordinator">Amaka Giwa</span>
                        </div>
                      </div>
                      <div class="subzone-count"><i class="fa-solid fa-users"></i><span>94</span></div>
                    </div>

                    <div class="subzone-card">
                      <div class="subzone-info">
                        <div class="subzone-icon-box"><i class="fa-solid fa-location-dot"></i></div>
                        <div class="subzone-details">
                          <span class="subzone-title">Oshodi-Isolo</span>
                          <span class="subzone-coordinator">Bode Gantos</span>
                        </div>
                      </div>
                      <div class="subzone-count"><i class="fa-solid fa-users"></i><span>82</span></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Abuja Zone -->
            <div class="zone-card">
              <div class="zone-info-group">
                <div class="zone-icon-box">
                  <i class="fa-solid fa-location-dot"></i>
                </div>
                <div class="zone-details">
                  <span class="zone-title">Abuja Zone</span>
                  <span class="zone-coordinator">Coordinator: Ray Charles</span>
                </div>
              </div>

              <div class="zone-meta-group">
                <div class="zone-stat-unit">
                  <span class="zone-stat-number">145</span>
                  <span class="zone-stat-text">members</span>
                </div>
                <div class="zone-stat-unit">
                  <span class="zone-stat-number">2</span>
                  <span class="zone-stat-text">sub-zones</span>
                </div>
                <div class="zone-actions">
                  <a href="#" class="btn-zone-action" aria-label="Edit Zone"><i class="fa-solid fa-pen"></i></a>
                  <a href="#" class="btn-zone-action" aria-label="View Zone"><i class="fa-solid fa-chevron-right"></i></a>
                </div>
              </div>
            </div>

            <!-- Port Harcourt Zone -->
            <div class="zone-card">
              <div class="zone-info-group">
                <div class="zone-icon-box">
                  <i class="fa-solid fa-location-dot"></i>
                </div>
                <div class="zone-details">
                  <span class="zone-title">Port Harcourt Zone</span>
                  <span class="zone-coordinator">Coordinator: Joseph Raymond</span>
                </div>
              </div>

              <div class="zone-meta-group">
                <div class="zone-stat-unit">
                  <span class="zone-stat-number">112</span>
                  <span class="zone-stat-text">members</span>
                </div>
                <div class="zone-stat-unit">
                  <span class="zone-stat-number">3</span>
                  <span class="zone-stat-text">sub-zones</span>
                </div>
                <div class="zone-actions">
                  <a href="#" class="btn-zone-action" aria-label="Edit Zone"><i class="fa-solid fa-pen"></i></a>
                  <a href="#" class="btn-zone-action" aria-label="View Zone"><i class="fa-solid fa-chevron-right"></i></a>
                </div>
              </div>
            </div>

            <!-- Kano Zone -->
            <div class="zone-card">
              <div class="zone-info-group">
                <div class="zone-icon-box">
                  <i class="fa-solid fa-location-dot"></i>
                </div>
                <div class="zone-details">
                  <span class="zone-title">Kano Zone</span>
                  <span class="zone-coordinator">Coordinator: Musa Abdulahi</span>
                </div>
              </div>

              <div class="zone-meta-group">
                <div class="zone-stat-unit">
                  <span class="zone-stat-number">112</span>
                  <span class="zone-stat-text">members</span>
                </div>
                <div class="zone-stat-unit">
                  <span class="zone-stat-number">3</span>
                  <span class="zone-stat-text">sub-zones</span>
                </div>
                <div class="zone-actions">
                  <a href="#" class="btn-zone-action" aria-label="Edit Zone"><i class="fa-solid fa-pen"></i></a>
                  <a href="#" class="btn-zone-action" aria-label="View Zone"><i class="fa-solid fa-chevron-right"></i></a>
                </div>
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