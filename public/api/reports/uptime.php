<?php

/**
 * Reports Uptime API Endpoint
 *
 * GET /api/reports/uptime.php?range=7d
 */

require dirname(__DIR__, 3) . '/bootstrap/app.php';

$auth = app(\App\Infrastructure\Authentication\AuthenticationService::class);
if (!$auth->isAuthenticated() || !$auth->hasPermission('reports.view')) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    // Sample uptime data
    $uptime = [
        ['server_name' => 'Web Server 1', 'uptime_24h' => 99.9, 'uptime_7d' => 99.95, 'uptime_30d' => 99.87],
        ['server_name' => 'Web Server 2', 'uptime_24h' => 100, 'uptime_7d' => 99.98, 'uptime_30d' => 99.92],
        ['server_name' => 'Database Server', 'uptime_24h' => 99.95, 'uptime_7d' => 99.99, 'uptime_30d' => 99.98],
        ['server_name' => 'Mail Server', 'uptime_24h' => 100, 'uptime_7d' => 99.99, 'uptime_30d' => 99.95],
    ];

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Uptime data retrieved successfully',
        'data' => $uptime
    ]);
} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['success' => false, 'message' => 'Failed to retrieve uptime data']);
}
