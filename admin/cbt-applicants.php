<?php
require_once "inc/auth.php";
require_once "../inc/db.php";

$adminRole = $_SESSION['admin_role'] ?? 'admin';
$orgId = isset($_SESSION['org_id']) && $_SESSION['org_id'] !== null ? (int) $_SESSION['org_id'] : null;

$applicantsSql = "SELECT id, application_number, applicant_name, email, phone, status, applied_at, cbt_scheduled_at FROM admissions WHERE status = 'under_review'";
if ($adminRole !== 'super_admin' && $orgId !== null) {
  $applicantsSql .= " AND org_id = " . (int) $orgId;
}
$applicantsSql .= " ORDER BY applied_at DESC";
$applicantsResult = mysqli_query($conn, $applicantsSql);

$applicants = [];
if (!$applicantsResult) {
  error_log('CBT applicants query failed: ' . mysqli_error($conn));
  http_response_code(500);
  exit('CBT applicants are temporarily unavailable.');
}
while ($applicant = mysqli_fetch_assoc($applicantsResult)) {
    $applicants[] = $applicant;
}

$admissionStatsSql = 'SELECT status, COUNT(*) AS total FROM admissions';
if ($adminRole !== 'super_admin' && $orgId !== null) {
  $admissionStatsSql .= ' WHERE org_id = ' . (int) $orgId;
}
$admissionStatsSql .= ' GROUP BY status';
$admissionStatsResult = mysqli_query($conn, $admissionStatsSql);
$admissionStats = ['pending' => 0, 'under_review' => 0, 'cbt_completed' => 0, 'approved' => 0, 'rejected' => 0];
if (!$admissionStatsResult) {
  error_log('CBT applicant statistics query failed: ' . mysqli_error($conn));
  http_response_code(500);
  exit('CBT applicant statistics are temporarily unavailable.');
}
while ($stat = mysqli_fetch_assoc($admissionStatsResult)) {
  if (array_key_exists($stat['status'], $admissionStats)) {
    $admissionStats[$stat['status']] = (int) $stat['total'];
  }
}
$totalApplications = array_sum($admissionStats);
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>CBT Applicants - Associa8</title>

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
    <link rel="stylesheet" href="../css/dashboard.css?v=20261008-infotips-3" />
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
            <h1 class="page-title">CBT Management</h1>
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
              <h2 class="page-title-main">CBT Applicant</h2>
              <p class="page-subtitle">CBT Schedule & Onboarding</p>
            </div>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
              <button class="btn-outline-primary">
                <i class="fa-solid fa-file-export"></i>
                <span>Export Participant</span>
              </button>
              <button class="btn-outline-primary">
                <i class="fa-solid fa-upload"></i>
                <span>Bulk Upload</span>
              </button>
            </div>
          </div>

          <!-- Summary Stat Cards Grid -->
          <section class="stats-grid mb-4">
  <div class="suspension-stat-card">
    <div class="suspension-stat-value"><?php echo $totalApplications; ?></div>
    <div class="suspension-stat-label">Total Applications</div>
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
          <div class="table-responsive-card">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Full Name</th>
                  <th>Application No.</th>
                  <th>Email</th>
                  <th>Phone</th>
                  <th>Applied Date</th>
                  <th>CBT Scheduled</th>
                  <th style="text-align: right;">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($applicants): ?>
                  <?php foreach ($applicants as $applicant): ?>
                    <tr>
                      <td><?php echo (int) $applicant['id']; ?></td>
                      <td><?php echo htmlspecialchars($applicant['applicant_name']); ?></td>
                      <td><?php echo htmlspecialchars($applicant['application_number']); ?></td>
                      <td><?php echo htmlspecialchars($applicant['email']); ?></td>
                      <td><?php echo htmlspecialchars($applicant['phone']); ?></td>
                      <td><?php echo htmlspecialchars($applicant['applied_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                      <td><?php echo $applicant['cbt_scheduled_at'] === null ? 'Not scheduled' : htmlspecialchars((new DateTimeImmutable($applicant['cbt_scheduled_at'], new DateTimeZone('UTC')))->setTimezone(new DateTimeZone('Africa/Lagos'))->format('Y-m-d H:i') . ' (Africa/Lagos)', ENT_QUOTES, 'UTF-8'); ?></td>
                      <td style="text-align: right;"><span class="badge-pill status-under-review"><?php echo ucwords(str_replace('_', ' ', $applicant['status'])); ?></span></td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                <tr><td colspan="8" class="zone-empty-state">There are no applicants currently under review for CBT.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Footer -->
        <?php include('inc/footer.php')?>
      </main>
    </div>

    <!-- Interactive Scripts -->
    <script>
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