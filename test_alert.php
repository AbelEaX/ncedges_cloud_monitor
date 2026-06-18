<?php
// Load config
$config = require __DIR__ . '/config.php';
date_default_timezone_set($config['timezone'] ?? 'Africa/Kampala');

// Load PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require __DIR__ . '/vendor/autoload.php';

// Define sendAlert() function (copy from index.php)
function sendAlert($to, $from, $subject, $message) {
    global $config;
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Timeout    = 15; // Set a 15-second timeout
        $mail->Host       = $config['smtp']['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $config['smtp']['username'];
        $mail->Password   = $config['smtp']['password'];
        $mail->SMTPSecure = $config['smtp']['secure'];
        $mail->Port       = $config['smtp']['port'];

        $mail->setFrom($from, $config['company_name']);
        $mail->addAddress($to);
        $mail->Subject = $subject;
        $mail->Body    = $message;

        $mail->send();
        echo "Test alert sent!\n";
    } catch (Exception $e) {
        echo "SMTP Mail Error: {$mail->ErrorInfo}\n";
    }
}

// Send test alert
sendAlert(
    $config['alert_email'],
    $config['email_from'],
    "Test Alert from Nova Cloud Hosting",
    "This is a test SMTP alert at " . date('Y-m-d H:i:s')
);
