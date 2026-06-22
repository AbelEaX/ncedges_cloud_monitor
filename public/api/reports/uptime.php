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
    $range = $_GET['range'] ?? '7d';
    $connection = app(\App\Infrastructure\Database\Connection::class);
    
    // SQLite query for 24h, 7d, 30d
    $sql = "
    SELECT 
        s.name as server_name,
        s.status as current_status,
        (SUM(CASE WHEN sm.status = 'online' AND sm.checked_at >= datetime('now', '-1 day') THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(CASE WHEN sm.checked_at >= datetime('now', '-1 day') THEN 1 END), 0)) as uptime_24h,
        (SUM(CASE WHEN sm.status = 'online' AND sm.checked_at >= datetime('now', '-7 days') THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(CASE WHEN sm.checked_at >= datetime('now', '-7 days') THEN 1 END), 0)) as uptime_7d,
        (SUM(CASE WHEN sm.status = 'online' AND sm.checked_at >= datetime('now', '-30 days') THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(CASE WHEN sm.checked_at >= datetime('now', '-30 days') THEN 1 END), 0)) as uptime_30d
    FROM servers s
    LEFT JOIN server_metrics sm ON s.id = sm.server_id
    GROUP BY s.id, s.name, s.status
    ORDER BY s.name ASC
    ";
    
    $uptime = $connection->fetchAll($sql);
    
    // Format results and handle nulls for servers with no metrics yet
    foreach ($uptime as &$row) {
        $row['uptime_24h'] = $row['uptime_24h'] !== null ? round((float)$row['uptime_24h'], 2) : 100.0;
        $row['uptime_7d'] = $row['uptime_7d'] !== null ? round((float)$row['uptime_7d'], 2) : 100.0;
        $row['uptime_30d'] = $row['uptime_30d'] !== null ? round((float)$row['uptime_30d'], 2) : 100.0;
    }

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
