<?php
require_once "inc/auth.php";
require_once "../inc/db.php";

$admissionStatsResult = mysqli_query($conn, "SELECT status, COUNT(*) AS total FROM admissions GROUP BY status");
$admissionStats = ['pending' => 0, 'under_review' => 0, 'approved' => 0, 'rejected' => 0];

if ($admissionStatsResult) { 
  while ($stat = mysqli_fetch_assoc($admissionStatsResult)) { 
    $admissionStats[$stat['status']] = (int) $stat['total']; 
  } 
}

$admissionsResult = mysqli_query($conn, "SELECT id, application_number, applicant_name, email, guarantor_name, guarantor_relationship, status, applied_at FROM admissions ORDER BY applied_at DESC");

$admissions = [];
if ($admissionsResult) { 
  while ($admission = mysqli_fetch_assoc($admissionsResult)) {
    $admissions[] = $admission; 
  } 
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admission Management - Associa8</title>

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
            <h1 class="page-title">Admission Management</h1>
          </div>

          <div class="header-right">
            <div class="header-search" style="border-right: 1px solid #e2e8f0; padding-right: 12px;">
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
              <h2 class="page-title-main">Admission Management</h2>
              <p class="page-subtitle">Schedules, Applications, CBT, Sponsors & Onboarding</p>
            </div>
            <a href="add-admission.php" class="btn-outline-primary">
              <i class="fa-solid fa-user-plus"></i>
              <span>New Application</span>
            </a>
          </div>

          <!-- Summary Stat Cards Grid -->
          <section class="stats-grid mb-4">
            <div class="suspension-stat-card">
              <div class="suspension-stat-value"><?php echo $admissionStats['pending'] + $admissionStats['under_review']; ?></div>
              <div class="suspension-stat-label">Application Review</div>
            </div>
            <div class="suspension-stat-card">
              <div class="suspension-stat-value"><?php echo $admissionStats['under_review']; ?></div>
              <div class="suspension-stat-label">CBT Review</div>
            </div>
            <div class="suspension-stat-card">
              <div class="suspension-stat-value"><?php echo $admissionStats['pending']; ?></div>
              <div class="suspension-stat-label">Onboarding</div>
            </div>
            <div class="suspension-stat-card">
              <div class="suspension-stat-value text-primary"><?php echo $admissionStats['approved']; ?></div>
              <div class="suspension-stat-label">Approved</div>
            </div>
          </section>
          <!-- Data Table Card -->
          <div class="table-responsive-card admission-table-card">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Applicant</th>
                  <th>Guarantor</th>
                  <th>Stage</th>
                  <th>Date</th>
                  <th>CBT Score</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <?php if ($admissions): ?>
                  <?php foreach ($admissions as $admission): ?>
                    <?php $statusClass = $admission['status'] === 'approved' ? 'status-active' : ($admission['status'] === 'rejected' ? 'status-inactive' : 'status-under-review'); ?>
                    <tr>
                      <td>
                        <div class="member-cell">
                          <div class="member-avatar">
                            <?php echo htmlspecialchars(strtoupper(substr($admission['applicant_name'], 0, 2))); ?>
                          </div>
                          <div class="member-info">
                            <span class="member-name"><?php echo htmlspecialchars($admission['applicant_name']); ?></span>
                            <span class="member-email"><?php echo htmlspecialchars($admission['email']); ?></span>
                          </div>
                        </div>
                      </td>
                      <td>
                        <div class="member-info">
                          <span class="member-name"><?php echo htmlspecialchars($admission['guarantor_name'] ?: 'Not provided'); ?></span>
                          <span class="member-email"><?php echo htmlspecialchars($admission['guarantor_relationship'] ?: ''); ?></span>
                        </div>
                      </td>
                      <td>
                        <span class="badge-pill <?php echo $statusClass; ?>">
                          <?php echo ucwords(str_replace('_', ' ', $admission['status'])); ?>
                        </span>
                      </td>
                      <td><?php echo htmlspecialchars($admission['applied_at']); ?></td>
                      <td>
                        <span class="badge-pill dues-arrears">Pending</span>
                      </td>
                      <td>
                        <?php if (in_array($admission['status'], ['pending', 'under_review'], true)): ?>
                          <div class="dropdown admission-actions">
                            <button class="btn-action-trigger" type="button" aria-label="Admission actions" aria-expanded="false">
                              <i class="fa-solid fa-ellipsis"></i>
                            </button>
                            <div class="dropdown-menu">
                              <?php if ($admission['status'] === 'pending'): ?>
                                <form action="proc-update-admission-status.php" method="POST">
                                  <input type="hidden" name="admission_id" value="<?php echo (int) $admission['id']; ?>" />
                                  <input type="hidden" name="status" value="under_review" />
                                  <button class="dropdown-item" type="submit"><i class="fa-solid fa-forward"></i> Move to CBT</button>
                                </form>
                              <?php else: ?>
                                <form action="proc-update-admission-status.php" method="POST">
                                  <input type="hidden" name="admission_id" value="<?php echo (int) $admission['id']; ?>" />
                                  <input type="hidden" name="status" value="approved" />
                                  <button class="dropdown-item" type="submit"><i class="fa-solid fa-check"></i> Approve</button>
                                </form>
                                <form action="proc-update-admission-status.php" method="POST">
                                  <input type="hidden" name="admission_id" value="<?php echo (int) $admission['id']; ?>" />
                                  <input type="hidden" name="status" value="rejected" />
                                  <button class="dropdown-item danger-item" type="submit"><i class="fa-solid fa-xmark"></i> Reject</button>
                                </form>
                              <?php endif; ?>
                            </div>
                          </div>
                        <?php else: ?>
                          <span class="badge-pill <?php echo $statusClass; ?>">Final</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                <!-- Row 1 -->
                <tr>
                  <td>
                    <div class="member-cell">
                      <div class="member-avatar">CO</div>
                      <div class="member-info">
                        <span class="member-name">Chukwuemeka Obi</span>
                        <span class="member-email">c.obi@gmail.com</span>
                      </div>
                    </div>
                  </td>
                  <td>Guarantor placeholder</td>
                  <td><span class="badge-pill type-reinstatement">CBT Schedule</span></td>
                  <td>20-05-2026</td>
                  <td><span class="badge-pill dues-arrears">Pending</span></td>
                  <td>
                    <button class="btn-action-trigger" aria-label="Options">
                      <i class="fa-solid fa-ellipsis"></i>
                    </button>
                  </td>
                </tr>
                <!-- Row 2 -->
                <tr>
                  <td>
                    <div class="member-cell">
                      <div class="member-avatar">CO</div>
                      <div class="member-info">
                        <span class="member-name">Chukwuemeka Obi</span>
                        <span class="member-email">c.obi@gmail.com</span>
                      </div>
                    </div>
                  </td>
                  <td>Guarantor placeholder</td>
                  <td><span class="badge-pill type-suspension">Onboarding</span></td>
                  <td>20-05-2026</td>
                  <td><span class="badge-pill dues-current">78%</span></td>
                  <td>
                    <button class="btn-action-trigger" aria-label="Options">
                      <i class="fa-solid fa-ellipsis"></i>
                    </button>
                  </td>
                </tr>

                <!-- Row 3 -->
                <tr>
                  <td>
                    <div class="member-cell">
                      <div class="member-avatar">CO</div>
                      <div class="member-info">
                        <span class="member-name">Chukwuemeka Obi</span>
                        <span class="member-email">c.obi@gmail.com</span>
                      </div>
                    </div>
                  </td>
                  <td>Guarantor placeholder</td>
                  <td><span class="badge-pill status-under-review">Application Review</span></td>
                  <td>20-05-2026</td>
                  <td><span class="badge-pill dues-arrears">Pending</span></td>
                  <td>
                    <button class="btn-action-trigger" aria-label="Options">
                      <i class="fa-solid fa-ellipsis"></i>
                    </button>
                  </td>
                </tr>

                <!-- Row 4 -->
                <tr>
                  <td>
                    <div class="member-cell">
                      <div class="member-avatar">CO</div>
                      <div class="member-info">
                        <span class="member-name">Chukwuemeka Obi</span>
                        <span class="member-email">c.obi@gmail.com</span>
                      </div>
                    </div>
                  </td>
                  <td>Guarantor placeholder</td>
                  <td><span class="badge-pill status-active">Approved</span></td>
                  <td>20-05-2026</td>
                  <td><span class="badge-pill dues-current">85%</span></td>
                  <td>
                    <button class="btn-action-trigger" aria-label="Options">
                      <i class="fa-solid fa-ellipsis"></i>
                    </button>
                  </td>
                </tr>

                <!-- Row 5 -->
                <tr>
                  <td>
                    <div class="member-cell">
                      <div class="member-avatar">CO</div>
                      <div class="member-info">
                        <span class="member-name">Chukwuemeka Obi</span>
                        <span class="member-email">c.obi@gmail.com</span>
                      </div>
                    </div>
                  </td>
                  <td>Guarantor placeholder</td>
                  <td><span class="badge-pill status-inactive">Rejected</span></td>
                  <td>20-05-2026</td>
                  <td><span class="badge-pill status-inactive">2%</span></td>
                  <td>
                    <button class="btn-action-trigger" aria-label="Options">
                      <i class="fa-solid fa-ellipsis"></i>
                    </button>
                  </td>
                </tr>
                <?php endif; ?>
              </tbody>
            </table>

            <!-- Table Pagination Footer -->
            <div class="table-pagination-footer">
              <span>Showing 5 of 5</span>
              <div class="pagination-controls">
                <button class="page-btn">Prev</button>
                <button class="page-btn active">1</button>
                <button class="page-btn">Next</button>
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
      window.addEventListener("DOMContentLoaded", () => {
        const params = new URLSearchParams(window.location.search);
        const status = params.get("status");
        if (!status || !window.AppModal) return;

        const success = status === "success";
        window.AppModal.open({
          type: success ? "success" : "error",
          heading: success ? "Admission status updated" : "Admission status not updated",
          body: params.get("msg") || "Please try again.",
          detail: success ? "The applicant will now appear in the dashboard stage that matches the new status." : "No admission status was changed.",
        });
        window.history.replaceState({}, document.title, window.location.pathname);
      });
    </script>
    <script>
      window.addEventListener("DOMContentLoaded", () => {
        const params = new URLSearchParams(window.location.search);
        if (!params.get("status") || !window.AppModal) return;
        const success = params.get("status") === "success";
        window.AppModal.open({
          type: success ? "success" : "error",
          heading: success ? "Admission saved" : "Admission not saved",
          body: params.get("msg") || (success ? "The application is now in the review queue." : "Please try again."),
          detail: success ? "The applicant can now move through the admission stages." : "No admission record was created.",
        });
        window.history.replaceState({}, document.title, window.location.pathname);
      });
    </script>
    <script>
      // 1. Sidebar Dropdown Accordion Toggle Logic
      document.querySelectorAll(".admission-actions").forEach((dropdown) => {
        const trigger = dropdown.querySelector(".btn-action-trigger");

        trigger.addEventListener("click", (event) => {
          event.stopPropagation();
          document.querySelectorAll(".admission-actions.show").forEach((openDropdown) => {
            if (openDropdown !== dropdown) {
              openDropdown.classList.remove("show");
              openDropdown.querySelector(".btn-action-trigger").setAttribute("aria-expanded", "false");
            }
          });
          const isOpen = dropdown.classList.toggle("show");
          trigger.setAttribute("aria-expanded", String(isOpen));
        });
      });

      document.addEventListener("click", () => {
        document.querySelectorAll(".admission-actions.show").forEach((dropdown) => {
          dropdown.classList.remove("show");
          dropdown.querySelector(".btn-action-trigger").setAttribute("aria-expanded", "false");
        });
      });

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