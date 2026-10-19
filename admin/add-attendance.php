<?php
require_once 'inc/auth.php';
require_once '../inc/db.php';

$orgId = isset($_SESSION['org_id']) ? (int) $_SESSION['org_id'] : 0;
$isPlatformAdmin = ($_SESSION['admin_role'] ?? '') === 'super_admin';
$canViewAllOrganizations = $isPlatformAdmin && $orgId < 1;
$scopeSql = $canViewAllOrganizations ? '1 = 1' : 'm.org_id = ' . $orgId;
$members = [];
$oldStatuses = $_SESSION['attendance_old_statuses'] ?? [];
if (!is_array($oldStatuses)) {
    $oldStatuses = [];
}
unset($_SESSION['attendance_old_statuses']);
$notice = $_SESSION['attendance_notice'] ?? null;
unset($_SESSION['attendance_notice']);

if (empty($_SESSION['attendance_csrf'])) {
    $_SESSION['attendance_csrf'] = bin2hex(random_bytes(32));
}

$memberResult = mysqli_query(
    $conn,
    "SELECT m.id, m.member_code, m.first_name, m.last_name
     FROM members m
     WHERE $scopeSql
     ORDER BY m.first_name, m.last_name"
);
if ($memberResult) {
    while ($member = mysqli_fetch_assoc($memberResult)) {
        $members[] = $member;
    }
} else {
    error_log('Attendance member list query failed: ' . mysqli_error($conn));
    $notice = ['type' => 'error', 'text' => 'Member list could not be loaded. Please try again later.'];
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Add Attendance - Associa8</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="shortcut icon" href="../images/fav-logo.png" type="image/x-icon" />
    <link rel="stylesheet" href="../css/preloader.css" />
    <link rel="stylesheet" href="../css/dashboard.css?v=20261016-responsive-tables-1" />
  </head>
  <body class="admin-body">
    <?php include('inc/preloader.php'); ?>
    <div class="admin-layout">
      <?php include('inc/sidebar.php'); ?>
      <div class="sidebar-overlay" id="sidebarOverlay"></div>
      <main class="admin-main">
        <header class="admin-header">
          <div class="header-left">
            <button class="header-toggle-btn" id="sidebarToggle" type="button" aria-label="Toggle navigation">
              <i class="fa-solid fa-bars"></i>
            </button>
            <h1 class="page-title">Add Attendance</h1>
          </div>
          <div class="header-right">
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
              <h2 class="page-title-main">Record Attendance</h2>
              <p class="page-subtitle">Set a status for each member you want to record. <?php admin_info_tip('Only one general attendance record is allowed per member per local calendar day. The date and time use Africa/Lagos time.', 'Attendance recording help'); ?></p>
            </div>
            <a href="attendance.php" class="btn-navy-outline">
              <i class="fa-solid fa-arrow-left"></i> Back to attendance
            </a>
          </div>

          <?php if (is_array($notice)): ?>
            <div class="dashboard-card" role="alert" style="margin-bottom: 1rem; color: #b91c1c;">
              <?php echo htmlspecialchars($notice['text'], ENT_QUOTES, 'UTF-8'); ?>
            </div>
          <?php endif; ?>

          <section class="dashboard-card structure-form-card">
            <div class="structure-form-heading">
              <div class="structure-form-icon"><i class="fa-solid fa-user-check"></i></div>
              <div>
                <h3>Attendance information</h3>
              </div>
            </div>
            <?php if ($members): ?>
              <form action="proc-record-attendance.php" method="post" class="structure-form">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['attendance_csrf'], ENT_QUOTES, 'UTF-8'); ?>" />
                <input type="hidden" name="roster_count" value="<?php echo count($members); ?>" />
                <div class="form-row-2col">
                  <div class="form-group">
                    <label for="check_in" class="form-label">Date and time (Africa/Lagos)</label>
                    <input class="form-control" type="datetime-local" id="check_in" name="check_in" value="<?php echo (new DateTimeImmutable('now', new DateTimeZone('Africa/Lagos')))->format('Y-m-d\TH:i'); ?>" required />
                  </div>
                </div>
                <div class="form-group">
                  <label for="attendanceMemberSearch" class="form-label">Find a member</label>
                  <input class="form-control" type="search" id="attendanceMemberSearch" placeholder="Search by name or member code" />
                </div>
                <div class="table-responsive-card">
                  <table class="admin-table attendance-roster-table">
                    <thead>
                      <tr><th>Member</th><th>Attendance status</th></tr>
                    </thead>
                    <tbody>
                      <?php foreach ($members as $member): ?>
                        <?php $memberName = $member['first_name'] . ' ' . $member['last_name']; ?>
                        <tr data-member-search="<?php echo htmlspecialchars(strtolower($member['member_code'] . ' ' . $memberName), ENT_QUOTES, 'UTF-8'); ?>">
                          <td><?php echo htmlspecialchars($member['member_code'] . ' — ' . $memberName, ENT_QUOTES, 'UTF-8'); ?></td>
                          <td>
                            <select class="form-select" name="statuses[<?php echo (int) $member['id']; ?>]" aria-label="Attendance status for <?php echo htmlspecialchars($memberName, ENT_QUOTES, 'UTF-8'); ?>">
                              <option value="">Not marked</option>
                              <option value="present" <?php echo ($oldStatuses[(int) $member['id']] ?? '') === 'present' ? 'selected' : ''; ?>>Present</option>
                              <option value="late" <?php echo ($oldStatuses[(int) $member['id']] ?? '') === 'late' ? 'selected' : ''; ?>>Late</option>
                              <option value="absent" <?php echo ($oldStatuses[(int) $member['id']] ?? '') === 'absent' ? 'selected' : ''; ?>>Absent</option>
                            </select>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
                <div class="structure-form-actions">
                  <a href="attendance.php" class="btn-action-dark-outline">Cancel</a>
                  <button type="submit" class="btn-navy-filled"><i class="fa-solid fa-check"></i> Save attendance</button>
                </div>
              </form>
            <?php else: ?>
              <div class="zone-empty-state">No members are available for your organization. Add a member before recording attendance.</div>
            <?php endif; ?>
          </section>
        </div>
        <?php include('inc/footer.php'); ?>
      </main>
    </div>
    <script src="../js/admin-sidebar.js"></script>
    <script>
      const attendanceMemberSearch = document.getElementById('attendanceMemberSearch');
      attendanceMemberSearch?.addEventListener('input', () => {
        const query = attendanceMemberSearch.value.trim().toLowerCase();
        document.querySelectorAll('.attendance-roster-table tbody tr').forEach((row) => {
          row.hidden = !row.dataset.memberSearch.includes(query);
        });
      });
    </script>
    <script src="../js/preloader.js"></script>
  </body>
</html>
