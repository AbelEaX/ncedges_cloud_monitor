<?php

/**
 * Reports Metrics API Endpoint
 *
 * GET /api/reports/metrics.php?range=7d
 */

require dirname(__DIR__, 3) . '/bootstrap/app.php';

$auth = app(\App\Infrastructure\Authentication\AuthenticationService::class);
if (!$auth->isAuthenticated() || !$auth->hasPermission('reports.view')) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $range = $_GET['range'] ?? '7d';

    // Get metrics data
    $metrics = [
        'total_servers' => 4,
        'online_servers' => 4,
        'offline_servers' => 0,
        'avg_uptime' => 99.95,
        'alert_count' => 2,
    ];

    // Log action
    $audit = app(\App\Infrastructure\Logging\AuditService::class);
    $audit->log('read', 'reports_metrics', null, $auth->user()->id, ['message' => "Retrieved metrics for period: $range"]);

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Metrics retrieved successfully',
        'data' => $metrics
    ]);
} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode([
        'success' => false,
        'message' => 'Failed to retrieve metrics',
        'errors' => [$e->getMessage()]
    ]);
}
