<?php
require_once 'inc/auth.php';
require_once '../../inc/db.php';

$memberId = (int) $_SESSION['member_id'];
$memberOrgId = (int) $_SESSION['member_org_id'];
$memberName = trim((string) ($_SESSION['member_name'] ?? 'Member'));
$memberNameParts = preg_split('/\s+/', $memberName) ?: [];
$memberInitials = strtoupper(substr($memberNameParts[0] ?? 'M', 0, 1) . substr($memberNameParts[1] ?? '', 0, 1));
$csrfToken = $_SESSION['member_message_csrf'] ?? '';
if (!is_string($csrfToken) || $csrfToken === '') {
    $csrfToken = bin2hex(random_bytes(32));
    $_SESSION['member_message_csrf'] = $csrfToken;
}

$threads = [];
$threadQuery = mysqli_prepare(
    $conn,
    "SELECT t.id, t.subject, t.sender_name, t.sent_at, t.staff_last_read_at,
            COALESCE((SELECT MAX(r.sent_at) FROM message_replies r WHERE r.thread_id = t.id), t.sent_at) AS last_activity,
            EXISTS(
                SELECT 1 FROM message_replies r
                WHERE r.thread_id = t.id AND r.sender_type = 'staff'
                  AND (t.member_last_read_at IS NULL OR r.sent_at > t.member_last_read_at)
            ) AS has_unread
     FROM message_threads t
     WHERE t.member_id = ? AND t.org_id = ?
     ORDER BY last_activity DESC, t.id DESC"
);
if (!$threadQuery) {
    error_log('Member message list could not be prepared: ' . mysqli_error($conn));
    http_response_code(500);
    exit('Your messages could not be loaded right now.');
}
mysqli_stmt_bind_param($threadQuery, 'ii', $memberId, $memberOrgId);
if (!mysqli_stmt_execute($threadQuery)) {
    error_log('Member message list failed: ' . mysqli_stmt_error($threadQuery));
    mysqli_stmt_close($threadQuery);
    http_response_code(500);
    exit('Your messages could not be loaded right now.');
}
$threadResult = mysqli_stmt_get_result($threadQuery);
if ($threadResult) {
    while ($thread = mysqli_fetch_assoc($threadResult)) {
        $threads[] = $thread;
    }
}
mysqli_stmt_close($threadQuery);

$selectedThreadId = filter_input(INPUT_GET, 'thread', FILTER_VALIDATE_INT);
if (!array_key_exists('thread', $_GET)) {
    $selectedThreadId = isset($threads[0]) ? (int) $threads[0]['id'] : null;
} elseif ($selectedThreadId === false || $selectedThreadId === null || $selectedThreadId < 1) {
    $selectedThreadId = 0;
}

$selectedThread = null;
$replies = [];
if ($selectedThreadId !== null && $selectedThreadId > 0) {
    $markRead = mysqli_prepare(
        $conn,
        'UPDATE message_threads SET member_last_read_at = UTC_TIMESTAMP(6)
         WHERE id = ? AND member_id = ? AND org_id = ?'
    );
    if (!$markRead) {
        error_log('Member message read update could not be prepared: ' . mysqli_error($conn));
        http_response_code(500);
        exit('Your message could not be opened right now.');
    }
    mysqli_stmt_bind_param($markRead, 'iii', $selectedThreadId, $memberId, $memberOrgId);
    if (!mysqli_stmt_execute($markRead)) {
        error_log('Member message read update failed: ' . mysqli_stmt_error($markRead));
        mysqli_stmt_close($markRead);
        http_response_code(500);
        exit('Your message could not be opened right now.');
    }
    mysqli_stmt_close($markRead);

    $selectedQuery = mysqli_prepare(
        $conn,
        'SELECT id, subject, body, sender_name, tag, sent_at
         FROM message_threads WHERE id = ? AND member_id = ? AND org_id = ? LIMIT 1'
    );
    if (!$selectedQuery) {
        error_log('Member message lookup could not be prepared: ' . mysqli_error($conn));
        http_response_code(500);
        exit('Your message could not be opened right now.');
    }
    mysqli_stmt_bind_param($selectedQuery, 'iii', $selectedThreadId, $memberId, $memberOrgId);
    if (!mysqli_stmt_execute($selectedQuery)) {
        error_log('Member message lookup failed: ' . mysqli_stmt_error($selectedQuery));
        mysqli_stmt_close($selectedQuery);
        http_response_code(500);
        exit('Your message could not be opened right now.');
    }
    $selectedResult = mysqli_stmt_get_result($selectedQuery);
    $selectedThread = $selectedResult ? mysqli_fetch_assoc($selectedResult) : null;
    mysqli_stmt_close($selectedQuery);

    if ($selectedThread) {
        $replyQuery = mysqli_prepare(
            $conn,
            "SELECT sender_type, sender_name, body, sent_at
             FROM message_replies
             WHERE thread_id = ?
             ORDER BY sent_at ASC, id ASC"
        );
        if (!$replyQuery) {
            error_log('Member message replies could not be prepared: ' . mysqli_error($conn));
            http_response_code(500);
            exit('The conversation could not be loaded right now.');
        }
        mysqli_stmt_bind_param($replyQuery, 'i', $selectedThreadId);
        if (!mysqli_stmt_execute($replyQuery)) {
            error_log('Member message replies failed: ' . mysqli_stmt_error($replyQuery));
            mysqli_stmt_close($replyQuery);
            http_response_code(500);
            exit('The conversation could not be loaded right now.');
        }
        $replyResult = mysqli_stmt_get_result($replyQuery);
        if ($replyResult) {
            while ($reply = mysqli_fetch_assoc($replyResult)) {
                $replies[] = $reply;
            }
        }
        mysqli_stmt_close($replyQuery);
    }
}

$unreadCount = 0;
foreach ($threads as &$thread) {
    if ($selectedThreadId !== null && (int) $thread['id'] === $selectedThreadId) {
        $thread['has_unread'] = 0;
    }
    $unreadCount += (int) $thread['has_unread'];
}
unset($thread);

$messageStatus = is_string($_GET['status'] ?? null) ? $_GET['status'] : '';
$statusMessages = [
    'sent' => ['success', 'Your message was sent.'],
    'invalid' => ['error', 'Check the subject and message, then try again.'],
    'csrf' => ['error', 'Your form expired. Reload this page and try again.'],
    'limited' => ['error', 'Please wait a minute before sending another message.'],
    'error' => ['error', 'The message could not be sent. Please try again.'],
];
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Messages - Associa8</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="shortcut icon" href="../../images/fav-logo.png" type="image/x-icon" />
    <link rel="stylesheet" href="../../css/preloader.css" />
    <link rel="stylesheet" href="../../css/dashboard.css?v=20261015-member-message-alignment-1" />
  </head>
  <body class="admin-body">
    <?php include('../inc/preloader.php') ?>
    <div class="admin-layout">
      <?php include('inc/sidebar.php') ?>
      <div class="sidebar-overlay" id="sidebarOverlay"></div>
      <main class="admin-main">
        <header class="admin-header">
          <div class="header-left">
            <button class="header-toggle-btn" id="sidebarToggle" type="button">
              <i class="fa-solid fa-bars"></i>
            </button>
            <h1 class="page-title">Message</h1>
          </div>
          <div class="header-right">
            <div class="header-search">
              <i class="fa-solid fa-magnifying-glass header-search-icon"></i>
              <input type="text" placeholder="Search...." />
            </div>
            <button class="notification-btn" type="button" aria-label="Notifications">
              <i class="fa-solid fa-bell"></i>
              <span class="notification-badge"></span>
            </button>
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
          <div class="page-action-header mb-4">
            <div>
              <h2 class="page-title-main">Contact your organization</h2>
              <p class="page-subtitle">Send a private message to your organization’s staff and continue the conversation here.</p>
            </div>
            <span class="badge-pill"><?php echo $unreadCount; ?> unread</span>
          </div>
          <?php if (isset($statusMessages[$messageStatus])): ?>
            <div class="inline-form-message <?php echo $statusMessages[$messageStatus][0]; ?>" role="status"><?php echo htmlspecialchars($statusMessages[$messageStatus][1], ENT_QUOTES, 'UTF-8'); ?></div>
          <?php endif; ?>

          <section class="messages-layout">
            <div class="message-list-panel">
              <div class="list-card-dark-header">
                <div class="list-card-dark-header-left"><span class="list-card-dark-header-icon"><i class="fa-regular fa-comment"></i></span>Conversations</div>
              </div>
              <details class="message-compose">
                <summary><i class="fa-solid fa-plus"></i> New message</summary>
                <form action="proc-messages.php" method="post" class="message-compose-form">
                  <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>" />
                  <input type="hidden" name="action" value="create" />
                  <label class="form-label" for="message-subject">Subject</label>
                  <input class="form-control" id="message-subject" name="subject" type="text" maxlength="150" required />
                  <label class="form-label" for="message-body">Message</label>
                  <textarea class="form-control" id="message-body" name="body" rows="5" maxlength="5000" required></textarea>
                  <button class="btn-navy-filled" type="submit">Send message</button>
                </form>
              </details>

              <?php if ($threads): ?>
                <?php foreach ($threads as $thread): ?>
                  <a class="message-list-item <?php echo (int) $thread['id'] === (int) ($selectedThread['id'] ?? 0) ? 'active' : ''; ?>" href="messages.php?thread=<?php echo (int) $thread['id']; ?>">
                    <div class="message-list-item-left">
                      <div class="message-avatar-initials"><?php echo htmlspecialchars('ST', ENT_QUOTES, 'UTF-8'); ?></div>
                      <div>
                        <div class="message-sender-name">Organization staff</div>
                        <div class="message-preview-text"><?php echo htmlspecialchars((string) $thread['subject'], ENT_QUOTES, 'UTF-8'); ?></div>
                      </div>
                    </div>
                    <span class="message-list-item-time <?php echo (int) $thread['has_unread'] === 1 ? 'unread' : ''; ?>"><?php echo htmlspecialchars(date('M d, Y', strtotime((string) $thread['last_activity'])), ENT_QUOTES, 'UTF-8'); ?></span>
                  </a>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="zone-empty-state">No conversations yet. Start a new message to contact your organization.</div>
              <?php endif; ?>
            </div>

            <div class="message-detail-panel">
              <?php if ($selectedThread): ?>
                <div class="message-detail-header">
                  <div class="message-detail-subject"><?php echo htmlspecialchars((string) $selectedThread['subject'], ENT_QUOTES, 'UTF-8'); ?></div>
                  <div class="message-detail-meta-row"><span>Conversation with your organization’s staff</span><span class="message-detail-tag">Private</span></div>
                </div>
                <div class="message-conversation member-view">
                  <article class="message-bubble member">
                    <div class="message-bubble-meta"><strong>You</strong><time><?php echo htmlspecialchars(date('D, d M Y · H:i', strtotime((string) $selectedThread['sent_at'])), ENT_QUOTES, 'UTF-8'); ?></time></div>
                    <p><?php echo nl2br(htmlspecialchars((string) $selectedThread['body'], ENT_QUOTES, 'UTF-8')); ?></p>
                  </article>
                  <?php foreach ($replies as $reply): ?>
                    <article class="message-bubble <?php echo $reply['sender_type'] === 'staff' ? 'staff' : 'member'; ?>">
                      <div class="message-bubble-meta">
                        <strong><?php echo htmlspecialchars($reply['sender_type'] === 'staff' ? (string) ($reply['sender_name'] ?: 'Organization staff') : 'You', ENT_QUOTES, 'UTF-8'); ?></strong>
                        <time><?php echo htmlspecialchars(date('D, d M Y · H:i', strtotime((string) $reply['sent_at'])), ENT_QUOTES, 'UTF-8'); ?></time>
                      </div>
                      <p><?php echo nl2br(htmlspecialchars((string) $reply['body'], ENT_QUOTES, 'UTF-8')); ?></p>
                    </article>
                  <?php endforeach; ?>
                </div>
                <form class="message-reply-bar message-reply-form" action="proc-messages.php" method="post">
                  <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>" />
                  <input type="hidden" name="action" value="reply" />
                  <input type="hidden" name="thread_id" value="<?php echo (int) $selectedThread['id']; ?>" />
                  <label class="sr-only" for="member-reply-body">Write a reply</label>
                  <textarea class="message-reply-input" id="member-reply-body" name="body" placeholder="Write your reply…" maxlength="5000" rows="2" required></textarea>
                  <button class="btn-reply-send" type="submit"><i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Reply</button>
                </form>
              <?php elseif ($selectedThreadId !== null): ?>
                <div class="zone-empty-state">That conversation could not be found.</div>
              <?php else: ?>
                <div class="zone-empty-state">Choose a conversation or start a new message.</div>
              <?php endif; ?>
            </div>
          </section>
        </div>
        <?php include('../inc/footer.php') ?>
      </main>
    </div>
    <script src="../../js/admin-sidebar.js"></script>
    <script src="../../js/preloader.js"></script>
  </body>
</html>
