<?php
require_once 'inc/auth.php';
require_once '../../inc/db.php';

$memberId = (int) $_SESSION['member_id'];
$orgId = (int) $_SESSION['member_org_id'];
$today = (new DateTimeImmutable('now', new DateTimeZone('Africa/Lagos')))->format('Y-m-d');
$events = [];
$queryFailed = false;
$notice = $_SESSION['member_event_notice'] ?? null;
unset($_SESSION['member_event_notice']);
if (empty($_SESSION['member_event_csrf'])) {
    $_SESSION['member_event_csrf'] = bin2hex(random_bytes(32));
}

$bookingFeeColumnCheck = mysqli_query($conn, "SHOW COLUMNS FROM events LIKE 'booking_fee'");
if ($bookingFeeColumnCheck && mysqli_num_rows($bookingFeeColumnCheck) === 0) {
    mysqli_query($conn, "ALTER TABLE events ADD COLUMN booking_fee DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER capacity");
}

$stmt = mysqli_prepare(
    $conn,
    "SELECT e.*, COALESCE(r.status, 'not_booked') AS member_status,
            (SELECT COUNT(*) FROM event_rsvps er WHERE er.event_id = e.id AND er.status = 'booked') AS booked_count,
            (SELECT COUNT(*) FROM event_rsvps ew WHERE ew.event_id = e.id AND ew.status = 'waitlisted') AS waitlist_count
     FROM events e
     LEFT JOIN event_rsvps r ON r.event_id = e.id AND r.member_id = ?
     WHERE e.org_id = ?
     ORDER BY e.event_date ASC, e.event_time ASC, e.id ASC"
);
if ($stmt && mysqli_stmt_bind_param($stmt, 'ii', $memberId, $orgId) && mysqli_stmt_execute($stmt)) {
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $events[] = $row;
    }
    mysqli_stmt_close($stmt);
} else {
    error_log('Member events query failed: ' . ($stmt ? mysqli_stmt_error($stmt) : mysqli_error($conn)));
    if ($stmt) {
        mysqli_stmt_close($stmt);
    }
    $queryFailed = true;
}

$upcomingEvents = array_filter($events, static function ($event) use ($today) {
    return $event['event_date'] >= $today;
});
$pastEvents = array_filter($events, static function ($event) use ($today) {
    return $event['event_date'] < $today;
});
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
    <link rel="shortcut icon" href="../../images/fav-logo.png" type="image/x-icon" />
    <link rel="stylesheet" href="../../css/preloader.css" />
    <link rel="stylesheet" href="../../css/dashboard.css?v=20261015-member-events-inline-1" />
  </head>
  <body class="admin-body">
    <?php include('../inc/preloader.php'); ?>
    <div class="admin-layout">
      <?php include('inc/sidebar.php'); ?>
      <div class="sidebar-overlay" id="sidebarOverlay"></div>
      <main class="admin-main">
        <header class="admin-header">
          <div class="header-left">
            <button class="header-toggle-btn" id="sidebarToggle" type="button" aria-label="Toggle navigation"><i class="fa-solid fa-bars"></i></button>
            <h1 class="page-title">Events</h1>
          </div>
          <div class="header-right">
            <div class="header-search">
              <i class="fa-solid fa-magnifying-glass header-search-icon"></i>
              <input type="text" placeholder="Search...." />
            </div>
            <button class="notification-btn" aria-label="Notifications"><i class="fa-solid fa-bell"></i><span class="notification-badge"></span></button>
            <div class="admin-user-profile">
              <div class="avatar-badge member-avatar" aria-label="<?php echo htmlspecialchars($memberAvatarLabel, ENT_QUOTES, 'UTF-8'); ?>">
                <?php if ($memberPhotoPath !== ''): ?>
                  <img src="<?php echo htmlspecialchars($memberPhotoPath, ENT_QUOTES, 'UTF-8'); ?>" alt="" />
                <?php else: ?>
                  <?php echo htmlspecialchars($memberInitials, ENT_QUOTES, 'UTF-8'); ?>
                <?php endif; ?>
              </div>
              <span class="badge-pill status-active" style="margin-left: -0.5rem;">Active</span>
            </div>
          </div>
        </header>

        <div class="dashboard-content">
          <?php if (is_array($notice)): ?>
            <div class="dashboard-card" role="status" style="margin-bottom: 1rem; color: <?php echo ($notice['type'] ?? '') === 'success' ? '#166534' : '#b91c1c'; ?>;">
              <?php echo htmlspecialchars((string) ($notice['text'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
            </div>
          <?php endif; ?>
          <?php if ($queryFailed): ?>
            <div class="dashboard-card" role="alert">Events could not be loaded. Please try again later.</div>
          <?php else: ?>
            <section>
              <div class="section-label" style="margin-bottom: 1rem;">
                Upcoming Events (<span class="count-highlight"><?php echo count($upcomingEvents); ?></span>)
                <?php admin_info_tip('Book a seat if space is available. If a limited event is full, you can join its waitlist and may be offered a seat if one opens. Events without a seat limit allow direct booking.', 'Event booking help'); ?>
              </div>
              <div class="events-list">
                <?php if ($upcomingEvents): ?>
                  <?php foreach ($upcomingEvents as $event): ?>
                    <?php
                    $eventStarted = $event['event_date'] < $today
                        || ($event['event_date'] === $today && $event['event_time'] && $event['event_time'] < (new DateTimeImmutable('now', new DateTimeZone('Africa/Lagos')))->format('H:i:s'));
                    $canBook = $event['status'] === 'scheduled' && !$eventStarted;
                    $hasSeatLimit = (int) $event['capacity'] > 0;
                    $capacityFull = $hasSeatLimit && (int) $event['booked_count'] >= (int) $event['capacity'];
                    ?>
                    <article class="event-card">
                      <div>
                        <span class="event-card-tag"><?php echo htmlspecialchars($event['event_type'] ?: 'Event', ENT_QUOTES, 'UTF-8'); ?></span>
                        <div class="event-card-title"><?php echo htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8'); ?></div>
                        <div class="event-card-meta">
                          <span class="event-card-meta-item"><i class="fa-regular fa-calendar"></i> <?php echo htmlspecialchars(date('D, j M Y', strtotime($event['event_date'])), ENT_QUOTES, 'UTF-8'); ?></span>
                          <span class="event-card-meta-item"><i class="fa-regular fa-clock"></i> <?php echo $event['event_time'] ? htmlspecialchars(date('h:i A', strtotime($event['event_time'])) . ' (local time)', ENT_QUOTES, 'UTF-8') : 'Time TBA'; ?></span>
                          <span class="event-card-meta-item"><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($event['venue'] ?: 'Venue TBA', ENT_QUOTES, 'UTF-8'); ?></span>
                          <span class="event-card-meta-item"><i class="fa-solid fa-users"></i> Seats: <?php echo (int) $event['booked_count']; ?><?php echo $hasSeatLimit ? ' / ' . (int) $event['capacity'] : ' booked · unlimited'; ?></span>
                          <?php if ((float) $event['booking_fee'] > 0): ?><span class="event-card-meta-item"><i class="fa-solid fa-wallet"></i> Booking fee: NGN <?php echo number_format((float) $event['booking_fee'], 2, '.', ','); ?></span><?php endif; ?>
                          <?php if ((int) $event['waitlist_count'] > 0): ?><span class="event-card-meta-item"><?php echo (int) $event['waitlist_count']; ?> on waitlist</span><?php endif; ?>
                        </div>
                        <?php if (!empty($event['description'])): ?><p class="event-card-description"><?php echo nl2br(htmlspecialchars($event['description'], ENT_QUOTES, 'UTF-8')); ?></p><?php endif; ?>
                      </div>
                      <div class="event-card-actions">
                        <?php if ($event['status'] === 'cancelled'): ?>
                          <span class="btn-book-seat" aria-label="Event cancelled">Cancelled</span>
                        <?php elseif ($event['member_status'] === 'booked'): ?>
                          <form action="proc-event-rsvp.php" method="post">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['member_event_csrf'], ENT_QUOTES, 'UTF-8'); ?>" />
                            <input type="hidden" name="event_id" value="<?php echo (int) $event['id']; ?>" />
                            <input type="hidden" name="action" value="cancel" />
                            <button class="btn-book-seat" type="submit" data-app-modal-confirm="Cancel your booking for this event?" data-confirm-title="Cancel event booking" data-confirm-detail="Your seat will be released to another member.">Booked · Cancel</button>
                          </form>
                        <?php elseif ($event['member_status'] === 'waitlisted'): ?>
                          <form action="proc-event-rsvp.php" method="post">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['member_event_csrf'], ENT_QUOTES, 'UTF-8'); ?>" />
                            <input type="hidden" name="event_id" value="<?php echo (int) $event['id']; ?>" />
                            <input type="hidden" name="action" value="cancel" />
                            <button class="btn-book-seat" type="submit" data-app-modal-confirm="Leave the waitlist for this event?" data-confirm-title="Leave event waitlist" data-confirm-detail="You can join again if booking is still open.">Waitlisted · Leave</button>
                          </form>
                        <?php elseif ($canBook): ?>
                          <form action="proc-event-rsvp.php" method="post">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['member_event_csrf'], ENT_QUOTES, 'UTF-8'); ?>" />
                            <input type="hidden" name="event_id" value="<?php echo (int) $event['id']; ?>" />
                            <input type="hidden" name="action" value="book" />
                            <button class="btn-book-seat" type="submit"><?php echo ($event['booking_fee'] ?? 0) > 0 ? 'Pay & Book' : ($capacityFull ? 'Join Waitlist' : 'Book a Seat'); ?></button>
                          </form>
                        <?php else: ?>
                          <span class="btn-book-seat">Booking closed</span>
                        <?php endif; ?>
                      </div>
                    </article>
                  <?php endforeach; ?>
                <?php else: ?>
                  <div class="zone-empty-state">No upcoming events have been published yet.</div>
                <?php endif; ?>
              </div>
            </section>

            <section style="margin-top: 2rem;">
              <div class="section-label" style="margin-bottom: 1rem;">Past Events</div>
              <div class="events-list">
                <?php if ($pastEvents): ?>
                  <?php foreach ($pastEvents as $event): ?>
                    <article class="past-event-row">
                      <div class="past-event-row-left">
                        <span class="event-type-pill"><?php echo htmlspecialchars($event['event_type'] ?: 'Event', ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="past-event-title"><?php echo htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8'); ?></span>
                      </div>
                      <span class="past-event-date"><?php echo htmlspecialchars(date('D, d M Y', strtotime($event['event_date'])), ENT_QUOTES, 'UTF-8'); ?></span>
                      <div class="past-event-details">
                        <span class="event-card-meta-item"><i class="fa-regular fa-clock"></i> <?php echo $event['event_time'] ? htmlspecialchars(date('h:i A', strtotime($event['event_time'])) . ' (local time)', ENT_QUOTES, 'UTF-8') : 'Time TBA'; ?></span>
                        <span class="event-card-meta-item"><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($event['venue'] ?: 'Venue TBA', ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php if (!empty($event['description'])): ?><p class="event-card-description"><?php echo nl2br(htmlspecialchars($event['description'], ENT_QUOTES, 'UTF-8')); ?></p><?php endif; ?>
                      </div>
                    </article>
                  <?php endforeach; ?>
                <?php else: ?>
                  <div class="zone-empty-state">No past events are available yet.</div>
                <?php endif; ?>
              </div>
            </section>
          <?php endif; ?>
        </div>
        <?php include('../inc/footer.php'); ?>
      </main>
    </div>
    <script src="../../js/admin-sidebar.js"></script>
    <script src="../../js/preloader.js"></script>
  </body>
</html>
