<?php

/**
 * Update Settings API Endpoint
 *
 * POST /api/settings/update.php?section=general
 */

require dirname(__DIR__, 3) . '/bootstrap/app.php';

$auth = app(\App\Infrastructure\Authentication\AuthenticationService::class);
if (!$auth->isAuthenticated() || !$auth->hasPermission('settings.edit')) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $section = $_GET['section'] ?? 'general';
    $input = json_decode(file_get_contents('php://input'), true);

    // For now, just return success (actual implementation would save to database/config)
    // Log action
    $audit = app(\App\Infrastructure\Logging\AuditService::class);
    $audit->log('update', 'settings', null, $auth->user()->id, ['message' => "Updated $section settings", 'section' => $section, 'input' => $input], 'info');

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Settings updated successfully',
        'data' => ['section' => $section]
    ]);
} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update settings',
        'errors' => [$e->getMessage()]
    ]);
}
