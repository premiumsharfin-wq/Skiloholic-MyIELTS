<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/email_template.php';

function sendEmail($to, $subject, $content, $is_content_html = true) {
    $mail = new PHPMailer(true);

    // If body is already HTML, assume it's just content and wrap it.
    // However, some existing calls might pass full HTML.
    // Let's wrap the content in our template always, assuming the caller passes the main message body.
    $body = getEmailTemplate($subject, $content);

    try {
        //Server settings
        $mail->isSMTP();                                            // Send using SMTP
        $mail->Host       = 'mail.skiloholic.com';                    // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
        $mail->Username   = 'no_reply@skiloholic.com';              // SMTP username
        $mail->Password   = 'DevNerds@Sharfin9090';                 // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            // Enable implicit TLS encryption
        $mail->Port       = 465;                                    // TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

        //Recipients
        $mail->setFrom('no_reply@skiloholic.com', 'MyIELTS - Skiloholic');
        $mail->addAddress($to);     // Add a recipient

        //Content
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($content);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>