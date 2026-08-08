<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>My Dashboard - Associa8</title>
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
      href="../images/fav-logo.png"
      type="image/x-icon"
    />
    <link rel="stylesheet" href="../css/preloader.css" />
    <link rel="stylesheet" href="../css/dashboard.css" />
  </head>
  <body class="admin-body">
    <?php include('inc/preloader.php') ?>
    <div class="admin-layout">
      <!-- ==========================================
         MEMBER SIDEBAR NAVIGATION
         (Move to inc/member-sidebar.php if reused across member pages)
         ========================================== -->
      <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-brand">
          <!-- reuse your existing Associa8 logo markup here -->
        </div>
        <ul class="sidebar-menu">
          <li class="sidebar-item">
            <a href="member-dashboard.php" class="sidebar-link active">
              <span class="sidebar-link-content">
                <i class="fa-solid fa-cube"></i> Dashboard
              </span>
            </a>
          </li>
          <li class="sidebar-item">
            <a href="profile.php" class="sidebar-link">
              <span class="sidebar-link-content">
                <i class="fa-solid fa-user"></i> Profile
              </span>
            </a>
          </li>
          <li class="sidebar-item">
            <a href="attendance.php" class="sidebar-link">
              <span class="sidebar-link-content">
                <i class="fa-solid fa-user-check"></i> Attendance
              </span>
            </a>
          </li>
          <li class="sidebar-item">
            <a href="payment.php" class="sidebar-link">
              <span class="sidebar-link-content">
                <i class="fa-solid fa-video"></i> Payment
              </span>
            </a>
          </li>
          <li class="sidebar-item">
            <a href="events.php" class="sidebar-link">
              <span class="sidebar-link-content">
                <i class="fa-regular fa-calendar"></i> Events
              </span>
            </a>
          </li>
          <li class="sidebar-item">
            <a href="messages.php" class="sidebar-link">
              <span class="sidebar-link-content">
                <i class="fa-regular fa-comment"></i> Messages
              </span>
            </a>
          </li>
          <li class="sidebar-item">
            <a href="documents.php" class="sidebar-link">
              <span class="sidebar-link-content">
                <i class="fa-regular fa-file-lines"></i> Document
              </span>
            </a>
          </li>
          <li class="sidebar-item">
            <a href="settings.php" class="sidebar-link">
              <span class="sidebar-link-content">
                <i class="fa-solid fa-gear"></i> Settings
              </span>
            </a>
          </li>
        </ul>
        <div class="sidebar-footer">
          <a href="logout.php" class="sidebar-link">
            <span class="sidebar-link-content">
              <i class="fa-solid fa-right-from-bracket"></i> Logout
            </span>
          </a>
        </div>
      </aside>

      <!-- MAIN CONTENT AREA -->
      <main class="admin-main">
        <header class="admin-header">
          <div class="header-left">
            <button class="header-toggle-btn" id="sidebarToggle">
              <i class="fa-solid fa-bars"></i>
            </button>
            <h1 class="page-title">My Dashboard</h1>
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
          <!-- Welcome Banner -->
          <section class="welcome-banner">
            <h2 class="welcome-title">Good morning, Joseph 👋</h2>
            <p class="welcome-subtitle">
              Here's what's happening in the organization today..
            </p>

            <div class="banner-actions">
              <button class="btn-banner-ghost">
                <i class="fa-solid fa-user"></i> Profile
              </button>
              <button class="btn-banner-ghost">
                <i class="fa-solid fa-user-check"></i> Attendance
              </button>
              <button class="btn-banner-ghost">
                <i class="fa-regular fa-comment"></i> Messages
              </button>
            </div>
          </section>

          <!-- Summary Cards -->
          <section class="summary-cards-grid">
            <div class="summary-card">
              <div class="summary-card-header">
                <span class="summary-card-title">Profile Completion</span>
                <div class="summary-icon-circle">
                  <i class="fa-solid fa-user"></i>
                </div>
              </div>
              <div class="summary-value">90%</div>
              <div class="summary-sublabel">1 field remaining to complete</div>
              <div class="summary-progress-row">
                <div class="progress-bar-wrapper">
                  <div class="progress-bar-fill" style="width: 90%"></div>
                </div>
                <span class="summary-progress-percent">90%</span>
              </div>
            </div>

            <div class="summary-card">
              <div class="summary-card-header">
                <span class="summary-card-title">Outstanding Dues</span>
                <div class="summary-icon-circle">
                  <i class="fa-solid fa-circle-info"></i>
                </div>
              </div>
              <div class="summary-value">&#8358;10,000</div>
              <div class="summary-sublabel">Due date by 1 August 2026</div>
              <button class="btn-summary-action">Pay up</button>
            </div>

            <div class="summary-card">
              <div class="summary-card-header">
                <span class="summary-card-title">Upcoming Events</span>
                <div class="summary-icon-circle">
                  <i class="fa-regular fa-calendar"></i>
                </div>
              </div>
              <div class="summary-value" style="font-size: 1.1rem;">Annual Meetings</div>
              <div class="summary-sublabel">Sat, 20 August 2026, 10:00am</div>
              <button class="btn-summary-action">View details</button>
            </div>
          </section>

          <!-- Notifications & Recent Activities -->
          <section class="dashboard-lists-grid">
            <!-- Notifications -->
            <div class="list-card-dark">
              <div class="list-card-dark-header">
                <div class="list-card-dark-header-left">
                  <span class="list-card-dark-header-icon">
                    <i class="fa-solid fa-bell"></i>
                  </span>
                  Notification
                </div>
              </div>
              <div class="list-card-body">
                <div class="list-card-row">
                  <div class="list-card-row-left">
                    <div class="list-card-row-icon">
                      <i class="fa-regular fa-credit-card"></i>
                    </div>
                    <div>
                      <div class="list-card-row-title">Dues Reminder</div>
                      <div class="list-card-row-desc">Your annual dues of 10,000 are due on 1 Aug.</div>
                    </div>
                  </div>
                  <span class="list-card-row-time unread">2 Hours ago</span>
                </div>

                <div class="list-card-row">
                  <div class="list-card-row-left">
                    <div class="list-card-row-icon">
                      <i class="fa-regular fa-calendar"></i>
                    </div>
                    <div>
                      <div class="list-card-row-title">Events: Annual meeting</div>
                      <div class="list-card-row-desc">Annual general meetings is scheduled for 20 Aug</div>
                    </div>
                  </div>
                  <span class="list-card-row-time unread">2 Hours ago</span>
                </div>

                <div class="list-card-row">
                  <div class="list-card-row-left">
                    <div class="list-card-row-icon">
                      <i class="fa-solid fa-user"></i>
                    </div>
                    <div>
                      <div class="list-card-row-title">Profile Complete</div>
                      <div class="list-card-row-desc">Please update your emergency contact information</div>
                    </div>
                  </div>
                  <span class="list-card-row-time">3 Days ago</span>
                </div>

                <div class="list-card-row">
                  <div class="list-card-row-left">
                    <div class="list-card-row-icon">
                      <i class="fa-solid fa-microphone"></i>
                    </div>
                    <div>
                      <div class="list-card-row-title">Announcement</div>
                      <div class="list-card-row-desc">New document has been uploaded. Review now</div>
                    </div>
                  </div>
                  <span class="list-card-row-time">1 Week ago</span>
                </div>

                <div class="list-card-row">
                  <div class="list-card-row-left">
                    <div class="list-card-row-icon">
                      <i class="fa-solid fa-bell"></i>
                    </div>
                    <div>
                      <div class="list-card-row-title">Membership Renewal</div>
                      <div class="list-card-row-desc">Your membership has been renewed for 2026</div>
                    </div>
                  </div>
                  <span class="list-card-row-time">2 Weeks ago</span>
                </div>
              </div>
              <a href="#" class="list-card-footer-btn">View All Notifications</a>
            </div>

            <!-- Recent Activities -->
            <div class="list-card-dark">
              <div class="list-card-dark-header">
                <div class="list-card-dark-header-left">
                  <span class="list-card-dark-header-icon">
                    <i class="fa-solid fa-arrow-right"></i>
                  </span>
                  Recent Activities
                </div>
                <span class="list-card-dark-header-meta">Last 30 days</span>
              </div>
              <div class="list-card-body">
                <div class="list-card-row">
                  <div class="list-card-row-left">
                    <div class="list-card-row-icon">
                      <i class="fa-regular fa-calendar-check"></i>
                    </div>
                    <div>
                      <div class="list-card-row-title">RSVP Confirmed</div>
                      <div class="list-card-row-desc">Annual general meeting - 1 Aug</div>
                    </div>
                  </div>
                  <span class="list-card-row-time">Today, 9:14am</span>
                </div>

                <div class="list-card-row">
                  <div class="list-card-row-left">
                    <div class="list-card-row-icon">
                      <i class="fa-regular fa-file-lines"></i>
                    </div>
                    <div>
                      <div class="list-card-row-title">Documents Uploaded</div>
                      <div class="list-card-row-desc">Updated national ID on profile</div>
                    </div>
                  </div>
                  <span class="list-card-row-time">Yesterday, 3:12pm</span>
                </div>

                <div class="list-card-row">
                  <div class="list-card-row-left">
                    <div class="list-card-row-icon">
                      <i class="fa-regular fa-credit-card"></i>
                    </div>
                    <div>
                      <div class="list-card-row-title">Payment Received</div>
                      <div class="list-card-row-desc">&#8358;5,000 partial dues payment</div>
                    </div>
                  </div>
                  <span class="list-card-row-time">Jul 27, 11:00am</span>
                </div>

                <div class="list-card-row">
                  <div class="list-card-row-left">
                    <div class="list-card-row-icon">
                      <i class="fa-solid fa-user"></i>
                    </div>
                    <div>
                      <div class="list-card-row-title">Profile Updated</div>
                      <div class="list-card-row-desc">Updated phone number and address</div>
                    </div>
                  </div>
                  <span class="list-card-row-time">Jun 20, 10:17pm</span>
                </div>

                <div class="list-card-row">
                  <div class="list-card-row-left">
                    <div class="list-card-row-icon">
                      <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <div>
                      <div class="list-card-row-title">Membership Activated</div>
                      <div class="list-card-row-desc">2026 membership successfully activated</div>
                    </div>
                  </div>
                  <span class="list-card-row-time">Jun 20, 12:16pm</span>
                </div>
              </div>
              <a href="#" class="list-card-footer-btn">View Full History</a>
            </div>
          </section>
        </div>

        <?php include('inc/footer.php') ?>
      </main>
    </div>

    <script>
      document.getElementById("sidebarToggle").addEventListener("click", () => {
        document.getElementById("adminSidebar").classList.toggle("open");
      });
    </script>
    <script src="../js/preloader.js"></script>
  </body>
</html>