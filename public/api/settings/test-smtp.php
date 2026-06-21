<?php
/**
 * API: Test SMTP Connection
 * 
 * Sends a test email to verify SMTP configuration
 */
require dirname(__DIR__, 3) . '/bootstrap/app.php';

use App\Presentation\Responses\ApiResponse;
use App\Presentation\Middleware\AuthenticationMiddleware;

// Check authentication
$auth = new AuthenticationMiddleware();
$auth->handle();

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ApiResponse::error('Method not allowed', [], 405);
}

$email = $_POST['email'] ?? '';

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    ApiResponse::validationError(['email' => 'Valid email address is required']);
}

$mailService = app(\App\Infrastructure\Mail\MailService::class);
$logger = app(\App\Infrastructure\Logging\Logger::class);

try {
    $result = $mailService->sendTest($email);
    
    if ($result) {
        $logger->info("SMTP test sent to {$email}", [], 'security');
        ApiResponse::success(
            ['email' => $email],
            'Test email sent successfully. Please check your inbox.',
            200
        );
    } else {
        ApiResponse::error('Failed to send test email. Check logs for details.', [], 500);
    }
} catch (\Exception $e) {
    $logger->error("SMTP test error: " . $e->getMessage(), [], 'security');
    ApiResponse::error('Error sending test email: ' . $e->getMessage(), [], 500);
}
