<?php

/**
 * Background Check Script
 * 
 * Runs an immediate health check on a single server, typically after creation.
 * Usage: php background_check.php <server_id>
 */

require __DIR__ . '/bootstrap/app.php';

if (php_sapi_name() !== 'cli') {
    exit("This script must be run from the command line.\n");
}

$serverId = (int) ($argv[1] ?? 0);
if (!$serverId) {
    exit("Server ID is required.\n");
}

global $container;
$connection = $container->resolve(\App\Infrastructure\Database\Connection::class);
$monitoringService = $container->resolve(\App\Infrastructure\Monitoring\MonitoringService::class);
$logger = $container->resolve(\App\Infrastructure\Logging\Logger::class);

try {
    // Update status to in_progress
    $connection->update('servers', ['status' => 'in_progress'], 'id = ?', [$serverId]);

    // Fetch server
    $server = $connection->fetchOne("SELECT * FROM servers WHERE id = ?", [$serverId]);
    if (!$server) {
        exit("Server not found.\n");
    }

    // Perform check
    $isHealthy = $monitoringService->checkServerHealth($server['host'], $server['port']);
    $newStatus = $isHealthy ? 'online' : 'offline';

    // Update status
    $connection->update('servers', [
        'status' => $newStatus,
        'last_check_at' => date('Y-m-d H:i:s'),
        'last_status_change_at' => date('Y-m-d H:i:s')
    ], 'id = ?', [$serverId]);

    // Insert metric
    $connection->insert('server_metrics', [
        'server_id' => $serverId,
        'response_time' => 0,
        'status' => $newStatus,
        'checked_at' => date('Y-m-d H:i:s')
    ]);

    $logger->info("Background check completed for server ID {$serverId}: {$newStatus}");

} catch (Exception $e) {
    $logger->error("Background check failed for server ID {$serverId}", ['error' => $e->getMessage()]);
    // Try to set to offline if it failed
    try {
        $connection->update('servers', ['status' => 'offline'], 'id = ?', [$serverId]);
    } catch (Exception $e2) {
        // Ignore
    }
}
