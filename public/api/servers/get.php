<?php

/**
 * Get Single Server API Endpoint
 *
 * GET /api/servers/get.php?id=1
 */

require dirname(__DIR__, 3) . '/bootstrap/app.php';

$auth = app(\App\Infrastructure\Authentication\AuthenticationService::class);
if (!$auth->isAuthenticated() || !$auth->hasPermission('server.view')) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
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

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Server retrieved successfully',
        'data' => $server
    ]);
} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode([
        'success' => false,
        'message' => 'Failed to retrieve server',
        'errors' => [$e->getMessage()]
    ]);
}
