<?php
require_once "inc/auth.php";
require_once "../inc/db.php";
require_once __DIR__ . '/inc/scope.php';

if (!admin_is_super_admin()) {
  admin_render_access_denied('Only the Super Admin can manage admin access.');
}
$scopeContext = admin_scope_context($conn);
if (empty($scopeContext['valid'])) {
  admin_render_access_denied('Your account does not have a valid zone or sub-zone assignment.');
}
$orgId = (int) ($_SESSION['org_id'] ?? 0);
if ($orgId < 1) {
  admin_render_access_denied('Your account is not connected to an organization. Contact your administrator for help.');
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
  admin_render_access_denied('Your organization workspace could not be found. Contact your administrator for help.');
}
$organizationName = (string) $organizationRow['name'];
$notice = $_SESSION['user_control_notice'] ?? null;
unset($_SESSION['user_control_notice']);
$isPlatformAdmin = admin_is_super_admin();
if (empty($_SESSION['user_control_csrf'])) {
  $_SESSION['user_control_csrf'] = bin2hex(random_bytes(32));
}
$users = [];
$invitations = [];
$userSql = 'SELECT a.id AS account_id, a.org_id, a.username, a.status, a.created_at,
                 ai.`first-name` AS first_name, ai.`last-name` AS last_name, ai.email, ai.role,
                 ai.zone_id, ai.subzone_id, z.name AS zone_name, sz.name AS subzone_name,
                 oi.name AS organization_name
          FROM `acc-info` a
          INNER JOIN `admin-info` ai ON ai.acc_id = a.id AND ai.org_id = a.org_id
          INNER JOIN `org-info` oi ON oi.id = a.org_id
          LEFT JOIN zones z ON z.id = ai.zone_id
          LEFT JOIN subzones sz ON sz.id = ai.subzone_id
          WHERE a.org_id = ? AND ai.role <> \'super_admin\'';
if ($scopeContext['role'] === 'zonal_admin') {
  $subzoneIds = array_map('intval', $scopeContext['subzone_ids']);
  $userSql .= ' AND ai.role = \'subzonal_admin\' AND ai.zone_id = ? AND ai.subzone_id IN (' . ($subzoneIds ? implode(',', $subzoneIds) : '0') . ')';
}
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
if ($scopeContext['role'] === 'zonal_admin') {
  $zoneId = (int) $scopeContext['zone_id'];
  mysqli_stmt_bind_param($userStmt, 'ii', $orgId, $zoneId);
} else {
  mysqli_stmt_bind_param($userStmt, 'i', $orgId);
}
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
                         i.zone_id, i.subzone_id,
                         o.name AS organization_name, z.name AS zone_name, sz.name AS subzone_name
                  FROM staff_invitations i
                  INNER JOIN `org-info` o ON o.id = i.org_id
                  LEFT JOIN zones z ON z.id = i.zone_id
                  LEFT JOIN subzones sz ON sz.id = i.subzone_id
                  WHERE i.accepted_at IS NULL AND i.expires_at > UTC_TIMESTAMP()';
$invitationSql .= ' AND i.org_id = ?';
if ($scopeContext['role'] === 'zonal_admin') {
  $subzoneIds = array_map('intval', $scopeContext['subzone_ids']);
  $invitationSql .= ' AND i.role = \'subzonal_admin\' AND i.zone_id = ? AND i.subzone_id IN (' . ($subzoneIds ? implode(',', $subzoneIds) : '0') . ')';
}
$invitationSql .= ' ORDER BY i.created_at DESC, i.id DESC';
$invitationStmt = mysqli_prepare($conn, $invitationSql);
if (!$invitationStmt) {
  error_log('Staff invitation list could not be prepared: ' . mysqli_error($conn));
  http_response_code(500);
  exit('Pending invitations are temporarily unavailable.');
}
if ($scopeContext['role'] === 'zonal_admin') {
  $zoneId = (int) $scopeContext['zone_id'];
  mysqli_stmt_bind_param($invitationStmt, 'ii', $orgId, $zoneId);
} else {
  mysqli_stmt_bind_param($invitationStmt, 'i', $orgId);
}
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
    <link rel="stylesheet" href="../css/dashboard.css?v=20261016-responsive-tables-1" />
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
      <p class="page-subtitle">Invite an existing member to an admin role. Their name and area are detected from their member email; invitees choose their own password.</p>
    </div>
  </div>

  <?php if (is_array($notice) && ($notice['type'] ?? '') !== 'success'): ?>
    <div class="dashboard-card" style="margin-bottom: 1.25rem;">
      <div class="alert-box error" role="status">
        <?php echo htmlspecialchars((string) ($notice['message'] ?? 'Please review the form and try again.'), ENT_QUOTES, 'UTF-8'); ?>
      </div>
    </div>
  <?php endif; ?>

  <div class="dashboard-card" style="margin-top: 1.5rem;">
    <form action="process-user.php" method="POST" id="userControlForm">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['user_control_csrf'], ENT_QUOTES, 'UTF-8'); ?>" />
      <div class="form-row-2col mb-3">
        <div class="form-group">
          <label for="email" class="form-label">Email</label>
          <input type="email" id="email" name="email" class="form-control" placeholder="Email address on their member record" maxlength="100" required />
        </div>
        <div class="form-group">
          <label for="role" class="form-label">Access role (assigned by the Super Admin)</label>
          <select id="role" name="role" class="form-select" required>
            <option value="" selected disabled>Choose their access role</option>
            <option value="admin">Admin — Association modules</option>
            <option value="zonal_admin">Zonal Admin — members in one zone</option>
            <option value="subzonal_admin">Sub-zonal Admin — members in one sub-zone</option>
          </select>
        </div>
      </div>

      <div class="form-row-2col mb-3">
        <div class="form-group">
          <label for="jobTitle" class="form-label">Job title (optional)</label>
          <input type="text" id="jobTitle" name="job_title" class="form-control" maxlength="200" />
        </div>
      </div>
      <div class="form-actions">
        <?php admin_info_tip("The email must match one active member in this Association. Their name, zone, and sub-zone come from that member record. The invitation link expires after 48 hours.", 'Invitation help'); ?>
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
                <td><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', (string) $invitation['role'])), ENT_QUOTES, 'UTF-8'); ?><?php if (!empty($invitation['subzone_name'])): ?><small class="cell-subtext"><?php echo htmlspecialchars($invitation['subzone_name'], ENT_QUOTES, 'UTF-8'); ?></small><?php elseif (!empty($invitation['zone_name'])): ?><small class="cell-subtext"><?php echo htmlspecialchars($invitation['zone_name'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?></td>
                <td><?php echo htmlspecialchars((string) $invitation['expires_at'], ENT_QUOTES, 'UTF-8'); ?> UTC</td>
                <td>
                  <form action="proc-invitation-action.php" method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['user_control_csrf'], ENT_QUOTES, 'UTF-8'); ?>" />
                    <input type="hidden" name="invitation_id" value="<?php echo (int) $invitation['id']; ?>" />
                    <button class="btn-navy-outline" type="submit" name="action" value="resend">Resend</button>
                    <button class="btn-navy-outline" type="submit" name="action" value="revoke" data-app-modal-confirm="Cancel this admin invitation?" data-confirm-title="Cancel invitation" data-confirm-detail="The invitation link will no longer work.">Cancel invitation</button>
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
        <p class="page-subtitle">Zonal role changes use the member area matched to that account's email.</p>
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
                  $canManageAccount = admin_can_manage_staff_account(
                    $user,
                    (int) ($_SESSION['user_id'] ?? 0),
                    $isPlatformAdmin
                  );
                ?>
                <td style="font-weight: 600; color: var(--text-primary);"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $user['role'])), ENT_QUOTES, 'UTF-8'); ?><?php if (!empty($user['subzone_name'])): ?><small class="cell-subtext"><?php echo htmlspecialchars($user['subzone_name'], ENT_QUOTES, 'UTF-8'); ?></small><?php elseif (!empty($user['zone_name'])): ?><small class="cell-subtext"><?php echo htmlspecialchars($user['zone_name'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?></td>
                <td><span class="badge-pill <?php echo $user['status'] === 'active' ? 'status-active' : 'status-inactive'; ?>"><?php echo htmlspecialchars($user['status'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                <td>
                  <?php if ($canManageAccount): ?>
                    <form action="proc-user-action.php" method="post" class="user-role-form" style="display: inline-flex; gap: .5rem; flex-wrap: wrap;">
                      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['user_control_csrf'], ENT_QUOTES, 'UTF-8'); ?>" />
                      <input type="hidden" name="account_id" value="<?php echo (int) $user['account_id']; ?>" />
                      <select class="form-select user-role-select" name="new_role" aria-label="New role for <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name'], ENT_QUOTES, 'UTF-8'); ?>">
                        <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        <option value="zonal_admin" <?php echo $user['role'] === 'zonal_admin' ? 'selected' : ''; ?>>Zonal Admin</option>
                        <option value="subzonal_admin" <?php echo $user['role'] === 'subzonal_admin' ? 'selected' : ''; ?>>Sub-zonal Admin</option>
                      </select>
                      <button class="btn-navy-outline" type="submit" name="action" value="change_role">Update role</button>
                      <?php if ($user['status'] === 'active'): ?>
                        <button class="btn-navy-outline" type="submit" name="action" value="disable" data-app-modal-confirm="Disable this admin account?" data-confirm-title="Disable admin account" data-confirm-detail="This person will lose access to the Association workspace.">Disable</button>
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
        <?php if (is_array($notice) && ($notice['type'] ?? '') === 'success'): ?>
          <script>
            document.addEventListener("DOMContentLoaded", () => {
              window.AppModal?.open({
                type: "success",
                heading: "Invitation sent",
                body: <?php echo json_encode((string) ($notice['message'] ?? ''), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>,
              });
            });
          </script>
        <?php endif; ?>
      </main>
    </div>

    <!-- Interactive Scripts -->
    <script>
    </script>
    <style>
      .alert-box {
        padding: 0.9rem 1rem;
        border-radius: 10px;
        font-weight: 600;
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