<?php
$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET['startDate'] = '2026-06-01';
$_GET['endDate'] = '2026-06-30';

// Mock AuthenticationService to always return true
require 'bootstrap/app.php';

// We can't easily mock within the container because it's already bound,
// but let's just create a new app instance or mock the session.
$_SESSION['user_id'] = 1;

// Let's test the database queries instead of the full API since Auth is required.
$connection = app(\App\Infrastructure\Database\Connection::class);

echo "Testing metrics.php query...\n";
$startDate = $_GET['startDate'];
$endDate = $_GET['endDate'];
$whereClause = "checked_at BETWEEN :start AND :end";
$params = ['start' => $startDate . ' 00:00:00', 'end' => $endDate . ' 23:59:59'];
$uptimeSql = "SELECT (SUM(CASE WHEN status = 'online' THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(*), 0)) as avg_uptime FROM server_metrics WHERE $whereClause";
$uptimeRow = $connection->fetchOne($uptimeSql, $params);
print_r($uptimeRow);

echo "\nTesting alerts.php query...\n";
$whereClause = "created_at BETWEEN :start AND :end";
$sql = "SELECT subject, message, type as channel, status, created_at FROM notifications WHERE $whereClause ORDER BY created_at DESC LIMIT 50";
$notifications = $connection->fetchAll($sql, $params);
echo "Alerts count: " . count($notifications) . "\n";

echo "\nTesting activity.php query...\n";
$whereClause = "a.created_at BETWEEN :start AND :end";
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
echo "Activity count: " . count($activity) . "\n";
