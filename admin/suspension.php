<?php
require_once "inc/auth.php";
require_once "../inc/db.php";
$page = max(1, (int) ($_GET['page'] ?? 1));
$itemsPerPage = 10;
$suspensionStatsResult = mysqli_query($conn, "SELECT status, COUNT(*) AS total FROM suspensions GROUP BY status");
$suspensionStats = ['active' => 0, 'under_review' => 0, 'completed' => 0];
if ($suspensionStatsResult) {
  while ($stat = mysqli_fetch_assoc($suspensionStatsResult)) {
    $suspensionStats[$stat['status']] = (int) $stat['total'];
  }
}
$recordCountResult = mysqli_query($conn, "SELECT COUNT(*) AS total FROM suspensions");
$recordCount = $recordCountResult ? (int) mysqli_fetch_assoc($recordCountResult)['total'] : 0;
$totalPages = max(1, (int) ceil($recordCount / $itemsPerPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $itemsPerPage;
$recordsResult = mysqli_query($conn, "SELECT s.id, s.reason, s.action_type, s.status, s.action_date, m.first_name, m.last_name, m.member_code FROM suspensions s INNER JOIN members m ON m.id = s.member_id ORDER BY s.action_date DESC, s.id DESC LIMIT $itemsPerPage OFFSET $offset");
$suspensionRecords = [];
if ($recordsResult) {
  while ($record = mysqli_fetch_assoc($recordsResult)) {
    $suspensionRecords[] = $record;
  }
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Suspension & Reinstatement - Associa8</title>
    
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
    
    <!-- Fav Icon -->
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
    <?php include('inc/preloader.php'); ?>

    <div class="admin-layout">
      
      <!-- SIDEBAR NAVIGATION -->
      <?php include('inc/sidebar.php'); ?>

      <!-- MAIN CONTENT AREA -->
      <main class="admin-main">
        
        <!-- Top Navigation Header -->
        <header class="admin-header">
          <div class="header-left">
            <button class="header-toggle-btn" id="sidebarToggle">
              <i class="fa-solid fa-bars"></i>
            </button>
            <h1 class="page-title">Suspension & Reinstatement</h1>
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

          <!-- Page Header & Actions -->
          <div class="page-action-header mb-4">
            <div>
              <h2 class="page-title-main">Suspension & Reinstatement</h2>
              <p class="page-subtitle">Manage member disciplinary actions and appeals.</p>
            </div>
            <div class="d-flex gap-2">
              <a href="add-suspension.php?type=suspension" class="btn-action-danger-outline">
                <i class="fa-ban"></i>
                <span>Suspended</span>
              </a>
              <a href="add-suspension.php?type=reinstatement" class="btn-action-dark-outline">
                <i class="fa-solid fa-user-plus"></i>
                <span>Reinstate</span>
              </a>
            </div>
          </div>

          <!-- Top Summary Stat Cards Grid -->
          <section class="suspension-stats-grid">
            <div class="suspension-stat-card">
              <div class="suspension-stat-value text-danger"><?php echo $suspensionStats['active']; ?></div>
              <div class="suspension-stat-label">Active Suspended</div>
            </div>
            <div class="suspension-stat-card">
              <div class="suspension-stat-value text-primary"><?php echo $suspensionStats['under_review']; ?></div>
              <div class="suspension-stat-label">Under-Review</div>
            </div>
            <div class="suspension-stat-card">
              <div class="suspension-stat-value text-dark"><?php echo $suspensionStats['completed']; ?></div>
              <div class="suspension-stat-label">Reinstate</div>
            </div>
          </section>

          <!-- Suspension Records Table -->
          <section class="table-responsive-card">
            <table class="custom-admin-table" data-record-count="<?php echo $recordCount; ?>" data-server-pagination="true">
              <thead>
                <tr>
                  <th>Member</th>
                  <th>Reason</th>
                  <th>Type</th>
                  <th>Date</th>
                  <th>Status</th>
                  <th style="width: 40px;"></th>
                </tr>
              </thead>
              <tbody>
                <?php if ($suspensionRecords): ?>
                  <?php foreach ($suspensionRecords as $record): ?>
                    <?php
                      $fullName = $record['first_name'] . ' ' . $record['last_name'];
                      $initials = strtoupper(substr($record['first_name'], 0, 1) . substr($record['last_name'], 0, 1));
                      $statusClass = $record['status'] === 'under_review' ? 'status-under-review' : ($record['status'] === 'completed' ? 'status-completed' : 'status-active');
                      $typeClass = $record['action_type'] === 'reinstatement' ? 'type-reinstatement' : 'type-suspension';
                    ?>
                    <tr>
                      <td><div class="table-member-profile"><div class="member-avatar"><?php echo htmlspecialchars($initials); ?></div><div class="member-meta"><span class="member-name"><?php echo htmlspecialchars($fullName); ?></span><span class="member-id"><?php echo htmlspecialchars($record['member_code']); ?></span></div></div></td>
                      <td><?php echo htmlspecialchars($record['reason']); ?></td>
                      <td><span class="badge-pill <?php echo $typeClass; ?>"><?php echo ucfirst($record['action_type']); ?></span></td>
                      <td><?php echo htmlspecialchars($record['action_date']); ?></td>
                      <td><span class="badge-pill <?php echo $statusClass; ?>"><?php echo ucwords(str_replace('_', ' ', $record['status'])); ?></span></td>
                      <td><button class="btn-row-action" type="button" aria-label="More actions"><i class="fa-solid fa-ellipsis"></i></button></td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="6" class="zone-empty-state">No suspension or reinstatement records have been added yet.</td>
                  </tr>
                <?php endif; ?>

              </tbody>
            </table>

            <!-- Table Pagination Footer -->
            <div class="table-footer">
              <span class="table-footer-info"><?php echo $recordCount ? 'Showing ' . ($offset + 1) . '-' . min($offset + $itemsPerPage, $recordCount) . ' of ' . $recordCount : 'Showing 0-0 of 0'; ?></span>
              <?php include('inc/pagination.php'); renderPagination($page, $recordCount, $itemsPerPage); ?>
            </div>
          </section>

        </div>

        <!-- FOOTER -->
        <?php include('inc/footer.php'); ?>

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
          heading: success ? "Action saved successfully" : "Action could not be saved",
          body: params.get("msg") || (success ? "The member status and history were updated." : "Please review the action and try again."),
          detail: success ? "The updated record is now visible in the suspension history." : "No member status change was committed.",
        });
        window.history.replaceState({}, document.title, window.location.pathname);
      });
    </script>
    <script>
      // 1. Sidebar Dropdown Accordion Toggle Logic
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