<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Titles & Hierarchy - Associa8</title>

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

      <!-- Mobile sidebar backdrop: tap it to close the sidebar -->
      <div class="sidebar-overlay" id="sidebarOverlay"></div>

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
            <h1 class="page-title">Titles & Hierarchy</h1>
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
          <!-- Page Action Header -->
          <div class="page-action-header">
            <div>
              <h2 class="page-title-main">Titles & Hierarchy</h2>
              <p class="page-subtitle">
                Manage membership grades and organizational hierarchy
              </p>
            </div>
            <button class="btn-outline-primary">
              <i class="fa-solid fa-user-plus"></i>
              <span>Add titles</span>
            </button>
          </div>

          <!-- Hierarchy List Outer Container -->
          <div class="hierarchy-container">
            <!-- Info Subheader -->
            <div class="hierarchy-info-bar">
              <i class="fa-solid fa-shield-halved"></i>
              <span
                >Hierarchy flow from level 1 (highest) to level 7(entry)</span
              >
            </div>

            <!-- L1 Card -->
            <div class="hierarchy-item-card">
              <div class="hierarchy-left-col">
                <div class="level-reorder-group">
                  <button class="reorder-btn" aria-label="Move Up">
                    <i class="fa-solid fa-chevron-up"></i>
                  </button>
                  <div class="level-badge">L1</div>
                  <button class="reorder-btn" aria-label="Move Down">
                    <i class="fa-solid fa-chevron-down"></i>
                  </button>
                </div>
                <div class="hierarchy-details">
                  <div class="title-header-group">
                    <span class="title-name">Patron</span>
                    <span class="tag-outline tag-patron">Patron</span>
                  </div>
                  <p class="title-description">
                    Honorary position , highest distinction
                  </p>
                </div>
              </div>
              <div class="hierarchy-member-count">
                <span class="count-number">3</span>
                <span class="count-label">members</span>
              </div>
            </div>

            <!-- L2 Card -->
            <div class="hierarchy-item-card">
              <div class="hierarchy-left-col">
                <div class="level-reorder-group">
                  <button class="reorder-btn" aria-label="Move Up">
                    <i class="fa-solid fa-chevron-up"></i>
                  </button>
                  <div class="level-badge">L2</div>
                  <button class="reorder-btn" aria-label="Move Down">
                    <i class="fa-solid fa-chevron-down"></i>
                  </button>
                </div>
                <div class="hierarchy-details">
                  <div class="title-header-group">
                    <span class="title-name">President</span>
                    <span class="tag-outline tag-president">President</span>
                  </div>
                  <p class="title-description">
                    Honorary position , highest distinction
                  </p>
                </div>
              </div>
              <div class="hierarchy-member-count">
                <span class="count-number">1</span>
                <span class="count-label">members</span>
              </div>
            </div>

            <!-- L3 Card -->
            <div class="hierarchy-item-card">
              <div class="hierarchy-left-col">
                <div class="level-reorder-group">
                  <button class="reorder-btn" aria-label="Move Up">
                    <i class="fa-solid fa-chevron-up"></i>
                  </button>
                  <div class="level-badge">L3</div>
                  <button class="reorder-btn" aria-label="Move Down">
                    <i class="fa-solid fa-chevron-down"></i>
                  </button>
                </div>
                <div class="hierarchy-details">
                  <div class="title-header-group">
                    <span class="title-name">Vice President</span>
                    <span class="tag-outline tag-vice-president"
                      >Vice President</span
                    >
                  </div>
                  <p class="title-description">
                    Elected head of the organization
                  </p>
                </div>
              </div>
              <div class="hierarchy-member-count">
                <span class="count-number">2</span>
                <span class="count-label">members</span>
              </div>
            </div>

            <!-- L4 Card -->
            <div class="hierarchy-item-card">
              <div class="hierarchy-left-col">
                <div class="level-reorder-group">
                  <button class="reorder-btn" aria-label="Move Up">
                    <i class="fa-solid fa-chevron-up"></i>
                  </button>
                  <div class="level-badge">L4</div>
                  <button class="reorder-btn" aria-label="Move Down">
                    <i class="fa-solid fa-chevron-down"></i>
                  </button>
                </div>
                <div class="hierarchy-details">
                  <div class="title-header-group">
                    <span class="title-name">Senior Fellow</span>
                    <span class="tag-outline tag-president">President</span>
                  </div>
                  <p class="title-description">
                    Long standing professionals with distinction
                  </p>
                </div>
              </div>
              <div class="hierarchy-member-count">
                <span class="count-number">48</span>
                <span class="count-label">members</span>
              </div>
            </div>

            <!-- L5 Card -->
            <div class="hierarchy-item-card">
              <div class="hierarchy-left-col">
                <div class="level-reorder-group">
                  <button class="reorder-btn" aria-label="Move Up">
                    <i class="fa-solid fa-chevron-up"></i>
                  </button>
                  <div class="level-badge">L5</div>
                  <button class="reorder-btn" aria-label="Move Down">
                    <i class="fa-solid fa-chevron-down"></i>
                  </button>
                </div>
                <div class="hierarchy-details">
                  <div class="title-header-group">
                    <span class="title-name">Fellow</span>
                    <span class="tag-outline tag-fellow">Fellow</span>
                  </div>
                  <p class="title-description">Full professional in training</p>
                </div>
              </div>
              <div class="hierarchy-member-count">
                <span class="count-number">212</span>
                <span class="count-label">members</span>
              </div>
            </div>

            <!-- L6 Card -->
            <div class="hierarchy-item-card">
              <div class="hierarchy-left-col">
                <div class="level-reorder-group">
                  <button class="reorder-btn" aria-label="Move Up">
                    <i class="fa-solid fa-chevron-up"></i>
                  </button>
                  <div class="level-badge">L6</div>
                  <button class="reorder-btn" aria-label="Move Down">
                    <i class="fa-solid fa-chevron-down"></i>
                  </button>
                </div>
                <div class="hierarchy-details">
                  <div class="title-header-group">
                    <span class="title-name">Associate</span>
                    <span class="tag-outline tag-associate">Associate</span>
                  </div>
                  <p class="title-description">
                    Professional members in training
                  </p>
                </div>
              </div>
              <div class="hierarchy-member-count">
                <span class="count-number">186</span>
                <span class="count-label">members</span>
              </div>
            </div>

            <!-- L7 Card -->
            <div class="hierarchy-item-card">
              <div class="hierarchy-left-col">
                <div class="level-reorder-group">
                  <button class="reorder-btn" aria-label="Move Up">
                    <i class="fa-solid fa-chevron-up"></i>
                  </button>
                  <div class="level-badge">L7</div>
                  <button class="reorder-btn" aria-label="Move Down">
                    <i class="fa-solid fa-chevron-down"></i>
                  </button>
                </div>
                <div class="hierarchy-details">
                  <div class="title-header-group">
                    <span class="title-name">Member</span>
                    <span class="tag-outline tag-member">Member</span>
                  </div>
                  <p class="title-description">General membership upgrade</p>
                </div>
              </div>
              <div class="hierarchy-member-count">
                <span class="count-number">195</span>
                <span class="count-label">members</span>
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
