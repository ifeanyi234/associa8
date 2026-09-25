<?php
require_once "inc/auth.php";
require_once "../inc/db.php";

$memberId = (int) ($_GET['id'] ?? 0);
$adminRole = $_SESSION['admin_role'] ?? 'admin';
$orgId = isset($_SESSION['org_id']) && $_SESSION['org_id'] !== null ? (int) $_SESSION['org_id'] : null;
$scope = $adminRole === 'super_admin' ? '' : ' AND org_id = ' . (int) $orgId;
$memberResult = mysqli_query($conn, 'SELECT id, org_id, member_code, first_name, last_name, email, phone, title_id, zone_id, subzone_id, joined_date, status FROM members WHERE id = ' . $memberId . $scope . ' LIMIT 1');
$member = $memberResult ? mysqli_fetch_assoc($memberResult) : null;
if (!$member) {
    header('Location: member-directory.php?status=error&msg=' . urlencode('The member could not be found.'));
    exit;
}
$titlesResult = mysqli_query($conn, 'SELECT id, title, level FROM titles WHERE org_id = ' . (int) $member['org_id'] . ' ORDER BY level, title');
$zonesResult = mysqli_query($conn, 'SELECT id, name FROM zones WHERE org_id = ' . (int) $member['org_id'] . ' ORDER BY name');
$subzonesResult = mysqli_query($conn, 'SELECT id, zone_id, name FROM subzones ORDER BY name');
$subzonesByZone = [];
if ($subzonesResult) {
    while ($subzone = mysqli_fetch_assoc($subzonesResult)) {
        $subzonesByZone[(int) $subzone['zone_id']][] = ['id' => (int) $subzone['id'], 'name' => $subzone['name']];
    }
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
  <link rel="stylesheet" href="../css/dashboard.css" />
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
          <div class="form-row-2col"><div class="form-group"><label class="form-label">Member code</label><input class="form-control" value="<?php echo htmlspecialchars($member['member_code']); ?>" readonly /></div><div class="form-group"></div></div>
          <div class="form-row-2col"><div class="form-group"><label class="form-label" for="firstName">First name</label><input class="form-control" id="firstName" name="first_name" value="<?php echo htmlspecialchars($member['first_name']); ?>" required /></div><div class="form-group"><label class="form-label" for="lastName">Last name</label><input class="form-control" id="lastName" name="last_name" value="<?php echo htmlspecialchars($member['last_name']); ?>" required /></div></div>
          <div class="form-row-2col"><div class="form-group"><label class="form-label" for="email">Email</label><input class="form-control" type="email" id="email" name="email" value="<?php echo htmlspecialchars($member['email']); ?>" required /></div><div class="form-group"><label class="form-label" for="phone">Phone</label><input class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($member['phone'] ?? ''); ?>" /></div></div>
          <div class="form-row-2col"><div class="form-group"><label class="form-label" for="titleId">Membership title</label><select class="form-select" id="titleId" name="title_id"><option value="0">Unassigned</option><?php if ($titlesResult): while ($title = mysqli_fetch_assoc($titlesResult)): ?><option value="<?php echo (int) $title['id']; ?>" <?php echo (int) $member['title_id'] === (int) $title['id'] ? 'selected' : ''; ?>>L<?php echo (int) $title['level']; ?> - <?php echo htmlspecialchars($title['title']); ?></option><?php endwhile; endif; ?></select></div><div class="form-group"><label class="form-label" for="zoneId">Zone</label><select class="form-select" id="zoneId" name="zone_id"><option value="0">Unassigned</option><?php if ($zonesResult): while ($zone = mysqli_fetch_assoc($zonesResult)): ?><option value="<?php echo (int) $zone['id']; ?>" <?php echo (int) $member['zone_id'] === (int) $zone['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($zone['name']); ?></option><?php endwhile; endif; ?></select></div></div>
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
const selectedSubzone = <?php echo (int) ($member['subzone_id'] ?? 0); ?>;
function loadSubzones() { subzoneSelect.innerHTML = '<option value="0">Unassigned</option>'; (subzones[zoneSelect.value] || []).forEach((item) => { const option = new Option(item.name, item.id); if (item.id === selectedSubzone) option.selected = true; subzoneSelect.add(option); }); }
zoneSelect.addEventListener('change', loadSubzones);
loadSubzones();

window.addEventListener('DOMContentLoaded', () => {
  const params = new URLSearchParams(window.location.search);
  const status = params.get('status');
  if (!status || !window.AppModal) return;

  const success = status === 'success';
  window.AppModal.open({
    type: success ? 'success' : 'error',
    heading: success ? 'Member updated successfully' : 'Member could not be updated',
    body: params.get('msg') || (success ? 'The member details were saved.' : 'Please review the member data and try again.'),
    detail: success ? 'Updated name, title, zone, and profile information are now reflected in the directory.' : 'No changes were saved to this member record.'
  });
  window.history.replaceState({}, document.title, window.location.pathname);
});
</script>
<script src="../js/preloader.js"></script>
</body>
</html>
