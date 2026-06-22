<?php
require __DIR__ . '/vendor/autoload.php';

// Load environment variables from .env file manually
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) continue; // Skip comments
        if (strpos($line, '=') === false) continue;
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if (in_array($value[0] ?? null, ['"', "'"])) {
            $value = substr($value, 1, -1);
        }
        putenv("$key=$value");
    }
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

$mail = new PHPMailer(true);

try {
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    $mail->isSMTP();
    $mail->Host       = getenv('MAIL_HOST');
    $mail->SMTPAuth   = true;
    $mail->Username   = getenv('MAIL_USERNAME');
    $mail->Password   = getenv('MAIL_PASSWORD');
    $mail->SMTPSecure = getenv('MAIL_ENCRYPTION');
    $mail->Port       = getenv('MAIL_PORT');

    $mail->setFrom(getenv('MAIL_FROM_ADDRESS'), 'Mailer');
    $mail->addAddress('aekode@ug.ncedges.com', 'Admin User');

    $mail->isHTML(true);
    $mail->Subject = 'SMTP Test';
    $mail->Body    = '<b>Test Successful</b>';

    $mail->send();
    echo "Message has been sent\n";
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}\n";
}
