<?php
require_once "../inc/db.php";
$zoneStatsResult = mysqli_query($conn, "SELECT COUNT(*) AS total_zones FROM zones");
$zoneStats = $zoneStatsResult ? mysqli_fetch_assoc($zoneStatsResult) : ['total_zones' => 0];
$subzoneStatsResult = mysqli_query($conn, "SELECT COUNT(*) AS total_subzones FROM subzones");
$subzoneStats = $subzoneStatsResult ? mysqli_fetch_assoc($subzoneStatsResult) : ['total_subzones' => 0];
$memberStatsResult = mysqli_query($conn, "SELECT COUNT(*) AS total_members FROM members");
$memberStats = $memberStatsResult ? mysqli_fetch_assoc($memberStatsResult) : ['total_members' => 0];
$zonesResult = mysqli_query($conn, "SELECT z.id, z.name, z.coordinator_name, (SELECT COUNT(*) FROM members m WHERE m.zone_id = z.id) AS member_count, (SELECT COUNT(*) FROM subzones sz WHERE sz.zone_id = z.id) AS subzone_count FROM zones z ORDER BY z.name");
$zones = [];
if ($zonesResult) {
  while ($zone = mysqli_fetch_assoc($zonesResult)) {
    $zone['subzones'] = [];
    $subzonesResult = mysqli_query($conn, "SELECT id, name, coordinator_name FROM subzones WHERE zone_id = " . (int) $zone['id'] . " ORDER BY name");
    if ($subzonesResult) {
      while ($subzone = mysqli_fetch_assoc($subzonesResult)) {
        $zone['subzones'][] = $subzone;
      }
    }
    $zones[] = $zone;
  }
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Zones & Sub-Zones - Associa8</title>
    
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
            <h1 class="page-title">Zones & Sub-Zones</h1>
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

          <!-- Page Header & Action -->
          <div class="page-action-header mb-4">
            <div>
              <h2 class="page-title-main">Zones & Sub-Zones</h2>
              <p class="page-subtitle">Manage geographic divisions and coordinators</p>
            </div>
            <a href="add-zone.php" class="btn-outline-primary">
              <i class="fa-solid fa-plus"></i>
              <span>New Zone</span>
            </a>
          </div>

          <!-- Top Summary Stat Cards Grid -->
          <section class="zone-stats-grid">
            <div class="zone-stat-card">
              <div class="zone-stat-value"><?php echo (int) $zoneStats['total_zones']; ?></div>
              <div class="zone-stat-label">Total Zones</div>
            </div>
            <div class="zone-stat-card">
              <div class="zone-stat-value"><?php echo (int) $subzoneStats['total_subzones']; ?></div>
              <div class="zone-stat-label">Total Sub-Zones</div>
            </div>
            <div class="zone-stat-card">
              <div class="zone-stat-value"><?php echo (int) $memberStats['total_members']; ?></div>
              <div class="zone-stat-label">Total Members</div>
            </div>
            <div class="zone-stat-card">
              <div class="zone-stat-value">12</div>
              <div class="zone-stat-label">Active Coordinators</div>
            </div>
          </section>

          <!-- Zone Cards List -->
          <section class="zone-list">
            <?php if ($zones): ?>
              <?php foreach ($zones as $zone): ?>
                <div class="zone-group">
                  <div class="zone-card">
                    <div class="zone-info-group">
                      <div class="zone-icon-box"><i class="fa-solid fa-location-dot"></i></div>
                      <div class="zone-details">
                        <span class="zone-title"><?php echo htmlspecialchars($zone['name']); ?></span>
                        <span class="zone-coordinator">Coordinator: <?php echo htmlspecialchars($zone['coordinator_name'] ?: 'Not assigned'); ?></span>
                      </div>
                    </div>
                    <div class="zone-meta-group">
                      <div class="zone-stat-unit">
                        <span class="zone-stat-number"><?php echo (int) $zone['member_count']; ?></span>
                        <span class="zone-stat-text">members</span>
                      </div>
                      <div class="zone-stat-unit">
                        <span class="zone-stat-number"><?php echo (int) $zone['subzone_count']; ?></span>
                        <span class="zone-stat-text">sub-zones</span>
                      </div>
                      <div class="zone-actions">
                        <a href="edit-zone.php?id=<?php echo (int) $zone['id']; ?>" class="btn-zone-action" aria-label="Edit <?php echo htmlspecialchars($zone['name']); ?>"><i class="fa-solid fa-pen"></i></a>
                        <button type="button" class="btn-zone-action btn-zone-toggle" aria-label="Toggle sub-zones" aria-expanded="false">
                          <i class="fa-solid fa-chevron-right"></i>
                        </button>
                      </div>
                    </div>
                  </div>

                  <div class="subzone-panel">
                    <div class="subzone-panel-inner">
                      <div class="subzone-panel-header">
                        <span class="subzone-panel-title">Sub-Zones</span>
                        <a href="add-zone.php?zone_id=<?php echo (int) $zone['id']; ?>" class="subzone-add-link"><i class="fa-solid fa-plus"></i> Add sub-zones</a>
                      </div>
                      <div class="subzone-grid">
                        <?php if ($zone['subzones']): ?>
                          <?php foreach ($zone['subzones'] as $subzone): ?>
                            <div class="subzone-card">
                              <div class="subzone-info">
                                <div class="subzone-icon-box"><i class="fa-solid fa-location-dot"></i></div>
                                <div class="subzone-details">
                                  <span class="subzone-title"><?php echo htmlspecialchars($subzone['name']); ?></span>
                                  <span class="subzone-coordinator"><?php echo htmlspecialchars($subzone['coordinator_name'] ?: 'No coordinator assigned'); ?></span>
                                </div>
                              </div>
                              <div class="subzone-count"><i class="fa-solid fa-users"></i><span>0</span></div>
                            </div>
                          <?php endforeach; ?>
                        <?php else: ?>
                          <p class="zone-empty-state">No sub-zones have been added yet.</p>
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <p class="zone-empty-state">No zones have been added yet. Use New Zone to create the first one.</p>
            <?php endif; ?>

            <?php if (!$zones): ?>
            
            <!-- Lagos Zone -->
            <div class="zone-group">
              <div class="zone-card">
                <div class="zone-info-group">
                  <div class="zone-icon-box">
                    <i class="fa-solid fa-location-dot"></i>
                  </div>
                  <div class="zone-details">
                    <span class="zone-title">Lagos Zone</span>
                    <span class="zone-coordinator">Coordinator: Joseph Raymond</span>
                  </div>
                </div>

                <div class="zone-meta-group">
                  <div class="zone-stat-unit">
                    <span class="zone-stat-number">218</span>
                    <span class="zone-stat-text">members</span>
                  </div>
                  <div class="zone-stat-unit">
                    <span class="zone-stat-number">4</span>
                    <span class="zone-stat-text">sub-zones</span>
                  </div>
                  <div class="zone-actions">
                    <a href="#" class="btn-zone-action" aria-label="Edit Zone"><i class="fa-solid fa-pen"></i></a>
                    <button type="button" class="btn-zone-action btn-zone-toggle" aria-label="Toggle sub-zones" aria-expanded="false">
                      <i class="fa-solid fa-chevron-right"></i>
                    </button>
                  </div>
                </div>
              </div>

              <div class="subzone-panel">
                <div class="subzone-panel-inner">
                  <div class="subzone-panel-header">
                    <span class="subzone-panel-title">Sub-Zones</span>
                    <a href="#" class="subzone-add-link"><i class="fa-solid fa-plus"></i> Add sub-zones</a>
                  </div>

                  <div class="subzone-grid">
                    <div class="subzone-card">
                      <div class="subzone-info">
                        <div class="subzone-icon-box"><i class="fa-solid fa-location-dot"></i></div>
                        <div class="subzone-details">
                          <span class="subzone-title">Lagos Island</span>
                          <span class="subzone-coordinator">Amaka Giwa</span>
                        </div>
                      </div>
                      <div class="subzone-count"><i class="fa-solid fa-users"></i><span>94</span></div>
                    </div>

                    <div class="subzone-card">
                      <div class="subzone-info">
                        <div class="subzone-icon-box"><i class="fa-solid fa-location-dot"></i></div>
                        <div class="subzone-details">
                          <span class="subzone-title">Surulere</span>
                          <span class="subzone-coordinator">Bode Gantos</span>
                        </div>
                      </div>
                      <div class="subzone-count"><i class="fa-solid fa-users"></i><span>82</span></div>
                    </div>

                    <div class="subzone-card">
                      <div class="subzone-info">
                        <div class="subzone-icon-box"><i class="fa-solid fa-location-dot"></i></div>
                        <div class="subzone-details">
                          <span class="subzone-title">Lagos Mainland</span>
                          <span class="subzone-coordinator">Amaka Giwa</span>
                        </div>
                      </div>
                      <div class="subzone-count"><i class="fa-solid fa-users"></i><span>94</span></div>
                    </div>

                    <div class="subzone-card">
                      <div class="subzone-info">
                        <div class="subzone-icon-box"><i class="fa-solid fa-location-dot"></i></div>
                        <div class="subzone-details">
                          <span class="subzone-title">Oshodi-Isolo</span>
                          <span class="subzone-coordinator">Bode Gantos</span>
                        </div>
                      </div>
                      <div class="subzone-count"><i class="fa-solid fa-users"></i><span>82</span></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Abuja Zone -->
            <div class="zone-group">
              <div class="zone-card">
                <div class="zone-info-group">
                  <div class="zone-icon-box">
                    <i class="fa-solid fa-location-dot"></i>
                  </div>
                  <div class="zone-details">
                    <span class="zone-title">Abuja Zone</span>
                    <span class="zone-coordinator">Coordinator: Ray Charles</span>
                  </div>
                </div>

                <div class="zone-meta-group">
                  <div class="zone-stat-unit">
                    <span class="zone-stat-number">145</span>
                    <span class="zone-stat-text">members</span>
                  </div>
                  <div class="zone-stat-unit">
                    <span class="zone-stat-number">2</span>
                    <span class="zone-stat-text">sub-zones</span>
                  </div>
                  <div class="zone-actions">
                    <a href="#" class="btn-zone-action" aria-label="Edit Zone"><i class="fa-solid fa-pen"></i></a>
                    <button type="button" class="btn-zone-action btn-zone-toggle" aria-label="Toggle sub-zones" aria-expanded="false">
                      <i class="fa-solid fa-chevron-right"></i>
                    </button>
                  </div>
                </div>
              </div>

              <div class="subzone-panel">
                <div class="subzone-panel-inner">
                  <div class="subzone-panel-header">
                    <span class="subzone-panel-title">Sub-Zones</span>
                    <a href="#" class="subzone-add-link"><i class="fa-solid fa-plus"></i> Add sub-zones</a>
                  </div>

                  <div class="subzone-grid">
                    <div class="subzone-card">
                      <div class="subzone-info">
                        <div class="subzone-icon-box"><i class="fa-solid fa-location-dot"></i></div>
                        <div class="subzone-details">
                          <span class="subzone-title">Lagos Island</span>
                          <span class="subzone-coordinator">Amaka Giwa</span>
                        </div>
                      </div>
                      <div class="subzone-count"><i class="fa-solid fa-users"></i><span>94</span></div>
                    </div>

                    <div class="subzone-card">
                      <div class="subzone-info">
                        <div class="subzone-icon-box"><i class="fa-solid fa-location-dot"></i></div>
                        <div class="subzone-details">
                          <span class="subzone-title">Surulere</span>
                          <span class="subzone-coordinator">Bode Gantos</span>
                        </div>
                      </div>
                      <div class="subzone-count"><i class="fa-solid fa-users"></i><span>82</span></div>
                    </div>

                    <div class="subzone-card">
                      <div class="subzone-info">
                        <div class="subzone-icon-box"><i class="fa-solid fa-location-dot"></i></div>
                        <div class="subzone-details">
                          <span class="subzone-title">Lagos Mainland</span>
                          <span class="subzone-coordinator">Amaka Giwa</span>
                        </div>
                      </div>
                      <div class="subzone-count"><i class="fa-solid fa-users"></i><span>94</span></div>
                    </div>

                    <div class="subzone-card">
                      <div class="subzone-info">
                        <div class="subzone-icon-box"><i class="fa-solid fa-location-dot"></i></div>
                        <div class="subzone-details">
                          <span class="subzone-title">Oshodi-Isolo</span>
                          <span class="subzone-coordinator">Bode Gantos</span>
                        </div>
                      </div>
                      <div class="subzone-count"><i class="fa-solid fa-users"></i><span>82</span></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Port Harcourt Zone -->
            <div class="zone-group">
              <div class="zone-card">
                <div class="zone-info-group">
                  <div class="zone-icon-box">
                    <i class="fa-solid fa-location-dot"></i>
                  </div>
                  <div class="zone-details">
                    <span class="zone-title">Port Harcourt Zone</span>
                    <span class="zone-coordinator">Coordinator: Joseph Raymond</span>
                  </div>
                </div>

                <div class="zone-meta-group">
                  <div class="zone-stat-unit">
                    <span class="zone-stat-number">112</span>
                    <span class="zone-stat-text">members</span>
                  </div>
                  <div class="zone-stat-unit">
                    <span class="zone-stat-number">3</span>
                    <span class="zone-stat-text">sub-zones</span>
                  </div>
                  <div class="zone-actions">
                    <a href="#" class="btn-zone-action" aria-label="Edit Zone"><i class="fa-solid fa-pen"></i></a>
                    <button type="button" class="btn-zone-action btn-zone-toggle" aria-label="Toggle sub-zones" aria-expanded="false">
                      <i class="fa-solid fa-chevron-right"></i>
                    </button>
                  </div>
                </div>
              </div>

              <div class="subzone-panel">
                <div class="subzone-panel-inner">
                  <div class="subzone-panel-header">
                    <span class="subzone-panel-title">Sub-Zones</span>
                    <a href="#" class="subzone-add-link"><i class="fa-solid fa-plus"></i> Add sub-zones</a>
                  </div>

                  <div class="subzone-grid">
                    <div class="subzone-card">
                      <div class="subzone-info">
                        <div class="subzone-icon-box"><i class="fa-solid fa-location-dot"></i></div>
                        <div class="subzone-details">
                          <span class="subzone-title">Lagos Island</span>
                          <span class="subzone-coordinator">Amaka Giwa</span>
                        </div>
                      </div>
                      <div class="subzone-count"><i class="fa-solid fa-users"></i><span>94</span></div>
                    </div>

                    <div class="subzone-card">
                      <div class="subzone-info">
                        <div class="subzone-icon-box"><i class="fa-solid fa-location-dot"></i></div>
                        <div class="subzone-details">
                          <span class="subzone-title">Surulere</span>
                          <span class="subzone-coordinator">Bode Gantos</span>
                        </div>
                      </div>
                      <div class="subzone-count"><i class="fa-solid fa-users"></i><span>82</span></div>
                    </div>

                    <div class="subzone-card">
                      <div class="subzone-info">
                        <div class="subzone-icon-box"><i class="fa-solid fa-location-dot"></i></div>
                        <div class="subzone-details">
                          <span class="subzone-title">Lagos Mainland</span>
                          <span class="subzone-coordinator">Amaka Giwa</span>
                        </div>
                      </div>
                      <div class="subzone-count"><i class="fa-solid fa-users"></i><span>94</span></div>
                    </div>

                    <div class="subzone-card">
                      <div class="subzone-info">
                        <div class="subzone-icon-box"><i class="fa-solid fa-location-dot"></i></div>
                        <div class="subzone-details">
                          <span class="subzone-title">Oshodi-Isolo</span>
                          <span class="subzone-coordinator">Bode Gantos</span>
                        </div>
                      </div>
                      <div class="subzone-count"><i class="fa-solid fa-users"></i><span>82</span></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Kano Zone -->
            <div class="zone-group">
              <div class="zone-card">
                <div class="zone-info-group">
                  <div class="zone-icon-box">
                    <i class="fa-solid fa-location-dot"></i>
                  </div>
                  <div class="zone-details">
                    <span class="zone-title">Port Harcourt Zone</span>
                    <span class="zone-coordinator">Coordinator: Musa Abdulahi</span>
                  </div>
                </div>

                <div class="zone-meta-group">
                  <div class="zone-stat-unit">
                    <span class="zone-stat-number">112</span>
                    <span class="zone-stat-text">members</span>
                  </div>
                  <div class="zone-stat-unit">
                    <span class="zone-stat-number">3</span>
                    <span class="zone-stat-text">sub-zones</span>
                  </div>
                  <div class="zone-actions">
                    <a href="#" class="btn-zone-action" aria-label="Edit Zone"><i class="fa-solid fa-pen"></i></a>
                    <button type="button" class="btn-zone-action btn-zone-toggle" aria-label="Toggle sub-zones" aria-expanded="false">
                      <i class="fa-solid fa-chevron-right"></i>
                    </button>
                  </div>
                </div>
              </div>

              <div class="subzone-panel">
                <div class="subzone-panel-inner">
                  <div class="subzone-panel-header">
                    <span class="subzone-panel-title">Sub-Zones</span>
                    <a href="#" class="subzone-add-link"><i class="fa-solid fa-plus"></i> Add sub-zones</a>
                  </div>

                  <div class="subzone-grid">
                    <div class="subzone-card">
                      <div class="subzone-info">
                        <div class="subzone-icon-box"><i class="fa-solid fa-location-dot"></i></div>
                        <div class="subzone-details">
                          <span class="subzone-title">Lagos Island</span>
                          <span class="subzone-coordinator">Amaka Giwa</span>
                        </div>
                      </div>
                      <div class="subzone-count"><i class="fa-solid fa-users"></i><span>94</span></div>
                    </div>

                    <div class="subzone-card">
                      <div class="subzone-info">
                        <div class="subzone-icon-box"><i class="fa-solid fa-location-dot"></i></div>
                        <div class="subzone-details">
                          <span class="subzone-title">Surulere</span>
                          <span class="subzone-coordinator">Bode Gantos</span>
                        </div>
                      </div>
                      <div class="subzone-count"><i class="fa-solid fa-users"></i><span>82</span></div>
                    </div>

                    <div class="subzone-card">
                      <div class="subzone-info">
                        <div class="subzone-icon-box"><i class="fa-solid fa-location-dot"></i></div>
                        <div class="subzone-details">
                          <span class="subzone-title">Lagos Mainland</span>
                          <span class="subzone-coordinator">Amaka Giwa</span>
                        </div>
                      </div>
                      <div class="subzone-count"><i class="fa-solid fa-users"></i><span>94</span></div>
                    </div>

                    <div class="subzone-card">
                      <div class="subzone-info">
                        <div class="subzone-icon-box"><i class="fa-solid fa-location-dot"></i></div>
                        <div class="subzone-details">
                          <span class="subzone-title">Oshodi-Isolo</span>
                          <span class="subzone-coordinator">Bode Gantos</span>
                        </div>
                      </div>
                      <div class="subzone-count"><i class="fa-solid fa-users"></i><span>82</span></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <?php endif; ?>
          </section>

        </div>

        <!-- FOOTER -->
        <?php include('inc/footer.php'); ?>

      </main>
    </div>

    <!-- Interactive Scripts -->
    <script>
      window.addEventListener("DOMContentLoaded", () => {
        const zoneStatus = new URLSearchParams(window.location.search);
        if (zoneStatus.get("status") && window.AppModal) {
          window.AppModal.open({
            type: zoneStatus.get("status"),
            heading: zoneStatus.get("status") === "success" ? "Saved successfully" : "Could not save record",
            body: zoneStatus.get("msg") || "Please try again.",
          });
        }
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
        if (sidebarOverlay) sidebarOverlay.classList.add("active");
      }

      function closeSidebar() {
        sidebarEl.classList.remove("open");
        if (sidebarOverlay) sidebarOverlay.classList.remove("active");
      }

      document.getElementById("sidebarToggle").addEventListener("click", () => {
        sidebarEl.classList.contains("open") ? closeSidebar() : openSidebar();
      });

      if (sidebarOverlay) sidebarOverlay.addEventListener("click", closeSidebar);

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
      document.querySelectorAll(".zone-group").forEach((group) => {
        const toggleBtn = group.querySelector(".btn-zone-toggle");
        const panel = group.querySelector(".subzone-panel");
        if (!toggleBtn || !panel) return;

        toggleBtn.addEventListener("click", () => {
          const isOpen = group.classList.contains("open");

          if (isOpen) {
            group.classList.remove("open");
            panel.style.maxHeight = null;
            toggleBtn.setAttribute("aria-expanded", "false");
          } else {
            group.classList.add("open");
            panel.style.maxHeight = panel.scrollHeight + "px";
            toggleBtn.setAttribute("aria-expanded", "true");
          }
        });
      });
    </script>
    <script src="../js/preloader.js"></script>
  </body>
</html>