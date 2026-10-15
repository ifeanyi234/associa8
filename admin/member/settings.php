<?php
require_once 'inc/auth.php';
require_once '../../inc/db.php';

$memberId = (int) $_SESSION['member_id'];
$memberOrgId = (int) $_SESSION['member_org_id'];
$memberQuery = mysqli_prepare($conn, 'SELECT * FROM members WHERE id = ? AND org_id = ? LIMIT 1');
if (!$memberQuery) {
  error_log('Member settings lookup could not be prepared: ' . mysqli_error($conn));
  http_response_code(500);
  exit('Your settings could not be loaded right now.');
}
mysqli_stmt_bind_param($memberQuery, 'ii', $memberId, $memberOrgId);
if (!mysqli_stmt_execute($memberQuery)) {
  error_log('Member settings lookup failed: ' . mysqli_stmt_error($memberQuery));
  mysqli_stmt_close($memberQuery);
  http_response_code(500);
  exit('Your settings could not be loaded right now.');
}
$memberResult = mysqli_stmt_get_result($memberQuery);
$member = $memberResult ? mysqli_fetch_assoc($memberResult) : null;
mysqli_stmt_close($memberQuery);
if (!$member) {
  http_response_code(404);
  exit('Your member profile could not be found.');
}

$preferenceQuery = mysqli_prepare(
  $conn,
  'SELECT email_due_reminder, email_event_reminder, email_general_announcement, sms_reminder
   FROM member_notification_preferences WHERE member_id = ? LIMIT 1'
);
if (!$preferenceQuery) {
  error_log('Member notification preferences lookup could not be prepared: ' . mysqli_error($conn));
  http_response_code(500);
  exit('Your notification settings could not be loaded right now.');
}
mysqli_stmt_bind_param($preferenceQuery, 'i', $memberId);
if (!mysqli_stmt_execute($preferenceQuery)) {
  error_log('Member notification preferences lookup failed: ' . mysqli_stmt_error($preferenceQuery));
  mysqli_stmt_close($preferenceQuery);
  http_response_code(500);
  exit('Your notification settings could not be loaded right now.');
}
$preferenceResult = mysqli_stmt_get_result($preferenceQuery);
$notificationPreferences = $preferenceResult ? mysqli_fetch_assoc($preferenceResult) : null;
mysqli_stmt_close($preferenceQuery);
$notificationPreferences = $notificationPreferences ?: [
  'email_due_reminder' => 1,
  'email_event_reminder' => 1,
  'email_general_announcement' => 1,
  'sms_reminder' => 1,
];

$memberName = trim(($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? ''));
$settingsCsrf = $_SESSION['member_settings_csrf'] ?? '';
if (!is_string($settingsCsrf) || $settingsCsrf === '') {
  $settingsCsrf = bin2hex(random_bytes(32));
  $_SESSION['member_settings_csrf'] = $settingsCsrf;
}
$safe = static function ($value): string {
  return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Settings - Associa8</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="shortcut icon" href="../../images/fav-logo.png" type="image/x-icon" />
    <link rel="stylesheet" href="../../css/preloader.css" />
    <link rel="stylesheet" href="../../css/dashboard.css?v=20261009-member-settings-4" />
  </head>
  <body class="admin-body">
    <?php include('../inc/preloader.php') ?>
    <div class="admin-layout">
      <?php include('inc/sidebar.php') ?>
      <div class="sidebar-overlay" id="sidebarOverlay"></div>

      <main class="admin-main">
        <header class="admin-header">
          <div class="header-left">
            <button class="header-toggle-btn" id="sidebarToggle" type="button" aria-label="Open sidebar">
              <i class="fa-solid fa-bars"></i>
            </button>
            <h1 class="page-title">Settings</h1>
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
              <div class="avatar-badge member-avatar" aria-label="<?php echo $safe($memberAvatarLabel); ?>">
                <?php if ($memberPhotoPath !== ''): ?>
                  <img src="<?php echo $safe($memberPhotoPath); ?>" alt="" />
                <?php else: ?>
                  <?php echo $safe($memberInitials); ?>
                <?php endif; ?>
              </div>
              <span class="badge-pill status-active" style="margin-left: -0.5rem;"><?php echo $safe(ucfirst($member['status'] ?? 'active')); ?></span>
            </div>
          </div>
        </header>

        <div class="dashboard-content">
          <div class="section-label">Preference</div>
          <section class="settings-grid">
            <div class="settings-card">
              <div class="settings-card-header"><i class="fa-regular fa-user"></i> Account</div>
              <div class="settings-card-body">
                <div class="settings-row">
                  <div><div class="settings-row-label">Full Name</div><div class="settings-row-value"><?php echo $safe($memberName); ?></div></div>
                  <a class="btn-settings-action" href="edit-profile.php">Edit</a>
                </div>
                <div class="settings-row">
                  <div><div class="settings-row-label">Email Address</div><div class="settings-row-value"><?php echo $safe($member['email'] ?? 'Not set'); ?></div></div>
                  <a class="btn-settings-action" href="edit-profile.php">Edit</a>
                </div>
                <div class="settings-row">
                  <div><div class="settings-row-label">Phone Number</div><div class="settings-row-value"><?php echo $safe($member['phone'] ?? 'Not set'); ?></div></div>
                  <a class="btn-settings-action" href="edit-profile.php">Edit</a>
                </div>
                <div class="settings-row">
                  <div><div class="settings-row-label">Member ID</div><div class="settings-row-value"><?php echo $safe($member['member_code'] ?? 'N/A'); ?></div></div>
                  <span class="settings-row-note">Managed by your organization</span>
                </div>
              </div>
            </div>

            <div class="settings-card">
              <div class="settings-card-header"><i class="fa-solid fa-lock"></i> Security</div>
              <div class="settings-card-body">
                <div class="settings-row">
                  <div><div class="settings-row-label">Password</div><div class="settings-row-value">Keep your account secure with a strong password</div></div>
                  <a class="btn-settings-action" href="edit-settings.php?section=security">Change</a>
                </div>
                <div class="settings-row">
                  <div>
                    <div class="settings-row-label">Two-Factor Authentication</div>
                    <div class="settings-row-value"><?php echo !empty($member['two_factor_enabled']) ? 'Marked enabled, but sign-in does not verify it yet.' : 'Unavailable until secure verification is added to sign-in.'; ?></div>
                  </div>
                  <label class="form-switch">
                    <input type="checkbox" class="form-switch-input" disabled aria-label="Two-factor authentication unavailable" <?php echo !empty($member['two_factor_enabled']) ? 'checked' : ''; ?> />
                  </label>
                </div>
                <div class="settings-row">
                  <div><div class="settings-row-label">Active Session</div><div class="settings-row-value">Session management is not available yet.</div></div>
                  <button class="btn-settings-action danger" type="button" disabled>Revoke All</button>
                </div>
              </div>
            </div>

            <form action="proc-member-settings.php" method="post" class="settings-card" id="notification-preferences">
              <input type="hidden" name="csrf_token" value="<?php echo $safe($settingsCsrf); ?>" />
              <input type="hidden" name="settings_group" value="notifications" />
              <div class="settings-card-header"><i class="fa-solid fa-bell"></i> Notification Preference</div>
              <div class="settings-card-body">
                <div class="settings-row">
                  <div><div class="settings-row-label">Emails - Due Reminder</div><div class="settings-row-value" style="font-weight: 400; color: var(--text-secondary); font-size: 0.85rem;">Get notified when dues is approaching</div></div>
                  <label class="form-switch"><input type="checkbox" name="email_due_reminder" class="form-switch-input" value="1" <?php echo !empty($notificationPreferences['email_due_reminder']) ? 'checked' : ''; ?> /></label>
                </div>
                <div class="settings-row">
                  <div><div class="settings-row-label">Emails - Event Reminder</div><div class="settings-row-value" style="font-weight: 400; color: var(--text-secondary); font-size: 0.85rem;">Receive event announcements</div></div>
                  <label class="form-switch"><input type="checkbox" name="email_event_reminder" class="form-switch-input" value="1" <?php echo !empty($notificationPreferences['email_event_reminder']) ? 'checked' : ''; ?> /></label>
                </div>
                <div class="settings-row">
                  <div><div class="settings-row-label">Email - General Announcement</div><div class="settings-row-value" style="font-weight: 400; color: var(--text-secondary); font-size: 0.85rem;">Receive newsletter and admin updates</div></div>
                  <label class="form-switch"><input type="checkbox" name="email_general_announcement" class="form-switch-input" value="1" <?php echo !empty($notificationPreferences['email_general_announcement']) ? 'checked' : ''; ?> /></label>
                </div>
                <div class="settings-row">
                  <div><div class="settings-row-label">SMS Reminder</div><div class="settings-row-value" style="font-weight: 400; color: var(--text-secondary); font-size: 0.85rem;">Text message reminders for key updates</div></div>
                  <label class="form-switch"><input type="checkbox" name="sms_reminder" class="form-switch-input" value="1" <?php echo !empty($notificationPreferences['sms_reminder']) ? 'checked' : ''; ?> /></label>
                </div>
              </div>
            </form>

            <div class="settings-card">
              <div class="settings-card-header"><i class="fa-solid fa-globe"></i> Regional &amp; Display</div>
              <div class="settings-card-body">
                <a href="edit-settings.php?section=regional" class="settings-row settings-row-link">
                  <div><div class="settings-row-label">Language</div><div class="settings-row-value"><?php echo $safe($member['language'] ?? 'English (Nigeria)'); ?></div></div>
                  <i class="fa-solid fa-chevron-right settings-row-chevron"></i>
                </a>
                <a href="edit-settings.php?section=regional" class="settings-row settings-row-link">
                  <div><div class="settings-row-label">Timezone</div><div class="settings-row-value"><?php echo $safe($member['timezone'] ?? 'Africa/Lagos'); ?></div></div>
                  <i class="fa-solid fa-chevron-right settings-row-chevron"></i>
                </a>
                <a href="edit-settings.php?section=regional" class="settings-row settings-row-link">
                  <div><div class="settings-row-label">Date / Format</div><div class="settings-row-value"><?php echo $safe($member['date_format'] ?? 'DD/MM/YYYY'); ?></div></div>
                  <i class="fa-solid fa-chevron-right settings-row-chevron"></i>
                </a>
                <a href="edit-settings.php?section=regional" class="settings-row settings-row-link">
                  <div><div class="settings-row-label">Currency Display</div><div class="settings-row-value"><?php echo $safe($member['currency_display'] ?? 'Nigeria/Naira'); ?></div></div>
                  <i class="fa-solid fa-chevron-right settings-row-chevron"></i>
                </a>
              </div>
            </div>
          </section>

          <div class="danger-zone-card">
            <div>
              <div class="danger-zone-title">Danger Zone</div>
              <div class="danger-zone-desc">Deactivate Account</div>
              <div class="danger-zone-sub">Account deactivation is not available here. Contact your organization administrator for help.</div>
            </div>
            <button class="btn-settings-action danger" type="button" disabled>Deactivate</button>
          </div>
        </div>

        <?php include('../inc/footer.php') ?>
      </main>
    </div>
    <script>
      const sidebarEl = document.getElementById("adminSidebar");
      const sidebarOverlay = document.getElementById("sidebarOverlay");
      const sidebarToggle = document.getElementById("sidebarToggle");
      const sidebarCloseBtn = document.getElementById("sidebarCloseBtn");

      function closeSidebar() {
        sidebarEl.classList.remove("open");
        if (sidebarOverlay) sidebarOverlay.classList.remove("active");
      }
      if (sidebarToggle) {
        sidebarToggle.addEventListener("click", () => {
          const isOpen = sidebarEl.classList.toggle("open");
          if (sidebarOverlay) sidebarOverlay.classList.toggle("active", isOpen);
        });
      }
      if (sidebarCloseBtn) sidebarCloseBtn.addEventListener("click", closeSidebar);
      if (sidebarOverlay) sidebarOverlay.addEventListener("click", closeSidebar);
      document.addEventListener("keydown", (event) => {
        if (event.key === "Escape") closeSidebar();
      });
      sidebarEl.querySelectorAll(".sidebar-link").forEach((link) => {
        link.addEventListener("click", closeSidebar);
      });

      const notificationForm = document.getElementById("notification-preferences");
      notificationForm.addEventListener("change", (event) => {
        if (event.target.matches('input[type="checkbox"]')) notificationForm.requestSubmit();
      });

      window.addEventListener("DOMContentLoaded", () => {
        const status = new URLSearchParams(window.location.search).get("status");
        const messages = {
          success: ["Preferences saved", "Your settings have been saved."],
          "password-success": ["Password changed", "Your password has been changed."],
          invalid: ["Check your preferences", "Some values were invalid. Review your choices and try again."],
          "password-invalid": ["Password not changed", "Choose a password between 12 and 72 characters and confirm it correctly."],
          "password-mismatch": ["Passwords do not match", "Enter the same new password in both fields."],
          "password-error": ["Password not changed", "The current password could not be verified. Check it and try again."],
          csrf: ["Form expired", "Reload the settings page and try again."],
          error: ["Settings not saved", "We could not save your settings. Please try again."],
        };
        if (!messages[status] || !window.AppModal) return;
        window.AppModal.open({
          type: status === "success" || status === "password-success" ? "success" : "error",
          heading: messages[status][0],
          body: messages[status][1],
        });
        window.history.replaceState({}, document.title, window.location.pathname);
      });
    </script>
    <script src="../../js/preloader.js"></script>
  </body>
</html>
