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
    $startDate = $_GET['startDate'] ?? null;
    $endDate = $_GET['endDate'] ?? null;
    
    if ($startDate && $endDate) {
        $whereClause = "created_at BETWEEN :start AND :end";
        $params = ['start' => $startDate . ' 00:00:00', 'end' => $endDate . ' 23:59:59'];
    } else {
        $range = $_GET['range'] ?? '7d';
        $rangeFilter = match($range) {
            '24h' => '-1 day',
            '30d' => '-30 days',
            '90d' => '-90 days',
            default => '-7 days',
        };
        $whereClause = "created_at >= datetime('now', :range)";
        $params = ['range' => $rangeFilter];
    }
    
    $connection = app(\App\Infrastructure\Database\Connection::class);
    $sql = "SELECT subject, message, type as channel, status, created_at FROM notifications WHERE $whereClause ORDER BY created_at DESC LIMIT 50";
    $notifications = $connection->fetchAll($sql, $params);

    $alerts = [];
    foreach ($notifications as $n) {
        $isAlert = strpos($n['subject'], '[ALERT]') !== false;
        $serverName = trim(str_replace(['[ALERT]', '[RECOVERY]'], '', $n['subject']));
        
        $alerts[] = [
            'server_name' => $serverName ?: 'System',
            'alert_type' => $isAlert ? 'Downtime Alert' : 'Recovery',
            'message' => $n['message'],
            'severity' => $isAlert ? 'critical' : 'info',
            'created_at' => $n['created_at']
        ];
    }

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
