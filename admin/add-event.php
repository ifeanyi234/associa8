<?php
require_once 'inc/auth.php';
require_once '../inc/db.php';

$orgId = (int) ($_SESSION['org_id'] ?? 0);
if ($orgId < 1) {
    http_response_code(403);
    exit('Select an organization before creating events.');
}
$notice = $_SESSION['event_notice'] ?? null;
unset($_SESSION['event_notice']);
if (empty($_SESSION['event_csrf'])) {
    $_SESSION['event_csrf'] = bin2hex(random_bytes(32));
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Create Event - Associa8</title>
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
            <button class="header-toggle-btn" id="sidebarToggle" type="button" aria-label="Toggle navigation"><i class="fa-solid fa-bars"></i></button>
            <h1 class="page-title">Create Event</h1>
          </div>
        </header>
        <div class="dashboard-content">
          <div class="page-action-header mb-4">
            <div>
              <h2 class="page-title-main">Create an event</h2>
              <p class="page-subtitle">Event details <?php admin_info_tip('Event dates and times are entered and shown to members in Africa/Lagos time.', 'Event time help'); ?></p>
            </div>
            <a href="events.php" class="btn-navy-outline"><i class="fa-solid fa-arrow-left"></i> Back to events</a>
          </div>
          <?php if (is_array($notice)): ?>
            <div class="inline-form-message <?php echo ($notice['type'] ?? '') === 'success' ? 'success' : 'error'; ?>" role="status">
              <?php echo htmlspecialchars((string) ($notice['text'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
            </div>
          <?php endif; ?>
          <section class="dashboard-card structure-form-card">
            <div class="structure-form-heading">
              <div class="structure-form-icon"><i class="fa-solid fa-calendar-plus"></i></div>
              <div><h3>Event information <?php admin_info_tip('Members can book until the event starts. If there is a seat limit, later bookings join the waitlist; when a booked seat opens, the next person is promoted.', 'Event booking help'); ?></h3></div>
            </div>
            <form action="proc-save-event.php" method="post" class="structure-form">
              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['event_csrf'], ENT_QUOTES, 'UTF-8'); ?>" />
              <div class="form-row-2col">
                <div class="form-group">
                  <label for="title" class="form-label">Event name</label>
                  <input class="form-control" id="title" name="title" type="text" maxlength="200" required />
                </div>
                <div class="form-group">
                  <label for="event_type" class="form-label">Event type</label>
                  <input class="form-control" id="event_type" name="event_type" type="text" maxlength="50" placeholder="Meeting, workshop, service..." />
                </div>
              </div>
              <div class="form-row-2col">
                <div class="form-group">
                  <label for="event_date" class="form-label">Date (Africa/Lagos)</label>
                  <input class="form-control" id="event_date" name="event_date" type="date" min="<?php echo (new DateTimeImmutable('now', new DateTimeZone('Africa/Lagos')))->format('Y-m-d'); ?>" required />
                </div>
                <div class="form-group">
                  <label for="event_time" class="form-label">Time (Africa/Lagos, optional)</label>
                  <input class="form-control" id="event_time" name="event_time" type="time" />
                </div>
              </div>
              <div class="form-row-2col">
                <div class="form-group">
                  <label for="venue" class="form-label">Venue</label>
                  <input class="form-control" id="venue" name="venue" type="text" maxlength="255" />
                </div>
                <div class="form-group">
                  <label for="capacity" class="form-label">Seat limit (optional) <?php admin_info_tip('Leave this blank for unlimited seats. Enter a number to cap bookings and use a waitlist when full.', 'Seat limit help'); ?></label>
                  <input class="form-control" id="capacity" name="capacity" type="number" min="1" max="4294967295" step="1" placeholder="Leave blank for unlimited seats" />
                </div>
              </div>
              <div class="form-group">
                <label for="description" class="form-label">Details (optional)</label>
                <textarea class="form-control" id="description" name="description" rows="5" maxlength="5000"></textarea>
              </div>
              <div class="structure-form-actions">
                <a href="events.php" class="btn-action-dark-outline">Cancel</a>
                <button type="submit" class="btn-navy-filled"><i class="fa-solid fa-check"></i> Save event</button>
              </div>
            </form>
          </section>
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
