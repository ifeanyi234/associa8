<?php
require_once "inc/auth.php";
require_once "../inc/db.php";

$adminRole = $_SESSION['admin_role'] ?? 'admin';
$orgId = isset($_SESSION['org_id']) && $_SESSION['org_id'] !== null ? (int) $_SESSION['org_id'] : null;
$adminZoneId = isset($_SESSION['admin_zone_id']) && $_SESSION['admin_zone_id'] !== null ? (int) $_SESSION['admin_zone_id'] : null;
$adminSubzoneId = isset($_SESSION['admin_subzone_id']) && $_SESSION['admin_subzone_id'] !== null ? (int) $_SESSION['admin_subzone_id'] : null;
$shouldScopeAdmin = $adminRole !== 'super_admin' && $adminZoneId !== null;

$documentCountSql = "SELECT COUNT(*) AS total FROM documents d";
$documentsSql = "SELECT d.id, d.title, d.file_path, d.file_type, d.file_size, d.category, d.uploaded_by, d.zone_id, d.subzone_id, d.created_at, z.name AS zone_name, sz.name AS subzone_name FROM documents d LEFT JOIN zones z ON z.id = d.zone_id LEFT JOIN subzones sz ON sz.id = d.subzone_id";

if ($adminRole !== 'super_admin' && $orgId !== null) {
  $documentCountSql .= " WHERE d.org_id = " . (int) $orgId;
  $documentsSql .= " WHERE d.org_id = " . (int) $orgId;
}

if ($shouldScopeAdmin) {
  $documentsSql .= " AND ((d.zone_id IS NULL AND d.subzone_id IS NULL) OR d.zone_id = ? OR d.subzone_id = ?)";
  $documentCountSql .= " AND ((d.zone_id IS NULL AND d.subzone_id IS NULL) OR d.zone_id = ? OR d.subzone_id = ?)";
}

$documentsSql .= " ORDER BY d.created_at DESC";

$documentCountResult = mysqli_query($conn, $documentCountSql);
if ($shouldScopeAdmin) {
  $documentCountStmt = mysqli_prepare($conn, $documentCountSql);
  mysqli_stmt_bind_param($documentCountStmt, 'ii', $adminZoneId, $adminSubzoneId);
  mysqli_stmt_execute($documentCountStmt);
  $documentCountResult = mysqli_stmt_get_result($documentCountStmt);
}
$documentCount = $documentCountResult ? (int) mysqli_fetch_assoc($documentCountResult)['total'] : 0;

$documentsResult = null;
if ($shouldScopeAdmin) {
  $documentsStmt = mysqli_prepare($conn, $documentsSql);
  mysqli_stmt_bind_param($documentsStmt, 'ii', $adminZoneId, $adminSubzoneId);
  mysqli_stmt_execute($documentsStmt);
  $documentsResult = mysqli_stmt_get_result($documentsStmt);
} else {
  $documentsResult = mysqli_query($conn, $documentsSql);
}
$documents = [];
if ($documentsResult) {
  while ($document = mysqli_fetch_assoc($documentsResult)) {
    $documents[] = $document;
  }
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Document Manager - Associa8</title>

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
            <h1 class="page-title">Document</h1>
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
          <!-- Page Action Header -->
          <div class="page-action-header">
            <div>
              <h2 class="page-title-main">Document Manager</h2>
              <p class="page-subtitle">
                Upload, organize, and securely share documents while maintaining complete control over access and permissions.
              </p>
            </div>
            <div style="display: flex; gap: 0.75rem;">
              <button class="btn-navy-outline">
                <i class="fa-solid fa-sliders"></i> Filter
              </button>
              <a href="upload-document.php">
              <button class="btn-navy-filled" style="height: 100%;">
                <i class="fa-solid fa-circle-plus"></i> Add Document
              </button>
              </a>
            </div>
          </div>

          <!-- Document Search Bar -->
          <div class="directory-toolbar" style="margin-bottom: 1.5rem;">
            <div class="toolbar-search" style="max-width: 100%; flex: 1;">
              <i class="fa-solid fa-magnifying-glass search-icon"></i>
              <input
                type="text"
                placeholder="Search by title or file type"
              />
            </div>
          </div>

          <!-- Document Data Table Card -->
          <div class="table-responsive-card">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Title</th>
                  <th>Visibility</th>
                  <th>File Type</th>
                  <th>File Path</th>
                  <th>Category</th>
                  <th style="text-align: center;">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($documents): ?>
                  <?php foreach ($documents as $document): ?>
                    <?php
                      $visibilityLabel = 'Organization-wide';
                      if ($document['subzone_name']) {
                        $visibilityLabel = $document['zone_name'] . ' / ' . $document['subzone_name'];
                      } elseif ($document['zone_name']) {
                        $visibilityLabel = $document['zone_name'];
                      }
                    ?>
                    <tr>
                      <td class="fw-semibold"><?php echo htmlspecialchars($document['title']); ?></td>
                      <td><?php echo htmlspecialchars($visibilityLabel); ?></td>
                      <td><?php echo htmlspecialchars($document['file_type'] ?: 'Not set'); ?></td>
                      <td><a href="<?php echo htmlspecialchars($document['file_path']); ?>" class="file-path-tag" target="_blank"><?php echo htmlspecialchars(basename($document['file_path'])); ?></a></td>
                      <td><?php echo htmlspecialchars($document['category'] ?: 'General'); ?></td>
                      <td style="text-align: center;">
                        <div style="display: inline-flex; gap: 0.5rem;">
                          <a href="<?php echo htmlspecialchars($document['file_path']); ?>" class="btn-action-edit" target="_blank">View</a>
                          <form action="proc-delete-document.php" method="POST" onsubmit="return confirm('Delete this document?');" style="display: inline;">
                            <input type="hidden" name="document_id" value="<?php echo (int) $document['id']; ?>" />
                            <button type="submit" class="btn-action-delete">Delete</button>
                          </form>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="6" class="zone-empty-state">No documents have been uploaded yet.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Footer -->
        <?php include('inc/footer.php') ?>
      </main>
    </div>

    <!-- Interactive Scripts -->
    <script>
      // 1. Sidebar Dropdown Dynamic Accordion Logic
      const dropdownItems = document.querySelectorAll(".sidebar-item.dropdown");

      dropdownItems.forEach((item) => {
        const link = item.querySelector(".sidebar-link");
        const submenu = item.querySelector(".sidebar-submenu");

        if (link && submenu) {
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
        }
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
    <script>
      window.addEventListener("DOMContentLoaded", () => {
        const params = new URLSearchParams(window.location.search);
        const status = params.get("status");
        const action = params.get("action");
        if (!status || !window.AppModal) return;

        const success = status === "success";
        const isUpload = action === "upload";

        const headings = {
          upload: { success: "Document uploaded", error: "Upload failed" },
          delete: { success: "Document deleted", error: "Delete failed" },
        };
        const defaultHeading = success ? "Success" : "Something went wrong";
        const heading = headings[action] ? headings[action][success ? "success" : "error"] : defaultHeading;

        window.AppModal.open({
          type: success ? "success" : "error",
          heading: heading,
          body: params.get("msg") || (success ? "The action completed successfully." : "Please try again."),
        });
        window.history.replaceState({}, document.title, window.location.pathname);
      });
    </script>
    <script src="../js/preloader.js"></script>
  </body>
</html>