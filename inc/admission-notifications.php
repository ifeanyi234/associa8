<?php
require_once __DIR__ . '/mailer.php';

function notify_admission_status(mysqli $conn, int $admissionId, string $status, string $applicantName, string $email, ?int $score = null, ?string $resultStatus = null): void
{
    $statusLabel = ucwords(str_replace('_', ' ', $status));
    $title = 'Application status: ' . $statusLabel;
    $message = 'Your Associa8 application is now ' . strtolower($statusLabel) . '.';
    $cbtLink = '';
    if ($status === 'under_review') {
        $deadlineStatement = mysqli_prepare($conn, 'SELECT cbt_response_deadline FROM admissions WHERE id = ? LIMIT 1');
        $deadline = null;
        if ($deadlineStatement) {
            mysqli_stmt_bind_param($deadlineStatement, 'i', $admissionId);
            mysqli_stmt_execute($deadlineStatement);
            $deadlineResult = mysqli_stmt_get_result($deadlineStatement);
            $deadlineRow = $deadlineResult ? mysqli_fetch_assoc($deadlineResult) : null;
            $deadline = $deadlineRow['cbt_response_deadline'] ?? null;
        }
        $formattedDeadline = $deadline
            ? (new DateTimeImmutable($deadline, new DateTimeZone('UTC')))->format('F j, Y g:i A') . ' UTC'
            : '36 hours after this notice';
        $basePath = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\');
        $assessmentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $basePath . '/cbt-code.php';
        $message = 'Your application is under review. You have 24 hours to request your CBT access code, followed by a 12-hour grace period.';
        $cbtLink = '<p><strong>Final access deadline:</strong> ' . htmlspecialchars($formattedDeadline, ENT_QUOTES, 'UTF-8') . '</p>'
            . '<p>After this deadline, you will no longer be able to take this assessment. Once you request the code, the exam timer starts and you will have the configured exam duration to finish.</p>'
            . '<p><a href="' . htmlspecialchars($assessmentUrl, ENT_QUOTES, 'UTF-8') . '">Start your CBT assessment</a></p>';
    }
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
        . $cbtLink
        . '<p>' . ($status === 'cbt_completed'
            ? 'Please wait for the admissions team to make and communicate the final decision.'
            : 'We will send another message when the next step is ready.') . '</p>';

    if (!send_app_mail($email, $applicantName, $subject, $body)) {
        error_log('Admission status email failed for admission ' . $admissionId . '.');
    }
}
