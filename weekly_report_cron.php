<?php
/**
 * Weekly Report Cron Script
 * 
 * Usage: php weekly_report_cron.php
 * This script aggregates the monitoring metrics for the past 7 days
 * and emails a comprehensive health report to the primary recipient.
 */

require __DIR__ . '/bootstrap/app.php';

$logger = app(\App\Infrastructure\Logging\Logger::class);
$connection = app(\App\Infrastructure\Database\Connection::class);
$serverRepo = app(\App\Infrastructure\Repositories\ServerRepository::class);
$mailService = app(\App\Infrastructure\Mail\MailService::class);

echo "Starting Weekly Report Generation...\n";

try {
    // 1. Gather Metrics for the last 7 days
    $servers = $serverRepo->findAll();
    $totalServers = count($servers);
    
    $offlineServers = [];
    foreach ($servers as $s) {
        if ($s->status === 'offline') {
            $offlineServers[] = [
                'name' => $s->name,
                'host' => $s->host,
                'port' => $s->port
            ];
        }
    }
    
    // Uptime Calculation
    $uptimeSql = "SELECT (SUM(CASE WHEN status = 'online' THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(*), 0)) as avg_uptime 
                  FROM server_metrics 
                  WHERE checked_at >= datetime('now', '-7 days')";
    $uptimeRow = $connection->fetchOne($uptimeSql);
    $avgUptime = ($uptimeRow && $uptimeRow['avg_uptime'] !== null) ? round((float)$uptimeRow['avg_uptime'], 2) : 100.0;
    
    // Alerts Count
    $alertsSql = "SELECT COUNT(*) as cnt 
                  FROM notifications 
                  WHERE created_at >= datetime('now', '-7 days') 
                  AND type = 'alert'";
    $alertsRow = $connection->fetchOne($alertsSql);
    $alertCount = $alertsRow ? (int)$alertsRow['cnt'] : 0;
    
    $metrics = [
        'total_servers' => $totalServers,
        'avg_uptime' => $avgUptime,
        'alert_count' => $alertCount
    ];
    
    // 2. Render Template
    ob_start();
    require RESOURCES_PATH . '/views/emails/weekly_report.php';
    $htmlBody = ob_get_clean();
    
    // 3. Send Email
    // Fetch recipient from settings (fallback to default)
    $settingsSql = "SELECT value FROM settings WHERE key = 'alerts.primary_recipient'";
    $recipientRow = $connection->fetchOne($settingsSql);
    
    $config = require CONFIG_PATH . '/app.php';
    $toEmail = ($recipientRow && !empty($recipientRow['value'])) 
        ? $recipientRow['value'] 
        : ($config['alerts']['primary_recipient'] ?? 'admin@monitor.local');
        
    $subject = "Weekly Health Report - " . date('M j, Y');
    
    if ($mailService->send($toEmail, $subject, $htmlBody)) {
        echo "Weekly report sent successfully to {$toEmail}.\n";
        $logger->info("Weekly report sent to {$toEmail}");
    } else {
        echo "Failed to send weekly report.\n";
        $logger->error("Failed to send weekly report to {$toEmail}");
    }
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    $logger->error("Error in weekly report script", ['error' => $e->getMessage()]);
    exit(1);
}
