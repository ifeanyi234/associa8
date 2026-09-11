<?php
require_once "../inc/db.php";
$zoneOptions = mysqli_query($conn, "SELECT id, name FROM zones ORDER BY name");
$selectedZoneId = (int) ($_GET['zone_id'] ?? 0);
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Add Zone - Associa8</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="shortcut icon" href="../images/fav-logo.png" type="image/x-icon" />
    <link rel="stylesheet" href="../css/preloader.css" />
    <link rel="stylesheet" href="../css/dashboard.css" />
  </head>
  <body class="admin-body">
    <?php include('inc/preloader.php'); ?>
    <div class="admin-layout">
      <?php include('inc/sidebar.php'); ?>
      <div class="sidebar-overlay" id="sidebarOverlay"></div>
      <main class="admin-main">
        <header class="admin-header">
          <div class="header-left">
            <button class="header-toggle-btn" id="sidebarToggle" type="button" aria-label="Open navigation">
              <i class="fa-solid fa-bars"></i>
            </button>
            <h1 class="page-title">Add Zone</h1>
          </div>
          <div class="header-right">
            <div class="header-search">
              <i class="fa-solid fa-magnifying-glass header-search-icon"></i>
              <input type="text" placeholder="Search...." />
            </div>
            <button class="notification-btn" aria-label="Notifications" type="button">
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

        <div class="dashboard-content">
          <div class="page-action-header mb-4">
            <div>
              <h2 class="page-title-main">Create Zone Structure</h2>
              <p class="page-subtitle">Create a zone first, then add the sub-zones that belong to it.</p>
            </div>
            <a href="zones.php" class="btn-outline-primary">
              <i class="fa-solid fa-arrow-left"></i>
              <span>Back to zones</span>
            </a>
          </div>

          <div class="structure-form-stack">
            <section class="dashboard-card structure-form-card">
              <div class="structure-form-heading">
                <div class="structure-form-icon"><i class="fa-solid fa-location-dot"></i></div>
                <div>
                  <h3>Zone information</h3>
                  <p>A zone is the top-level geographic division.</p>
                </div>
              </div>
              <form action="proc-add-zone.php" method="POST" class="structure-form">
                <input type="hidden" name="type" value="zone" />
                <div class="form-row-2col">
                  <div class="form-group">
                    <label for="zoneName" class="form-label">Zone name</label>
                    <input class="form-control" type="text" id="zoneName" name="name" placeholder="e.g. Lagos Zone" required />
                  </div>
                  <div class="form-group">
                    <label for="zoneCoordinator" class="form-label">Coordinator name</label>
                    <input class="form-control" type="text" id="zoneCoordinator" name="coordinator_name" placeholder="Enter coordinator name" />
                  </div>
                </div>
                <div class="structure-form-actions">
                  <a href="zones.php" class="btn-action-dark-outline">Cancel</a>
                  <button type="submit" class="btn-navy-filled"><i class="fa-solid fa-check"></i> Save zone</button>
                </div>
              </form>
            </section>

            <section class="dashboard-card structure-form-card">
              <div class="structure-form-heading">
                <div class="structure-form-icon"><i class="fa-solid fa-sitemap"></i></div>
                <div>
                  <h3>Sub-zone information</h3>
                  <p>Every sub-zone must be attached to an existing parent zone.</p>
                </div>
              </div>
              <form action="proc-add-zone.php" method="POST" class="structure-form">
                <input type="hidden" name="type" value="subzone" />
                <div class="form-row-2col">
                  <div class="form-group">
                    <label for="parentZone" class="form-label">Parent zone</label>
                    <select class="form-select" id="parentZone" name="zone_id" required>
                      <option value="" selected disabled>Select parent zone</option>
                      <?php if ($zoneOptions): ?>
                        <?php while ($zone = mysqli_fetch_assoc($zoneOptions)): ?>
                          <option value="<?php echo (int) $zone['id']; ?>" <?php echo $selectedZoneId === (int) $zone['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($zone['name']); ?></option>
                        <?php endwhile; ?>
                      <?php endif; ?>
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="subzoneName" class="form-label">Sub-zone name</label>
                    <input class="form-control" type="text" id="subzoneName" name="name" placeholder="e.g. Lagos Island" required />
                  </div>
                </div>
                <div class="form-group">
                  <label for="subzoneCoordinator" class="form-label">Coordinator name</label>
                  <input class="form-control" type="text" id="subzoneCoordinator" name="coordinator_name" placeholder="Enter coordinator name" />
                </div>
                <div class="structure-form-actions">
                  <a href="zones.php" class="btn-action-dark-outline">Cancel</a>
                  <button type="submit" class="btn-navy-filled"><i class="fa-solid fa-check"></i> Save sub-zone</button>
                </div>
              </form>
            </section>
          </div>
        </div>
        <?php include('inc/footer.php'); ?>
      </main>
    </div>
    <script src="../js/admin-sidebar.js"></script>
    <script>
      window.addEventListener("DOMContentLoaded", () => {
        const params = new URLSearchParams(window.location.search);
        const status = params.get("status");
        if (!status || !window.AppModal) return;

        const success = status === "success";
        const kind = params.get("type") === "subzone" ? "Sub-zone" : "Zone";
        window.AppModal.open({
          type: success ? "success" : "error",
          heading: success ? `${kind} added successfully` : `${kind} could not be added`,
          body: params.get("msg") || (success ? `The ${kind.toLowerCase()} has been saved.` : "Please review the form and try again."),
          detail: success ? "The new record is now available for assignment and management." : "Check that the name is valid and does not already exist.",
        });
        window.history.replaceState({}, document.title, window.location.pathname);
      });
    </script>
    <script src="../js/preloader.js"></script>
  </body>
</html>
