<?php

/**
 * Update Server API Endpoint
 *
 * PUT /api/servers/update.php?id=1
 */

require dirname(__DIR__, 3) . '/bootstrap/app.php';

$auth = app(\App\Infrastructure\Authentication\AuthenticationService::class);
if (!$auth->isAuthenticated() || !$auth->hasPermission('server.edit')) {
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
    $id = $_GET['id'] ?? null;
    if (!$id) {
        throw new Exception('Server ID required');
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $serverRepo = app(\App\Infrastructure\Repositories\ServerRepository::class);

    $server = $serverRepo->findById($id);
    if (!$server) {
        header('HTTP/1.1 404 Not Found');
        echo json_encode(['success' => false, 'message' => 'Server not found']);
        exit;
    }

    // Update fields
    if (isset($input['name'])) {
        $server->name = htmlspecialchars(trim($input['name']), ENT_QUOTES, 'UTF-8');
    }
    if (isset($input['host'])) {
        $server->host = htmlspecialchars(trim($input['host']), ENT_QUOTES, 'UTF-8');
    }
    if (isset($input['port'])) {
        $server->port = (int) $input['port'];
    }
    if (isset($input['description'])) {
        $server->description = htmlspecialchars(trim($input['description']), ENT_QUOTES, 'UTF-8');
    }
    if (isset($input['is_active'])) {
        $server->is_active = (bool) $input['is_active'];
    }
    if (isset($input['group_name'])) {
        $server->group_name = htmlspecialchars(trim($input['group_name']), ENT_QUOTES, 'UTF-8');
    }

    $serverRepo->update($server);

    // Log action
    $audit = app(\App\Infrastructure\Logging\AuditService::class);
    $audit->log('update', 'servers', $id, $auth->user()->id, ['message' => 'Updated server', 'server' => (array)$server], 'info');

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Server updated successfully',
        'data' => $server
    ]);
} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update server',
        'errors' => [$e->getMessage()]
    ]);
}
