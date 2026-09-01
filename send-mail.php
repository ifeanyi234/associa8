<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: contact.php');
    exit;
}

$fullName = trim($_POST['fullName'] ?? '');
$orgName = trim($_POST['orgName'] ?? '');
$orgType = trim($_POST['orgType'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$location = trim($_POST['location'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($fullName === '' || $email === '' || $orgType === '' || $location === '') {
    header('Location: contact.php?status=error&msg=' . urlencode('Please complete the required fields before submitting.'));
    exit;
}

$to = 'info@associa8.com.ng';
$subject = 'New Contact Message from ' . $fullName;
$body = "Full Name: $fullName\n" .
    "Organization: " . ($orgName !== '' ? $orgName : 'Not provided') . "\n" .
    "Organization Type: $orgType\n" .
    "Phone: " . ($phone !== '' ? $phone : 'Not provided') . "\n" .
    "Email: $email\n" .
    "Location: $location\n\n" .
    "Message:\n$message";

$headers = "From: $email\r\n" .
    "Reply-To: $email\r\n" .
    "Content-Type: text/plain; charset=UTF-8\r\n" .
    "X-Mailer: PHP/" . phpversion();

if (mail($to, $subject, $body, $headers)) {
    header('Location: contact.php?status=success&msg=' . urlencode('Your message was sent successfully. We will get back to you soon.'));
} else {
    header('Location: contact.php?status=error&msg=' . urlencode('Your message could not be sent. Please try again later.'));
}

exit;
?>
