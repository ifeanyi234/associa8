<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Messages - Associa8</title>
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
            <h1 class="page-title">Message</h1>
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
          <div class="section-label" style="margin-bottom: 0.25rem;">
            Unread (<span class="count-highlight">3</span>)
          </div>

          <section class="messages-layout">
            <!-- Message List -->
            <div class="message-list-panel">
              <div class="list-card-dark-header">
                <div class="list-card-dark-header-left">
                  <span class="list-card-dark-header-icon">
                    <i class="fa-regular fa-comment"></i>
                  </span>
                  Messages
                </div>
              </div>

              <div class="message-list-item active">
                <div class="message-list-item-left">
                  <div class="message-avatar-initials">SC</div>
                  <div>
                    <div class="message-sender-name">Secretariat</div>
                    <div class="message-preview-text">Annual General Meeting - 12 May 2026</div>
                  </div>
                </div>
                <span class="message-list-item-time unread">2 Hours ago</span>
              </div>

              <div class="message-list-item">
                <div class="message-list-item-left">
                  <div class="message-avatar-initials">FO</div>
                  <div>
                    <div class="message-sender-name">Finance Official</div>
                    <div class="message-preview-text">Outstanding Reminder - 12 May 2026</div>
                  </div>
                </div>
                <span class="message-list-item-time unread">Yesterday</span>
              </div>

              <div class="message-list-item">
                <div class="message-list-item-left">
                  <div class="message-avatar-initials">AD</div>
                  <div>
                    <div class="message-sender-name">Admin</div>
                    <div class="message-preview-text">2026 Member Portal</div>
                  </div>
                </div>
                <span class="message-list-item-time">Mar 10 2026</span>
              </div>

              <div class="message-list-item">
                <div class="message-list-item-left">
                  <div class="message-avatar-initials">ST</div>
                  <div>
                    <div class="message-sender-name">Security Tips</div>
                    <div class="message-preview-text">Save Environment Guidlines</div>
                  </div>
                </div>
                <span class="message-list-item-time">Feb 09 2026</span>
              </div>
            </div>

            <!-- Message Detail -->
            <div class="message-detail-panel">
              <div class="message-detail-header">
                <div class="message-detail-subject">Annual General Meeting - 12 May 2026</div>
                <div class="message-detail-meta-row">
                  <div class="message-detail-meta-left">
                    <div class="message-avatar-initials" style="background-color: rgba(255,255,255,0.12); color: #ffffff;">SC</div>
                    From: <strong>Secretariat</strong> Today, 2:30 PM
                  </div>
                  <span class="message-detail-tag">Official</span>
                </div>
              </div>

              <div class="message-detail-body">
                <p>Dear Member,</p>
                <p>This is to formally notify you of the upcoming Annual General Meeting (AGM) scheduled for Saturday, 12 July 2026 at 10:00 AM.</p>
                <p>Venue: Eko Hotel, Victoria Island, Lagos</p>
                <p>All members are required to attend. Please confirm your RSVP via the Events section of your portal.</p>
                <p>Regards, The Secretariat</p>
              </div>

              <div class="message-reply-bar">
                <input type="text" class="message-reply-input" placeholder="Write your reply" />
                <button class="btn-reply-send">
                  <i class="fa-solid fa-paper-plane"></i> Reply
                </button>
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

      // Message list item selection
      document.querySelectorAll(".message-list-item").forEach((item) => {
        item.addEventListener("click", () => {
          document.querySelectorAll(".message-list-item").forEach((i) => i.classList.remove("active"));
          item.classList.add("active");
        });
      });
    </script>
    <script src="../../js/preloader.js"></script>
  </body>
</html>