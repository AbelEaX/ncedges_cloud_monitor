<?php

/**
 * Test Email API Endpoint
 *
 * POST /api/settings/test-email.php
 */

require dirname(__DIR__, 3) . '/bootstrap/app.php';

$auth = app(\App\Infrastructure\Authentication\AuthenticationService::class);
if (!$auth->isAuthenticated() || !$auth->hasPermission('settings.edit')) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $to = $input['to'] ?? $auth->user()->getEmail();

    if (!$to) {
        throw new Exception('Email address required');
    }

    $mailer = app(\App\Infrastructure\Mail\MailService::class);
    $result = $mailer->send(
        $to,
        'Test Email from Monitor',
        'This is a test email to verify your SMTP configuration is working correctly.'
    );

    if ($result) {
        $audit = app(\App\Infrastructure\Logging\AuditService::class);
        $audit->log('send', 'test_email', null, $auth->user()->id, "Sent test email to $to");

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => "Test email sent successfully to $to"
        ]);
    } else {
        throw new Exception('Failed to send test email');
    }
} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode([
        'success' => false,
        'message' => 'Failed to send test email',
        'errors' => [$e->getMessage()]
    ]);
}
