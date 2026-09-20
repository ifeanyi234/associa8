<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>My Document - Associa8</title>
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
            <h1 class="page-title">My Document</h1>
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
          <!-- Toolbar -->
          <div class="doc-toolbar-row">
            <div class="toolbar-search">
              <i class="fa-solid fa-magnifying-glass search-icon"></i>
              <input type="text" placeholder="Search by title or file type" />
            </div>
            <div class="doc-toolbar-actions">
              <button class="btn-outline-primary">
                <i class="fa-solid fa-sliders"></i>
                <span>Filter</span>
              </button>
              <button class="btn-outline-primary">
                <i class="fa-regular fa-circle-user"></i>
                <span>Add Document</span>
              </button>
            </div>
          </div>

          <!-- Document Table -->
          <section class="table-responsive-card">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Title</th>
                  <th>Visibility</th>
                  <th>File Type</th>
                  <th>File Path</th>
                  <th>Category</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($documents): ?>
                  <?php foreach ($documents as $document): ?>
                    <?php
                      $visibilityText = 'Organization-wide';
                      if ($document['subzone_name']) {
                        $visibilityText = ($document['zone_name'] ?: 'Zone') . ' / ' . $document['subzone_name'];
                      } elseif ($document['zone_name']) {
                        $visibilityText = $document['zone_name'];
                      }
                    ?>
                    <tr>
                      <td style="font-weight: 600; color: var(--text-primary);"><?php echo htmlspecialchars($document['title']); ?></td>
                      <td><?php echo htmlspecialchars($visibilityText); ?></td>
                      <td><?php echo htmlspecialchars($document['file_type'] ?: 'Document'); ?></td>
                      <td><a href="<?php echo htmlspecialchars($document['file_path']); ?>" class="file-path-tag" target="_blank"><?php echo htmlspecialchars(basename($document['file_path'])); ?></a></td>
                      <td><?php echo htmlspecialchars($document['category'] ?: 'General'); ?></td>
                      <td>
                        <a href="<?php echo htmlspecialchars($document['file_path']); ?>" class="btn-action-edit" target="_blank">View</a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="6" class="zone-empty-state">No documents are available for your zone or sub-zone yet.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
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
    </script>
    <script src="../../js/preloader.js"></script>
  </body>
</html>