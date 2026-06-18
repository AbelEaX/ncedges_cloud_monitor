<?php

namespace App\Infrastructure\Monitoring;

use App\Infrastructure\Database\Connection;
use App\Infrastructure\Notifications\NotificationManager;
use App\Infrastructure\Logging\Logger;

/**
 * Monitoring Service
 * 
 * Handles server health checks, status monitoring, and alert generation.
 * Features:
 * - Server connectivity checks
 * - Status tracking
 * - Alert generation
 * - Metrics collection
 * - Notification dispatch
 */
class MonitoringService
{
    /**
     * Database connection
     * 
     * @var Connection
     */
    protected Connection $connection;
    
    /**
     * Notification manager
     * 
     * @var NotificationManager
     */
    protected NotificationManager $notifications;
    
    /**
     * Logger service
     * 
     * @var Logger
     */
    protected Logger $logger;
    
    /**
     * Monitoring configuration
     * 
     * @var array
     */
    protected array $config;
    
    /**
     * Constructor
     * 
     * @param Connection $connection
     * @param NotificationManager $notifications
     * @param Logger $logger
     * @param array $config
     */
    public function __construct(
        Connection $connection,
        NotificationManager $notifications,
        Logger $logger,
        array $config
    ) {
        $this->connection = $connection;
        $this->notifications = $notifications;
        $this->logger = $logger;
        $this->config = $config;
    }
    
    /**
     * Check server health
     * 
     * @param string $host
     * @param int $port
     * @return bool
     */
    public function checkServerHealth(string $host, int $port): bool
    {
        $timeout = $this->config['health_check']['timeout'];
        
        try {
            $socket = @fsockopen($host, $port, $errno, $errstr, $timeout);
            
            if ($socket) {
                fclose($socket);
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            $this->logger->warning(
                "Health check error for {$host}:{$port}",
                ['error' => $e->getMessage()],
                'monitoring'
            );
            return false;
        }
    }
    
    /**
     * Monitor all servers
     * 
     * @return array
     */
    public function monitorAllServers(): array
    {
        $servers = $this->getActiveServers();
        $results = [];
        
        foreach ($servers as $server) {
            $isHealthy = $this->checkServerHealth($server['host'], $server['port']);
            $previousStatus = $server['status'] ?? 'unknown';
            $currentStatus = $isHealthy ? 'online' : 'offline';
            
            $results[] = [
                'server_id' => $server['id'],
                'name' => $server['name'],
                'host' => $server['host'],
                'port' => $server['port'],
                'previous_status' => $previousStatus,
                'current_status' => $currentStatus,
                'changed' => $previousStatus !== $currentStatus,
                'checked_at' => date('Y-m-d H:i:s'),
            ];
            
            // Handle status changes
            if ($previousStatus !== $currentStatus) {
                $this->handleStatusChange($server, $previousStatus, $currentStatus);
            }
        }
        
        return $results;
    }
    
    /**
     * Handle server status change
     * 
     * @param array $server
     * @param string $previousStatus
     * @param string $currentStatus
     * @return void
     */
    protected function handleStatusChange(array $server, string $previousStatus, string $currentStatus): void
    {
        $this->logger->info(
            "Server status changed: {$server['name']} - {$previousStatus} -> {$currentStatus}",
            ['server_id' => $server['id']],
            'monitoring'
        );
        
        // Update in database
        $this->updateServerStatus($server['id'], $currentStatus);
        
        // Send notification
        if ($currentStatus === 'offline') {
            $this->sendServerDownNotification($server);
        } elseif ($currentStatus === 'online' && $previousStatus === 'offline') {
            $this->sendServerRecoveredNotification($server);
        }
    }
    
    /**
     * Update server status in database
     * 
     * @param int $serverId
     * @param string $status
     * @return void
     */
    protected function updateServerStatus(int $serverId, string $status): void
    {
        // TODO: Update in database when DB structure is ready
        // $this->connection->update('servers', ['status' => $status], 'id = ?', [$serverId]);
    }
    
    /**
     * Send server down notification
     * 
     * @param array $server
     * @return void
     */
    protected function sendServerDownNotification(array $server): void
    {
        $recipient = config('smtp.alerts.primary_recipient');
        
        $subject = "ALERT: Server Down - {$server['name']}";
        $body = $this->buildServerDownEmailBody($server);
        
        $this->notifications->sendEmail($recipient, $subject, $body);
    }
    
    /**
     * Send server recovered notification
     * 
     * @param array $server
     * @return void
     */
    protected function sendServerRecoveredNotification(array $server): void
    {
        $recipient = config('smtp.alerts.primary_recipient');
        
        $subject = "RESOLVED: Server Recovered - {$server['name']}";
        $body = $this->buildServerRecoveredEmailBody($server);
        
        $this->notifications->sendEmail($recipient, $subject, $body);
    }
    
    /**
     * Build server down email body
     * 
     * @param array $server
     * @return string
     */
    protected function buildServerDownEmailBody(array $server): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .alert { background: #ef5350; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; border: 1px solid #ddd; }
        .detail { margin: 10px 0; padding: 10px; background: #f5f5f5; }
    </style>
</head>
<body>
    <div class="container">
        <div class="alert">
            <h1>⚠️ Server Offline</h1>
        </div>
        <div class="content">
            <p><strong>Server:</strong> {$server['name']}</p>
            <p><strong>Host:</strong> {$server['host']}</p>
            <p><strong>Port:</strong> {$server['port']}</p>
            <p><strong>Timestamp:</strong> {$_SERVER['REQUEST_TIME']}</p>
            <p>The server is currently unreachable. Please investigate immediately.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
    
    /**
     * Build server recovered email body
     * 
     * @param array $server
     * @return string
     */
    protected function buildServerRecoveredEmailBody(array $server): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .success { background: #66bb6a; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <div class="container">
        <div class="success">
            <h1>✓ Server Recovered</h1>
        </div>
        <div class="content">
            <p><strong>Server:</strong> {$server['name']}</p>
            <p><strong>Host:</strong> {$server['host']}</p>
            <p><strong>Port:</strong> {$server['port']}</p>
            <p><strong>Timestamp:</strong> {$_SERVER['REQUEST_TIME']}</p>
            <p>The server is now online and responding normally.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
    
    /**
     * Get active servers (placeholder)
     * 
     * @return array
     */
    protected function getActiveServers(): array
    {
        // TODO: Fetch from database when structure is ready
        return [];
    }
}
