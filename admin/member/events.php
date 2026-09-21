<?php
require_once 'inc/auth.php';
require_once '../../inc/db.php';

$memberId = (int) $_SESSION['member_id'];

$events = [];
$eventsResult = mysqli_query($conn, "SELECT e.*, IFNULL(r.status, 'not_booked') AS member_status FROM events e LEFT JOIN event_rsvps r ON r.event_id = e.id AND r.member_id = $memberId ORDER BY e.event_date ASC");
if ($eventsResult) {
  while ($row = mysqli_fetch_assoc($eventsResult)) {
    $events[] = $row;
  }
}

$upcomingEvents = array_filter($events, fn($event) => ($event['event_date'] ?? '') >= date('Y-m-d'));
$pastEvents = array_filter($events, fn($event) => ($event['event_date'] ?? '') < date('Y-m-d'));
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Events - Associa8</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap"
      rel="stylesheet"
    />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    />
    <link
      rel="shortcut icon"
      href="../../images/fav-logo.png"
      type="image/x-icon"
    />
    <link rel="stylesheet" href="../../css/preloader.css" />
    <link rel="stylesheet" href="../../css/dashboard.css" />
  </head>
  <body class="admin-body">
    <?php include('../inc/preloader.php') ?>
    <div class="admin-layout">
      <!-- ==========================================
         MEMBER SIDEBAR NAVIGATION
         ========================================== -->
      <?php include('inc/sidebar.php') ?>

      <!-- Mobile sidebar backdrop: tap it to close the sidebar -->
      <div class="sidebar-overlay" id="sidebarOverlay"></div>

      <!-- MAIN CONTENT AREA -->
      <main class="admin-main">
        <header class="admin-header">
          <div class="header-left">
            <button class="header-toggle-btn" id="sidebarToggle">
              <i class="fa-solid fa-bars"></i>
            </button>
            <h1 class="page-title">Events</h1>
          </div>

          <div class="header-right">
            <div class="header-search">
              <i class="fa-solid fa-magnifying-glass header-search-icon"></i>
              <input type="text" placeholder="Search...." />
            </div>

            <button class="notification-btn" aria-label="Notifications">
              <i class="fa-solid fa-bell"></i>
              <span class="notification-badge"></span>
            </button>

            <div class="admin-user-profile">
              <div class="avatar-badge"><?php echo htmlspecialchars(substr($_SESSION['member_id'] ?? 'M', 0, 2)); ?></div>
              <span class="badge-pill status-active" style="margin-left: -0.5rem;">Active</span>
            </div>
          </div>
        </header>

        <div class="dashboard-content">
          <!-- Upcoming Events -->
          <div>
            <div class="section-label" style="margin-bottom: 1rem;">
              Upcoming Events (<span class="count-highlight"><?php echo count($upcomingEvents); ?></span>)
            </div>

            <div class="events-list">
              <?php if ($upcomingEvents): ?>
                <?php foreach ($upcomingEvents as $event): ?>
                  <div class="event-card">
                    <div>
                      <span class="event-card-tag"><?php echo htmlspecialchars($event['event_type'] ?: 'Event'); ?></span>
                      <div class="event-card-title"><?php echo htmlspecialchars($event['title']); ?></div>
                      <div class="event-card-meta">
                        <span class="event-card-meta-item">
                          <i class="fa-regular fa-calendar"></i> <?php echo htmlspecialchars(date('l, jS M Y', strtotime($event['event_date']))); ?>
                        </span>
                        <span class="event-card-meta-item">
                          <i class="fa-regular fa-clock"></i> <?php echo !empty($event['event_time']) ? htmlspecialchars(date('h:i A', strtotime($event['event_time']))) : 'Time TBA'; ?>
                        </span>
                        <span class="event-card-meta-item">
                          <i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($event['venue'] ?: 'Venue TBA'); ?>
                        </span>
                        <span class="event-card-meta-item">
                          <i class="fa-solid fa-users"></i> Capacity: <?php echo (int) ($event['capacity'] ?? 0); ?>
                        </span>
                      </div>
                    </div>
                    <button class="btn-book-seat"><?php echo $event['member_status'] === 'booked' ? 'Booked' : 'Book a Seat'; ?></button>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="zone-empty-state">No upcoming events have been published yet.</div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Past Events -->
          <div>
            <div class="section-label" style="margin-bottom: 1rem;">Past Events</div>

            <div class="events-list">
              <?php if ($pastEvents): ?>
                <?php foreach ($pastEvents as $event): ?>
                  <div class="past-event-row">
                    <div class="past-event-row-left">
                      <span class="event-type-pill"><?php echo htmlspecialchars($event['event_type'] ?: 'Event'); ?></span>
                      <span class="past-event-title"><?php echo htmlspecialchars($event['title']); ?></span>
                    </div>
                    <span class="past-event-date"><?php echo htmlspecialchars(date('D, d M Y', strtotime($event['event_date']))); ?></span>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="zone-empty-state">No past events are available yet.</div>
              <?php endif; ?>
            </div>
          </div>
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

      if (sidebarToggle) {
        sidebarToggle.addEventListener("click", () => {
          sidebarEl.classList.contains("open") ? closeSidebar() : openSidebar();
        });
      }

      if (sidebarCloseBtn) {
        sidebarCloseBtn.addEventListener("click", closeSidebar);
      }

      if (sidebarOverlay) {
        sidebarOverlay.addEventListener("click", closeSidebar);
      }

      document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") closeSidebar();
      });

      sidebarEl.querySelectorAll(".sidebar-link").forEach((link) => {
        const parentItem = link.closest(".sidebar-item");
        const isDropdownToggle = parentItem && parentItem.classList.contains("dropdown") && link === parentItem.querySelector(":scope > .sidebar-link");
        if (!isDropdownToggle) {
          link.addEventListener("click", closeSidebar);
        }
      });
    </script>
    <script src="../../js/preloader.js"></script>
  </body>
</html>