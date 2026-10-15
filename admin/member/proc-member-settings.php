<?php
require_once 'inc/auth.php';
require_once '../../inc/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: settings.php');
    exit;
}

$csrfToken = $_POST['csrf_token'] ?? '';
$sessionToken = $_SESSION['member_settings_csrf'] ?? '';
if (!is_string($csrfToken) || !is_string($sessionToken) || $sessionToken === ''
    || !hash_equals($sessionToken, $csrfToken)) {
    header('Location: settings.php?status=csrf');
    exit;
}

$memberId = (int) $_SESSION['member_id'];
$memberOrgId = (int) $_SESSION['member_org_id'];
$settingsGroup = $_POST['settings_group'] ?? '';

if ($settingsGroup === 'notifications') {
    $dueReminder = isset($_POST['email_due_reminder']) ? 1 : 0;
    $eventReminder = isset($_POST['email_event_reminder']) ? 1 : 0;
    $generalAnnouncement = isset($_POST['email_general_announcement']) ? 1 : 0;
    $smsReminder = isset($_POST['sms_reminder']) ? 1 : 0;

    $update = mysqli_prepare(
        $conn,
        'INSERT INTO member_notification_preferences
            (member_id, email_due_reminder, email_event_reminder, email_general_announcement, sms_reminder)
         SELECT ?, ?, ?, ?, ?
         FROM members WHERE id = ? AND org_id = ?
         ON DUPLICATE KEY UPDATE
            email_due_reminder = VALUES(email_due_reminder),
            email_event_reminder = VALUES(email_event_reminder),
            email_general_announcement = VALUES(email_general_announcement),
            sms_reminder = VALUES(sms_reminder)'
    );
    if (!$update) {
        error_log('Member notification preferences update could not be prepared: ' . mysqli_error($conn));
        header('Location: settings.php?status=error');
        exit;
    }
    mysqli_stmt_bind_param(
        $update,
        'iiiiiii',
        $memberId,
        $dueReminder,
        $eventReminder,
        $generalAnnouncement,
        $smsReminder,
        $memberId,
        $memberOrgId
    );
} elseif ($settingsGroup === 'regional') {
    $language = $_POST['language'] ?? '';
    $timezone = $_POST['timezone'] ?? '';
    $dateFormat = $_POST['date_format'] ?? '';
    $currency = $_POST['currency_display'] ?? '';
    $allowedLanguages = ['English (Nigeria)', 'English (United Kingdom)', 'English (United States)'];
    $allowedTimezones = ['Africa/Lagos', 'Africa/Accra', 'Africa/Nairobi', 'UTC', 'Europe/London', 'America/New_York'];
    $allowedDateFormats = ['DD/MM/YYYY', 'MM/DD/YYYY', 'YYYY-MM-DD'];
    $allowedCurrencies = ['Nigeria/Naira', 'Ghana/Cedi', 'Kenya/Shilling', 'United States/Dollar', 'United Kingdom/Pound'];

    if (!is_string($language) || !in_array($language, $allowedLanguages, true)
        || !is_string($timezone) || !in_array($timezone, $allowedTimezones, true)
        || !is_string($dateFormat) || !in_array($dateFormat, $allowedDateFormats, true)
        || !is_string($currency) || !in_array($currency, $allowedCurrencies, true)) {
        header('Location: settings.php?status=invalid');
        exit;
    }

    $update = mysqli_prepare(
        $conn,
        'UPDATE members SET language = ?, timezone = ?, date_format = ?, currency_display = ?
         WHERE id = ? AND org_id = ?'
    );
    if (!$update) {
        error_log('Member regional preferences update could not be prepared: ' . mysqli_error($conn));
        header('Location: settings.php?status=error');
        exit;
    }
    mysqli_stmt_bind_param($update, 'ssssii', $language, $timezone, $dateFormat, $currency, $memberId, $memberOrgId);
} else {
    header('Location: settings.php?status=invalid');
    exit;
}

$saved = mysqli_stmt_execute($update);
if (!$saved) {
    error_log('Member settings update failed: ' . mysqli_stmt_error($update));
}
$affectedRows = mysqli_stmt_affected_rows($update);
mysqli_stmt_close($update);
if (!$saved || $affectedRows < 0) {
    header('Location: settings.php?status=error');
    exit;
}

unset($_SESSION['member_settings_csrf']);
header('Location: settings.php?status=success');
exit;
