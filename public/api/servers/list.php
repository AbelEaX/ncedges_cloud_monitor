<?php

/**
 * Server List API Endpoint
 *
 * GET /api/servers/list.php
 *
 * Returns a list of all servers with pagination support.
 */

require dirname(__DIR__, 3) . '/bootstrap/app.php';

// Check authentication
$auth = app(\App\Infrastructure\Authentication\AuthenticationService::class);
if (!$auth->isAuthenticated()) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized',
        'data' => null,
        'errors' => []
    ]);
    exit;
}

// Check permission
if (!$auth->hasPermission('server.view')) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode([
        'success' => false,
        'message' => 'Permission denied',
        'data' => null,
        'errors' => []
    ]);
    exit;
}

try {
    $serverRepo = app(\App\Infrastructure\Repositories\ServerRepository::class);
    $servers = $serverRepo->all();

    // Log action
    $audit = app(\App\Infrastructure\Logging\AuditService::class);
    $audit->log('read', 'servers', null, $auth->user()->id, 'Retrieved server list');

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Servers retrieved successfully',
        'data' => $servers,
        'errors' => []
    ]);
} catch (Exception $e) {
    $logger = app(\App\Infrastructure\Logging\Logger::class);
    $logger->error('Failed to retrieve servers', ['error' => $e->getMessage()]);

    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode([
        'success' => false,
        'message' => 'Failed to retrieve servers',
        'data' => null,
        'errors' => [$e->getMessage()]
    ]);
}
