<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>User Control - Associa8</title>

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
            <h1 class="page-title">Administration</h1>
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
      <h2 class="page-title-main">User Control</h2>
      <p class="page-subtitle">Control user access, permissions, and account settings from one centralized administration panel.</p>
    </div>
  </div>

  <!-- User Form Card -->
  <div class="dashboard-card">
    <form action="process-user.php" method="POST" id="userControlForm">
      
      <!-- First Name & Last Name -->
      <div class="form-row-2col mb-3">
        <div class="form-group">
          <label for="firstName" class="form-label">First Name</label>
          <input type="text" id="firstName" name="first_name" class="form-control" placeholder="Enter your name" required />
        </div>
        <div class="form-group">
          <label for="lastName" class="form-label">Last Name</label>
          <input type="text" id="lastName" name="last_name" class="form-control" placeholder="Enter your name" required />
        </div>
      </div>

      <!-- Email & Role -->
      <div class="form-row-2col mb-3">
        <div class="form-group">
          <label for="email" class="form-label">Email</label>
          <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" required />
        </div>
        <div class="form-group">
          <label for="role" class="form-label">Role</label>
          <select id="role" name="role" class="form-select" required>
            <option value="" selected disabled>Select role</option>
            <option value="super_admin">Super Admin</option>
            <option value="admin">Admin</option>
            <option value="manager">Manager</option>
            <option value="staff">Staff</option>
          </select>
        </div>
      </div>

      <!-- Module Checkboxes -->
      <div class="form-group mb-4">
        <label class="form-label mb-2">Module</label>
        <div class="d-flex align-center gap-3 flex-wrap">
          <label class="form-check">
            <input type="checkbox" class="form-check-input" name="modules[]" value="cbt_management" />
            <span class="form-label mb-0 cursor-pointer">CBT Management</span>
          </label>

          <label class="form-check">
            <input type="checkbox" class="form-check-input" name="modules[]" value="members" />
            <span class="form-label mb-0 cursor-pointer">Members</span>
          </label>

          <label class="form-check">
            <input type="checkbox" class="form-check-input" name="modules[]" value="admission" />
            <span class="form-label mb-0 cursor-pointer">Admission</span>
          </label>

          <label class="form-check">
            <input type="checkbox" class="form-check-input" name="modules[]" value="document" />
            <span class="form-label mb-0 cursor-pointer">Document</span>
          </label>

          <label class="form-check">
            <input type="checkbox" class="form-check-input" name="modules[]" value="attendance" />
            <span class="form-label mb-0 cursor-pointer">Attendance</span>
          </label>

          <label class="form-check">
            <input type="checkbox" class="form-check-input" name="modules[]" value="finance" />
            <span class="form-label mb-0 cursor-pointer">Finance</span>
          </label>
        </div>
      </div>

      <!-- Submit Button -->
      <div class="form-actions">
        <button type="submit" class="btn-primary-filled">Add User</button>
      </div>
    </form>
  </div>
</div>

        <!-- Footer -->
        <?php include('inc/footer.php')?>
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