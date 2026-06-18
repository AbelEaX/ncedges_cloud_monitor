<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';

/**
 * Checks server health and returns status and latency.
 */
function checkServer($host, $port, $timeout) {
    $start = microtime(true);
    $fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
    $end = microtime(true);
    
    if ($fp) {
        fclose($fp);
        return ['up' => true, 'latency' => round(($end - $start) * 1000, 2)];
    }
    return ['up' => false, 'latency' => 0];
}

/**
 * Performs an HTTP check using cURL to verify status codes.
 */
function checkUrl($url, $timeout) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $start = microtime(true);
    curl_exec($ch);
    $end = microtime(true);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Consider 200-399 as UP
    $isUp = ($code >= 200 && $code < 400);
    return ['up' => $isUp, 'latency' => round(($end - $start) * 1000, 2), 'code' => $code];
}

/**
 * Standardized mailing function.
 */
function sendAlert($config, $subject, $message) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Timeout    = 15;
        $mail->Host       = $config['smtp']['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $config['smtp']['username'];
        $mail->Password   = $config['smtp']['password'];
        $mail->SMTPSecure = $config['smtp']['secure'];
        $mail->Port       = $config['smtp']['port'];

        $mail->setFrom($config['email_from'], $config['company_name']);
        $mail->addAddress($config['alert_email']);
        $mail->Subject = $subject;
        $mail->Body    = $message;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("SMTP Mail Error: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Format seconds into human readable duration.
 */
function formatDuration($seconds) {
    if ($seconds < 60) return $seconds . "s";
    $h = floor($seconds / 3600);
    $m = floor(($seconds % 3600) / 60);
    return sprintf('%02dh %02dm', $h, $m);
}