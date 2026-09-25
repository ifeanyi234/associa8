<?php
require_once "inc/auth.php";
require_once "../inc/db.php";

$adminRole = $_SESSION['admin_role'] ?? 'admin';
$orgId = isset($_SESSION['org_id']) && $_SESSION['org_id'] !== null ? (int) $_SESSION['org_id'] : null;

$memberCountSql = "SELECT COUNT(*) AS total FROM members";
if ($adminRole !== 'super_admin' && $orgId !== null) {
  $memberCountSql .= " WHERE org_id = " . (int) $orgId;
}
$memberCountResult = mysqli_query($conn, $memberCountSql);
$memberCount = $memberCountResult ? (int) mysqli_fetch_assoc($memberCountResult)['total'] : 0;
$page = max(1, (int) ($_GET['page'] ?? 1));
$itemsPerPage = 10;
$search = trim($_GET['q'] ?? '');
$statusFilter = $_GET['status'] ?? '';
$where = '';
if ($adminRole !== 'super_admin' && $orgId !== null) {
  $where .= " WHERE m.org_id = " . (int) $orgId;
}
if ($search !== '') {
  $safeSearch = mysqli_real_escape_string($conn, $search);
  $where .= $where === '' ? ' WHERE ' : ' AND ';
  $where .= "(CONCAT(m.first_name, ' ', m.last_name) LIKE '%$safeSearch%' OR m.member_code LIKE '%$safeSearch%' OR m.email LIKE '%$safeSearch%')";
}
if (in_array($statusFilter, ['active', 'pending', 'suspended'], true)) {
  $where .= $where === '' ? ' WHERE ' : ' AND ';
  $where .= "m.status = '" . mysqli_real_escape_string($conn, $statusFilter) . "'";
}
$filteredCountResult = mysqli_query($conn, "SELECT COUNT(*) AS total FROM members m$where");
$filteredCount = $filteredCountResult ? (int) mysqli_fetch_assoc($filteredCountResult)['total'] : 0;
$totalPages = max(1, (int) ceil($filteredCount / $itemsPerPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $itemsPerPage;
$membersResult = mysqli_query($conn, "SELECT m.id, m.member_code, m.first_name, m.last_name, m.email, m.status, m.joined_date, t.title, z.name AS zone_name, sz.name AS subzone_name FROM members m LEFT JOIN titles t ON t.id = m.title_id LEFT JOIN zones z ON z.id = m.zone_id LEFT JOIN subzones sz ON sz.id = m.subzone_id$where ORDER BY m.created_at DESC, m.id DESC LIMIT $itemsPerPage OFFSET $offset");
$members = [];
if ($membersResult) {
  while ($member = mysqli_fetch_assoc($membersResult)) {
    $members[] = $member;
  }
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Member Directory - Associa8</title>

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
            <h1 class="page-title">Members Directory</h1>
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
          <div class="page-action-header">
            <div>
              <h2 class="page-title-main">Member Directory</h2>
              <p class="page-subtitle"><?php echo $memberCount; ?> total members across all zones</p>
            </div>
            <a href="add-member.php" class="btn-outline-primary">
              <i class="fa-solid fa-user-plus"></i>
              <span>Add members</span>
            </a>
          </div>

          <!-- Search & Status Filter Toolbar -->
          <div class="directory-toolbar">
            <div class="toolbar-search">
              <i class="fa-solid fa-magnifying-glass search-icon"></i>
              <input
                type="text"
                placeholder="Search by name or member code..."
              />
            </div>

            <div class="filter-pill-group">
              <button class="filter-pill active">All</button>
              <button class="filter-pill">Active</button>
              <button class="filter-pill">Suspended</button>
              <button class="filter-pill">Pending</button>
            </div>
          </div>

          <!-- Directory Data Table Card -->
          <div class="table-responsive-card member-directory-table-card">
            <table class="admin-table" data-record-count="<?php echo $memberCount; ?>" data-server-pagination="true">
              <thead>
                <tr>
                  <th>Member</th>
                  <th>Code</th>
                  <th>Title</th>
                  <th>Zone</th>
                  <th>Status</th>
                  <th>Dues</th>
                  <th>Joined</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <?php if ($members): ?>
                  <?php foreach ($members as $member): ?>
                    <?php
                      $fullName = $member['first_name'] . ' ' . $member['last_name'];
                      $initials = strtoupper(substr($member['first_name'], 0, 1) . substr($member['last_name'], 0, 1));
                      $statusClass = $member['status'] === 'active' ? 'status-active' : ($member['status'] === 'suspended' ? 'status-suspension' : 'status-inactive');
                      $zoneLabel = $member['zone_name'] ?: 'Unassigned';
                      $subzoneLabel = $member['subzone_name'] ?: '';
                    ?>
                    <tr>
                      <td><div class="member-cell"><div class="member-avatar"><?php echo htmlspecialchars($initials); ?></div><div class="member-info"><span class="member-name"><?php echo htmlspecialchars($fullName); ?></span><span class="member-email"><?php echo htmlspecialchars($member['email']); ?></span></div></div></td>
                      <td><?php echo htmlspecialchars($member['member_code']); ?></td>
                      <td><span class="cell-with-icon"><i class="fa-solid fa-shield-halved"></i> <?php echo htmlspecialchars($member['title'] ?: 'Unassigned'); ?></span></td>
                      <td>
                        <div class="cell-stack">
                          <span class="cell-with-icon"><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($zoneLabel); ?></span>
                          <?php if ($subzoneLabel): ?>
                            <small class="cell-subtext"><?php echo htmlspecialchars($subzoneLabel); ?></small>
                          <?php endif; ?>
                        </div>
                      </td>
                      <td><span class="badge-pill <?php echo $statusClass; ?>"><?php echo ucfirst($member['status']); ?></span></td>
                      <td><span class="badge-pill dues-current">Not available</span></td>
                      <td><?php echo htmlspecialchars($member['joined_date'] ?: 'Not provided'); ?></td>
                      <td>
                        <div class="dropdown admission-actions">
                          <button class="btn-action-trigger" type="button" aria-label="Member actions" aria-expanded="false"><i class="fa-solid fa-ellipsis"></i></button>
                          <div class="dropdown-menu">
                            <a class="dropdown-item" href="edit-member.php?id=<?php echo (int) $member['id']; ?>"><i class="fa-solid fa-pen"></i> Edit</a>
                            <form action="proc-delete-member.php" method="POST" onsubmit="return confirm('Delete this member? This cannot be undone.');">
                              <input type="hidden" name="member_id" value="<?php echo (int) $member['id']; ?>" />
                              <button class="dropdown-item danger-item" type="submit"><i class="fa-solid fa-trash"></i> Delete</button>
                            </form>
                          </div>
                        </div>
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
                  <td>ASC-001</td>
                  <td>
                    <span class="cell-with-icon">
                      <i class="fa-solid fa-shield-halved"></i> Fellow
                    </span>
                  </td>
                  <td>
                    <span class="cell-with-icon">
                      <i class="fa-solid fa-location-dot"></i> Lagos
                    </span>
                  </td>
                  <td><span class="badge-pill status-active">Active</span></td>
                  <td><span class="badge-pill dues-current">Current</span></td>
                  <td>20-05-2026</td>
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
                      <div class="member-avatar">AP</div>
                      <div class="member-info">
                        <span class="member-name">Adamu Philips</span>
                        <span class="member-email">ph_2gmail.com</span>
                      </div>
                    </div>
                  </td>
                  <td>ASC-002</td>
                  <td>
                    <span class="cell-with-icon">
                      <i class="fa-solid fa-shield-halved"></i> Associate
                    </span>
                  </td>
                  <td>
                    <span class="cell-with-icon">
                      <i class="fa-solid fa-location-dot"></i> Kano
                    </span>
                  </td>
                  <td>
                    <span class="badge-pill status-suspension">Suspension</span>
                  </td>
                  <td><span class="badge-pill dues-arrears">Arrears</span></td>
                  <td>20-05-2026</td>
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
                      <div class="member-avatar">JK</div>
                      <div class="member-info">
                        <span class="member-name">James Kehinde</span>
                        <span class="member-email">j.kn2@gmail.com</span>
                      </div>
                    </div>
                  </td>
                  <td>ASC-003</td>
                  <td>
                    <span class="cell-with-icon">
                      <i class="fa-solid fa-shield-halved"></i> Sen Fellow
                    </span>
                  </td>
                  <td>
                    <span class="cell-with-icon">
                      <i class="fa-solid fa-location-dot"></i> Abuja
                    </span>
                  </td>
                  <td><span class="badge-pill status-active">Active</span></td>
                  <td><span class="badge-pill dues-current">Current</span></td>
                  <td>20-05-2026</td>
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
                  <td>ASC-004</td>
                  <td>
                    <span class="cell-with-icon">
                      <i class="fa-solid fa-shield-halved"></i> Fellow
                    </span>
                  </td>
                  <td>
                    <span class="cell-with-icon">
                      <i class="fa-solid fa-location-dot"></i> Lagos
                    </span>
                  </td>
                  <td><span class="badge-pill status-active">Active</span></td>
                  <td><span class="badge-pill dues-current">Current</span></td>
                  <td>20-05-2026</td>
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
                  <td>ASC-005</td>
                  <td>
                    <span class="cell-with-icon">
                      <i class="fa-solid fa-shield-halved"></i> Fellow
                    </span>
                  </td>
                  <td>
                    <span class="cell-with-icon">
                      <i class="fa-solid fa-location-dot"></i> Lagos
                    </span>
                  </td>
                  <td><span class="badge-pill status-active">Active</span></td>
                  <td><span class="badge-pill dues-current">Current</span></td>
                  <td>20-05-2026</td>
                  <td>
                    <button class="btn-action-trigger" aria-label="Options">
                      <i class="fa-solid fa-ellipsis"></i>
                    </button>
                  </td>
                </tr>

                <!-- Row 6 -->
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
                  <td>ASC-006</td>
                  <td>
                    <span class="cell-with-icon">
                      <i class="fa-solid fa-shield-halved"></i> Fellow
                    </span>
                  </td>
                  <td>
                    <span class="cell-with-icon">
                      <i class="fa-solid fa-location-dot"></i> Lagos
                    </span>
                  </td>
                  <td><span class="badge-pill status-active">Active</span></td>
                  <td><span class="badge-pill dues-current">Current</span></td>
                  <td>20-05-2026</td>
                  <td>
                    <button class="btn-action-trigger" aria-label="Options">
                      <i class="fa-solid fa-ellipsis"></i>
                    </button>
                  </td>
                </tr>

                <!-- Row 7 -->
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
                  <td>ASC-007</td>
                  <td>
                    <span class="cell-with-icon">
                      <i class="fa-solid fa-shield-halved"></i> Fellow
                    </span>
                  </td>
                  <td>
                    <span class="cell-with-icon">
                      <i class="fa-solid fa-location-dot"></i> Lagos
                    </span>
                  </td>
                  <td><span class="badge-pill status-active">Active</span></td>
                  <td><span class="badge-pill dues-current">Current</span></td>
                  <td>20-05-2026</td>
                  <td>
                    <button class="btn-action-trigger" aria-label="Options">
                      <i class="fa-solid fa-ellipsis"></i>
                    </button>
                  </td>
                </tr>

                <!-- Row 8 -->
                <tr>
                  <td>
                    <div class="member-cell">
                      <div class="member-avatar">JP</div>
                      <div class="member-info">
                        <span class="member-name">Joy Peters</span>
                        <span class="member-email">pj1_2gmail.com</span>
                      </div>
                    </div>
                  </td>
                  <td>ASC-008</td>
                  <td>
                    <span class="cell-with-icon">
                      <i class="fa-solid fa-shield-halved"></i> Member
                    </span>
                  </td>
                  <td>
                    <span class="cell-with-icon">
                      <i class="fa-solid fa-location-dot"></i> Port-Har..
                    </span>
                  </td>
                  <td>
                    <span class="badge-pill status-inactive">Inactive</span>
                  </td>
                  <td><span class="badge-pill dues-current">Current</span></td>
                  <td>20-05-2026</td>
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
              <span><?php echo $filteredCount ? 'Showing ' . ($offset + 1) . '-' . min($offset + $itemsPerPage, $filteredCount) . ' of ' . $filteredCount : 'Showing 0-0 of 0'; ?></span>
              <?php include('inc/pagination.php'); renderPagination($page, $filteredCount, $itemsPerPage, ['q' => $search, 'status' => $statusFilter]); ?>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <?php include('inc/footer.php')?>
      </main>
    </div>

    <!-- Interactive Scripts -->
    <script>
      // 1. Sidebar Dropdown Dynamic Accordion Logic
      const dropdownItems = document.querySelectorAll(".sidebar-item.dropdown");

      dropdownItems.forEach((item) => {
        const link = item.querySelector(".sidebar-link");
        const submenu = item.querySelector(".sidebar-submenu");

        link.addEventListener("click", (e) => {
          e.preventDefault();
          const isOpen = item.classList.contains("open");

          // Close other open dropdowns
          dropdownItems.forEach((otherItem) => {
            if (otherItem !== item) {
              otherItem.classList.remove("open");
              const otherSub = otherItem.querySelector(".sidebar-submenu");
              if (otherSub) otherSub.style.maxHeight = null;
            }
          });

          // Toggle target dropdown
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

      document.querySelectorAll(".admission-actions").forEach((dropdown) => {
        const trigger = dropdown.querySelector(".btn-action-trigger");
        trigger.addEventListener("click", (event) => {
          event.stopPropagation();
          document.querySelectorAll(".admission-actions.show").forEach((openDropdown) => {
            if (openDropdown !== dropdown) openDropdown.classList.remove("show");
          });
          dropdown.classList.toggle("show");
        });
      });
      document.addEventListener("click", () => document.querySelectorAll(".admission-actions.show").forEach((dropdown) => dropdown.classList.remove("show")));

      // 3. Filter Pill Toggle State
      const filterPills = document.querySelectorAll(".filter-pill");
      filterPills.forEach((pill) => {
        pill.addEventListener("click", () => {
          filterPills.forEach((p) => p.classList.remove("active"));
          pill.classList.add("active");
        });
      });
    </script>
    <script src="../js/preloader.js"></script>
  </body>
</html>
