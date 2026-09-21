<?php
require_once 'inc/auth.php';
require_once '../../inc/db.php';

$memberId = (int) $_SESSION['member_id'];

$threads = [];
$threadsResult = mysqli_query($conn, "SELECT * FROM message_threads WHERE member_id = $memberId ORDER BY sent_at DESC");
if ($threadsResult) {
  while ($thread = mysqli_fetch_assoc($threadsResult)) {
    $threads[] = $thread;
  }
}

$selectedThread = $threads[0] ?? null;
if ($selectedThread) {
  $replies = [];
  $replyResult = mysqli_query($conn, "SELECT * FROM message_replies WHERE thread_id = " . (int) $selectedThread['id'] . " ORDER BY sent_at ASC");
  if ($replyResult) {
    while ($reply = mysqli_fetch_assoc($replyResult)) {
      $replies[] = $reply;
    }
  }
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Messages - Associa8</title>
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
            <h1 class="page-title">Message</h1>
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
          <div class="section-label" style="margin-bottom: 0.25rem;">
            Unread (<span class="count-highlight"><?php echo count($threads); ?></span>)
          </div>

          <section class="messages-layout">
            <div class="message-list-panel">
              <div class="list-card-dark-header">
                <div class="list-card-dark-header-left">
                  <span class="list-card-dark-header-icon">
                    <i class="fa-regular fa-comment"></i>
                  </span>
                  Messages
                </div>
              </div>

              <?php if ($threads): ?>
                <?php foreach ($threads as $thread): ?>
                  <div class="message-list-item <?php echo $thread['id'] === ($selectedThread['id'] ?? null) ? 'active' : ''; ?>">
                    <div class="message-list-item-left">
                      <div class="message-avatar-initials"><?php echo htmlspecialchars(substr($thread['sender_name'] ?? 'NA', 0, 2)); ?></div>
                      <div>
                        <div class="message-sender-name"><?php echo htmlspecialchars($thread['sender_name'] ?? 'System'); ?></div>
                        <div class="message-preview-text"><?php echo htmlspecialchars($thread['subject'] ?? 'No subject'); ?></div>
                      </div>
                    </div>
                    <span class="message-list-item-time <?php echo (int) ($thread['is_read'] ?? 0) === 0 ? 'unread' : ''; ?>"><?php echo htmlspecialchars(date('M d Y', strtotime($thread['sent_at']))); ?></span>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="zone-empty-state">No messages have been sent to this member yet.</div>
              <?php endif; ?>
            </div>

            <div class="message-detail-panel">
              <?php if ($selectedThread): ?>
                <div class="message-detail-header">
                  <div class="message-detail-subject"><?php echo htmlspecialchars($selectedThread['subject']); ?></div>
                  <div class="message-detail-meta-row">
                    <div class="message-detail-meta-left">
                      <div class="message-avatar-initials" style="background-color: rgba(255,255,255,0.12); color: #ffffff;">"><?php echo htmlspecialchars(substr($selectedThread['sender_name'] ?? 'NA', 0, 2)); ?></div>
                      From: <strong><?php echo htmlspecialchars($selectedThread['sender_name'] ?? 'System'); ?></strong> <?php echo htmlspecialchars(date('D, d M Y', strtotime($selectedThread['sent_at']))); ?>
                    </div>
                    <span class="message-detail-tag"><?php echo htmlspecialchars($selectedThread['tag'] ?: 'Official'); ?></span>
                  </div>
                </div>

                <div class="message-detail-body">
                  <p><?php echo nl2br(htmlspecialchars($selectedThread['body'])); ?></p>
                </div>

                <div class="message-reply-bar">
                  <input type="text" class="message-reply-input" placeholder="Write your reply" />
                  <button class="btn-reply-send">
                    <i class="fa-solid fa-paper-plane"></i> Reply
                  </button>
                </div>
              <?php else: ?>
                <div class="zone-empty-state">Select a thread to read your messages.</div>
              <?php endif; ?>
            </div>
          </section>
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

      // Message list item selection
      document.querySelectorAll(".message-list-item").forEach((item) => {
        item.addEventListener("click", () => {
          document.querySelectorAll(".message-list-item").forEach((i) => i.classList.remove("active"));
          item.classList.add("active");
        });
      });
    </script>
    <script src="../../js/preloader.js"></script>
  </body>
</html>