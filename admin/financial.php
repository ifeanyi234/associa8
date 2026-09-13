<?php require_once "inc/auth.php"; ?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Financial Management - Associa8</title>
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
    <!-- PRELOADER -->
    <?php include('inc/preloader.php')?>
    
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
            <h1 class="page-title">Financial Management</h1>
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

        <!-- Page Body Content -->
        <div class="dashboard-content">
          <!-- Page Action Header -->
          <div class="page-action-header">
            <div>
              <h2 class="page-title-main">Financial Management</h2>
              <p class="page-subtitle">Schedules, Applications, CBT, Sponsors & Onboarding</p>
            </div>
            <div style="display: flex; gap: 0.75rem;">
              <button class="btn-navy-outline">
                <i class="fa-solid fa-arrow-up-from-bracket"></i> Export
              </button>
              <button class="btn-navy-filled">
                <i class="fa-solid fa-circle-plus"></i> Record New Payment
              </button>
            </div>
          </div>

          <!-- Financial Overview Stats Grid (4 Cards) -->
          <section class="stats-grid-4">
            <div class="stat-card-simple">
              <div class="stat-card-icon">
                <i class="fa-solid fa-naira-sign"></i>
              </div>
              <div class="stat-card-number">18</div>
              <div class="stat-card-label">Total Collected</div>
              <div class="stat-subtext">This Fiscal year</div>
            </div>

            <div class="stat-card-simple">
              <div class="stat-card-icon">
                <i class="fa-regular fa-clock"></i>
              </div>
              <div class="stat-card-number">&#8358;312k</div>
              <div class="stat-card-label">Pending Dues</div>
              <div class="stat-subtext">122 members in arrears</div>
            </div>

            <div class="stat-card-simple">
              <div class="stat-card-icon">
                <i class="fa-solid fa-chart-column"></i>
              </div>
              <div class="stat-card-number">68%</div>
              <div class="stat-card-label">Budget Utilized</div>
              <div class="stat-subtext">&#8358;1.4m of &#8358;2.4m</div>
            </div>

            <div class="stat-card-simple">
              <div class="stat-card-icon">
                <i class="fa-regular fa-receipt"></i>
              </div>
              <div class="stat-card-number">1,024</div>
              <div class="stat-card-label">Receipts Issued</div>
              <div class="stat-subtext">This year</div>
            </div>
          </section>

                    <!-- Module Navigation Tabs -->
          <div class="filter-pill-group" style="margin-bottom: 0.5rem;">
            <button class="filter-pill active" data-tab="overview">Overview</button>
            <button class="filter-pill" data-tab="dues-levies">Dues & Levies</button>
            <button class="filter-pill" data-tab="payment">Payment</button>
            <button class="filter-pill" data-tab="budget-tracking">Budget Tracking</button>
          </div>

          <div class="tab-content">

            <!-- ============ OVERVIEW TAB ============ -->
            <div class="tab-pane active" id="tab-overview">
              <section class="dashboard-split-grid">
                <!-- Left Card: Monthly Collection Vs Expected Bar Chart -->
                <div class="dashboard-card">
                  <div class="dashboard-card-header">
                    <div>
                      <h3 class="dashboard-card-title">Monthly Collection Vs Expected</h3>
                      <p class="dashboard-card-subtitle">&#8358; in naira</p>
                    </div>
                  </div>
                  <div style="height: 280px; position: relative">
                    <canvas id="monthlyCollectionChart"></canvas>
                  </div>
                </div>

                <!-- Right Card: Budget Allocations Progress Bars -->
                <div class="dashboard-card">
                  <div class="dashboard-card-header">
                    <h3 class="dashboard-card-title">Budget Allocations</h3>
                  </div>
                  <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                    <!-- Item 1 -->
                    <div>
                      <div style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 600;">
                        <span>Programs & Events</span>
                        <span style="color: #2563eb;">72% <span style="font-weight: 400; color: #64748b;">(420K - 320K)</span></span>
                      </div>
                      <div class="progress-bar-wrapper" style="margin-top: 0.4rem; height: 8px;">
                        <div class="progress-bar-fill" style="width: 72%; background-color: #3b82f6;"></div>
                      </div>
                    </div>

                    <!-- Item 2 -->
                    <div>
                      <div style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 600;">
                        <span>Administrations</span>
                        <span style="color: #2563eb;">72% <span style="font-weight: 400; color: #64748b;">(420K - 320K)</span></span>
                      </div>
                      <div class="progress-bar-wrapper" style="margin-top: 0.4rem; height: 8px;">
                        <div class="progress-bar-fill" style="width: 72%; background-color: #0f172a;"></div>
                      </div>
                    </div>

                    <!-- Item 3 -->
                    <div>
                      <div style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 600;">
                        <span>Infrastructure</span>
                        <span style="color: #2563eb;">65% <span style="font-weight: 400; color: #64748b;">(520K - 820K)</span></span>
                      </div>
                      <div class="progress-bar-wrapper" style="margin-top: 0.4rem; height: 8px;">
                        <div class="progress-bar-fill" style="width: 65%; background-color: #3b82f6;"></div>
                      </div>
                    </div>

                    <!-- Item 4 -->
                    <div>
                      <div style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 600;">
                        <span>Welfare Dues</span>
                        <span style="color: #2563eb;">50% <span style="font-weight: 400; color: #64748b;">(180K - 320K)</span></span>
                      </div>
                      <div class="progress-bar-wrapper" style="margin-top: 0.4rem; height: 8px;">
                        <div class="progress-bar-fill" style="width: 50%; background-color: #3b82f6;"></div>
                      </div>
                    </div>

                    <!-- Item 5 -->
                    <div>
                      <div style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 600;">
                        <span>Publications</span>
                        <span style="color: #2563eb;">43% <span style="font-weight: 400; color: #64748b;">(130K - 300K)</span></span>
                      </div>
                      <div class="progress-bar-wrapper" style="margin-top: 0.4rem; height: 8px;">
                        <div class="progress-bar-fill" style="width: 43%; background-color: #3b82f6;"></div>
                      </div>
                    </div>
                  </div>
                </div>
              </section>
            </div>

            <!-- ============ DUES & LEVIES TAB ============ -->
            <div class="tab-pane" id="tab-dues-levies">
              <section class="dashboard-split-grid">
                <!-- Annual Dues -->
                <div class="dashboard-card">
                  <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                      <h3 class="dashboard-card-title">Annual Dues</h3>
                      <p class="dashboard-card-subtitle">Per Year</p>
                    </div>
                    <div style="font-size: 1.4rem; font-weight: 700; color: var(--text-primary);">&#8358;45,000</div>
                  </div>
                  <div style="display: flex; justify-content: space-between; font-size: 0.85rem; color: var(--text-muted); margin-top: 1.25rem;">
                    <span>505 / 648 members paid</span>
                    <span style="font-weight: 600; color: var(--text-primary);">78%</span>
                  </div>
                  <div class="progress-bar-wrapper" style="margin-top: 0.5rem; height: 8px;">
                    <div class="progress-bar-fill" style="width: 78%; background-color: #3b82f6;"></div>
                  </div>
                </div>

                <!-- Development Levy -->
                <div class="dashboard-card">
                  <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                      <h3 class="dashboard-card-title">Development Levy</h3>
                      <p class="dashboard-card-subtitle">Per Year</p>
                    </div>
                    <div style="font-size: 1.4rem; font-weight: 700; color: var(--text-primary);">&#8358;15,000</div>
                  </div>
                  <div style="display: flex; justify-content: space-between; font-size: 0.85rem; color: var(--text-muted); margin-top: 1.25rem;">
                    <span>420 / 648 members paid</span>
                    <span style="font-weight: 600; color: var(--text-primary);">65%</span>
                  </div>
                  <div class="progress-bar-wrapper" style="margin-top: 0.5rem; height: 8px;">
                    <div class="progress-bar-fill" style="width: 65%; background-color: #3b82f6;"></div>
                  </div>
                </div>

                <!-- Special Project Levy -->
                <div class="dashboard-card">
                  <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                      <h3 class="dashboard-card-title">Special Project Levy</h3>
                      <p class="dashboard-card-subtitle">One - Time</p>
                    </div>
                    <div style="font-size: 1.4rem; font-weight: 700; color: var(--text-primary);">&#8358;25,000</div>
                  </div>
                  <div style="display: flex; justify-content: space-between; font-size: 0.85rem; color: var(--text-muted); margin-top: 1.25rem;">
                    <span>312 / 648 members paid</span>
                    <span style="font-weight: 600; color: var(--text-primary);">48%</span>
                  </div>
                  <div class="progress-bar-wrapper" style="margin-top: 0.5rem; height: 8px;">
                    <div class="progress-bar-fill" style="width: 48%; background-color: #3b82f6;"></div>
                  </div>
                </div>

                <!-- Welfare Fund -->
                <div class="dashboard-card">
                  <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                      <h3 class="dashboard-card-title">Welfare Fund</h3>
                      <p class="dashboard-card-subtitle">Per Quarter</p>
                    </div>
                    <div style="font-size: 1.4rem; font-weight: 700; color: var(--text-primary);">&#8358;5,000</div>
                  </div>
                  <div style="display: flex; justify-content: space-between; font-size: 0.85rem; color: var(--text-muted); margin-top: 1.25rem;">
                    <span>600 / 648 members paid</span>
                    <span style="font-weight: 600; color: var(--text-primary);">93%</span>
                  </div>
                  <div class="progress-bar-wrapper" style="margin-top: 0.5rem; height: 8px;">
                    <div class="progress-bar-fill" style="width: 93%; background-color: #3b82f6;"></div>
                  </div>
                </div>
              </section>
            </div>

            <!-- ============ PAYMENT TAB ============ -->
            <div class="tab-pane" id="tab-payment">
              <div class="table-responsive-card">
                <table class="custom-admin-table">
                  <thead>
                    <tr>
                      <th>Reference</th>
                      <th>Member</th>
                      <th>Type</th>
                      <th>Amount</th>
                      <th>Date</th>
                      <th>Status</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td><span class="tx-ref-code">Pay - 2024 - 1180</span></td>
                      <td class="fw-semibold">James Raymond</td>
                      <td>Annual Dues</td>
                      <td class="amount-cell amount-neutral">&#8358;45,000</td>
                      <td>2026 - 06 - 26</td>
                      <td><span class="badge-finance badge-finance-paid">Confirmed</span></td>
                      <td style="text-align: right;"><button class="btn-row-action" type="button" aria-label="Receipts"><i class="fa-regular fa-file-lines"></i> Receipts</button></td>
                    </tr>
                    <tr>
                      <td><span class="tx-ref-code">Pay - 2024 - 1181</span></td>
                      <td class="fw-semibold">Joy Peters</td>
                      <td>Develop. Levy</td>
                      <td class="amount-cell amount-neutral">&#8358;15,000</td>
                      <td>2026 - 06 - 26</td>
                      <td><span class="badge-finance badge-finance-paid">Confirmed</span></td>
                      <td style="text-align: right;"><button class="btn-row-action" type="button" aria-label="Receipts"><i class="fa-regular fa-file-lines"></i> Receipts</button></td>
                    </tr>
                    <tr>
                      <td><span class="tx-ref-code">Pay - 2024 - 1182</span></td>
                      <td class="fw-semibold">James Raymond</td>
                      <td>Annual Dues</td>
                      <td class="amount-cell amount-neutral">&#8358;45,000</td>
                      <td>2026 - 06 - 26</td>
                      <td><span class="badge-finance badge-finance-pending">Pending</span></td>
                      <td style="text-align: right;"><button class="btn-row-action" type="button" aria-label="Receipts"><i class="fa-regular fa-file-lines"></i> Receipts</button></td>
                    </tr>
                    <tr>
                      <td><span class="tx-ref-code">Pay - 2024 - 1183</span></td>
                      <td class="fw-semibold">Adamu Philips</td>
                      <td>Special Levy</td>
                      <td class="amount-cell amount-neutral">&#8358;25,000</td>
                      <td>2026 - 06 - 26</td>
                      <td><span class="badge-finance badge-finance-paid">Confirmed</span></td>
                      <td style="text-align: right;"><button class="btn-row-action" type="button" aria-label="Receipts"><i class="fa-regular fa-file-lines"></i> Receipts</button></td>
                    </tr>
                    <tr>
                      <td><span class="tx-ref-code">Pay - 2024 - 1184</span></td>
                      <td class="fw-semibold">James Raymond</td>
                      <td>Annual Dues</td>
                      <td class="amount-cell amount-neutral">&#8358;45,000</td>
                      <td>2026 - 06 - 26</td>
                      <td><span class="badge-finance badge-finance-paid">Confirmed</span></td>
                      <td style="text-align: right;"><button class="btn-row-action" type="button" aria-label="Receipts"><i class="fa-regular fa-file-lines"></i> Receipts</button></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- ============ BUDGET TRACKING TAB ============ -->
            <div class="tab-pane" id="tab-budget-tracking">
              <div class="dashboard-card">
                <div class="dashboard-card-header">
                  <h3 class="dashboard-card-title">FYB 2026 Budget</h3>
                  <button class="btn-outline-primary" type="button">
                    <i class="fa-solid fa-file-export"></i>
                    <span>Generate Report</span>
                  </button>
                </div>
                <div style="display: flex; justify-content: space-around; text-align: center; padding: 1.5rem 0;">
                  <div>
                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.4rem;">Total Budget</div>
                    <div style="font-size: 1.75rem; font-weight: 700; color: var(--text-primary);">&#8358;2.4M</div>
                  </div>
                  <div>
                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.4rem;">Total Spent</div>
                    <div style="font-size: 1.75rem; font-weight: 700; color: #2563eb;">&#8358;1.64M</div>
                  </div>
                  <div>
                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.4rem;">Balance</div>
                    <div style="font-size: 1.75rem; font-weight: 700; color: var(--text-primary);">&#8358;750k</div>
                  </div>
                </div>
                <div class="progress-bar-wrapper" style="height: 10px;">
                  <div class="progress-bar-fill" style="width: 68%; background-color: #2563eb;"></div>
                </div>
                <div style="text-align: center; font-size: 0.8rem; color: var(--text-muted); margin-top: 0.6rem;">68% annual budget utilized</div>
              </div>
            </div>

          </div>
        </div>

        <!-- Footer -->
        <?php include('inc/footer.php') ?>
      </main>
    </div>

    <!-- Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
// Financial page tab switching
const filterPills = document.querySelectorAll(".filter-pill-group .filter-pill[data-tab]");
const tabPanes = document.querySelectorAll(".tab-pane");

filterPills.forEach((pill) => {
  pill.addEventListener("click", () => {
    filterPills.forEach((p) => p.classList.remove("active"));
    pill.classList.add("active");

    const targetId = "tab-" + pill.dataset.tab;
    tabPanes.forEach((pane) => {
      pane.classList.toggle("active", pane.id === targetId);
    });
  });
});
    </script>

    <script>
      // Sidebar Dropdown Accordion Toggle Logic
      const dropdownItems = document.querySelectorAll(".sidebar-item.dropdown");

      dropdownItems.forEach((item) => {
        const link = item.querySelector(".sidebar-link");
        const submenu = item.querySelector(".sidebar-submenu");

        if (link && submenu) {
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
        }
      });

      // Mobile Sidebar Toggle
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

      // Monthly Collection Vs Expected Bar Chart Initialization
      const ctxCollection = document.getElementById("monthlyCollectionChart").getContext("2d");
      new Chart(ctxCollection, {
        type: "bar",
        data: {
          labels: ["Jun", "Jul", "Aug", "Sept", "Oct", "Nov", "Dec"],
          datasets: [
            {
              label: "Excepted",
              data: [120, 115, 110, 100, 95, 105, 95],
              backgroundColor: "#60a5fa",
              borderRadius: 3,
              barThickness: 16
            },
            {
              label: "Collected",
              data: [85, 50, 85, 12, 70, 50, 50],
              backgroundColor: "#22c55e",
              borderRadius: 3,
              barThickness: 16
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: "bottom",
              labels: {
                usePointStyle: true,
                boxWidth: 8
              }
            }
          },
          scales: {
            y: {
              min: 0,
              max: 120,
              ticks: {
                stepSize: 30,
                callback: (value) => (value === 0 ? "0" : value + "k")
              },
              grid: {
                borderDash: [4, 4]
              }
            },
            x: {
              grid: { display: false }
            }
          }
        }
      });
    </script>
    <script src="../js/preloader.js"></script>
  </body>
</html>