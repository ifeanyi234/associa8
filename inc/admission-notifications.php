<?php
require_once __DIR__ . '/mailer.php';

function notify_admission_status(mysqli $conn, int $admissionId, string $status, string $applicantName, string $email, ?int $score = null, ?string $resultStatus = null): void
{
    $statusLabel = ucwords(str_replace('_', ' ', $status));
    $title = 'Application status: ' . $statusLabel;
    $message = 'Your Associa8 application is now ' . strtolower($statusLabel) . '.';
    if ($status === 'cbt_completed' && $score !== null && $resultStatus !== null) {
        $message = 'Your CBT assessment is complete. Score: ' . $score . '%. Result: ' . ucfirst($resultStatus) . '. Final admission approval is still pending.';
    }

    $notification = mysqli_prepare($conn, 'INSERT INTO notifications (admission_id, type, title, message) VALUES (?, ?, ?, ?)');
    if ($notification) {
        mysqli_stmt_bind_param($notification, 'isss', $admissionId, $status, $title, $message);
        if (!mysqli_stmt_execute($notification)) {
            error_log('Admission notification insert failed: ' . mysqli_stmt_error($notification));
        }
    } else {
        error_log('Admission notification prepare failed: ' . mysqli_error($conn));
    }

    $subject = 'Associa8 application update: ' . $statusLabel;
    $body = '<p>Hello ' . htmlspecialchars($applicantName, ENT_QUOTES, 'UTF-8') . ',</p>'
        . '<p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>'
        . '<p>' . ($status === 'cbt_completed'
            ? 'Please wait for the admissions team to make and communicate the final decision.'
            : 'We will send another message when the next step is ready.') . '</p>';

    if (!send_app_mail($email, $applicantName, $subject, $body)) {
        error_log('Admission status email failed for admission ' . $admissionId . '.');
    }
}
