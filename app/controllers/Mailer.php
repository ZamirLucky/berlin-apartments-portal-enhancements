<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php'; // Adjust path if needed

class Mailer {
    public function sendEmail($to, $subject, $body) {
        $mail = new PHPMailer(true);
        try {
            // SMTP config from config.php
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->Port       = SMTP_PORT;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USERNAME;
            $mail->Password   = SMTP_PASSWORD;
            $mail->setFrom(SMTP_FROM_EMAIL);
            $mail->addReplyTo(SMTP_REPLY_TO);
            $mail->addAddress($to);

            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->isHTML(false);

            $mail->send();
            return true;
        } catch (Exception $e) {
            // Optionally log error
            return false;
        }
    }

}
