<?php
require_once "inc/auth.php";
require_once "../inc/db.php";
$zoneOptions = mysqli_query($conn, "SELECT id, name FROM zones ORDER BY name");
$subzonesByZone = [];
$subzonesResult = mysqli_query($conn, "SELECT id, zone_id, name FROM subzones ORDER BY name");
if ($subzonesResult) {
  while ($subzone = mysqli_fetch_assoc($subzonesResult)) {
    $subzonesByZone[(int) $subzone['zone_id']][] = [
      'id' => (int) $subzone['id'],
      'name' => $subzone['name'],
    ];
  }
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Add Document & Files - Associa8</title>

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
          <!-- Page Main Action Header -->
          <div class="page-action-header mb-4">
            <div>
              <h2 class="page-title-main">Add Document & Files</h2>
              <p class="page-subtitle">Upload and Organize your Document & Files.</p>
            </div>
          </div>

          <!-- Document Upload Form Card -->
          <div class="dashboard-card" style="padding: 2.5rem 2rem;">
            <form action="proc-upload-document.php" method="POST" enctype="multipart/form-data">
              
              <!-- Document Title -->
              <div style="margin-bottom: 1.75rem;">
                <label style="display: block; font-weight: 500; margin-bottom: 0.6rem; color: var(--text-primary); font-size: 0.9rem;">
                  Document Title
                </label>
                <input 
                  type="text" 
                  name="document_title" 
                  style="width: 100%; padding: 1rem 1.25rem; background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: var(--radius-md); outline: none; font-size: 0.9rem; color: var(--text-primary);"
                  required 
                />
              </div>

              <!-- Document Owner -->
              <div style="margin-bottom: 1.75rem;">
                <label style="display: block; font-weight: 500; margin-bottom: 0.6rem; color: var(--text-primary); font-size: 0.9rem;">
                  Document Owner
                </label>
                <input 
                  type="text" 
                  name="document_owner" 
                  style="width: 100%; padding: 1rem 1.25rem; background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: var(--radius-md); outline: none; font-size: 0.9rem; color: var(--text-primary);"
                  required 
                />
              </div>

              <!-- Visibility -->
              <div style="margin-bottom: 1.75rem;">
                <label style="display: block; font-weight: 500; margin-bottom: 0.6rem; color: var(--text-primary); font-size: 0.9rem;">
                  Visibility
                </label>
                <div style="display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 0.75rem;">
                  <label style="display: inline-flex; align-items: center; gap: 0.5rem;">
                    <input type="radio" name="visibility" value="org" checked />
                    <span>Organization-wide</span>
                  </label>
                  <label style="display: inline-flex; align-items: center; gap: 0.5rem;">
                    <input type="radio" name="visibility" value="zone" />
                    <span>Specific zone</span>
                  </label>
                  <label style="display: inline-flex; align-items: center; gap: 0.5rem;">
                    <input type="radio" name="visibility" value="subzone" />
                    <span>Specific sub-zone</span>
                  </label>
                </div>
                <label style="display: inline-flex; align-items: center; gap: 0.5rem; margin-top: 0.5rem;">
                  <input type="checkbox" name="organization_wide" value="1" />
                  <span>Also mark as organization-wide</span>
                </label>
              </div>

              <div class="form-row-2col" style="margin-bottom: 1.75rem;">
                <div class="form-group">
                  <label for="documentZone" class="form-label">Zone</label>
                  <select class="form-select" id="documentZone" name="zone_id">
                    <option value="" selected disabled>Select zone</option>
                    <?php if ($zoneOptions): ?>
                      <?php while ($zone = mysqli_fetch_assoc($zoneOptions)): ?>
                        <option value="<?php echo (int) $zone['id']; ?>"><?php echo htmlspecialchars($zone['name']); ?></option>
                      <?php endwhile; ?>
                    <?php endif; ?>
                  </select>
                </div>
                <div class="form-group">
                  <label for="documentSubzone" class="form-label">Sub-zone</label>
                  <select class="form-select" id="documentSubzone" name="subzone_id" disabled>
                    <option value="" selected disabled>Select sub-zone</option>
                  </select>
                </div>
              </div>

              <!-- File Type -->
              <div style="margin-bottom: 1.75rem;">
                <label style="display: block; font-weight: 500; margin-bottom: 0.6rem; color: var(--text-primary); font-size: 0.9rem;">
                  File Type
                </label>
                <select 
                  name="file_type" 
                  style="width: 100%; padding: 1rem 1.25rem; background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: var(--radius-md); outline: none; font-size: 0.9rem; color: var(--text-primary); cursor: pointer; appearance: auto;"
                  required
                >
                  <option value="" disabled selected></option>
                  <option value="JPEG">JPEG</option>
                  <option value="PNG">PNG</option>
                  <option value="PDF">PDF</option>
                </select>
              </div>

              <!-- Upload Document File Input Box -->
              <div style="margin-bottom: 2.25rem;">
                <label style="display: block; font-weight: 500; margin-bottom: 0.6rem; color: var(--text-primary); font-size: 0.9rem;">
                  Upload Document (JPEG,PNG,PDF)
                </label>
                <div style="width: 100%; padding: 0.85rem 1.25rem; background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: var(--radius-md); display: flex; align-items: center; gap: 1rem;">
                  <input 
                    type="file" 
                    name="document_file" 
                    id="document_file" 
                    accept=".jpeg,.jpg,.png,.pdf" 
                    style="display: none;" 
                    required
                    onchange="document.getElementById('fileNameDisplay').textContent = this.files[0] ? this.files[0].name : '';"
                  />
                  <label 
                    for="document_file" 
                    style="background-color: var(--banner-bg, #0a2244); color: #ffffff; padding: 0.6rem 1.5rem; border-radius: var(--radius-pill); font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: opacity 0.2s; display: inline-block;"
                  >
                    Choose File
                  </label>
                  <span id="fileNameDisplay" style="font-size: 0.85rem; color: var(--text-muted);"></span>
                </div>
              </div>

              <!-- Submit Button -->
              <div>
                <button 
                  type="submit" 
                  style="background-color: #cbd5e1; color: var(--text-primary, #0f172a); border: none; padding: 0.85rem 2rem; border-radius: var(--radius-sm); font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: background-color 0.2s;"
                >
                  Add Document
                </button>
              </div>

            </form>
          </div>
        </div>

        <!-- Footer -->
        <?php include('inc/footer.php') ?>
      </main>
    </div>

    <!-- Interactive Scripts -->
    <script>
      const documentSubzones = <?php echo json_encode($subzonesByZone, JSON_HEX_TAG | JSON_HEX_AMP); ?>;

      function populateDocumentSubzones(zoneId) {
        const subzoneSelect = document.getElementById("documentSubzone");
        const options = documentSubzones[Number(zoneId)] || [];

        subzoneSelect.innerHTML = '<option value="" selected disabled>' + (options.length ? 'Select sub-zone' : 'No sub-zone available') + '</option>';
        subzoneSelect.disabled = options.length === 0;

        options.forEach((subzone) => {
          const option = document.createElement("option");
          option.value = subzone.id;
          option.textContent = subzone.name;
          subzoneSelect.appendChild(option);
        });
      }

      document.addEventListener("DOMContentLoaded", () => {
        const zoneSelect = document.getElementById("documentZone");
        const subzoneSelect = document.getElementById("documentSubzone");
        const visibilityRadios = document.querySelectorAll('input[name="visibility"]');
        const orgWideToggle = document.querySelector('input[name="organization_wide"]');

        function syncVisibilityState() {
          const selectedVisibility = document.querySelector('input[name="visibility"]:checked')?.value || 'org';
          const isOrgWide = orgWideToggle.checked;
          const disableZone = isOrgWide || selectedVisibility === 'org';
          const disableSubzone = isOrgWide || selectedVisibility !== 'subzone';

          zoneSelect.disabled = disableZone;
          subzoneSelect.disabled = disableSubzone || !zoneSelect.value || !documentSubzones[Number(zoneSelect.value)] || documentSubzones[Number(zoneSelect.value)].length === 0;

          if (disableZone) {
            zoneSelect.value = '';
            subzoneSelect.innerHTML = '<option value="" selected disabled>Select sub-zone</option>';
            subzoneSelect.disabled = true;
          }
        }

        zoneSelect.addEventListener("change", (event) => {
          if (event.target.value) {
            populateDocumentSubzones(event.target.value);
          } else {
            subzoneSelect.innerHTML = '<option value="" selected disabled>Select sub-zone</option>';
            subzoneSelect.disabled = true;
          }
        });

        visibilityRadios.forEach((radio) => {
          radio.addEventListener("change", syncVisibilityState);
        });
        orgWideToggle.addEventListener("change", syncVisibilityState);

        syncVisibilityState();
      });

      // Sidebar Accordion Logic
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
    </script>
    <script>
      window.addEventListener("DOMContentLoaded", () => {
        const params = new URLSearchParams(window.location.search);
        const status = params.get("status");
        if (!status || !window.AppModal) return;
        const success = status === "success";
        window.AppModal.open({
          type: success ? "success" : "error",
          heading: success ? "Document uploaded" : "Upload failed",
          body: params.get("msg") || (success ? "The document was saved." : "Please try again."),
        });
        window.history.replaceState({}, document.title, window.location.pathname);
      });
    </script>
    <script src="../js/preloader.js"></script>
  </body>
</html>