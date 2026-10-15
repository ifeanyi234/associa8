<?php
require_once 'inc/auth.php';
require_once '../inc/db.php';

$orgId = (int) $_SESSION['org_id'];
$accountId = (int) $_SESSION['user_id'];
$adminName = trim((string) ($_SESSION['admin_name'] ?? 'Organization staff'));
$csrfToken = $_SESSION['admin_message_csrf'] ?? '';
if (!is_string($csrfToken) || $csrfToken === '') {
    $csrfToken = bin2hex(random_bytes(32));
    $_SESSION['admin_message_csrf'] = $csrfToken;
}

$threadQuery = mysqli_prepare(
    $conn,
    "SELECT t.id, t.subject, t.sender_name, t.sent_at, m.member_code,
            COALESCE((SELECT MAX(r.sent_at) FROM message_replies r WHERE r.thread_id = t.id), t.sent_at) AS last_activity,
            (
                (t.staff_last_read_at IS NULL OR t.sent_at > t.staff_last_read_at)
                OR EXISTS(
                    SELECT 1 FROM message_replies r
                    WHERE r.thread_id = t.id AND r.sender_type = 'member'
                      AND (t.staff_last_read_at IS NULL OR r.sent_at > t.staff_last_read_at)
                )
            ) AS has_unread
     FROM message_threads t
     INNER JOIN members m ON m.id = t.member_id AND m.org_id = t.org_id
     WHERE t.org_id = ?
     ORDER BY last_activity DESC, t.id DESC"
);
if (!$threadQuery) {
    error_log('Organization message inbox could not be prepared: ' . mysqli_error($conn));
    http_response_code(500);
    exit('The message inbox could not be loaded right now.');
}
mysqli_stmt_bind_param($threadQuery, 'i', $orgId);
if (!mysqli_stmt_execute($threadQuery)) {
    error_log('Organization message inbox failed: ' . mysqli_stmt_error($threadQuery));
    mysqli_stmt_close($threadQuery);
    http_response_code(500);
    exit('The message inbox could not be loaded right now.');
}
$threadResult = mysqli_stmt_get_result($threadQuery);
$threads = [];
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
        'UPDATE message_threads SET staff_last_read_at = UTC_TIMESTAMP(6)
         WHERE id = ? AND org_id = ?'
    );
    if (!$markRead) {
        error_log('Staff message read update could not be prepared: ' . mysqli_error($conn));
        http_response_code(500);
        exit('The conversation could not be opened right now.');
    }
    mysqli_stmt_bind_param($markRead, 'ii', $selectedThreadId, $orgId);
    if (!mysqli_stmt_execute($markRead)) {
        error_log('Staff message read update failed: ' . mysqli_stmt_error($markRead));
        mysqli_stmt_close($markRead);
        http_response_code(500);
        exit('The conversation could not be opened right now.');
    }
    mysqli_stmt_close($markRead);

    $selectedQuery = mysqli_prepare(
        $conn,
        "SELECT t.id, t.subject, t.body, t.sender_name, t.sender_avatar_initials, t.sent_at,
                m.member_code, m.email
         FROM message_threads t
         INNER JOIN members m ON m.id = t.member_id AND m.org_id = t.org_id
         WHERE t.id = ? AND t.org_id = ?
         LIMIT 1"
    );
    if (!$selectedQuery) {
        error_log('Organization message lookup could not be prepared: ' . mysqli_error($conn));
        http_response_code(500);
        exit('The conversation could not be opened right now.');
    }
    mysqli_stmt_bind_param($selectedQuery, 'ii', $selectedThreadId, $orgId);
    if (!mysqli_stmt_execute($selectedQuery)) {
        error_log('Organization message lookup failed: ' . mysqli_stmt_error($selectedQuery));
        mysqli_stmt_close($selectedQuery);
        http_response_code(500);
        exit('The conversation could not be opened right now.');
    }
    $selectedResult = mysqli_stmt_get_result($selectedQuery);
    $selectedThread = $selectedResult ? mysqli_fetch_assoc($selectedResult) : null;
    mysqli_stmt_close($selectedQuery);

    if ($selectedThread) {
        $replyQuery = mysqli_prepare(
            $conn,
            'SELECT sender_type, sender_name, body, sent_at
             FROM message_replies WHERE thread_id = ? ORDER BY sent_at ASC, id ASC'
        );
        if (!$replyQuery) {
            error_log('Organization message replies could not be prepared: ' . mysqli_error($conn));
            http_response_code(500);
            exit('The conversation could not be loaded right now.');
        }
        mysqli_stmt_bind_param($replyQuery, 'i', $selectedThreadId);
        if (!mysqli_stmt_execute($replyQuery)) {
            error_log('Organization message replies failed: ' . mysqli_stmt_error($replyQuery));
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
    'sent' => ['success', 'Your reply was sent.'],
    'invalid' => ['error', 'Enter a reply of no more than 5,000 characters.'],
    'csrf' => ['error', 'Your form expired. Reload the page and try again.'],
    'limited' => ['error', 'Please wait a minute before sending another reply.'],
    'error' => ['error', 'The reply could not be sent. Please try again.'],
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
    <link rel="shortcut icon" href="../images/fav-logo.png" type="image/x-icon" />
    <link rel="stylesheet" href="../css/preloader.css" />
    <link rel="stylesheet" href="../css/dashboard.css?v=20261013-messages-workflow-1" />
  </head>
  <body class="admin-body">
    <?php include('inc/preloader.php') ?>
    <div class="admin-layout">
      <?php include('inc/sidebar.php') ?>
      <div class="sidebar-overlay" id="sidebarOverlay"></div>
      <main class="admin-main">
        <header class="admin-header">
          <div class="header-left">
            <button class="header-toggle-btn" id="sidebarToggle" type="button" aria-label="Open menu"><i class="fa-solid fa-bars"></i></button>
            <h1 class="page-title">Messages</h1>
          </div>
          <div class="header-right">
            <div class="admin-user-profile">
              <div class="avatar-badge"><?php echo htmlspecialchars(strtoupper(substr($adminName, 0, 2)), ENT_QUOTES, 'UTF-8'); ?></div>
              <div class="user-info"><span class="user-name"><?php echo htmlspecialchars($adminName, ENT_QUOTES, 'UTF-8'); ?></span><span class="user-role"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', (string) $_SESSION['admin_role'])), ENT_QUOTES, 'UTF-8'); ?></span></div>
            </div>
          </div>
        </header>
        <div class="dashboard-content">
          <div class="page-action-header mb-4">
            <div><h2 class="page-title-main">Member conversations</h2><p class="page-subtitle">This inbox is shared by active staff in your organization. Replies are visible to the member.</p></div>
            <span class="badge-pill"><?php echo $unreadCount; ?> unread</span>
          </div>
          <?php if (isset($statusMessages[$messageStatus])): ?>
            <div class="inline-form-message <?php echo $statusMessages[$messageStatus][0]; ?>" role="status"><?php echo htmlspecialchars($statusMessages[$messageStatus][1], ENT_QUOTES, 'UTF-8'); ?></div>
          <?php endif; ?>
          <section class="messages-layout">
            <div class="message-list-panel">
              <div class="list-card-dark-header"><div class="list-card-dark-header-left"><span class="list-card-dark-header-icon"><i class="fa-regular fa-comment"></i></span>Inbox</div></div>
              <?php if ($threads): ?>
                <?php foreach ($threads as $thread): ?>
                  <a class="message-list-item <?php echo (int) $thread['id'] === (int) ($selectedThread['id'] ?? 0) ? 'active' : ''; ?>" href="messages.php?thread=<?php echo (int) $thread['id']; ?>">
                    <div class="message-list-item-left">
                      <div class="message-avatar-initials"><?php echo htmlspecialchars(substr((string) $thread['sender_name'], 0, 2), ENT_QUOTES, 'UTF-8'); ?></div>
                      <div><div class="message-sender-name"><?php echo htmlspecialchars((string) $thread['sender_name'], ENT_QUOTES, 'UTF-8'); ?></div><div class="message-preview-text"><?php echo htmlspecialchars((string) $thread['subject'], ENT_QUOTES, 'UTF-8'); ?> · <?php echo htmlspecialchars((string) $thread['member_code'], ENT_QUOTES, 'UTF-8'); ?></div></div>
                    </div>
                    <span class="message-list-item-time <?php echo (int) $thread['has_unread'] === 1 ? 'unread' : ''; ?>"><?php echo htmlspecialchars(date('M d, Y', strtotime((string) $thread['last_activity'])), ENT_QUOTES, 'UTF-8'); ?></span>
                  </a>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="zone-empty-state">There are no member conversations yet.</div>
              <?php endif; ?>
            </div>
            <div class="message-detail-panel">
              <?php if ($selectedThread): ?>
                <div class="message-detail-header">
                  <div class="message-detail-subject"><?php echo htmlspecialchars((string) $selectedThread['subject'], ENT_QUOTES, 'UTF-8'); ?></div>
                  <div class="message-detail-meta-row"><span>From: <strong><?php echo htmlspecialchars((string) $selectedThread['sender_name'], ENT_QUOTES, 'UTF-8'); ?></strong> · <?php echo htmlspecialchars((string) $selectedThread['member_code'], ENT_QUOTES, 'UTF-8'); ?> · <?php echo htmlspecialchars((string) $selectedThread['email'], ENT_QUOTES, 'UTF-8'); ?></span><span class="message-detail-tag">Private</span></div>
                </div>
                <div class="message-conversation">
                  <article class="message-bubble member">
                    <div class="message-bubble-meta"><strong><?php echo htmlspecialchars((string) $selectedThread['sender_name'], ENT_QUOTES, 'UTF-8'); ?></strong><time><?php echo htmlspecialchars(date('D, d M Y · H:i', strtotime((string) $selectedThread['sent_at'])), ENT_QUOTES, 'UTF-8'); ?></time></div>
                    <p><?php echo nl2br(htmlspecialchars((string) $selectedThread['body'], ENT_QUOTES, 'UTF-8')); ?></p>
                  </article>
                  <?php foreach ($replies as $reply): ?>
                    <article class="message-bubble <?php echo $reply['sender_type'] === 'staff' ? 'staff' : 'member'; ?>">
                      <div class="message-bubble-meta"><strong><?php echo htmlspecialchars($reply['sender_type'] === 'staff' ? (string) ($reply['sender_name'] ?: 'Organization staff') : (string) $selectedThread['sender_name'], ENT_QUOTES, 'UTF-8'); ?></strong><time><?php echo htmlspecialchars(date('D, d M Y · H:i', strtotime((string) $reply['sent_at'])), ENT_QUOTES, 'UTF-8'); ?></time></div>
                      <p><?php echo nl2br(htmlspecialchars((string) $reply['body'], ENT_QUOTES, 'UTF-8')); ?></p>
                    </article>
                  <?php endforeach; ?>
                </div>
                <form class="message-reply-bar message-reply-form" action="proc-message-reply.php" method="post">
                  <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>" />
                  <input type="hidden" name="thread_id" value="<?php echo (int) $selectedThread['id']; ?>" />
                  <label class="sr-only" for="staff-reply-body">Write a reply</label>
                  <textarea class="message-reply-input" id="staff-reply-body" name="body" placeholder="Write a reply to the member…" maxlength="5000" rows="2" required></textarea>
                  <button class="btn-reply-send" type="submit"><i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Reply</button>
                </form>
              <?php elseif ($selectedThreadId !== null): ?>
                <div class="zone-empty-state">That conversation could not be found in your organization.</div>
              <?php else: ?>
                <div class="zone-empty-state">Member conversations will appear here.</div>
              <?php endif; ?>
            </div>
          </section>
        </div>
        <?php include('inc/footer.php') ?>
      </main>
    </div>
    <script src="../js/admin-sidebar.js"></script>
    <script src="../js/preloader.js"></script>
  </body>
</html>
