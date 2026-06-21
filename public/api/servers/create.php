<?php

/**
 * Create Server API Endpoint
 *
 * POST /api/servers/create.php
 */

require dirname(__DIR__, 3) . '/bootstrap/app.php';

$auth = app(\App\Infrastructure\Authentication\AuthenticationService::class);
if (!$auth->isAuthenticated() || !$auth->hasPermission('server.create')) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || !isset($input['name']) || !isset($input['host'])) {
        throw new Exception('Name and host are required');
    }

    $serverRepo = app(\App\Infrastructure\Repositories\ServerRepository::class);
    $server = new \App\Domain\Entities\Server(
        id: null,
        name: $input['name'],
        host: $input['host'],
        port: isset($input['port']) ? (int) $input['port'] : 443,
        description: $input['description'] ?? null,
        status: 'unknown',
        group_name: $input['group_name'] ?? null,
        is_active: isset($input['is_active']) ? (bool) $input['is_active'] : true
    );

    $serverId = $serverRepo->create($server);

    // Log action
    $audit = app(\App\Infrastructure\Logging\AuditService::class);
    $audit->log('create', 'servers', $serverId, $auth->user()->id, ['message' => 'Created new server', 'server' => (array)$server], 'info');

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Server created successfully',
        'data' => ['id' => $serverId]
    ]);
} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode([
        'success' => false,
        'message' => 'Failed to create server',
        'errors' => [$e->getMessage()]
    ]);
}
