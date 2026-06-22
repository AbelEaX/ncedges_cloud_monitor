<?php

/**
 * Delete Server API Endpoint
 *
 * DELETE /api/servers/delete.php?id=1
 */

require dirname(__DIR__, 3) . '/bootstrap/app.php';

$auth = app(\App\Infrastructure\Authentication\AuthenticationService::class);
if (!$auth->isAuthenticated() || !$auth->hasPermission('server.delete')) {
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

    $serverRepo = app(\App\Infrastructure\Repositories\ServerRepository::class);
    $server = $serverRepo->findById($id);

    if (!$server) {
        header('HTTP/1.1 404 Not Found');
        echo json_encode(['success' => false, 'message' => 'Server not found']);
        exit;
    }

    $serverRepo->delete($id);

    // Log action
    $audit = app(\App\Infrastructure\Logging\AuditService::class);
    $audit->log('delete', 'servers', $id, $auth->user()->id, ['message' => 'Deleted server', 'name' => $server->name], 'info');

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Server deleted successfully'
    ]);
} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode([
        'success' => false,
        'message' => 'Failed to delete server',
        'errors' => [$e->getMessage()]
    ]);
}
