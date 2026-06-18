<?php

/**
 * Reports Alerts API Endpoint
 *
 * GET /api/reports/alerts.php?range=7d
 */

require dirname(__DIR__, 3) . '/bootstrap/app.php';

$auth = app(\App\Infrastructure\Authentication\AuthenticationService::class);
if (!$auth->isAuthenticated() || !$auth->hasPermission('reports.view')) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $alerts = [
        ['server_name' => 'Web Server 1', 'alert_type' => 'High CPU', 'message' => 'CPU usage above 80%', 'severity' => 'warning', 'created_at' => date('Y-m-d H:i:s', time() - 3600)],
        ['server_name' => 'Mail Server', 'alert_type' => 'Disk Space', 'message' => 'Disk usage at 85%', 'severity' => 'critical', 'created_at' => date('Y-m-d H:i:s', time() - 7200)],
    ];

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Alerts retrieved successfully',
        'data' => $alerts
    ]);
} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['success' => false, 'message' => 'Failed to retrieve alerts']);
}
