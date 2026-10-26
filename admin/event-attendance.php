<?php
require_once 'inc/auth.php';
require_once '../inc/db.php';

$orgId = (int) ($_SESSION['org_id'] ?? 0);
$eventId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if ($orgId < 1) {
    admin_render_access_denied('Your account is not connected to an organization. Contact your administrator for help.');
}
if (!$eventId || $eventId < 1) {
    http_response_code(400);
    exit('Choose a valid event.');
}

$eventStmt = mysqli_prepare(
    $conn,
    'SELECT id, title, event_date, event_time, status FROM events WHERE id = ? AND org_id = ? LIMIT 1'
);
if (!$eventStmt) {
    error_log('Event attendance event lookup could not be prepared: ' . mysqli_error($conn));
    http_response_code(500);
    exit('Event attendance could not be loaded right now.');
}
mysqli_stmt_bind_param($eventStmt, 'ii', $eventId, $orgId);
if (!mysqli_stmt_execute($eventStmt)) {
    error_log('Event attendance event lookup failed: ' . mysqli_stmt_error($eventStmt));
    mysqli_stmt_close($eventStmt);
    http_response_code(500);
    exit('Event attendance could not be loaded right now.');
}
$eventResult = mysqli_stmt_get_result($eventStmt);
$event = $eventResult ? mysqli_fetch_assoc($eventResult) : null;
mysqli_stmt_close($eventStmt);
if (!$event) {
    http_response_code(404);
    exit('That event could not be found in your organization.');
}

$eventTimezone = new DateTimeZone('Africa/Lagos');
$eventEndsAt = new DateTimeImmutable(
    $event['event_date'] . ' ' . ($event['event_time'] ?: '23:59:59'),
    $eventTimezone
);
$canRecordAttendance = $event['status'] === 'scheduled'
    && $eventEndsAt <= new DateTimeImmutable('now', $eventTimezone);

$roster = [];
$rosterQueryFailed = false;
$rosterStmt = mysqli_prepare(
    $conn,
    "SELECT m.id AS member_id, m.first_name, m.last_name, m.member_code, r.status AS rsvp_status,
            ea.status AS attendance_status
     FROM event_rsvps r
     INNER JOIN members m ON m.id = r.member_id AND m.org_id = ?
     LEFT JOIN event_attendance ea ON ea.event_id = r.event_id AND ea.member_id = r.member_id
     WHERE r.event_id = ? AND r.status IN ('booked', 'waitlisted')
     ORDER BY m.first_name, m.last_name, m.id"
);
if (!$rosterStmt) {
    error_log('Event attendance roster could not be prepared: ' . mysqli_error($conn));
    $rosterQueryFailed = true;
} else {
    mysqli_stmt_bind_param($rosterStmt, 'ii', $orgId, $eventId);
    if (!mysqli_stmt_execute($rosterStmt)) {
        error_log('Event attendance roster query failed: ' . mysqli_stmt_error($rosterStmt));
        $rosterQueryFailed = true;
    } else {
        $rosterResult = mysqli_stmt_get_result($rosterStmt);
        while ($rosterResult && ($row = mysqli_fetch_assoc($rosterResult))) {
            $roster[] = $row;
        }
    }
    mysqli_stmt_close($rosterStmt);
}

if (empty($_SESSION['event_csrf'])) {
    $_SESSION['event_csrf'] = bin2hex(random_bytes(32));
}
$notice = $_SESSION['event_attendance_notice'] ?? null;
unset($_SESSION['event_attendance_notice']);
$attendanceStatuses = [
    'attended' => 'Attended',
    'missed' => 'Missed',
    'excused' => 'Excused',
];
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Event Attendance - Associa8</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="shortcut icon" href="../images/fav-logo.png" type="image/x-icon" />
    <link rel="stylesheet" href="../css/preloader.css" />
    <link rel="stylesheet" href="../css/dashboard.css?v=20261019-event-attendance-1" />
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
            <h1 class="page-title">Event Attendance</h1>
          </div>
          <div class="header-right">
            <div class="admin-user-profile">
              <div class="avatar-badge">EV</div>
              <div class="user-info">
                <span class="user-name">Event management</span>
                <span class="user-role">Organization events</span>
              </div>
            </div>
          </div>
        </header>

        <div class="dashboard-content">
          <div class="page-action-header mb-4">
            <div>
              <h2 class="page-title-main"><?php echo htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
              <p class="page-subtitle">
                <?php echo htmlspecialchars(date('D, d M Y', strtotime($event['event_date'])), ENT_QUOTES, 'UTF-8'); ?>
                <?php if ($event['event_time']): ?> · <?php echo htmlspecialchars(date('h:i A', strtotime($event['event_time'])), ENT_QUOTES, 'UTF-8'); ?> local time<?php endif; ?>
                · Attendance is limited to members who booked or joined the waitlist.
              </p>
            </div>
            <a href="events.php" class="btn-navy-outline"><i class="fa-solid fa-arrow-left"></i> Back to events</a>
          </div>

          <?php if (is_array($notice)): ?>
            <div class="inline-form-message <?php echo ($notice['type'] ?? '') === 'success' ? 'success' : 'error'; ?>" role="status">
              <?php echo htmlspecialchars((string) ($notice['text'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
            </div>
          <?php endif; ?>

          <?php if ($rosterQueryFailed): ?>
            <div class="dashboard-card" role="alert">The RSVP roster could not be loaded. Please try again later.</div>
          <?php elseif (!$canRecordAttendance): ?>
            <div class="dashboard-card" role="status">
              Attendance can be recorded after a scheduled event has ended. Cancelled events cannot have attendance recorded.
            </div>
          <?php elseif (!$roster): ?>
            <div class="dashboard-card zone-empty-state">No members currently have an active booking or waitlist spot for this event.</div>
          <?php else: ?>
            <form action="proc-record-event-attendance.php" method="post">
              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['event_csrf'], ENT_QUOTES, 'UTF-8'); ?>" />
              <input type="hidden" name="event_id" value="<?php echo (int) $event['id']; ?>" />
              <div class="dashboard-card" style="padding: 0; overflow: hidden;">
                <div style="overflow-x: auto;">
                  <table class="admin-table">
                    <thead>
                      <tr>
                        <th>Member</th>
                        <th>Member code</th>
                        <th>RSVP</th>
                        <th>Attendance</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($roster as $attendee): ?>
                        <?php $fullName = trim($attendee['first_name'] . ' ' . $attendee['last_name']); ?>
                        <tr>
                          <td><?php echo htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'); ?></td>
                          <td><?php echo htmlspecialchars($attendee['member_code'], ENT_QUOTES, 'UTF-8'); ?></td>
                          <td><?php echo htmlspecialchars(ucfirst($attendee['rsvp_status']), ENT_QUOTES, 'UTF-8'); ?></td>
                          <td>
                            <select class="form-control" name="attendance[<?php echo (int) $attendee['member_id']; ?>]" aria-label="Attendance status for <?php echo htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'); ?>">
                              <option value="">Leave unchanged</option>
                              <?php foreach ($attendanceStatuses as $value => $label): ?>
                                <option value="<?php echo $value; ?>" <?php echo $attendee['attendance_status'] === $value ? 'selected' : ''; ?>>
                                  <?php echo $label; ?>
                                </option>
                              <?php endforeach; ?>
                            </select>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>
              <div class="structure-form-actions" style="margin-top: 1rem;">
                <button class="btn-navy-filled" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save attendance</button>
              </div>
            </form>
          <?php endif; ?>
        </div>
        <?php include('inc/footer.php'); ?>
      </main>
    </div>
    <script src="../js/admin-sidebar.js"></script>
    <script src="../js/preloader.js"></script>
    <style>
      .inline-form-message {
        margin-bottom: 1.25rem;
        padding: .85rem 1rem;
        border-radius: var(--radius-sm);
        font-weight: 700;
      }
      .inline-form-message.error {
        color: #991b1b;
        background: #fee2e2;
        border: 1px solid #fecaca;
      }
      .inline-form-message.success {
        color: #166534;
        background: #dcfce7;
        border: 1px solid #bbf7d0;
      }
    </style>
  </body>
</html>
