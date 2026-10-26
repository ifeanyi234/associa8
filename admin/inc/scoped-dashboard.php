<?php
$scopeFilter = admin_scope_sql('m', $scopeContext);
$orgId = (int) $scopeContext['org_id'];
$memberStatuses = ['active' => 0, 'pending' => 0, 'suspended' => 0];
$memberTotal = 0;
$memberStatement = mysqli_prepare(
    $conn,
    "SELECT m.status, COUNT(*) AS total
     FROM members m
     WHERE m.org_id = ? $scopeFilter
     GROUP BY m.status"
);
if (!$memberStatement) {
    error_log('Scoped dashboard member summary could not be prepared: ' . mysqli_error($conn));
    http_response_code(500);
    exit('Your dashboard is temporarily unavailable.');
}
mysqli_stmt_bind_param($memberStatement, 'i', $orgId);
if (!mysqli_stmt_execute($memberStatement)) {
    error_log('Scoped dashboard member summary failed: ' . mysqli_stmt_error($memberStatement));
    mysqli_stmt_close($memberStatement);
    http_response_code(500);
    exit('Your dashboard is temporarily unavailable.');
}
$memberResult = mysqli_stmt_get_result($memberStatement);
if (!$memberResult) {
    mysqli_stmt_close($memberStatement);
    http_response_code(500);
    exit('Your dashboard is temporarily unavailable.');
}
while ($status = mysqli_fetch_assoc($memberResult)) {
    $statusName = (string) $status['status'];
    $total = (int) $status['total'];
    $memberTotal += $total;
    if (array_key_exists($statusName, $memberStatuses)) {
        $memberStatuses[$statusName] = $total;
    }
}
mysqli_stmt_close($memberStatement);

$recentMembersStatement = mysqli_prepare(
    $conn,
    "SELECT m.first_name, m.last_name, m.status, COALESCE(m.joined_date, DATE(m.created_at)) AS joined_date
     FROM members m
     WHERE m.org_id = ? $scopeFilter
     ORDER BY COALESCE(m.joined_date, DATE(m.created_at)) DESC, m.id DESC
     LIMIT 5"
);
if (!$recentMembersStatement) {
    error_log('Scoped dashboard recent members could not be prepared: ' . mysqli_error($conn));
    http_response_code(500);
    exit('Your dashboard is temporarily unavailable.');
}
mysqli_stmt_bind_param($recentMembersStatement, 'i', $orgId);
if (!mysqli_stmt_execute($recentMembersStatement)) {
    error_log('Scoped dashboard recent members query failed: ' . mysqli_stmt_error($recentMembersStatement));
    mysqli_stmt_close($recentMembersStatement);
    http_response_code(500);
    exit('Your dashboard is temporarily unavailable.');
}
$recentMembersResult = mysqli_stmt_get_result($recentMembersStatement);
if (!$recentMembersResult) {
    error_log('Scoped dashboard recent members result failed: ' . mysqli_stmt_error($recentMembersStatement));
    mysqli_stmt_close($recentMembersStatement);
    http_response_code(500);
    exit('Your dashboard is temporarily unavailable.');
}
$recentMembers = [];
while ($member = mysqli_fetch_assoc($recentMembersResult)) {
    $recentMembers[] = $member;
}
mysqli_stmt_close($recentMembersStatement);

if ($scopeContext['role'] === 'zonal_admin') {
    $locationStatement = mysqli_prepare($conn, 'SELECT name AS zone_name, NULL AS subzone_name FROM zones WHERE id = ? AND org_id = ? LIMIT 1');
    $locationId = (int) $scopeContext['zone_id'];
} else {
    $locationStatement = mysqli_prepare(
        $conn,
        'SELECT z.name AS zone_name, sz.name AS subzone_name
         FROM subzones sz INNER JOIN zones z ON z.id = sz.zone_id
         WHERE sz.id = ? AND z.org_id = ? LIMIT 1'
    );
    $locationId = (int) $scopeContext['subzone_id'];
}
if (!$locationStatement) {
    error_log('Scoped dashboard location lookup could not be prepared: ' . mysqli_error($conn));
    http_response_code(500);
    exit('Your assigned area could not be loaded.');
}
mysqli_stmt_bind_param($locationStatement, 'ii', $locationId, $orgId);
if (!mysqli_stmt_execute($locationStatement)) {
    error_log('Scoped dashboard location lookup failed: ' . mysqli_error($conn));
    if ($locationStatement) {
        mysqli_stmt_close($locationStatement);
    }
    http_response_code(500);
    exit('Your assigned area could not be loaded.');
}
$locationResult = mysqli_stmt_get_result($locationStatement);
$location = $locationResult ? mysqli_fetch_assoc($locationResult) : null;
mysqli_stmt_close($locationStatement);
if (!$location) {
    http_response_code(403);
    exit('Your assigned area could not be found.');
}
$locationLabel = $scopeContext['role'] === 'zonal_admin'
    ? (string) $location['zone_name']
    : (string) $location['zone_name'] . ' · ' . (string) $location['subzone_name'];
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Member Dashboard - Associa8</title>
    <link rel="shortcut icon" href="../images/fav-logo.png" type="image/x-icon" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="../css/preloader.css" />
    <link rel="stylesheet" href="../css/dashboard.css?v=20261016-responsive-tables-1" />
  </head>
  <body class="admin-body">
    <?php include __DIR__ . '/preloader.php'; ?>
    <div class="admin-layout">
      <?php include __DIR__ . '/sidebar.php'; ?>
      <div class="sidebar-overlay" id="sidebarOverlay"></div>
      <main class="admin-main">
        <header class="admin-header">
          <div class="header-left">
            <button class="header-toggle-btn" id="sidebarToggle" type="button" aria-label="Open navigation"><i class="fa-solid fa-bars"></i></button>
            <h1 class="page-title">Member Dashboard</h1>
          </div>
          <div class="header-right">
            <div class="admin-user-profile">
              <div class="avatar-badge"><?php echo htmlspecialchars(strtoupper(substr((string) ($_SESSION['admin_name'] ?? 'A'), 0, 1)), ENT_QUOTES, 'UTF-8'); ?></div>
              <div class="user-info">
                <span class="user-name"><?php echo htmlspecialchars((string) ($_SESSION['admin_name'] ?? 'Admin'), ENT_QUOTES, 'UTF-8'); ?></span>
                <span class="user-role"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $scopeContext['role'])), ENT_QUOTES, 'UTF-8'); ?></span>
              </div>
            </div>
          </div>
        </header>
        <div class="dashboard-content">
          <div class="page-action-header mb-4">
            <div>
              <h2 class="page-title-main"><?php echo htmlspecialchars($locationLabel, ENT_QUOTES, 'UTF-8'); ?></h2>
              <p class="page-subtitle">Member activity and actions for your assigned area.</p>
            </div>
            <div style="display:flex;gap:.75rem;flex-wrap:wrap;">
              <a href="member-directory.php" class="btn-outline-primary"><i class="fa-solid fa-users"></i> View members</a>
              <a href="add-member.php" class="btn-navy-filled"><i class="fa-solid fa-user-plus"></i> Add member</a>
            </div>
          </div>
          <section class="stats-grid mb-4" aria-label="Member summary">
            <div class="suspension-stat-card"><div class="suspension-stat-value"><?php echo number_format($memberTotal); ?></div><div class="suspension-stat-label">Members in your area</div></div>
            <div class="suspension-stat-card"><div class="suspension-stat-value"><?php echo number_format($memberStatuses['active']); ?></div><div class="suspension-stat-label">Active</div></div>
            <div class="suspension-stat-card"><div class="suspension-stat-value"><?php echo number_format($memberStatuses['pending']); ?></div><div class="suspension-stat-label">Pending</div></div>
            <div class="suspension-stat-card"><div class="suspension-stat-value"><?php echo number_format($memberStatuses['suspended']); ?></div><div class="suspension-stat-label">Suspended</div></div>
          </section>
          <section class="dashboard-card">
            <h2 class="page-title-main" style="font-size:1.1rem;">Recently added members</h2>
            <div class="table-responsive-card">
              <table class="admin-table">
                <thead><tr><th>Name</th><th>Status</th><th>Joined</th></tr></thead>
                <tbody>
                  <?php if ($recentMembers): ?>
                    <?php foreach ($recentMembers as $member): ?>
                      <tr>
                        <td><?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst((string) $member['status']), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars((string) ($member['joined_date'] ?? '—'), ENT_QUOTES, 'UTF-8'); ?></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr><td colspan="3" class="zone-empty-state">No members have been added to your area yet.</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </section>
        </div>
        <?php include __DIR__ . '/footer.php'; ?>
      </main>
    </div>
    <script src="../js/admin-sidebar.js"></script>
    <script src="../js/preloader.js"></script>
  </body>
</html>
