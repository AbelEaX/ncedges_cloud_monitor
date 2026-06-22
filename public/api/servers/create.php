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

$csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!$auth->validateCsrfToken($csrfToken)) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || !isset($input['name']) || !isset($input['host'])) {
        throw new Exception('Name and host are required');
    }

    $name = htmlspecialchars(trim($input['name']), ENT_QUOTES, 'UTF-8');
    $host = htmlspecialchars(trim($input['host']), ENT_QUOTES, 'UTF-8');
    $description = isset($input['description']) ? htmlspecialchars(trim($input['description']), ENT_QUOTES, 'UTF-8') : null;
    $group_name = isset($input['group_name']) ? htmlspecialchars(trim($input['group_name']), ENT_QUOTES, 'UTF-8') : null;

    $serverRepo = app(\App\Infrastructure\Repositories\ServerRepository::class);
    $server = new \App\Domain\Entities\Server(
        id: null,
        name: $name,
        host: $host,
        port: isset($input['port']) ? (int) $input['port'] : 443,
        description: $description,
        status: 'unknown',
        group_name: $group_name,
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
