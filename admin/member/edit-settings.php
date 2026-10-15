<?php
require_once 'inc/auth.php';
require_once '../../inc/db.php';

$section = $_GET['section'] ?? '';
if (!is_string($section) || !in_array($section, ['security', 'regional'], true)) {
  header('Location: settings.php');
  exit;
}

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

$settingsCsrf = $_SESSION['member_settings_csrf'] ?? '';
if (!is_string($settingsCsrf) || $settingsCsrf === '') {
  $settingsCsrf = bin2hex(random_bytes(32));
  $_SESSION['member_settings_csrf'] = $settingsCsrf;
}
$safe = static function ($value): string {
  return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$languageOptions = ['English (Nigeria)', 'English (United Kingdom)', 'English (United States)'];
$timezoneOptions = ['Africa/Lagos', 'Africa/Accra', 'Africa/Nairobi', 'UTC', 'Europe/London', 'America/New_York'];
$dateFormatOptions = ['DD/MM/YYYY', 'MM/DD/YYYY', 'YYYY-MM-DD'];
$currencyOptions = ['Nigeria/Naira', 'Ghana/Cedi', 'Kenya/Shilling', 'United States/Dollar', 'United Kingdom/Pound'];
$pageTitle = $section === 'security' ? 'Change Password' : 'Regional & Display';
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo $safe($pageTitle); ?> - Associa8</title>
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
            <h1 class="page-title"><?php echo $safe($pageTitle); ?></h1>
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
          <a class="settings-back-link" href="settings.php"><i class="fa-solid fa-arrow-left"></i> Back to Settings</a>
          <div class="section-label"><?php echo $section === 'security' ? 'Security' : 'Preferences'; ?></div>
          <section class="settings-grid">
            <?php if ($section === 'security'): ?>
              <form action="proc-change-member-password.php" method="post" class="settings-card member-settings-detail-card">
                <input type="hidden" name="csrf_token" value="<?php echo $safe($settingsCsrf); ?>" />
                <div class="settings-card-header"><i class="fa-solid fa-lock"></i> Change Password</div>
                <div class="settings-card-body member-settings-detail-body">
                  <p class="member-settings-help">Choose a new password between 12 and 72 characters.</p>
                  <div class="member-settings-password-fields">
                    <div class="form-group">
                      <label class="form-label" for="current-password">Current password</label>
                      <input class="form-control" id="current-password" name="current_password" type="password" autocomplete="current-password" required />
                    </div>
                    <div class="form-group">
                      <label class="form-label" for="new-password">New password</label>
                      <input class="form-control" id="new-password" name="new_password" type="password" minlength="12" maxlength="72" autocomplete="new-password" required />
                    </div>
                    <div class="form-group">
                      <label class="form-label" for="confirm-password">Confirm new password</label>
                      <input class="form-control" id="confirm-password" name="confirm_password" type="password" minlength="12" maxlength="72" autocomplete="new-password" required />
                    </div>
                  </div>
                  <div class="member-settings-actions">
                    <button class="btn-settings-action" type="submit">Update password</button>
                  </div>
                </div>
              </form>
            <?php else: ?>
              <form action="proc-member-settings.php" method="post" class="settings-card member-settings-detail-card">
                <input type="hidden" name="csrf_token" value="<?php echo $safe($settingsCsrf); ?>" />
                <input type="hidden" name="settings_group" value="regional" />
                <div class="settings-card-header"><i class="fa-solid fa-globe"></i> Regional &amp; Display</div>
                <div class="settings-card-body">
                  <div class="member-settings-regional-grid">
                    <div class="form-group">
                      <label class="form-label" for="member-language">Language</label>
                      <select class="form-control" id="member-language" name="language">
                        <?php foreach ($languageOptions as $option): ?>
                          <option value="<?php echo $safe($option); ?>" <?php echo ($member['language'] ?? 'English (Nigeria)') === $option ? 'selected' : ''; ?>><?php echo $safe($option); ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="form-group">
                      <label class="form-label" for="member-timezone">Timezone</label>
                      <select class="form-control" id="member-timezone" name="timezone">
                        <?php if (!in_array($member['timezone'] ?? '', $timezoneOptions, true)): ?>
                          <option value="<?php echo $safe($member['timezone'] ?? ''); ?>" selected><?php echo $safe($member['timezone'] ?? ''); ?></option>
                        <?php endif; ?>
                        <?php foreach ($timezoneOptions as $option): ?>
                          <option value="<?php echo $safe($option); ?>" <?php echo ($member['timezone'] ?? 'Africa/Lagos') === $option ? 'selected' : ''; ?>><?php echo $safe($option); ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="form-group">
                      <label class="form-label" for="member-date-format">Date format</label>
                      <select class="form-control" id="member-date-format" name="date_format">
                        <?php foreach ($dateFormatOptions as $option): ?>
                          <option value="<?php echo $safe($option); ?>" <?php echo ($member['date_format'] ?? 'DD/MM/YYYY') === $option ? 'selected' : ''; ?>><?php echo $safe($option); ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="form-group">
                      <label class="form-label" for="member-currency">Currency display</label>
                      <select class="form-control" id="member-currency" name="currency_display">
                        <?php foreach ($currencyOptions as $option): ?>
                          <option value="<?php echo $safe($option); ?>" <?php echo ($member['currency_display'] ?? 'Nigeria/Naira') === $option ? 'selected' : ''; ?>><?php echo $safe($option); ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                  <div class="member-settings-actions">
                    <button class="btn-settings-action" type="submit">Save preferences</button>
                  </div>
                </div>
              </form>
            <?php endif; ?>
          </section>
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
      sidebarEl.querySelectorAll(".sidebar-link").forEach((link) => link.addEventListener("click", closeSidebar));
    </script>
    <?php if ($section === 'security'): ?>
      <script src="../../js/password-visibility.js?v=20261009-password-toggle-3"></script>
    <?php endif; ?>
    <script src="../../js/preloader.js"></script>
  </body>
</html>
