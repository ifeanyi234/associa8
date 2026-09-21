<?php require_once 'inc/auth.php'; ?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Events - Associa8</title>
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
            <h1 class="page-title">Events</h1>
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
          <!-- Upcoming Events -->
          <div>
            <div class="section-label" style="margin-bottom: 1rem;">
              Upcoming Events (<span class="count-highlight">3</span>)
            </div>

            <div class="events-list">
              <div class="event-card">
                <div>
                  <span class="event-card-tag">AGM</span>
                  <div class="event-card-title">Annual General Meeting</div>
                  <div class="event-card-meta">
                    <span class="event-card-meta-item">
                      <i class="fa-regular fa-calendar"></i> Saturday, 21th Sept 2026
                    </span>
                    <span class="event-card-meta-item">
                      <i class="fa-regular fa-clock"></i> 10:00 AM
                    </span>
                    <span class="event-card-meta-item">
                      <i class="fa-solid fa-location-dot"></i> Eko Hotel, Victoria Island, Lagos.
                    </span>
                    <span class="event-card-meta-item">
                      <i class="fa-solid fa-users"></i> Capacity: 200
                    </span>
                  </div>
                </div>
                <button class="btn-book-seat">Book a Seat</button>
              </div>

              <div class="event-card">
                <div>
                  <span class="event-card-tag">Networking</span>
                  <div class="event-card-title">Mid Year Review &amp; Networking</div>
                  <div class="event-card-meta">
                    <span class="event-card-meta-item">
                      <i class="fa-regular fa-calendar"></i> Saturday, 20th Nov 2026
                    </span>
                    <span class="event-card-meta-item">
                      <i class="fa-regular fa-clock"></i> 10:00 AM
                    </span>
                    <span class="event-card-meta-item">
                      <i class="fa-solid fa-location-dot"></i> Tag Hotel, Victoria Island, Lagos.
                    </span>
                    <span class="event-card-meta-item">
                      <i class="fa-solid fa-users"></i> Capacity: 400
                    </span>
                  </div>
                </div>
                <button class="btn-book-seat">Book a Seat</button>
              </div>

              <div class="event-card">
                <div>
                  <span class="event-card-tag">Workshop</span>
                  <div class="event-card-title">Leadership Workshop: 2026 Edition</div>
                  <div class="event-card-meta">
                    <span class="event-card-meta-item">
                      <i class="fa-regular fa-calendar"></i> Saturday, 11th Dec 2026
                    </span>
                    <span class="event-card-meta-item">
                      <i class="fa-regular fa-clock"></i> 10:00 AM
                    </span>
                    <span class="event-card-meta-item">
                      <i class="fa-solid fa-location-dot"></i> Eko Hotel, Victoria Island, Lagos.
                    </span>
                    <span class="event-card-meta-item">
                      <i class="fa-solid fa-users"></i> Capacity: 100
                    </span>
                  </div>
                </div>
                <button class="btn-book-seat">Book a Seat</button>
              </div>
            </div>
          </div>

          <!-- Past Events -->
          <div>
            <div class="section-label" style="margin-bottom: 1rem;">Past Events</div>

            <div class="events-list">
              <div class="past-event-row">
                <div class="past-event-row-left">
                  <span class="event-type-pill">Meetings</span>
                  <span class="past-event-title">Q2 General Meeting</span>
                </div>
                <span class="past-event-date">Fri, 20 Mar 2026</span>
              </div>

              <div class="past-event-row">
                <div class="past-event-row-left">
                  <span class="event-type-pill">Workshop</span>
                  <span class="past-event-title">Leadership Workshop - May 2026</span>
                </div>
                <span class="past-event-date">Fri, 20 May 2026</span>
              </div>
            </div>
          </div>
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