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
    
    $rangeFilter = match($range) {
        '24h' => '-1 day',
        '30d' => '-30 days',
        '90d' => '-90 days',
        default => '-7 days',
    };

    // Get metrics data dynamically
    $total = 0;
    $online = 0;
    $offline = 0;
    $alertsCount = 0;
    $avgUptime = 100.0;

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
        
        $uptimeSql = "SELECT (SUM(CASE WHEN status = 'online' THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(*), 0)) as avg_uptime FROM server_metrics WHERE checked_at >= datetime('now', :range)";
        $uptimeRow = $connection->fetchOne($uptimeSql, ['range' => $rangeFilter]);
        if ($uptimeRow && $uptimeRow['avg_uptime'] !== null) {
            $avgUptime = round((float)$uptimeRow['avg_uptime'], 2);
        }
    } catch (Exception $e) {
        // Fallback
    }

    $metrics = [
        'total_servers' => $total,
        'online_servers' => $online,
        'offline_servers' => $offline,
        'avg_uptime' => $total > 0 ? $avgUptime : 0.0,
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
