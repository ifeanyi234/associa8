<?php
require_once "inc/auth.php";
require_once "../inc/db.php";

if (!in_array($_SESSION['admin_role'] ?? '', ['super_admin', 'admin'], true)) {
  http_response_code(403);
  exit('Only organization administrators can manage staff accounts.');
}
$isPlatformAdmin = ($_SESSION['admin_role'] ?? '') === 'super_admin';
$orgId = (int) ($_SESSION['org_id'] ?? 0);
if ($orgId < 1) {
  http_response_code(403);
  exit('Your account is not connected to an organization.');
}
$organizationNameStmt = mysqli_prepare($conn, 'SELECT name FROM `org-info` WHERE id = ? LIMIT 1');
if (!$organizationNameStmt) {
  error_log('Organization name for User Control could not be queried: ' . mysqli_error($conn));
  http_response_code(500);
  exit('Your organization details are temporarily unavailable.');
}
mysqli_stmt_bind_param($organizationNameStmt, 'i', $orgId);
if (!mysqli_stmt_execute($organizationNameStmt)) {
  error_log('Organization name lookup for User Control failed: ' . mysqli_stmt_error($organizationNameStmt));
  mysqli_stmt_close($organizationNameStmt);
  http_response_code(500);
  exit('Your organization details are temporarily unavailable.');
}
$organizationNameResult = mysqli_stmt_get_result($organizationNameStmt);
$organizationRow = $organizationNameResult ? mysqli_fetch_assoc($organizationNameResult) : null;
mysqli_stmt_close($organizationNameStmt);
if (!$organizationRow || trim((string) $organizationRow['name']) === '') {
  http_response_code(403);
  exit('Your organization account could not be found.');
}
$organizationName = (string) $organizationRow['name'];
$notice = $_SESSION['user_control_notice'] ?? null;
unset($_SESSION['user_control_notice']);
if (empty($_SESSION['user_control_csrf'])) {
  $_SESSION['user_control_csrf'] = bin2hex(random_bytes(32));
}
$users = [];
$invitations = [];
$userSql = 'SELECT a.id AS account_id, a.org_id, a.username, a.status, a.created_at,
                 ai.`first-name` AS first_name, ai.`last-name` AS last_name, ai.email, ai.role,
                 oi.name AS organization_name
          FROM `acc-info` a
          INNER JOIN `admin-info` ai ON ai.acc_id = a.id AND ai.org_id = a.org_id
          INNER JOIN `org-info` oi ON oi.id = a.org_id
          WHERE a.org_id = ? AND ai.role <> \'super_admin\'';
$userSql .= ' ORDER BY a.created_at DESC, a.id DESC';
$userStmt = mysqli_prepare(
  $conn,
  $userSql
);
if (!$userStmt) {
  error_log('Staff account list could not be prepared: ' . mysqli_error($conn));
  http_response_code(500);
  exit('Staff accounts are temporarily unavailable.');
}
mysqli_stmt_bind_param($userStmt, 'i', $orgId);
if (!mysqli_stmt_execute($userStmt)) {
  error_log('Staff account list query failed: ' . mysqli_stmt_error($userStmt));
  mysqli_stmt_close($userStmt);
  http_response_code(500);
  exit('Staff accounts are temporarily unavailable.');
}
$userResult = mysqli_stmt_get_result($userStmt);
if ($userResult) {
  while ($user = mysqli_fetch_assoc($userResult)) {
    $users[] = $user;
  }
}
mysqli_stmt_close($userStmt);
$invitationSql = 'SELECT i.id, i.org_id, i.first_name, i.last_name, i.email, i.role, i.expires_at,
                         o.name AS organization_name
                  FROM staff_invitations i
                  INNER JOIN `org-info` o ON o.id = i.org_id
                  WHERE i.accepted_at IS NULL AND i.expires_at > UTC_TIMESTAMP()';
$invitationSql .= ' AND i.org_id = ?';
$invitationSql .= ' ORDER BY i.created_at DESC, i.id DESC';
$invitationStmt = mysqli_prepare($conn, $invitationSql);
if (!$invitationStmt) {
  error_log('Staff invitation list could not be prepared: ' . mysqli_error($conn));
  http_response_code(500);
  exit('Pending invitations are temporarily unavailable.');
}
mysqli_stmt_bind_param($invitationStmt, 'i', $orgId);
if (!mysqli_stmt_execute($invitationStmt)) {
  error_log('Staff invitation list query failed: ' . mysqli_stmt_error($invitationStmt));
  mysqli_stmt_close($invitationStmt);
  http_response_code(500);
  exit('Pending invitations are temporarily unavailable.');
}
$invitationResult = mysqli_stmt_get_result($invitationStmt);
if ($invitationResult) {
  while ($invitation = mysqli_fetch_assoc($invitationResult)) {
    $invitations[] = $invitation;
  }
}
mysqli_stmt_close($invitationStmt);
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>User Control - Associa8</title>

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
    <link rel="stylesheet" href="../css/dashboard.css?v=20261008-infotips-3" />
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
            <h1 class="page-title">Administration</h1>
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
              <div class="avatar-badge"><?php echo htmlspecialchars(strtoupper(substr($organizationName, 0, 2)), ENT_QUOTES, 'UTF-8'); ?></div>
              <div class="user-info">
              <span class="user-name"><?php echo htmlspecialchars($organizationName, ENT_QUOTES, 'UTF-8'); ?></span>
              <span class="user-role"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', (string) $_SESSION['admin_role'])), ENT_QUOTES, 'UTF-8'); ?></span>
              </div>
            </div>
          </div>
        </header>

        <!-- Dashboard Body Content -->
<div class="dashboard-content">
  <!-- Page Main Action Header -->
  <div class="page-action-header mb-4">
    <div>
      <h2 class="page-title-main">User Control</h2>
      <p class="page-subtitle">Invite people to this organization's workspace. They choose their own password, and their role controls what they can access.</p>
    </div>
  </div>

  <?php if (is_array($notice)): ?>
    <div class="dashboard-card" style="margin-bottom: 1.25rem;">
      <div class="alert-box <?php echo ($notice['type'] ?? '') === 'success' ? 'success' : 'error'; ?>" role="status">
        <?php echo htmlspecialchars((string) ($notice['message'] ?? 'Please review the form and try again.'), ENT_QUOTES, 'UTF-8'); ?>
      </div>
    </div>
  <?php endif; ?>

  <!-- User Form Card -->
  <div class="dashboard-card">
    <form action="process-user.php" method="POST" id="userControlForm">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['user_control_csrf'], ENT_QUOTES, 'UTF-8'); ?>" />
      <!-- First Name & Last Name -->
      <div class="form-row-2col mb-3">
        <div class="form-group">
          <label for="firstName" class="form-label">First Name</label>
          <input type="text" id="firstName" name="first_name" class="form-control" placeholder="Enter your name" required />
        </div>
        <div class="form-group">
          <label for="lastName" class="form-label">Last Name</label>
          <input type="text" id="lastName" name="last_name" class="form-control" placeholder="Enter your name" required />
        </div>
      </div>

      <!-- Email & Role -->
      <div class="form-row-2col mb-3">
        <div class="form-group">
          <label for="email" class="form-label">Email</label>
          <input type="email" id="email" name="email" class="form-control" placeholder="Used as their login username" maxlength="100" required />
        </div>
        <div class="form-group">
          <label for="role" class="form-label">Access role (assigned by you)</label>
          <select id="role" name="role" class="form-select" required>
            <option value="" selected disabled>Choose their access role</option>
            <?php if (($_SESSION['admin_role'] ?? '') === 'super_admin'): ?>
              <option value="admin">Admin — all organization modules and access management</option>
            <?php endif; ?>
            <option value="manager">Manager — members, admissions, documents, events, attendance</option>
            <option value="staff">Staff — day-to-day members, documents, events, attendance</option>
          </select>
        </div>
      </div>

      <div class="form-row-2col mb-3">
        <div class="form-group">
          <label for="phone" class="form-label">Phone (optional)</label>
          <input type="tel" id="phone" name="phone" class="form-control" placeholder="e.g. +2348012345678" maxlength="16" />
        </div>
        <div class="form-group">
          <label for="jobTitle" class="form-label">Job title (optional)</label>
          <input type="text" id="jobTitle" name="job_title" class="form-control" maxlength="200" placeholder="e.g. Membership officer" />
        </div>
      </div>
      <!-- Submit Button -->
      <div class="form-actions">
        <?php admin_info_tip("We will email them a secure invitation to this organization's existing workspace. They choose their own password. The link expires after 48 hours.", 'Invitation help'); ?>
        <button type="submit" class="btn-primary-filled">Send Invitation</button>
      </div>
    </form>
  </div>

  <div class="dashboard-card" style="margin-top: 1.5rem;">
    <div class="page-action-header mb-3">
      <div>
        <h3 class="page-title-main" style="font-size: 1.2rem; margin: 0;">Pending invitations</h3>
      </div>
    </div>
    <div class="table-responsive-card">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Name</th><th>Email</th><th>Role</th><th>Expires</th><th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($invitations): ?>
            <?php foreach ($invitations as $invitation): ?>
              <tr>
                <td><?php echo htmlspecialchars($invitation['first_name'] . ' ' . $invitation['last_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($invitation['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars(ucfirst((string) $invitation['role']), ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars((string) $invitation['expires_at'], ENT_QUOTES, 'UTF-8'); ?> UTC</td>
                <td>
                  <form action="proc-invitation-action.php" method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['user_control_csrf'], ENT_QUOTES, 'UTF-8'); ?>" />
                    <input type="hidden" name="invitation_id" value="<?php echo (int) $invitation['id']; ?>" />
                    <button class="btn-navy-outline" type="submit" name="action" value="resend">Resend</button>
                    <button class="btn-navy-outline" type="submit" name="action" value="revoke">Cancel invitation</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="5" class="zone-empty-state">There are no pending invitations.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="dashboard-card" style="margin-top: 1.5rem;">
    <div class="page-action-header mb-3">
      <div>
        <h3 class="page-title-main" style="font-size: 1.2rem; margin: 0;">People with access</h3>
      </div>
    </div>

    <div class="table-responsive-card">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($users): ?>
            <?php foreach ($users as $user): ?>
              <tr>
                <?php
                  $canManageAccount = (int) $user['account_id'] !== (int) ($_SESSION['user_id'] ?? 0)
                    && $user['role'] !== 'super_admin'
                    && ($isPlatformAdmin || $user['role'] !== 'admin');
                ?>
                <td style="font-weight: 600; color: var(--text-primary);"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $user['role'])), ENT_QUOTES, 'UTF-8'); ?></td>
                <td><span class="badge-pill <?php echo $user['status'] === 'active' ? 'status-active' : 'status-inactive'; ?>"><?php echo htmlspecialchars($user['status'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                <td>
                  <?php if ($canManageAccount): ?>
                    <form action="proc-user-action.php" method="post" style="display: inline-flex; gap: .5rem; flex-wrap: wrap;">
                      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['user_control_csrf'], ENT_QUOTES, 'UTF-8'); ?>" />
                      <input type="hidden" name="account_id" value="<?php echo (int) $user['account_id']; ?>" />
                      <select class="form-select" name="new_role" aria-label="New role for <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name'], ENT_QUOTES, 'UTF-8'); ?>">
                        <?php if ($isPlatformAdmin): ?>
                          <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        <?php endif; ?>
                        <option value="manager" <?php echo $user['role'] === 'manager' ? 'selected' : ''; ?>>Manager</option>
                        <option value="staff" <?php echo $user['role'] === 'staff' ? 'selected' : ''; ?>>Staff</option>
                      </select>
                      <button class="btn-navy-outline" type="submit" name="action" value="change_role">Update role</button>
                      <?php if ($user['status'] === 'active'): ?>
                        <button class="btn-navy-outline" type="submit" name="action" value="disable">Disable</button>
                      <?php else: ?>
                        <button class="btn-navy-outline" type="submit" name="action" value="enable">Enable</button>
                      <?php endif; ?>
                    </form>
                  <?php else: ?>
                    <span>—</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="5" class="zone-empty-state">No one has accepted an invitation to this organization yet.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

        <!-- Footer -->
        <?php include('inc/footer.php')?>
      </main>
    </div>

    <!-- Interactive Scripts -->
    <style>
      .alert-box {
        padding: 0.9rem 1rem;
        border-radius: 10px;
        font-weight: 600;
      }
      .alert-box.success {
        background: rgba(34, 197, 94, 0.12);
        color: #166534;
        border: 1px solid rgba(34, 197, 94, 0.25);
      }
      .alert-box.error {
        background: rgba(239, 68, 68, 0.12);
        color: #991b1b;
        border: 1px solid rgba(239, 68, 68, 0.2);
      }
    </style>
    <script>
      // 1. Sidebar Dropdown Accordion Toggle Logic
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
    <script src="../js/preloader.js"></script>
  </body>
</html>