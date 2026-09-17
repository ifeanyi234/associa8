<?php
require_once "inc/auth.php";
require_once "../inc/db.php";
$id = (int) ($_GET['id'] ?? 0);
$zoneResult = mysqli_query($conn, "SELECT id, name, coordinator_name FROM zones WHERE id = $id LIMIT 1");
$zone = $zoneResult ? mysqli_fetch_assoc($zoneResult) : null;

if (!$zone) {
    header('Location: zones.php?status=error&msg=' . urlencode('The selected zone could not be found.'));
    exit;
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Edit Zone - Associa8</title>
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
<div class="sidebar-overlay" id="sidebarOverlay">
</div>
      <main class="admin-main">
        <header class="admin-header">
<div class="header-left">
<button class="header-toggle-btn" id="sidebarToggle" type="button" aria-label="Open navigation">
<i class="fa-solid fa-bars">
</i>
</button>
<h1 class="page-title">Edit Zone</h1>
</div>
<div class="header-right">
<div class="header-search">
<i class="fa-solid fa-magnifying-glass header-search-icon">
</i>
<input type="text" placeholder="Search...." />
</div>
<button class="notification-btn" aria-label="Notifications" type="button">
<i class="fa-solid fa-bell">
</i>
<span class="notification-badge">
</span>
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
<h2 class="page-title-main">Edit Zone</h2>
<p class="page-subtitle">Update this zone's name and coordinator.</p>
</div>
<a href="zones.php" class="btn-outline-primary">
<i class="fa-solid fa-arrow-left">
</i>
<span>Back to zones</span>
</a>
</div>
          <section class="dashboard-card structure-form-card">
<div class="structure-form-heading">
<div class="structure-form-icon">
<i class="fa-solid fa-pen">
</i>
</div>
<div>
<h3>Zone information</h3>
<p>Existing sub-zones will remain connected to this zone.</p>
</div>
</div>
            <form action="proc-edit-zone.php" method="POST" class="structure-form">
<input type="hidden" name="id" value="<?php echo (int) $zone['id']; ?>" />
<div class="form-row-2col">
<div class="form-group">
<label for="zoneName" class="form-label">Zone name</label>
<input class="form-control" type="text" id="zoneName" name="name" value="<?php echo htmlspecialchars($zone['name']); ?>" required />
</div>
<div class="form-group">
<label for="zoneCoordinator" class="form-label">Coordinator name</label>
<input class="form-control" type="text" id="zoneCoordinator" name="coordinator_name" value="<?php echo htmlspecialchars($zone['coordinator_name'] ?? ''); ?>" />
</div>
</div>
<div class="structure-form-actions">
<a href="zones.php" class="btn-action-dark-outline">Cancel</a>
<button type="submit" class="btn-navy-filled">
<i class="fa-solid fa-check">
</i> Save changes</button>
</div>
</form>
          </section>
        </div>
        <?php include('inc/footer.php'); ?>
      </main>
    </div>
    <script src="../js/admin-sidebar.js">
</script>
<script src="../js/preloader.js">
</script>
  </body>
</html>

