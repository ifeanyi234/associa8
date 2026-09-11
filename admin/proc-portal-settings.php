<?php
require_once "../inc/db.php";
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: portal-settings.php'); exit; }
$portal = $_POST['portal_key'] ?? '';
$start = $portal === 'admission' ? ($_POST['admission_start'] ?? '') : ($_POST['cbt_start'] ?? '');
$end = $portal === 'admission' ? ($_POST['admission_end'] ?? '') : ($_POST['cbt_end'] ?? '');
if (!in_array($portal, ['admission', 'cbt'], true) || $start === '' || $end === '' || strtotime($end) <= strtotime($start)) {
    header('Location: portal-settings.php?status=error&msg=' . urlencode('Enter valid start and deadline values.'));
    exit;
}
mysqli_report(MYSQLI_REPORT_OFF);
$statement = mysqli_prepare($conn, 'INSERT INTO portal_settings (portal_key, start_at, end_at) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE start_at = VALUES(start_at), end_at = VALUES(end_at)');
if (!$statement) {
    error_log('Portal settings prepare failed: ' . mysqli_error($conn));
    header('Location: portal-settings.php?status=error&msg=' . urlencode('Portal settings are not available yet. Import the portal_settings table first.'));
    exit;
}
mysqli_stmt_bind_param($statement, 'sss', $portal, $start, $end);
$success = mysqli_stmt_execute($statement);
$message = $success ? ucfirst($portal) . ' portal settings updated.' : 'Portal settings could not be saved.';
header('Location: portal-settings.php?status=' . ($success ? 'success' : 'error') . '&msg=' . urlencode($message));
exit;
