<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Payment - Associa8</title>
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
            <h1 class="page-title">Payment</h1>
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
          <!-- Stat Cards -->
          <section class="summary-cards-grid">
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
                <span class="summary-card-title">Paid This Year</span>
                <div class="summary-icon-circle">
                  <i class="fa-solid fa-check"></i>
                </div>
              </div>
              <div class="summary-value">&#8358;20,000</div>
              <div class="summary-sublabel">2 transactions in 2026</div>
            </div>

            <div class="summary-card">
              <div class="summary-card-header">
                <span class="summary-card-title">Total All Time</span>
                <div class="summary-icon-circle">
                  <i class="fa-regular fa-credit-card"></i>
                </div>
              </div>
              <div class="summary-value">&#8358;35,500</div>
              <div class="summary-sublabel">Since January 2020</div>
            </div>
          </section>

          <!-- Payment History -->
          <section class="table-responsive-card">
            <div class="list-card-dark-header">
              <div class="list-card-dark-header-left">
                <span class="list-card-dark-header-icon">
                  <i class="fa-solid fa-arrow-right"></i>
                </span>
                Payment History
              </div>
              <a href="#" class="list-card-dark-header-action">
                <i class="fa-solid fa-file-export"></i> Export
              </a>
            </div>

            <table class="admin-table">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Description</th>
                  <th>Reference</th>
                  <th>Amount</th>
                  <th>Status</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td style="font-weight: 600; color: var(--text-primary);">Feb 10, 2026</td>
                  <td>Partial Dues Payment</td>
                  <td><span class="tx-ref-code">Txn- 880</span></td>
                  <td style="font-weight: 600; color: var(--text-primary);">&#8358;5,000</td>
                  <td><span class="status-text-attended">Completed</span></td>
                  <td style="text-align: right;">
                    <button class="btn-action-trigger" aria-label="Options">
                      <i class="fa-solid fa-ellipsis"></i>
                    </button>
                  </td>
                </tr>
                <tr>
                  <td style="font-weight: 600; color: var(--text-primary);">Jan 10, 2026</td>
                  <td>Annual Membership Renewal</td>
                  <td><span class="tx-ref-code">Txn- 681</span></td>
                  <td style="font-weight: 600; color: var(--text-primary);">&#8358;15,000</td>
                  <td><span class="status-text-attended">Completed</span></td>
                  <td style="text-align: right;">
                    <button class="btn-action-trigger" aria-label="Options">
                      <i class="fa-solid fa-ellipsis"></i>
                    </button>
                  </td>
                </tr>
                <tr>
                  <td style="font-weight: 600; color: var(--text-primary);">Dec 5, 2025</td>
                  <td>Event Registration -Gala nite</td>
                  <td><span class="tx-ref-code">Txn-848</span></td>
                  <td style="font-weight: 600; color: var(--text-primary);">&#8358;2,500</td>
                  <td><span class="status-text-attended">Completed</span></td>
                  <td style="text-align: right;">
                    <button class="btn-action-trigger" aria-label="Options">
                      <i class="fa-solid fa-ellipsis"></i>
                    </button>
                  </td>
                </tr>
                <tr>
                  <td style="font-weight: 600; color: var(--text-primary);">Feb 10, 2025</td>
                  <td>Mid- Year Dues</td>
                  <td><span class="tx-ref-code">Txn-580</span></td>
                  <td style="font-weight: 600; color: var(--text-primary);">&#8358;10,000</td>
                  <td><span class="status-text-attended">Completed</span></td>
                  <td style="text-align: right;">
                    <button class="btn-action-trigger" aria-label="Options">
                      <i class="fa-solid fa-ellipsis"></i>
                    </button>
                  </td>
                </tr>
                <tr>
                  <td style="font-weight: 600; color: var(--text-primary);">Feb 10, 2026</td>
                  <td>Workshop Registration</td>
                  <td><span class="tx-ref-code">Txn-988</span></td>
                  <td style="font-weight: 600; color: var(--text-primary);">&#8358;3,500</td>
                  <td><span class="status-text-refunded">Refunded</span></td>
                  <td style="text-align: right;">
                    <button class="btn-action-trigger" aria-label="Options">
                      <i class="fa-solid fa-ellipsis"></i>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </section>
        </div>

        <?php include('../inc/footer.php') ?>
      </main>
    </div>

    <script>
      const sidebar = document.getElementById("adminSidebar");
      const sidebarToggle = document.getElementById("sidebarToggle");
      const sidebarCloseBtn = document.getElementById("sidebarCloseBtn");

      if (sidebarToggle) {
        sidebarToggle.addEventListener("click", () => {
          sidebar.classList.toggle("open");
          console.log("Sidebar opened")
        });
      }

      if (sidebarCloseBtn) {
        sidebarCloseBtn.addEventListener("click", () => {
          sidebar.classList.remove("open");
          console.log("Sidebar closed");
        });
      }
    </script>
    <script src="../../js/preloader.js"></script>
  </body>
</html>