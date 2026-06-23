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
    $startDate = $_GET['startDate'] ?? null;
    $endDate = $_GET['endDate'] ?? null;
    
    if ($startDate && $endDate) {
        $whereClause = "a.created_at BETWEEN :start AND :end";
        $params = ['start' => $startDate . ' 00:00:00', 'end' => $endDate . ' 23:59:59'];
    } else {
        $range = $_GET['range'] ?? '7d';
        $rangeFilter = match($range) {
            '24h' => '-1 day',
            '30d' => '-30 days',
            '90d' => '-90 days',
            default => '-7 days',
        };
        $whereClause = "a.created_at >= datetime('now', :range)";
        $params = ['range' => $rangeFilter];
    }
    
    $connection = app(\App\Infrastructure\Database\Connection::class);
    $sql = "
    SELECT 
        COALESCE(u.username, 'System') as user_name, 
        a.action, 
        a.details, 
        a.created_at 
    FROM audit_logs a
    LEFT JOIN users u ON a.user_id = u.id
    WHERE $whereClause
    ORDER BY a.created_at DESC 
    LIMIT 50
    ";
    
    $activity = $connection->fetchAll($sql, $params);

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
