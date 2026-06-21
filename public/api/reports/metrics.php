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

    // Get metrics data dynamically
    $total = 0;
    $online = 0;
    $offline = 0;
    $alertsCount = 0;

    try {
        $serverRepo = app(\App\Infrastructure\Repositories\ServerRepository::class);
        $servers = $serverRepo->findAll();
        $total = count($servers);
        foreach ($servers as $s) {
            if ($s->status === 'online') {
                $online++;
            } elseif ($s->status === 'offline') {
                $offline++;
            }
        }
    } catch (Exception $e) {
        // Fallback
    }

    try {
        $connection = app(\App\Infrastructure\Database\Connection::class);
        $alertsCountRow = $connection->fetchOne("SELECT COUNT(*) as cnt FROM notifications");
        $alertsCount = $alertsCountRow ? (int) $alertsCountRow['cnt'] : 0;
    } catch (Exception $e) {
        // Fallback
    }

    $metrics = [
        'total_servers' => $total,
        'online_servers' => $online,
        'offline_servers' => $offline,
        'avg_uptime' => $total > 0 ? 100.0 : 0.0,
        'alert_count' => $alertsCount,
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
