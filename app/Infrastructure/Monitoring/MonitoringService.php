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
        // Update in database
        $this->connection->update('servers', [
            'status' => $status,
            'last_status_change_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$serverId]);
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
        ob_start();
        require RESOURCES_PATH . '/views/emails/server_down.php';
        return ob_get_clean();
    }
    
    /**
     * Build server recovered email body
     * 
     * @param array $server
     * @return string
     */
    protected function buildServerRecoveredEmailBody(array $server): string
    {
        ob_start();
        require RESOURCES_PATH . '/views/emails/server_recovered.php';
        return ob_get_clean();
    }
    
    /**
     * Get active servers (placeholder)
     * 
     * @return array
     */
    protected function getActiveServers(): array
    {
        // Fetch from database
        $repo = app(\App\Infrastructure\Repositories\ServerRepository::class);
        $servers = $repo->findActive();
        
        $result = [];
        foreach ($servers as $server) {
            $result[] = [
                'id' => $server->id,
                'name' => $server->name,
                'host' => $server->host,
                'port' => $server->port,
                'status' => $server->status
            ];
        }
        
        return $result;
    }
}
