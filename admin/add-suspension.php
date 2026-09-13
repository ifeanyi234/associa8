<?php
require_once "inc/auth.php";
require_once "../inc/db.php";
$type = $_GET['type'] ?? 'suspension';
$type = in_array($type, ['suspension', 'reinstatement'], true) ? $type : 'suspension';
$membersResult = mysqli_query($conn, "SELECT id, member_code, first_name, last_name, status FROM members ORDER BY first_name, last_name");
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo ucfirst($type); ?> Member - Associa8</title>
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
            <h1 class="page-title"><?php echo ucfirst($type); ?> Member</h1>
          </div>
          <div class="header-right">
            <div class="header-search"><i class="fa-solid fa-magnifying-glass header-search-icon"></i><input type="text" placeholder="Search...." /></div>
            <button class="notification-btn" aria-label="Notifications" type="button"><i class="fa-solid fa-bell"></i><span class="notification-badge"></span></button>
            <div class="admin-user-profile"><div class="avatar-badge">SA</div><div class="user-info"><span class="user-name">Super Admin</span><span class="user-role">Full Access</span></div></div>
          </div>
        </header>
        <div class="dashboard-content">
          <div class="page-action-header mb-4">
            <div>
              <h2 class="page-title-main"><?php echo $type === 'reinstatement' ? 'Reinstate Member' : 'Suspend Member'; ?></h2>
              <p class="page-subtitle"><?php echo $type === 'reinstatement' ? 'Restore an eligible member to active status and keep a record of the action.' : 'Record a disciplinary action and update the member status.'; ?></p>
            </div>
            <a href="suspension.php" class="btn-outline-primary"><i class="fa-solid fa-arrow-left"></i><span>Back to suspension</span></a>
          </div>
          <section class="dashboard-card structure-form-card">
            <div class="structure-form-heading">
              <div class="structure-form-icon"><i class="fa-solid fa-<?php echo $type === 'reinstatement' ? 'user-check' : 'ban'; ?>"></i></div>
              <div><h3>Action information</h3><p>This action will update the member record and create a history entry.</p></div>
            </div>
            <form action="proc-suspension.php" method="POST" class="structure-form">
              <input type="hidden" name="action_type" value="<?php echo htmlspecialchars($type); ?>" />
              <div class="form-group">
                <label for="memberId" class="form-label">Member</label>
                <select class="form-select" id="memberId" name="member_id" required>
                  <option value="" selected disabled>Select member</option>
                  <?php if ($membersResult): ?>
                    <?php while ($member = mysqli_fetch_assoc($membersResult)): ?>
                      <option value="<?php echo (int) $member['id']; ?>">
                        <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name'] . ' (' . $member['member_code'] . ') - ' . ucfirst($member['status'])); ?>
                      </option>
                    <?php endwhile; ?>
                  <?php endif; ?>
                </select>
              </div>
              <div class="form-row-2col">
                <div class="form-group">
                  <label for="actionStatus" class="form-label">Record status</label>
                  <?php if ($type === 'reinstatement'): ?>
                    <input type="hidden" name="status" value="completed" />
                    <input class="form-control" type="text" value="Completed" disabled />
                  <?php else: ?>
                    <select class="form-select" id="actionStatus" name="status" required>
                      <option value="active">Active suspension</option>
                      <option value="under_review">Under review</option>
                    </select>
                  <?php endif; ?>
                </div>
                <div class="form-group">
                  <label for="actionDate" class="form-label">Action date</label>
                  <input class="form-control" type="date" id="actionDate" name="action_date" value="<?php echo date('Y-m-d'); ?>" required />
                </div>
              </div>
              <div class="form-group">
                <label for="reason" class="form-label">Reason or notes</label>
                <textarea class="form-control" id="reason" name="reason" rows="4" placeholder="Explain the reason for this action" required></textarea>
              </div>
              <div class="structure-form-actions"><a href="suspension.php" class="btn-action-dark-outline">Cancel</a><button type="submit" class="btn-navy-filled"><i class="fa-solid fa-check"></i> Save action</button></div>
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
          heading: success ? "Action saved successfully" : "Action could not be saved",
          body: params.get("msg") || (success ? "The member record has been updated." : "Please review the form and try again."),
          detail: success ? "The action is now visible in the suspension history." : "No member status was changed.",
        });
        window.history.replaceState({}, document.title, window.location.pathname + `?type=<?php echo $type; ?>`);
      });
    </script>
    <script src="../js/preloader.js"></script>
  </body>
</html>
