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

    if (!$input || !isset($input['name']) || !isset($input['hostname'])) {
        throw new Exception('Name and hostname are required');
    }

    $serverRepo = app(\App\Infrastructure\Repositories\ServerRepository::class);
    $server = new \App\Domain\Entities\Server(
        null,
        $input['name'],
        $input['hostname'],
        $input['ip_address'] ?? null,
        $input['description'] ?? null,
        $input['is_active'] ?? 1,
        new DateTime(),
        new DateTime()
    );

    $serverId = $serverRepo->create($server);

    // Log action
    $audit = app(\App\Infrastructure\Logging\AuditService::class);
    $audit->log('create', 'servers', $serverId, $auth->user()->id, 'Created new server', (array)$server);

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
