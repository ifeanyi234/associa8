<?php
require_once 'inc/auth.php';
require_once '../inc/db.php';

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store');

$orgId = (int) ($_SESSION['org_id'] ?? 0);
$accountId = (int) ($_SESSION['user_id'] ?? 0);
if ($orgId < 1 || $accountId < 1) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied.']);
    exit;
}

if (empty($_SESSION['admin_notification_csrf'])) {
    $_SESSION['admin_notification_csrf'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['admin_notification_csrf'], $token)) {
        http_response_code(403);
        echo json_encode(['error' => 'Your session expired. Reload the page and try again.']);
        exit;
    }

    $action = (string) ($_POST['action'] ?? '');
    if ($action === 'read_all') {
        $statement = mysqli_prepare($conn, 'UPDATE admin_notifications SET is_read = 1 WHERE org_id = ? AND account_id = ? AND is_read = 0');
        if (!$statement) {
            error_log('Admin notification read-all query could not be prepared: ' . mysqli_error($conn));
        } else {
            mysqli_stmt_bind_param($statement, 'ii', $orgId, $accountId);
        }
    } elseif ($action === 'read') {
        $notificationId = filter_var($_POST['notification_id'] ?? null, FILTER_VALIDATE_INT);
        if (!$notificationId || $notificationId < 1) {
            http_response_code(400);
            echo json_encode(['error' => 'Choose a valid notification.']);
            exit;
        }
        $statement = mysqli_prepare($conn, 'UPDATE admin_notifications SET is_read = 1 WHERE id = ? AND org_id = ? AND account_id = ?');
        if (!$statement) {
            error_log('Admin notification read query could not be prepared: ' . mysqli_error($conn));
        } else {
            mysqli_stmt_bind_param($statement, 'iii', $notificationId, $orgId, $accountId);
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Choose a valid notification action.']);
        exit;
    }

    if (!$statement) {
        http_response_code(500);
        echo json_encode(['error' => 'Notifications are temporarily unavailable.']);
        exit;
    }
    if (!mysqli_stmt_execute($statement)) {
        error_log('Admin notification read update failed: ' . mysqli_stmt_error($statement));
        mysqli_stmt_close($statement);
        http_response_code(500);
        echo json_encode(['error' => 'Notifications are temporarily unavailable.']);
        exit;
    }
    mysqli_stmt_close($statement);
    echo json_encode(['success' => true]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    header('Allow: GET, POST');
    echo json_encode(['error' => 'Method not allowed.']);
    exit;
}

$statement = mysqli_prepare(
    $conn,
    'SELECT id, type, title, message, link, is_read, created_at
     FROM admin_notifications
     WHERE org_id = ? AND account_id = ?
     ORDER BY created_at DESC, id DESC
     LIMIT 10'
);
if (!$statement) {
    error_log('Admin notification feed query could not be prepared: ' . mysqli_error($conn));
    http_response_code(500);
    echo json_encode(['error' => 'Notifications are temporarily unavailable.']);
    exit;
}
mysqli_stmt_bind_param($statement, 'ii', $orgId, $accountId);
if (!mysqli_stmt_execute($statement)) {
    error_log('Admin notification feed query failed: ' . mysqli_stmt_error($statement));
    mysqli_stmt_close($statement);
    http_response_code(500);
    echo json_encode(['error' => 'Notifications are temporarily unavailable.']);
    exit;
}
$result = mysqli_stmt_get_result($statement);
if (!$result) {
    error_log('Admin notification feed result could not be read: ' . mysqli_stmt_error($statement));
    mysqli_stmt_close($statement);
    http_response_code(500);
    echo json_encode(['error' => 'Notifications are temporarily unavailable.']);
    exit;
}
$notifications = [];
while ($notification = mysqli_fetch_assoc($result)) {
    $notification['id'] = (int) $notification['id'];
    $notification['is_read'] = (bool) $notification['is_read'];
    if (!preg_match('/^[A-Za-z0-9_-]+\.php(?:\?[A-Za-z0-9_=&%-]*)?$/', (string) $notification['link'])) {
        $notification['link'] = 'dashboard.php';
    }
    $notifications[] = $notification;
}
mysqli_stmt_close($statement);
$countStatement = mysqli_prepare($conn, 'SELECT COUNT(*) AS unread_count FROM admin_notifications WHERE org_id = ? AND account_id = ? AND is_read = 0');
if (!$countStatement) {
    error_log('Admin notification unread count could not be prepared: ' . mysqli_error($conn));
    http_response_code(500);
    echo json_encode(['error' => 'Notifications are temporarily unavailable.']);
    exit;
}
mysqli_stmt_bind_param($countStatement, 'ii', $orgId, $accountId);
if (!mysqli_stmt_execute($countStatement)) {
    error_log('Admin notification unread count failed: ' . mysqli_stmt_error($countStatement));
    mysqli_stmt_close($countStatement);
    http_response_code(500);
    echo json_encode(['error' => 'Notifications are temporarily unavailable.']);
    exit;
}
$countResult = mysqli_stmt_get_result($countStatement);
if (!$countResult) {
    error_log('Admin notification unread count result could not be read: ' . mysqli_stmt_error($countStatement));
    mysqli_stmt_close($countStatement);
    http_response_code(500);
    echo json_encode(['error' => 'Notifications are temporarily unavailable.']);
    exit;
}
$unreadCount = (int) (mysqli_fetch_assoc($countResult)['unread_count'] ?? 0);
mysqli_stmt_close($countStatement);
echo json_encode([
    'notifications' => $notifications,
    'unread_count' => $unreadCount,
    'csrf_token' => $_SESSION['admin_notification_csrf'],
]);
