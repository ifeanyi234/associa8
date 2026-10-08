<?php
require_once 'inc/auth.php';
require_once '../inc/db.php';

$orgId = (int) ($_SESSION['org_id'] ?? 0);
if ($orgId < 1) {
    http_response_code(403);
    exit('Select an organization before managing events.');
}

$events = [];
$queryFailed = false;
$notice = $_SESSION['event_notice'] ?? null;
unset($_SESSION['event_notice']);

$stmt = mysqli_prepare(
    $conn,
    "SELECT e.id, e.title, e.event_type, e.event_date, e.event_time, e.venue, e.capacity, e.status,
            (SELECT COUNT(*) FROM event_rsvps r WHERE r.event_id = e.id AND r.status = 'booked') AS booked_count,
            (SELECT COUNT(*) FROM event_rsvps r WHERE r.event_id = e.id AND r.status = 'waitlisted') AS waitlist_count
     FROM events e
     WHERE e.org_id = ?
     ORDER BY (e.status = 'cancelled'), e.event_date DESC, e.event_time DESC, e.id DESC"
);
if ($stmt && mysqli_stmt_bind_param($stmt, 'i', $orgId) && mysqli_stmt_execute($stmt)) {
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $events[] = $row;
    }
    mysqli_stmt_close($stmt);
} else {
    error_log('Organization events query failed: ' . ($stmt ? mysqli_stmt_error($stmt) : mysqli_error($conn)));
    if ($stmt) {
        mysqli_stmt_close($stmt);
    }
    $queryFailed = true;
}
if (empty($_SESSION['event_csrf'])) {
    $_SESSION['event_csrf'] = bin2hex(random_bytes(32));
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Events - Associa8</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="shortcut icon" href="../images/fav-logo.png" type="image/x-icon" />
    <link rel="stylesheet" href="../css/preloader.css" />
    <link rel="stylesheet" href="../css/dashboard.css?v=20261008-infotips-3" />
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
            <h1 class="page-title">Events</h1>
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
              <h2 class="page-title-main">Events</h2>
              <p class="page-subtitle">Create events for your members and track seat bookings.</p>
            </div>
            <a class="btn-navy-filled" href="add-event.php"><i class="fa-solid fa-calendar-plus"></i> Create Event</a>
          </div>

          <?php if (is_array($notice)): ?>
            <div class="inline-form-message <?php echo ($notice['type'] ?? '') === 'success' ? 'success' : 'error'; ?>" role="status">
              <?php echo htmlspecialchars((string) ($notice['text'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
            </div>
          <?php endif; ?>
          <?php if ($queryFailed): ?>
            <div class="dashboard-card" role="alert">Events could not be loaded. Please try again later.</div>
          <?php elseif (!$events): ?>
            <div class="dashboard-card zone-empty-state">No events have been created for this organization yet. Create an event to make it available to your members.</div>
          <?php else: ?>
            <div class="dashboard-card" style="padding: 0; overflow: hidden;">
              <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.875rem;">
                  <thead>
                    <tr style="background-color: #0f172a; color: #ffffff;">
                      <th style="padding: 1rem;">Event</th>
                      <th style="padding: 1rem;">Date &amp; time</th>
                      <th style="padding: 1rem;">Venue</th>
                      <th style="padding: 1rem;">Seats</th>
                      <th style="padding: 1rem;">Status</th>
                      <th style="padding: 1rem;">Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($events as $event): ?>
                      <tr style="border-bottom: 1px solid #e2e8f0;">
                        <td style="padding: 1rem;">
                          <strong><?php echo htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8'); ?></strong>
                          <div style="color: #64748b;"><?php echo htmlspecialchars($event['event_type'] ?: 'Event', ENT_QUOTES, 'UTF-8'); ?></div>
                        </td>
                        <td style="padding: 1rem;">
                          <?php echo htmlspecialchars(date('D, d M Y', strtotime($event['event_date'])), ENT_QUOTES, 'UTF-8'); ?>
                          <div style="color: #64748b;"><?php echo $event['event_time'] ? htmlspecialchars(date('h:i A', strtotime($event['event_time'])), ENT_QUOTES, 'UTF-8') . ' (Lagos)' : 'Time TBA'; ?></div>
                        </td>
                        <td style="padding: 1rem;"><?php echo htmlspecialchars($event['venue'] ?: 'Venue TBA', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td style="padding: 1rem;">
                          <?php echo (int) $event['booked_count']; ?> booked<?php echo (int) $event['capacity'] > 0 ? ' / ' . (int) $event['capacity'] : ' · unlimited'; ?>
                          <?php if ((int) $event['waitlist_count'] > 0): ?><div style="color: #64748b;"><?php echo (int) $event['waitlist_count']; ?> waiting</div><?php endif; ?>
                        </td>
                        <td style="padding: 1rem;"><?php echo htmlspecialchars(ucfirst($event['status']), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td style="padding: 1rem;">
                          <?php if ($event['status'] === 'scheduled'): ?>
                            <form action="proc-event-action.php" method="post" onsubmit="return confirm('Cancel this event? Members will no longer be able to book it.');">
                              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['event_csrf'], ENT_QUOTES, 'UTF-8'); ?>" />
                              <input type="hidden" name="event_id" value="<?php echo (int) $event['id']; ?>" />
                              <input type="hidden" name="action" value="cancel" />
                              <button class="btn-navy-outline" type="submit">Cancel event</button>
                            </form>
                          <?php else: ?>—<?php endif; ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
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
      .inline-form-message.success {
        color: #166534;
        background: #dcfce7;
        border: 1px solid #bbf7d0;
      }
      .inline-form-message.error {
        color: #991b1b;
        background: #fee2e2;
        border: 1px solid #fecaca;
      }
    </style>
  </body>
</html>
