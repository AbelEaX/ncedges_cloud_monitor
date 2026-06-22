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
    $range = $_GET['range'] ?? '7d';
    $rangeFilter = match($range) {
        '24h' => '-1 day',
        '30d' => '-30 days',
        '90d' => '-90 days',
        default => '-7 days',
    };
    
    $connection = app(\App\Infrastructure\Database\Connection::class);
    $sql = "
    SELECT 
        COALESCE(u.username, 'System') as user_name, 
        a.action, 
        a.details, 
        a.created_at 
    FROM audit_logs a
    LEFT JOIN users u ON a.user_id = u.id
    WHERE a.created_at >= datetime('now', :range)
    ORDER BY a.created_at DESC 
    LIMIT 50
    ";
    
    $activity = $connection->fetchAll($sql, ['range' => $rangeFilter]);

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
