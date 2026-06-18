<?php

/**
 * Reports Activity API Endpoint
 *
 * GET /api/reports/activity.php?range=7d
 */

require dirname(__DIR__, 3) . '/bootstrap/app.php';

$auth = app(\App\Infrastructure\Authentication\AuthenticationService::class);
if (!$auth->isAuthenticated() || !$auth->hasPermission('reports.view')) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $activity = [
        ['user_name' => 'Admin', 'action' => 'create', 'details' => 'Created new server: Web Server 3', 'created_at' => date('Y-m-d H:i:s', time() - 3600)],
        ['user_name' => 'Manager', 'action' => 'update', 'details' => 'Updated Mail Server settings', 'created_at' => date('Y-m-d H:i:s', time() - 7200)],
        ['user_name' => 'Admin', 'action' => 'delete', 'details' => 'Deleted server: Old Server', 'created_at' => date('Y-m-d H:i:s', time() - 10800)],
    ];

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Activity retrieved successfully',
        'data' => $activity
    ]);
} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['success' => false, 'message' => 'Failed to retrieve activity']);
}
