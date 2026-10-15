<?php
require_once 'inc/auth.php';
require_once '../../inc/db.php';

$memberId = (int) $_SESSION['member_id'];
$memberOrgId = (int) $_SESSION['member_org_id'];
$memberQuery = mysqli_prepare($conn, 'SELECT * FROM members WHERE id = ? AND org_id = ? LIMIT 1');
if (!$memberQuery) {
    error_log('Member edit profile lookup could not be prepared: ' . mysqli_error($conn));
    http_response_code(500);
    exit('Your profile could not be loaded right now.');
}
mysqli_stmt_bind_param($memberQuery, 'ii', $memberId, $memberOrgId);
if (!mysqli_stmt_execute($memberQuery)) {
    error_log('Member edit profile lookup failed: ' . mysqli_stmt_error($memberQuery));
    mysqli_stmt_close($memberQuery);
    http_response_code(500);
    exit('Your profile could not be loaded right now.');
}
$memberResult = mysqli_stmt_get_result($memberQuery);
$member = $memberResult ? mysqli_fetch_assoc($memberResult) : null;
mysqli_stmt_close($memberQuery);
if (!$member) {
    http_response_code(404);
    exit('Your member profile could not be found.');
}

$profileCsrfToken = $_SESSION['member_profile_csrf'] ?? '';
if (!is_string($profileCsrfToken) || $profileCsrfToken === '') {
    $profileCsrfToken = bin2hex(random_bytes(32));
    $_SESSION['member_profile_csrf'] = $profileCsrfToken;
}
$memberName = trim(($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? ''));
$memberStatus = ucfirst($member['status'] ?? 'active');
$initials = '';
foreach (explode(' ', $memberName) as $part) {
    if ($part !== '') {
        $initials .= strtoupper(substr($part, 0, 1));
    }
    if (strlen($initials) >= 2) {
        break;
    }
}
$memberInitials = $initials ?: 'M';
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Edit Profile - Associa8</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="shortcut icon" href="../../images/fav-logo.png" type="image/x-icon" />
    <link rel="stylesheet" href="../../css/preloader.css" />
    <link rel="stylesheet" href="../../css/dashboard.css?v=20261009-member-settings-2" />
  </head>
  <body class="admin-body">
    <?php include('../inc/preloader.php') ?>
    <div class="admin-layout">
      <?php include('inc/sidebar.php') ?>
      <div class="sidebar-overlay" id="sidebarOverlay"></div>
      <main class="admin-main">
        <header class="admin-header">
          <div class="header-left">
            <button class="header-toggle-btn" id="sidebarToggle" type="button" aria-label="Open menu">
              <i class="fa-solid fa-bars"></i>
            </button>
            <h1 class="page-title">Edit Profile</h1>
          </div>
          <div class="header-right">
            <div class="admin-user-profile">
              <div class="avatar-badge member-avatar" aria-label="<?php echo htmlspecialchars($memberAvatarLabel, ENT_QUOTES, 'UTF-8'); ?>">
                <?php if ($memberPhotoPath !== ''): ?>
                  <img src="<?php echo htmlspecialchars($memberPhotoPath, ENT_QUOTES, 'UTF-8'); ?>" alt="" />
                <?php else: ?>
                  <?php echo htmlspecialchars($memberInitials); ?>
                <?php endif; ?>
              </div>
              <span class="badge-pill status-active" style="margin-left: -0.5rem;"><?php echo htmlspecialchars($memberStatus); ?></span>
            </div>
          </div>
        </header>

        <div class="dashboard-content member-profile-edit-page">
          <a class="member-profile-back" href="profile.php"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to profile</a>

          <section class="info-card member-profile-edit">
            <div class="info-card-header">
              <div class="stat-card-icon" style="margin-bottom: 0;">
                <i class="fa-solid fa-pen-to-square"></i>
              </div>
              <div>
                <h2 class="info-card-title">Personal details</h2>
                <p class="member-profile-help">Update your personal and contact details. Membership information is managed by your organization.</p>
              </div>
            </div>

            <form action="proc-update-profile.php" method="post" class="member-profile-form">
              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($profileCsrfToken, ENT_QUOTES, 'UTF-8'); ?>" />
              <div class="member-profile-form-grid">
                <div class="form-group">
                  <label class="form-label" for="first-name">First name</label>
                  <input class="form-control" id="first-name" name="first_name" type="text" maxlength="50" required autocomplete="given-name" value="<?php echo htmlspecialchars((string) $member['first_name'], ENT_QUOTES, 'UTF-8'); ?>" />
                </div>
                <div class="form-group">
                  <label class="form-label" for="last-name">Last name</label>
                  <input class="form-control" id="last-name" name="last_name" type="text" maxlength="50" required autocomplete="family-name" value="<?php echo htmlspecialchars((string) $member['last_name'], ENT_QUOTES, 'UTF-8'); ?>" />
                </div>
                <div class="form-group">
                  <label class="form-label" for="profile-email">Email address</label>
                  <input class="form-control" id="profile-email" name="email" type="email" maxlength="100" required autocomplete="email" value="<?php echo htmlspecialchars((string) $member['email'], ENT_QUOTES, 'UTF-8'); ?>" />
                </div>
                <div class="form-group">
                  <label class="form-label" for="profile-phone">Phone number</label>
                  <input class="form-control" id="profile-phone" name="phone" type="tel" maxlength="20" autocomplete="tel" value="<?php echo htmlspecialchars((string) ($member['phone'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" />
                </div>
                <div class="form-group">
                  <label class="form-label" for="date-of-birth">Date of birth</label>
                  <input class="form-control" id="date-of-birth" name="date_of_birth" type="date" value="<?php echo htmlspecialchars((string) ($member['date_of_birth'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" />
                </div>
                <div class="form-group">
                  <label class="form-label" for="occupation">Occupation</label>
                  <input class="form-control" id="occupation" name="occupation" type="text" maxlength="100" value="<?php echo htmlspecialchars((string) ($member['occupation'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" />
                </div>
                <div class="form-group">
                  <label class="form-label" for="state-of-origin">State of origin</label>
                  <input class="form-control" id="state-of-origin" name="state_of_origin" type="text" maxlength="100" value="<?php echo htmlspecialchars((string) ($member['state_of_origin'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" />
                </div>
                <div class="form-group">
                  <label class="form-label" for="emergency-contact-name">Emergency contact name</label>
                  <input class="form-control" id="emergency-contact-name" name="emergency_contact_name" type="text" maxlength="150" autocomplete="off" value="<?php echo htmlspecialchars((string) ($member['emergency_contact_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" />
                </div>
                <div class="form-group">
                  <label class="form-label" for="emergency-contact-phone">Emergency contact phone</label>
                  <input class="form-control" id="emergency-contact-phone" name="emergency_contact_phone" type="tel" maxlength="30" autocomplete="off" value="<?php echo htmlspecialchars((string) ($member['emergency_contact_phone'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" />
                </div>
                <div class="form-group member-profile-address">
                  <label class="form-label" for="home-address">Home address</label>
                  <textarea class="form-control" id="home-address" name="home_address" rows="3" maxlength="500" autocomplete="street-address"><?php echo htmlspecialchars((string) ($member['home_address'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>
              </div>
              <div class="member-profile-form-actions">
                <button class="btn-summary-action member-profile-save" type="submit">Save changes</button>
              </div>
            </form>
          </section>

          <section class="info-card member-profile-edit" id="profile-photo">
            <div class="info-card-header">
              <div class="stat-card-icon" style="margin-bottom: 0;">
                <i class="fa-solid fa-camera"></i>
              </div>
              <div>
                <h2 class="info-card-title">Profile photo</h2>
                <p class="member-profile-help">Choose a JPEG, PNG, or WebP image up to 3 MB.</p>
              </div>
            </div>
            <?php if (!empty($member['profile_photo_path'])): ?>
              <img class="member-profile-current-photo" src="<?php echo htmlspecialchars($member['profile_photo_path'], ENT_QUOTES, 'UTF-8'); ?>" alt="Current profile photo" />
            <?php endif; ?>
            <form action="proc-upload-profile-photo.php" method="post" enctype="multipart/form-data" class="member-profile-form">
              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($profileCsrfToken, ENT_QUOTES, 'UTF-8'); ?>" />
              <div class="form-group">
                <label class="form-label" for="profile-photo-file">Choose photo</label>
                <input class="form-control" id="profile-photo-file" name="profile_photo" type="file" accept="image/jpeg,image/png,image/webp" required />
              </div>
              <div class="member-profile-form-actions">
                <button class="btn-summary-action member-profile-save" type="submit">Upload photo</button>
              </div>
            </form>
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
      function openSidebar() {
        sidebarEl.classList.add("open");
        if (sidebarOverlay) sidebarOverlay.classList.add("active");
      }
      function closeSidebar() {
        sidebarEl.classList.remove("open");
        if (sidebarOverlay) sidebarOverlay.classList.remove("active");
      }
      if (sidebarToggle) sidebarToggle.addEventListener("click", () => sidebarEl.classList.contains("open") ? closeSidebar() : openSidebar());
      if (sidebarCloseBtn) sidebarCloseBtn.addEventListener("click", closeSidebar);
      if (sidebarOverlay) sidebarOverlay.addEventListener("click", closeSidebar);
      document.addEventListener("keydown", (event) => {
        if (event.key === "Escape") closeSidebar();
      });
      sidebarEl.querySelectorAll(".sidebar-link").forEach((link) => link.addEventListener("click", closeSidebar));
    </script>
    <script src="../../js/preloader.js"></script>
  </body>
</html>
