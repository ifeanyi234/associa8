<?php
$to = "someone@example.com";
$subject = "Welcome to Associa8";
$message = "Hello,\n\nYour account has been created successfully.\n\nRegards,\nAssocia8 Team";
$headers = "From: no-reply@associa8.com\r\n" .
    "Reply-To: no-reply@associa8.com\r\n" .
    "X-Mailer: PHP/" . phpversion();

if (mail($to, $subject, $message, $headers)) {
    echo "Email sent successfully.";
} else {
    echo "Email failed.";
}
?>
