<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>My Profile - Associa8</title>
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
            <h1 class="page-title">My Profile</h1>
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
          <section class="profile-grid">
            <!-- Photo Card -->
            <div class="profile-photo-card">
              <div class="profile-photo-placeholder">
                <i class="fa-solid fa-user"></i>
              </div>
              <div class="profile-photo-name">Joseph Raymond</div>
              <div class="profile-photo-id">ID - ASC -2026-001</div>
              <button class="btn-summary-action">Upload Photo</button>
            </div>

            <!-- Personal Information Card -->
            <div class="info-card">
              <div class="info-card-header">
                <div class="stat-card-icon" style="margin-bottom: 0;">
                  <i class="fa-solid fa-file-lines"></i>
                </div>
                <span class="info-card-title">Personal Information</span>
              </div>

              <div class="info-row-grid">
                <div class="info-row">
                  <i class="fa-solid fa-user"></i>
                  <div>
                    <div class="info-row-label">Full Name</div>
                    <div class="info-row-value">Joseph Raymond</div>
                  </div>
                </div>
                <div class="info-row">
                  <i class="fa-regular fa-envelope"></i>
                  <div>
                    <div class="info-row-label">Email</div>
                    <div class="info-row-value">Jraymond@gmail.com</div>
                  </div>
                </div>
                <div class="info-row">
                  <i class="fa-solid fa-phone"></i>
                  <div>
                    <div class="info-row-label">Phone Number</div>
                    <div class="info-row-value">+234-80123456789</div>
                  </div>
                </div>
                <div class="info-row">
                  <i class="fa-solid fa-location-dot"></i>
                  <div>
                    <div class="info-row-label">Address</div>
                    <div class="info-row-value">12, Okota Road Isolo Lagos Nigeria</div>
                  </div>
                </div>
                <div class="info-row">
                  <i class="fa-regular fa-calendar"></i>
                  <div>
                    <div class="info-row-label">Date Of Birth</div>
                    <div class="info-row-value">March 14, 1986</div>
                  </div>
                </div>
                <div class="info-row">
                  <i class="fa-solid fa-user-tie"></i>
                  <div>
                    <div class="info-row-label">Occupation</div>
                    <div class="info-row-value">Civil Engineering</div>
                  </div>
                </div>
              </div>

              <button class="btn-summary-action" style="width: fit-content; padding-left: 1.5rem; padding-right: 1.5rem;">Edit Information</button>
            </div>

            <!-- Membership Card -->
            <div class="info-card">
              <div class="info-card-header">
                <div class="stat-card-icon" style="margin-bottom: 0; background-color: var(--text-primary);">
                  <i class="fa-solid fa-shield-halved"></i>
                </div>
                <span class="info-card-title">Membership</span>
              </div>

              <div class="detail-list">
                <div class="detail-list-row">
                  <span class="detail-list-label">Tier</span>
                  <span class="detail-list-value">Senior Fellow</span>
                </div>
                <div class="detail-list-row">
                  <span class="detail-list-label">Member Since</span>
                  <span class="detail-list-value">January 2024</span>
                </div>
                <div class="detail-list-row">
                  <span class="detail-list-label">Valid Until</span>
                  <span class="detail-list-value">December 2030</span>
                </div>
                <div class="detail-list-row">
                  <span class="detail-list-label">Status</span>
                  <span class="detail-list-value link-blue">Active</span>
                </div>
              </div>
            </div>

            <!-- Profile Completion Checklist -->
            <div class="info-card">
              <div class="info-card-header" style="border-bottom: none; padding-bottom: 0; margin-bottom: 0.75rem;">
                <div class="stat-card-icon" style="margin-bottom: 0;">
                  <i class="fa-solid fa-file-lines"></i>
                </div>
                <span class="info-card-title" style="flex-grow: 1;">Profile Completion</span>
              </div>

              <div class="checklist-header-row">
                <span></span>
                <span class="checklist-percent">78%</span>
              </div>
              <div class="checklist-progress-wrapper">
                <div class="checklist-progress-fill" style="width: 78%"></div>
              </div>

              <div class="checklist-row">
                <div class="checklist-row-left">
                  <span class="checklist-check checked"><i class="fa-solid fa-check"></i></span>
                  <span class="checklist-label">Full Name</span>
                </div>
                <span class="checklist-value">Joseph Raymond</span>
              </div>

              <div class="checklist-row">
                <div class="checklist-row-left">
                  <span class="checklist-check checked"><i class="fa-solid fa-check"></i></span>
                  <span class="checklist-label">Email Address</span>
                </div>
                <span class="checklist-value">Jraymond@gmail.com</span>
              </div>

              <div class="checklist-row">
                <div class="checklist-row-left">
                  <span class="checklist-check checked"><i class="fa-solid fa-check"></i></span>
                  <span class="checklist-label">Home Address</span>
                </div>
                <span class="checklist-value">12,Okota Road Lagos Nigeria</span>
              </div>

              <div class="checklist-row">
                <div class="checklist-row-left">
                  <span class="checklist-check checked"><i class="fa-solid fa-check"></i></span>
                  <span class="checklist-label">Phone number</span>
                </div>
                <span class="checklist-value">+234-80123456789</span>
              </div>

              <div class="checklist-row">
                <div class="checklist-row-left">
                  <span class="checklist-check checked"><i class="fa-solid fa-check"></i></span>
                  <span class="checklist-label">Date Of Birth</span>
                </div>
                <span class="checklist-value">March 14 1986</span>
              </div>

              <div class="checklist-row">
                <div class="checklist-row-left">
                  <span class="checklist-check checked"><i class="fa-solid fa-check"></i></span>
                  <span class="checklist-label">Occupation</span>
                </div>
                <span class="checklist-value">Civil Engineer</span>
              </div>

              <div class="checklist-row">
                <div class="checklist-row-left">
                  <span class="checklist-check checked"><i class="fa-solid fa-check"></i></span>
                  <span class="checklist-label">State Of Origin</span>
                </div>
                <span class="checklist-value">Delta State</span>
              </div>

              <div class="checklist-row">
                <div class="checklist-row-left">
                  <span class="checklist-check"></span>
                  <span class="checklist-label">Emergency Contact</span>
                </div>
                <span class="checklist-value required">Required</span>
              </div>

              <div class="checklist-row">
                <div class="checklist-row-left">
                  <span class="checklist-check"></span>
                  <span class="checklist-label">Profile Photo</span>
                </div>
                <span class="checklist-value required">Required</span>
              </div>
            </div>
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