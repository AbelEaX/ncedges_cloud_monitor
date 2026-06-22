<?php

/**
 * Monitoring Cron Job
 * 
 * This script runs the server health checks.
 * It should be executed periodically (e.g., every minute) via a cron job or scheduled task.
 * 
 * Usage (Linux): * * * * * php /path/to/monitor.ncedges.com/cron.php
 */

require __DIR__ . '/bootstrap/app.php';

// Define that we are running in CLI context to prevent redirecting to login
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    echo "This script must be run from the command line.\n";
    exit(1);
}

// Get the container (global from bootstrap)
global $container;

$logger = $container->resolve(\App\Infrastructure\Logging\Logger::class);
$monitoringService = $container->resolve(\App\Infrastructure\Monitoring\MonitoringService::class);

if (!config('monitoring.health_check.enabled', true)) {
    echo "Health checks are currently disabled in settings.\n";
    $logger->info("Cron: Skipped monitoring, health checks disabled in settings.", [], 'cron');
    exit(0);
}

$startTime = microtime(true);
$logger->info("Cron: Started monitoring all servers.", [], 'cron');

echo "Starting server health checks...\n";

try {
    $results = $monitoringService->monitorAllServers();
    $duration = round(microtime(true) - $startTime, 3);
    
    $onlineCount = count(array_filter($results, fn($r) => $r['current_status'] === 'online'));
    $offlineCount = count(array_filter($results, fn($r) => $r['current_status'] === 'offline'));
    $changedCount = count(array_filter($results, fn($r) => $r['changed'] === true));
    
    echo "Completed in {$duration}s.\n";
    echo "Checked: " . count($results) . " servers.\n";
    echo "Online: {$onlineCount} | Offline: {$offlineCount} | Changed: {$changedCount}\n";
    
    $logger->info("Cron: Completed monitoring.", [
        'duration_seconds' => $duration,
        'checked_count' => count($results),
        'online_count' => $onlineCount,
        'offline_count' => $offlineCount,
        'changed_count' => $changedCount
    ], 'cron');
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    $logger->error("Cron: Error during monitoring.", [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], 'cron');
    exit(1);
}

exit(0);
