<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Super Admin Dashboard - Associa8</title>
    <!-- Google font -->
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
    <!-- Fav icon -->
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
    <!-- ====================
    PRELOADER
    ==================== -->
    <?php include('inc/preloader.php')?>
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
            <h1 class="page-title">Dashboard Overview</h1>
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
          <!-- Welcome Banner -->
          <section class="welcome-banner">
            <h2 class="welcome-title">Good morning, <?php echo $_SESSION['username'] ?> 👋</h2>
            <p class="welcome-subtitle">
              Here's what's happening in your organization today..
            </p>

            <div class="banner-actions">
              <button class="btn-banner-action">
                <i class="fa-solid fa-graduation-cap"></i> Review Applications
              </button>
              <button class="btn-banner-action">
                <i class="fa-solid fa-wallet"></i> View Financials
              </button>
              <button class="btn-banner-action">
                <i class="fa-regular fa-calendar-days"></i> Attendance Reports
              </button>
            </div>
          </section>

          <!-- Stats Grid (4 Cards) -->
          <section class="stats-grid">
            <div class="stat-card">
              <div class="stat-card-header">
                <div class="stat-icon-wrapper icon-blue">
                  <i class="fa-solid fa-users"></i>
                </div>
                <span class="stat-badge positive"
                  ><i class="fa-solid fa-arrow-trend-up"></i> +5.2%</span
                >
              </div>
              <div class="stat-value">648</div>
              <div>
                <div class="stat-label">Total Members</div>
                <div class="stat-subtext">Across all zones</div>
              </div>
            </div>

            <div class="stat-card">
              <div class="stat-card-header">
                <div class="stat-icon-wrapper icon-green">
                  <i class="fa-solid fa-naira-sign"></i>
                </div>
                <span class="stat-badge positive"
                  ><i class="fa-solid fa-arrow-trend-up"></i> +12.2%</span
                >
              </div>
              <div class="stat-value">&#8358;2.4M</div>
              <div>
                <div class="stat-label">Total Revenue</div>
                <div class="stat-subtext">This fiscal year</div>
              </div>
            </div>

            <div class="stat-card">
              <div class="stat-card-header">
                <div class="stat-icon-wrapper icon-dark">
                  <i class="fa-solid fa-chart-simple"></i>
                </div>
                <span class="stat-badge negative"
                  ><i class="fa-solid fa-arrow-trend-down"></i> +5.2%</span
                >
              </div>
              <div class="stat-value">87%</div>
              <div>
                <div class="stat-label">Attendance Reports</div>
                <div class="stat-subtext">Last 3 meetings</div>
              </div>
            </div>

            <div class="stat-card">
              <div class="stat-card-header">
                <div class="stat-icon-wrapper icon-red">
                  <i class="fa-solid fa-graduation-cap"></i>
                </div>
                <span class="stat-badge positive"
                  ><i class="fa-solid fa-arrow-trend-up"></i> +5.2%</span
                >
              </div>
              <div class="stat-value">64</div>
              <div>
                <div class="stat-label">Pending Application</div>
                <div class="stat-subtext">Awaiting review</div>
              </div>
            </div>
          </section>

          <!-- Charts Row 1 -->
          <section class="dashboard-split-grid">
            <div class="dashboard-card">
              <div class="dashboard-card-header">
                <div>
                  <h3 class="dashboard-card-title">Member Growth</h3>
                  <p class="dashboard-card-subtitle">
                    Cumulative membership count
                  </p>
                </div>
              </div>
              <div style="height: 250px; position: relative">
                <canvas id="memberGrowthChart"></canvas>
              </div>
            </div>

            <div class="dashboard-card">
              <div class="dashboard-card-header">
                <div>
                  <h3 class="dashboard-card-title">Member Status</h3>
                  <p class="dashboard-card-subtitle">Current distribution</p>
                </div>
              </div>
              <div
                style="
                  height: 180px;
                  position: relative;
                  display: flex;
                  justify-content: center;
                "
              >
                <canvas id="memberStatusChart"></canvas>
              </div>
              <div class="legend-list">
                <div class="legend-item">
                  <div class="legend-info">
                    <span class="legend-dot active"></span> Active
                  </div>
                  <span class="legend-value">542</span>
                </div>
                <div class="legend-item">
                  <div class="legend-info">
                    <span class="legend-dot suspended"></span> Suspended
                  </div>
                  <span class="legend-value">28</span>
                </div>
                <div class="legend-item">
                  <div class="legend-info">
                    <span class="legend-dot pending"></span> Pending
                  </div>
                  <span class="legend-value">64</span>
                </div>
                <div class="legend-item">
                  <div class="legend-info">
                    <span class="legend-dot inactive"></span> Inactive
                  </div>
                  <span class="legend-value">14</span>
                </div>
              </div>
            </div>
          </section>

          <!-- Charts Row 2 -->
          <section class="dashboard-split-grid">
            <div class="dashboard-card">
              <div class="dashboard-card-header">
                <div>
                  <h3 class="dashboard-card-title">Revenue Overview</h3>
                  <p class="dashboard-card-subtitle">
                    Dues vs Levies collected
                  </p>
                </div>
              </div>
              <div style="height: 250px; position: relative">
                <canvas id="revenueChart"></canvas>
              </div>
            </div>

            <div class="dashboard-card">
              <div class="dashboard-card-header">
                <h3 class="dashboard-card-title">Recent Activities</h3>
                <a href="#" class="card-action-link">View all</a>
              </div>
              <div class="activity-list">
                <div class="activity-item">
                  <div class="activity-icon icon-blue">
                    <i class="fa-solid fa-users"></i>
                  </div>
                  <div class="activity-details">
                    <span class="activity-text"
                      >New application from Chukwuemeka ojo</span
                    >
                    <span class="activity-time">2 min ago</span>
                  </div>
                </div>

                <div class="activity-item">
                  <div class="activity-icon icon-green">
                    <i class="fa-solid fa-naira-sign"></i>
                  </div>
                  <div class="activity-details">
                    <span class="activity-text"
                      >Dues payment received - &#8358;45,000</span
                    >
                    <span class="activity-time">15 min ago</span>
                  </div>
                </div>

                <div class="activity-item">
                  <div class="activity-icon icon-red">
                    <i class="fa-solid fa-graduation-cap"></i>
                  </div>
                  <div class="activity-details">
                    <span class="activity-text"
                      >Member adaeze kazeem suspended</span
                    >
                    <span class="activity-time">1 hrs ago</span>
                  </div>
                </div>

                <div class="activity-item">
                  <div class="activity-icon icon-blue">
                    <i class="fa-regular fa-calendar-check"></i>
                  </div>
                  <div class="activity-details">
                    <span class="activity-text"
                      >Monthly meetings attendance captured</span
                    >
                    <span class="activity-time">2 hrs ago</span>
                  </div>
                </div>

                <div class="activity-item">
                  <div class="activity-icon icon-blue">
                    <i class="fa-solid fa-user-plus"></i>
                  </div>
                  <div class="activity-details">
                    <span class="activity-text">5 new members onboarded</span>
                    <span class="activity-time">Yesterday</span>
                  </div>
                </div>
              </div>
            </div>
          </section>

          <!-- Bottom Row -->
          <section class="bottom-widgets-grid">
            <div class="dashboard-card">
              <div class="mini-stat-header success">
                <i class="fa-solid fa-comment-dots"></i> Dues Compliance
              </div>
              <div class="mini-stat-value">
                78%
                <span class="mini-stat-value-sub">members current</span>
              </div>
              <div class="progress-bar-wrapper">
                <div class="progress-bar-fill" style="width: 78%"></div>
              </div>
            </div>

            <div class="dashboard-card">
              <div class="mini-stat-header danger">
                <i class="fa-regular fa-clock"></i> CBT Pending
              </div>
              <div class="mini-stat-value">
                23
                <span class="mini-stat-value-sub">applicants awaiting test</span>
              </div>
              <div class="mini-stat-footnote danger">
                Next session Dec,12 2026
              </div>
            </div>

            <div class="dashboard-card">
              <div class="mini-stat-header purple">
                <i class="fa-solid fa-bell"></i> Notification Sent
              </div>
              <div class="mini-stat-value">
                1,204
                <span class="mini-stat-value-sub">this month</span>
              </div>
              <div class="mini-stat-footnote purple">
                3 Newsletter pending
              </div>
            </div>
          </section>
        </div>

        <!-- Footer -->
        <?php include('inc/footer.php') ?>
      </main>
    </div>

    <!-- Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Interactive Dashboard Script -->
    <script>
      // 1. Sidebar Dropdown Accordion Toggle Logic (Dynamic Height Precision)
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

      // 2. Mobile Sidebar Toggle (with backdrop + outside-tap/Escape to close)
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

      // Tap the backdrop to close
      sidebarOverlay.addEventListener("click", closeSidebar);

      // Escape key to close
      document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") closeSidebar();
      });

      // Close as soon as a real nav link (not a dropdown toggle) is tapped,
      // so it doesn't stay open while the new page loads
      sidebarEl.querySelectorAll(".sidebar-link").forEach((link) => {
        const parentItem = link.closest(".sidebar-item");
        const isDropdownToggle = parentItem && parentItem.classList.contains("dropdown") && link === parentItem.querySelector(":scope > .sidebar-link");
        if (!isDropdownToggle) {
          link.addEventListener("click", closeSidebar);
        }
      });

      // 3. Member Growth Line Chart
      const ctxGrowth = document
        .getElementById("memberGrowthChart")
        .getContext("2d");
      new Chart(ctxGrowth, {
        type: "line",
        data: {
          labels: [
            "Jan",
            "Feb",
            "Mar",
            "Apr",
            "May",
            "Jun",
            "Jul",
            "Aug",
            "Sept",
            "Oct",
            "Nov",
            "Dec",
          ],
          datasets: [
            {
              data: [
                200, 240, 280, 310, 350, 390, 420, 460, 500, 530, 580, 620,
              ],
              borderColor: "#3b82f6",
              borderWidth: 3,
              pointRadius: 0,
              tension: 0.1,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false } },
          scales: {
            y: { min: 0, max: 800, ticks: { stepSize: 200 } },
            x: { grid: { display: false } },
          },
        },
      });

      // 4. Member Status Donut Chart
      const ctxStatus = document
        .getElementById("memberStatusChart")
        .getContext("2d");
      new Chart(ctxStatus, {
        type: "doughnut",
        data: {
          labels: ["Active", "Suspended", "Pending", "Inactive"],
          datasets: [
            {
              data: [542, 28, 64, 14],
              backgroundColor: ["#3b82f6", "#eab308", "#22c55e", "#ef4444"],
              borderWidth: 2,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: "75%",
          plugins: { legend: { display: false } },
        },
      });

      // 5. Revenue Overview Bar Chart
      const ctxRevenue = document
        .getElementById("revenueChart")
        .getContext("2d");
      new Chart(ctxRevenue, {
        type: "bar",
        data: {
          labels: [
            "Jan",
            "Feb",
            "Mar",
            "Apr",
            "May",
            "Jun",
            "Jul",
            "Aug",
            "Sept",
            "Oct",
            "Nov",
            "Dec",
          ],
          datasets: [
            {
              label: "Dues",
              data: [70, 95, 75, 100, 95, 50, 92, 115, 98, 48, 98, 112],
              backgroundColor: "#3b82f6",
              borderRadius: 2,
            },
            {
              label: "Levies",
              data: [12, 15, 12, 18, 16, 10, 15, 20, 18, 8, 18, 22],
              backgroundColor: "#22c55e",
              borderRadius: 2,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: "bottom",
              labels: { boxWidth: 12, usePointStyle: false },
            },
          },
          scales: {
            y: {
              min: 0,
              max: 120,
              ticks: {
                callback: (value) => (value === 0 ? "0" : value + "K"),
              },
            },
            x: { grid: { display: false } },
          },
        },
      });
    </script>
    <script src="../js/preloader.js"></script>
  </body>
</html>