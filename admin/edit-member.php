<?php
require_once "inc/auth.php";
require_once "../inc/db.php";
require_once __DIR__ . '/inc/scope.php';

$memberId = (int) ($_GET['id'] ?? 0);
$adminRole = $_SESSION['admin_role'] ?? 'admin';
$orgId = isset($_SESSION['org_id']) && $_SESSION['org_id'] !== null ? (int) $_SESSION['org_id'] : null;
$scopeContext = admin_scope_context($conn);
if (empty($scopeContext['valid'])) {
    admin_render_access_denied('Your account does not have a valid zone or sub-zone assignment.');
}
$memberScope = admin_scope_sql('m', $scopeContext);
$memberResult = mysqli_query($conn, 'SELECT m.id, m.org_id, m.member_code, m.first_name, m.last_name, m.email, m.phone, m.title_id, m.zone_id, m.subzone_id, m.joined_date, m.status FROM members m WHERE m.id = ' . $memberId . ($adminRole === 'super_admin' ? '' : ' AND m.org_id = ' . (int) $orgId) . $memberScope . ' LIMIT 1');
if (!$memberResult) {
    error_log('Member edit lookup failed: ' . mysqli_error($conn));
    http_response_code(500);
    exit('The member could not be loaded.');
}
$member = $memberResult ? mysqli_fetch_assoc($memberResult) : null;
if (!$member) {
    header('Location: member-directory.php?status=error&msg=' . urlencode('The member could not be found.'));
    exit;
}
$zoneFilter = $scopeContext['zone_id'] !== null ? ' AND id = ' . (int) $scopeContext['zone_id'] : '';
$subzoneZoneFilter = $scopeContext['zone_id'] !== null ? ' AND z.id = ' . (int) $scopeContext['zone_id'] : '';
$subzoneFilter = $scopeContext['subzone_id'] !== null ? ' AND sz.id = ' . (int) $scopeContext['subzone_id'] : '';
$titlesResult = mysqli_query($conn, 'SELECT id, title, scope, level FROM titles WHERE org_id = ' . (int) $member['org_id'] . ' ORDER BY scope, level, title');
$zonesResult = mysqli_query($conn, 'SELECT id, name FROM zones WHERE org_id = ' . (int) $member['org_id'] . $zoneFilter . ' ORDER BY name');
if (!$titlesResult || !$zonesResult) {
    error_log('Member edit assignment options query failed: ' . mysqli_error($conn));
    http_response_code(500);
    exit('Member assignment options are temporarily unavailable.');
}
$zones = [];
if ($zonesResult) {
    while ($zone = mysqli_fetch_assoc($zonesResult)) {
        $zones[] = $zone;
    }
}
$assignedZoneName = 'Assigned zone';
foreach ($zones as $zone) {
    if ((int) $zone['id'] === (int) ($scopeContext['zone_id'] ?? 0)) {
        $assignedZoneName = (string) $zone['name'];
        break;
    }
}
$subzonesResult = mysqli_query($conn, 'SELECT sz.id, sz.zone_id, sz.name FROM subzones sz INNER JOIN zones z ON z.id = sz.zone_id WHERE z.org_id = ' . (int) $member['org_id'] . $subzoneZoneFilter . $subzoneFilter . ' ORDER BY sz.name');
if (!$subzonesResult) {
    error_log('Member edit sub-zone options query failed: ' . mysqli_error($conn));
    http_response_code(500);
    exit('Member assignment options are temporarily unavailable.');
}
$subzonesByZone = [];
if ($subzonesResult) {
    while ($subzone = mysqli_fetch_assoc($subzonesResult)) {
        $subzonesByZone[(int) $subzone['zone_id']][] = ['id' => (int) $subzone['id'], 'name' => $subzone['name']];
    }
}
if (empty($_SESSION['member_edit_csrf'])) {
  $_SESSION['member_edit_csrf'] = bin2hex(random_bytes(32));
}
$status = $_GET['status'] ?? '';
$message = $_GET['msg'] ?? '';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Edit Member - Associa8</title>
  <link rel="shortcut icon" href="../images/fav-logo.png" type="image/x-icon" />
  <link rel="stylesheet" href="../css/preloader.css" />
  <link rel="stylesheet" href="../css/dashboard.css?v=20261016-responsive-tables-1" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
</head>
<body class="admin-body">
<?php include('inc/preloader.php'); ?>
<div class="admin-layout">
  <?php include('inc/sidebar.php'); ?>
  <div class="sidebar-overlay" id="sidebarOverlay"></div>
  <main class="admin-main">
    <header class="admin-header"><div class="header-left"><button class="header-toggle-btn" id="sidebarToggle" type="button"><i class="fa-solid fa-bars"></i></button><h1 class="page-title">Edit Member</h1></div></header>
    <div class="dashboard-content">
      <div class="page-action-header mb-4"><div><h2 class="page-title-main">Edit <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></h2><p class="page-subtitle">Assign or update the member's title, zone, and subzone.</p></div><a href="member-directory.php" class="btn-outline-primary"><i class="fa-solid fa-arrow-left"></i> Back to directory</a></div>
      <section class="dashboard-card structure-form-card">
        <form action="proc-edit-member.php" method="POST" class="structure-form">
          <input type="hidden" name="member_id" value="<?php echo (int) $member['id']; ?>" />
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['member_edit_csrf'], ENT_QUOTES, 'UTF-8'); ?>" />
          <div class="form-row-2col"><div class="form-group"><label class="form-label">Member code</label><input class="form-control" value="<?php echo htmlspecialchars($member['member_code']); ?>" readonly /></div><div class="form-group"></div></div>
          <div class="form-row-2col"><div class="form-group"><label class="form-label" for="firstName">First name</label><input class="form-control" id="firstName" name="first_name" value="<?php echo htmlspecialchars($member['first_name']); ?>" required /></div><div class="form-group"><label class="form-label" for="lastName">Last name</label><input class="form-control" id="lastName" name="last_name" value="<?php echo htmlspecialchars($member['last_name']); ?>" required /></div></div>
          <div class="form-row-2col"><div class="form-group"><label class="form-label" for="email">Email</label><input class="form-control" type="email" id="email" name="email" value="<?php echo htmlspecialchars($member['email']); ?>" required /></div><div class="form-group"><label class="form-label" for="phone">Phone</label><input class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($member['phone'] ?? ''); ?>" /></div></div>
          <div class="form-row-2col">
            <div class="form-group">
              <label class="form-label" for="titleId">Membership title</label>
              <select class="form-select" id="titleId" name="title_id">
                <option value="0">Unassigned</option>
                <?php if ($titlesResult): while ($title = mysqli_fetch_assoc($titlesResult)): ?>
                  <option value="<?php echo (int) $title['id']; ?>" data-scope="<?php echo htmlspecialchars($title['scope'], ENT_QUOTES, 'UTF-8'); ?>" <?php echo (int) $member['title_id'] === (int) $title['id'] ? 'selected' : ''; ?>><?php echo strtoupper($title['scope']); ?> · L<?php echo (int) $title['level']; ?> - <?php echo htmlspecialchars($title['title']); ?></option>
                <?php endwhile; endif; ?>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label" for="zoneId">Zone</label>
              <select class="form-select" id="zoneId" name="zone_id">
                <?php if (in_array($scopeContext['role'], ['zonal_admin', 'subzonal_admin'], true)): ?>
                  <option value="<?php echo (int) $scopeContext['zone_id']; ?>" selected><?php echo htmlspecialchars($assignedZoneName, ENT_QUOTES, 'UTF-8'); ?></option>
                <?php else: ?>
                  <option value="0">Unassigned</option>
                <?php endif; ?>
                <?php foreach ($zones as $zone): ?>
                  <?php if (!in_array($scopeContext['role'], ['zonal_admin', 'subzonal_admin'], true)): ?>
                    <option value="<?php echo (int) $zone['id']; ?>" <?php echo (int) $member['zone_id'] === (int) $zone['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($zone['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                  <?php endif; ?>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="form-row-2col"><div class="form-group"><label class="form-label" for="subzoneId">Subzone</label><select class="form-select" id="subzoneId" name="subzone_id"><option value="0">Unassigned</option></select></div><div class="form-group"><label class="form-label" for="joinedDate">Joined date</label><input class="form-control" type="date" id="joinedDate" name="joined_date" value="<?php echo htmlspecialchars($member['joined_date'] ?? ''); ?>" required /></div></div>
          <div class="structure-form-actions"><a href="member-directory.php" class="btn-action-dark-outline">Cancel</a><button class="btn-navy-filled" type="submit"><i class="fa-solid fa-check"></i> Save changes</button></div>
        </form>
      </section>
    </div>
    <?php include('inc/footer.php'); ?>
  </main>
</div>
<script src="../js/admin-sidebar.js"></script>
<script>
const subzones = <?php echo json_encode($subzonesByZone, JSON_HEX_TAG | JSON_HEX_AMP); ?>;
const zoneSelect = document.getElementById('zoneId');
const subzoneSelect = document.getElementById('subzoneId');
const titleSelect = document.getElementById('titleId');
const selectedSubzone = <?php echo (int) ($member['subzone_id'] ?? 0); ?>;
function loadSubzones() { subzoneSelect.innerHTML = '<option value="0">Unassigned</option>'; (subzones[zoneSelect.value] || []).forEach((item) => { const option = new Option(item.name, item.id); if (item.id === selectedSubzone) option.selected = true; subzoneSelect.add(option); }); }
function filterTitles() {
  const zoneSelected = Number(zoneSelect.value) > 0;
  const subzoneSelected = Number(subzoneSelect.value) > 0;
  Array.from(titleSelect.options).forEach((option) => {
    if (!option.dataset.scope) return;
    const available = option.dataset.scope === 'hq'
      || (option.dataset.scope === 'zone' && zoneSelected)
      || (option.dataset.scope === 'subzone' && subzoneSelected);
    option.hidden = !available;
    option.disabled = !available;
  });
  if (titleSelect.selectedOptions[0]?.disabled) titleSelect.value = '0';
}
zoneSelect.addEventListener('change', () => {
  loadSubzones();
  filterTitles();
});
subzoneSelect.addEventListener('change', filterTitles);
loadSubzones();
filterTitles();

window.addEventListener('DOMContentLoaded', () => {
  const params = new URLSearchParams(window.location.search);
  const status = params.get('status');
  if (!status || !window.AppModal) return;

  const success = status === 'success';
  window.AppModal.open({
    type: success ? 'success' : 'error',
    heading: success ? 'Member updated successfully' : 'Member could not be updated',
    body: params.get('msg') || (success ? 'The member details were saved.' : 'Please review the member data and try again.'),
    detail: success ? '' : 'No changes were saved to this member record.'
  });
  window.history.replaceState({}, document.title, window.location.pathname);
});
</script>
<script src="../js/preloader.js"></script>
</body>
</html>
