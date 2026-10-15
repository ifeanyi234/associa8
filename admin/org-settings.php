<?php
require_once 'inc/auth.php';
require_once '../inc/db.php';

$orgId = (int) ($_SESSION['org_id'] ?? 0);
$accountId = (int) ($_SESSION['user_id'] ?? 0);
if ($orgId < 1 || $accountId < 1) {
    http_response_code(403);
    exit('Organization settings are not available for this account.');
}

$organizationQuery = mysqli_prepare(
    $conn,
    'SELECT name, type, email, phone, country, state, pricing, `total-members`
     FROM `org-info` WHERE id = ? LIMIT 1'
);
if (!$organizationQuery) {
    error_log('Organization settings lookup could not be prepared: ' . mysqli_error($conn));
    http_response_code(500);
    exit('Organization settings could not be loaded right now.');
}
mysqli_stmt_bind_param($organizationQuery, 'i', $orgId);
if (!mysqli_stmt_execute($organizationQuery)) {
    error_log('Organization settings lookup failed: ' . mysqli_stmt_error($organizationQuery));
    mysqli_stmt_close($organizationQuery);
    http_response_code(500);
    exit('Organization settings could not be loaded right now.');
}
$organizationResult = mysqli_stmt_get_result($organizationQuery);
$organization = $organizationResult ? mysqli_fetch_assoc($organizationResult) : null;
mysqli_stmt_close($organizationQuery);
if (!$organization) {
    http_response_code(404);
    exit('The organization could not be found.');
}

$administratorStmt = mysqli_prepare(
    $conn,
    "SELECT ai.id AS admin_id, ai.`first-name` AS first_name, ai.`last-name` AS last_name,
            ai.email, ai.phone, ai.`job-title` AS job_title, ai.role,
            a.id AS account_id, a.username, a.status AS account_status
     FROM `admin-info` ai
     INNER JOIN `acc-info` a ON a.id = ai.acc_id AND a.org_id = ai.org_id
     WHERE ai.org_id = ? AND a.id = ?
     ORDER BY ai.id ASC
     LIMIT 1"
);
if (!$administratorStmt) {
    error_log('Organization administrator details could not be prepared: ' . mysqli_error($conn));
    http_response_code(500);
    exit('Organization administrator details could not be loaded right now.');
}
mysqli_stmt_bind_param($administratorStmt, 'ii', $orgId, $accountId);
if (!mysqli_stmt_execute($administratorStmt)) {
    error_log('Organization administrator details query failed: ' . mysqli_stmt_error($administratorStmt));
    mysqli_stmt_close($administratorStmt);
    http_response_code(500);
    exit('Organization administrator details could not be loaded right now.');
}
$administratorResult = mysqli_stmt_get_result($administratorStmt);
$administrator = $administratorResult ? mysqli_fetch_assoc($administratorResult) : null;
mysqli_stmt_close($administratorStmt);
if (!$administrator) {
    http_response_code(404);
    exit('Your administrator profile could not be found.');
}

$csrfToken = $_SESSION['org_settings_csrf'] ?? '';
if (!is_string($csrfToken) || $csrfToken === '') {
    $csrfToken = bin2hex(random_bytes(32));
    $_SESSION['org_settings_csrf'] = $csrfToken;
}
$status = $_GET['status'] ?? '';
$organizationTypes = [
    'association' => 'Association',
    'church' => 'Church / Religious Institution',
    'ngo' => 'NGO / Non-Profit',
    'club' => 'Club / Society',
    'corporate' => 'Corporate / Business',
];
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Organization Settings - Associa8</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="shortcut icon" href="../images/fav-logo.png" type="image/x-icon" />
    <link rel="stylesheet" href="../css/preloader.css" />
    <link rel="stylesheet" href="../css/dashboard.css?v=20261013-org-settings-3" />
  </head>
  <body class="admin-body">
    <?php include('inc/preloader.php') ?>
    <div class="admin-layout">
      <?php include('inc/sidebar.php') ?>
      <div class="sidebar-overlay" id="sidebarOverlay"></div>
      <main class="admin-main">
        <header class="admin-header">
          <div class="header-left">
            <button class="header-toggle-btn" id="sidebarToggle" type="button" aria-label="Open menu">
              <i class="fa-solid fa-bars"></i>
            </button>
            <h1 class="page-title">Organization Settings</h1>
          </div>
          <div class="header-right">
            <div class="admin-user-profile">
              <div class="avatar-badge"><?php echo htmlspecialchars(strtoupper(substr((string) ($_SESSION['admin_name'] ?? 'OR'), 0, 2)), ENT_QUOTES, 'UTF-8'); ?></div>
              <div class="user-info">
                <span class="user-name"><?php echo htmlspecialchars((string) ($_SESSION['admin_name'] ?? 'Administrator'), ENT_QUOTES, 'UTF-8'); ?></span>
                <span class="user-role"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', (string) ($_SESSION['admin_role'] ?? ''))), ENT_QUOTES, 'UTF-8'); ?></span>
              </div>
            </div>
          </div>
        </header>

        <div class="dashboard-content">
          <div class="page-action-header mb-4">
            <div>
              <h2 class="page-title-main">Organization details</h2>
              <p class="page-subtitle">Update your organization, administrator profile, and login account details.</p>
            </div>
          </div>

          <section class="info-card member-profile-edit">
            <form action="proc-org-settings.php" method="post">
              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>" />
              <div class="member-profile-form-grid">
                <div class="form-group">
                  <label class="form-label" for="organization-name">Organization name</label>
                  <input class="form-control" id="organization-name" name="name" type="text" maxlength="100" required value="<?php echo htmlspecialchars((string) $organization['name'], ENT_QUOTES, 'UTF-8'); ?>" />
                </div>
                <div class="form-group">
                  <label class="form-label" for="organization-type">Organization type</label>
                  <select class="form-control" id="organization-type" name="type" required>
                    <?php foreach ($organizationTypes as $value => $label): ?>
                      <option value="<?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $organization['type'] === $value ? 'selected' : ''; ?>><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="form-group">
                  <label class="form-label" for="organization-email">Contact email</label>
                  <input class="form-control" id="organization-email" name="email" type="email" maxlength="100" required value="<?php echo htmlspecialchars((string) $organization['email'], ENT_QUOTES, 'UTF-8'); ?>" />
                </div>
                <div class="form-group">
                  <label class="form-label" for="organization-phone">Contact phone</label>
                  <input class="form-control" id="organization-phone" name="phone" type="tel" maxlength="30" value="<?php echo htmlspecialchars((string) ($organization['phone'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" />
                </div>
                <div class="form-group">
                  <label class="form-label" for="organization-country">Country</label>
                  <input class="form-control" id="organization-country" name="country" type="text" maxlength="50" required value="<?php echo htmlspecialchars((string) $organization['country'], ENT_QUOTES, 'UTF-8'); ?>" />
                </div>
                <div class="form-group">
                  <label class="form-label" for="organization-state">State / Region</label>
                  <input class="form-control" id="organization-state" name="state" type="text" maxlength="40" required value="<?php echo htmlspecialchars((string) $organization['state'], ENT_QUOTES, 'UTF-8'); ?>" />
                </div>
              </div>

              <h2 class="page-title-main" style="font-size: 1.2rem; margin: 1.5rem 0 1rem;">Administrator details</h2>
              <div class="member-profile-form-grid">
                <div class="form-group">
                  <label class="form-label" for="admin-first-name">First name</label>
                  <input class="form-control" id="admin-first-name" name="first_name" type="text" maxlength="100" required value="<?php echo htmlspecialchars((string) $administrator['first_name'], ENT_QUOTES, 'UTF-8'); ?>" />
                </div>
                <div class="form-group">
                  <label class="form-label" for="admin-last-name">Last name</label>
                  <input class="form-control" id="admin-last-name" name="last_name" type="text" maxlength="100" required value="<?php echo htmlspecialchars((string) $administrator['last_name'], ENT_QUOTES, 'UTF-8'); ?>" />
                </div>
                <div class="form-group">
                  <label class="form-label" for="admin-email">Administrator email / login email</label>
                  <input class="form-control" id="admin-email" name="admin_email" type="email" maxlength="150" required value="<?php echo htmlspecialchars((string) $administrator['email'], ENT_QUOTES, 'UTF-8'); ?>" />
                </div>
                <div class="form-group">
                  <label class="form-label" for="admin-phone">Phone</label>
                  <input class="form-control" id="admin-phone" name="admin_phone" type="tel" maxlength="19" value="<?php echo htmlspecialchars((string) ($administrator['phone'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" />
                </div>
                <div class="form-group">
                  <label class="form-label" for="admin-job-title">Job title</label>
                  <input class="form-control" id="admin-job-title" name="job_title" type="text" maxlength="200" required value="<?php echo htmlspecialchars((string) $administrator['job_title'], ENT_QUOTES, 'UTF-8'); ?>" />
                </div>
                <div class="form-group">
                  <label class="form-label" for="admin-role">Role</label>
                  <input class="form-control" id="admin-role" type="text" value="<?php echo htmlspecialchars(ucwords(str_replace('_', ' ', (string) $administrator['role'])), ENT_QUOTES, 'UTF-8'); ?>" disabled />
                </div>
              </div>

              <h2 class="page-title-main" style="font-size: 1.2rem; margin: 1.5rem 0 1rem;">Login account details</h2>
              <div class="member-profile-form-grid">
                <div class="form-group">
                  <label class="form-label" for="account-username">Username</label>
                  <input class="form-control" id="account-username" name="username" type="text" maxlength="100" required autocomplete="username" value="<?php echo htmlspecialchars((string) $administrator['username'], ENT_QUOTES, 'UTF-8'); ?>" />
                </div>
                <div class="form-group">
                  <label class="form-label" for="account-status">Account status</label>
                  <input class="form-control" id="account-status" type="text" value="<?php echo htmlspecialchars(ucfirst((string) $administrator['account_status']), ENT_QUOTES, 'UTF-8'); ?>" disabled />
                </div>
                <div class="form-group">
                  <label class="form-label" for="current-password">Current password (only required to change password)</label>
                  <input class="form-control" id="current-password" name="current_password" type="password" autocomplete="current-password" />
                </div>
                <div class="form-group">
                  <label class="form-label" for="new-password">New password (optional)</label>
                  <input class="form-control" id="new-password" name="new_password" type="password" minlength="12" maxlength="72" autocomplete="new-password" />
                </div>
                <div class="form-group">
                  <label class="form-label" for="confirm-password">Confirm new password</label>
                  <input class="form-control" id="confirm-password" name="confirm_password" type="password" minlength="12" maxlength="72" autocomplete="new-password" />
                </div>
              </div>
              <p class="member-profile-help">Leave the password fields blank to keep your current password. Your role and account status are managed through User Control.</p>

              <div class="org-settings-readonly">
                <div><span>Current plan</span><strong><?php echo htmlspecialchars(ucfirst((string) $organization['pricing']), ENT_QUOTES, 'UTF-8'); ?></strong></div>
                <div><span>Member count</span><strong><?php echo htmlspecialchars((string) $organization['total-members'], ENT_QUOTES, 'UTF-8'); ?></strong></div>
              </div>
              <p class="member-profile-help">Plan and member count are managed by Associa8 and cannot be changed here.</p>
              <div class="member-profile-form-actions">
                <button class="btn-summary-action member-profile-save" type="submit">Save all details</button>
              </div>
            </form>
          </section>

        </div>
        <?php include('inc/footer.php') ?>
      </main>
    </div>
    <script>
      window.addEventListener("DOMContentLoaded", () => {
        const status = new URLSearchParams(window.location.search).get("status");
        if (!status || !window.AppModal) return;
        const success = status === "success";
        const knownStatus = success || status === "error" || status === "invalid" || status === "duplicate" || status === "password" || status === "csrf";
        if (!knownStatus) return;
        const copy = success
          ? ["Settings saved", "Your organization, administrator, and login account details were updated."]
          : status === "invalid"
            ? ["Check the organization details", "Some values are missing or invalid. Review the form and try again."]
            : status === "duplicate"
              ? ["Details already in use", "That username or email address is already used by another account."]
            : status === "password"
              ? ["Password was not changed", "Check your current password and make sure the new password and confirmation match (at least 12 characters)."]
            : status === "csrf"
              ? ["Form expired", "Reload this page and submit your changes again."]
              : ["Settings not saved", "We could not save the organization details. Please try again."];
        window.AppModal.open({ type: success ? "success" : "error", heading: copy[0], body: copy[1] });
        window.history.replaceState({}, document.title, window.location.pathname);
      });
    </script>
    <script src="../js/admin-sidebar.js"></script>
    <script src="../js/preloader.js"></script>
  </body>
</html>
