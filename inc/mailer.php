<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/mail-config.php';
// ...

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function app_email_template(string $contentHtml, string $preheader = ''): string
{
    $safePreheader = htmlspecialchars($preheader, ENT_QUOTES, 'UTF-8');

    return '<!doctype html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Associa8</title></head>'
        . '<body style="margin:0;background:#f4f7fb;color:#24324a;font-family:Arial,Helvetica,sans-serif;line-height:1.6;">'
        . '<div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">' . $safePreheader . '</div>'
        . '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f4f7fb;padding:32px 12px;font-family:Arial,Helvetica,sans-serif;">'
        . '<tr><td align="center"><table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:620px;background:#ffffff;border:1px solid #e4eaf2;border-radius:12px;overflow:hidden;font-family:Arial,Helvetica,sans-serif;">'
        . '<tr><td style="background:#0a2244;padding:24px 32px;"><img src="cid:associa8-logo" width="132" alt="Associa8" style="display:block;width:132px;height:auto;border:0;"></td></tr>'
        . '<tr><td style="padding:32px;font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:1.6;">' . $contentHtml . '</td></tr>'
        . '<tr><td style="border-top:1px solid #e4eaf2;padding:20px 32px;color:#6b778c;font-size:12px;">This is an automated message from Associa8. Please do not reply to this email.</td></tr>'
        . '</table></td></tr></table></body></html>';
}

function send_app_mail(string $toEmail, string $toName, string $subject, string $bodyHtml, string $preheader = ''): bool {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = MAIL_PORT;

        $mail->CharSet = 'UTF-8';
        $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        $mail->addReplyTo(MAIL_REPLY_TO, MAIL_FROM_NAME);
        $mail->addAddress($toEmail, $toName);
        if (defined('MAIL_LOGO_PATH') && is_file(MAIL_LOGO_PATH)) {
            $mail->addEmbeddedImage(MAIL_LOGO_PATH, 'associa8-logo', 'associa8-logo.png', 'base64', 'image/png');
        }

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = app_email_template($bodyHtml, $preheader ?: $subject);
        $mail->AltBody = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($bodyHtml), ENT_QUOTES, 'UTF-8')));

        return $mail->send();
    } catch (Exception $e) {
        error_log("Mailer error: " . $mail->ErrorInfo);
        return false;
    }
}
?>