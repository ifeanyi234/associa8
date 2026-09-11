<?php
require_once "../inc/db.php";
$titlesResult = mysqli_query($conn, "SELECT id, title, level FROM titles ORDER BY level");
$zonesResult = mysqli_query($conn, "SELECT id, name FROM zones ORDER BY name");
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Add Member - Associa8</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" /><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="shortcut icon" href="../images/fav-logo.png" type="image/x-icon" />
    <link rel="stylesheet" href="../css/preloader.css" /><link rel="stylesheet" href="../css/dashboard.css" />
  </head>
  <body class="admin-body">
    <?php include('inc/preloader.php'); ?>
    <div class="admin-layout">
      <?php include('inc/sidebar.php'); ?><div class="sidebar-overlay" id="sidebarOverlay"></div>
      <main class="admin-main">
        <header class="admin-header"><div class="header-left"><button class="header-toggle-btn" id="sidebarToggle" type="button" aria-label="Open navigation"><i class="fa-solid fa-bars"></i></button><h1 class="page-title">Add Member</h1></div><div class="header-right"><div class="header-search"><i class="fa-solid fa-magnifying-glass header-search-icon"></i><input type="text" placeholder="Search...." /></div><button class="notification-btn" type="button" aria-label="Notifications"><i class="fa-solid fa-bell"></i><span class="notification-badge"></span></button><div class="admin-user-profile"><div class="avatar-badge">SA</div><div class="user-info"><span class="user-name">Super Admin</span><span class="user-role">Full Access</span></div></div></div></header>
        <div class="dashboard-content">
          <div class="page-action-header mb-4"><div><h2 class="page-title-main">Create Member</h2><p class="page-subtitle">Add a member and assign a title and zone.</p></div><a href="member-directory.php" class="btn-outline-primary"><i class="fa-solid fa-arrow-left"></i><span>Back to directory</span></a></div>
          <section class="dashboard-card structure-form-card"><div class="structure-form-heading"><div class="structure-form-icon"><i class="fa-solid fa-user-plus"></i></div><div><h3>Member information</h3><p>Required setup records are loaded from your titles and zones.</p></div></div>
            <form action="proc-add-member.php" method="POST" class="structure-form">
              <div class="form-row-2col"><div class="form-group"><label for="firstName" class="form-label">First name</label><input class="form-control" id="firstName" name="first_name" required /></div><div class="form-group"><label for="lastName" class="form-label">Last name</label><input class="form-control" id="lastName" name="last_name" required /></div></div>
              <div class="form-row-2col"><div class="form-group"><label for="memberCode" class="form-label">Member code</label><input class="form-control" id="memberCode" name="member_code" placeholder="e.g. ASC-001" required /></div><div class="form-group"><label for="email" class="form-label">Email</label><input class="form-control" type="email" id="email" name="email" required /></div></div>
              <div class="form-row-2col"><div class="form-group"><label for="phone" class="form-label">Phone</label><input class="form-control" id="phone" name="phone" /></div><div class="form-group"><label for="joinedDate" class="form-label">Joined date</label><input class="form-control" type="date" id="joinedDate" name="joined_date" value="<?php echo date('Y-m-d'); ?>" required /></div></div>
              <div class="form-row-2col"><div class="form-group"><label for="titleId" class="form-label">Membership title</label><select class="form-select" id="titleId" name="title_id" required><option value="" selected disabled>Select title</option><?php if ($titlesResult): ?><?php while ($title = mysqli_fetch_assoc($titlesResult)): ?><option value="<?php echo (int) $title['id']; ?>">L<?php echo (int) $title['level']; ?> - <?php echo htmlspecialchars($title['title']); ?></option><?php endwhile; ?><?php endif; ?></select></div><div class="form-group"><label for="zoneId" class="form-label">Zone</label><select class="form-select" id="zoneId" name="zone_id" required><option value="" selected disabled>Select zone</option><?php if ($zonesResult): ?><?php while ($zone = mysqli_fetch_assoc($zonesResult)): ?><option value="<?php echo (int) $zone['id']; ?>"><?php echo htmlspecialchars($zone['name']); ?></option><?php endwhile; ?><?php endif; ?></select></div></div>
              <div class="structure-form-actions"><a href="member-directory.php" class="btn-action-dark-outline">Cancel</a><button type="submit" class="btn-navy-filled"><i class="fa-solid fa-check"></i> Save member</button></div>
            </form>
          </section>
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
        window.AppModal.open({
          type: success ? "success" : "error",
          heading: success ? "Member added successfully" : "Member could not be added",
          body: params.get("msg") || (success ? "The member is now in the directory." : "Please review the member details and try again."),
          detail: success ? "The selected title and zone are linked to this member." : "No member record was created.",
        });
        window.history.replaceState({}, document.title, window.location.pathname);
      });
    </script>
    <script src="../js/preloader.js"></script>
  </body>
</html>
