<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Document Manager - Associa8</title>

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
            <h1 class="page-title">Document</h1>
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
              <h2 class="page-title-main">Document Manager</h2>
              <p class="page-subtitle">
                Upload, organize, and securely share documents while maintaining complete control over access and permissions.
              </p>
            </div>
            <div style="display: flex; gap: 0.75rem;">
              <button class="btn-navy-outline">
                <i class="fa-solid fa-sliders"></i> Filter
              </button>
              <button class="btn-navy-filled">
                <i class="fa-solid fa-circle-plus"></i> Add Document
              </button>
            </div>
          </div>

          <!-- Document Search Bar -->
          <div class="directory-toolbar" style="margin-bottom: 1.5rem;">
            <div class="toolbar-search" style="max-width: 100%; flex: 1;">
              <i class="fa-solid fa-magnifying-glass search-icon"></i>
              <input
                type="text"
                placeholder="Search by title or file type"
              />
            </div>
          </div>

          <!-- Document Data Table Card -->
          <div class="table-responsive-card">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Title</th>
                  <th>Owner</th>
                  <th>File Type</th>
                  <th>File Path</th>
                  <th>Author ID</th>
                  <th style="text-align: center;">Action</th>
                </tr>
              </thead>
              <tbody>
                <!-- Row 1 -->
                <tr>
                  <td class="fw-semibold">CBD Study Guide vb.pdf</td>
                  <td>Super Admin</td>
                  <td>Document</td>
                  <td>CBD Guide 1st Edition.pdf</td>
                  <td>Admin</td>
                  <td style="text-align: center;">
                    <div style="display: inline-flex; gap: 0.5rem;">
                      <button class="btn-action-edit">Edit</button>
                      <button class="btn-action-delete">Delete</button>
                    </div>
                  </td>
                </tr>

                <!-- Row 2 -->
                <tr>
                  <td class="fw-semibold">CBD Study Guide vb.pdf</td>
                  <td>Super Admin</td>
                  <td>Document</td>
                  <td>CBD Guide 1st Edition.pdf</td>
                  <td>Admin</td>
                  <td style="text-align: center;">
                    <div style="display: inline-flex; gap: 0.5rem;">
                      <button class="btn-action-edit">Edit</button>
                      <button class="btn-action-delete">Delete</button>
                    </div>
                  </td>
                </tr>

                <!-- Row 3 -->
                <tr>
                  <td class="fw-semibold">CBD Study Guide vb.pdf</td>
                  <td>Super Admin</td>
                  <td>Document</td>
                  <td>CBD Guide 1st Edition.pdf</td>
                  <td>Admin</td>
                  <td style="text-align: center;">
                    <div style="display: inline-flex; gap: 0.5rem;">
                      <button class="btn-action-edit">Edit</button>
                      <button class="btn-action-delete">Delete</button>
                    </div>
                  </td>
                </tr>

                <!-- Row 4 -->
                <tr>
                  <td class="fw-semibold">CBD Study Guide vb.pdf</td>
                  <td>Super Admin</td>
                  <td>Document</td>
                  <td>CBD Guide 1st Edition.pdf</td>
                  <td>Admin</td>
                  <td style="text-align: center;">
                    <div style="display: inline-flex; gap: 0.5rem;">
                      <button class="btn-action-edit">Edit</button>
                      <button class="btn-action-delete">Delete</button>
                    </div>
                  </td>
                </tr>

                <!-- Row 5 -->
                <tr>
                  <td class="fw-semibold">CBD Study Guide vb.pdf</td>
                  <td>Super Admin</td>
                  <td>Document</td>
                  <td>CBD Guide 1st Edition.pdf</td>
                  <td>Admin</td>
                  <td style="text-align: center;">
                    <div style="display: inline-flex; gap: 0.5rem;">
                      <button class="btn-action-edit">Edit</button>
                      <button class="btn-action-delete">Delete</button>
                    </div>
                  </td>
                </tr>

                <!-- Row 6 -->
                <tr>
                  <td class="fw-semibold">CBD Study Guide vb.pdf</td>
                  <td>Super Admin</td>
                  <td>Document</td>
                  <td>CBD Guide 1st Edition.pdf</td>
                  <td>Admin</td>
                  <td style="text-align: center;">
                    <div style="display: inline-flex; gap: 0.5rem;">
                      <button class="btn-action-edit">Edit</button>
                      <button class="btn-action-delete">Delete</button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Footer -->
        <?php include('inc/footer.php') ?>
      </main>
    </div>

    <!-- Interactive Scripts -->
    <script>
      // 1. Sidebar Dropdown Dynamic Accordion Logic
      const dropdownItems = document.querySelectorAll(".sidebar-item.dropdown");

      dropdownItems.forEach((item) => {
        const link = item.querySelector(".sidebar-link");
        const submenu = item.querySelector(".sidebar-submenu");

        if (link && submenu) {
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
        }
      });

      // 2. Mobile Sidebar Toggle
      document.getElementById("sidebarToggle").addEventListener("click", () => {
        document.getElementById("adminSidebar").classList.toggle("open");
      });
    </script>
    <script src="../js/preloader.js"></script>
  </body>
</html>