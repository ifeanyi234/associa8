<?php
require_once "../inc/db.php";
$id = (int) ($_GET['id'] ?? 0);
$titleResult = mysqli_query($conn, "SELECT id, title, level, description FROM titles WHERE id = $id LIMIT 1");
$title = $titleResult ? mysqli_fetch_assoc($titleResult) : null;

if (!$title) {
    header('Location: titles-hierarchy.php?status=error&msg=' . urlencode('The selected title could not be found.'));
    exit;
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Edit Title - Associa8</title>
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
            <button class="header-toggle-btn" id="sidebarToggle" type="button" aria-label="Open navigation"><i class="fa-solid fa-bars"></i></button>
            <h1 class="page-title">Edit Title</h1>
          </div>
          <div class="header-right">
            <div class="header-search"><i class="fa-solid fa-magnifying-glass header-search-icon"></i><input type="text" placeholder="Search...." /></div>
            <button class="notification-btn" aria-label="Notifications" type="button"><i class="fa-solid fa-bell"></i><span class="notification-badge"></span></button>
            <div class="admin-user-profile"><div class="avatar-badge">SA</div><div class="user-info"><span class="user-name">Super Admin</span><span class="user-role">Full Access</span></div></div>
          </div>
        </header>
        <div class="dashboard-content">
          <div class="page-action-header mb-4">
            <div><h2 class="page-title-main">Edit Membership Title</h2><p class="page-subtitle">Update the title details and hierarchy position.</p></div>
            <a href="titles-hierarchy.php" class="btn-outline-primary"><i class="fa-solid fa-arrow-left"></i><span>Back to titles</span></a>
          </div>
          <section class="dashboard-card structure-form-card">
            <div class="structure-form-heading"><div class="structure-form-icon"><i class="fa-solid fa-pen"></i></div><div><h3>Title information</h3><p>Changes will apply to members assigned to this title.</p></div></div>
            <form action="proc-edit-title.php" method="POST" class="structure-form">
              <input type="hidden" name="id" value="<?php echo (int) $title['id']; ?>" />
              <div class="form-row-2col">
                <div class="form-group"><label for="titleName" class="form-label">Title name</label><input class="form-control" type="text" id="titleName" name="name" value="<?php echo htmlspecialchars($title['title']); ?>" required /></div>
                <div class="form-group"><label for="titleLevel" class="form-label">Hierarchy level</label><input class="form-control" type="number" id="titleLevel" name="level" min="1" max="99" value="<?php echo (int) $title['level']; ?>" required /></div>
              </div>
              <div class="form-group"><label for="titleDescription" class="form-label">Description</label><textarea class="form-control" id="titleDescription" name="description" rows="4"><?php echo htmlspecialchars($title['description'] ?? ''); ?></textarea></div>
              <div class="structure-form-actions"><a href="titles-hierarchy.php" class="btn-action-dark-outline">Cancel</a><button type="submit" class="btn-navy-filled"><i class="fa-solid fa-check"></i> Save changes</button></div>
            </form>
          </section>
        </div>
        <?php include('inc/footer.php'); ?>
      </main>
    </div>
    <script src="../js/admin-sidebar.js"></script>
    <script src="../js/preloader.js"></script>
  </body>
</html>
