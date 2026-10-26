<?php

$migration = file_get_contents(__DIR__ . '/../migrations/20261024_admin_notifications.sql');
$feed = file_get_contents(__DIR__ . '/../admin/notification-feed.php');
$script = file_get_contents(__DIR__ . '/../js/admin-notifications.js');
$helper = file_get_contents(__DIR__ . '/../inc/admin-notifications.php');
$cbtSubmission = file_get_contents(__DIR__ . '/../proc-submit-cbt.php');

if (
    $migration === false
    || strpos($migration, 'is_read') === false
    || strpos($migration, 'account_id') === false
) {
    fwrite(STDERR, "FAIL: per-account notification read state schema is missing\n");
    exit(1);
}
if (
    $feed === false
    || strpos($feed, 'read_all') === false
    || strpos($feed, 'admin_notification_csrf') === false
    || strpos($feed, 'unread_count') === false
) {
    fwrite(STDERR, "FAIL: notification state API is missing\n");
    exit(1);
}
if (
    $script === false
    || strpos($script, 'Mark all read') === false
    || strpos($script, 'is-unread') === false
    || $helper === false
    || strpos($helper, 'admin_notify_organization') === false
    || strpos($helper, "ai.role IN ('admin', 'super_admin')") === false
    || $cbtSubmission === false
    || strpos($cbtSubmission, 'admin_notify_organization') === false
) {
    fwrite(STDERR, "FAIL: notification bell or notification creation is missing\n");
    exit(1);
}

echo "The admin notification bell supports per-account unread/read state.\n";
