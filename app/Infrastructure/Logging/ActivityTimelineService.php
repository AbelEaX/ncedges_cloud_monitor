<?php

namespace App\Infrastructure\Logging;

use App\Infrastructure\Database\Connection;

/**
 * Activity Timeline Service
 * 
 * Manages activity logging and timeline display.
 * Tracks all user actions and system events.
 */
class ActivityTimelineService
{
    /**
     * Database connection
     * 
     * @var Connection
     */
    protected Connection $connection;
    
    /**
     * Logger service
     * 
     * @var Logger
     */
    protected Logger $logger;
    
    /**
     * Constructor
     * 
     * @param Connection $connection
     * @param Logger $logger
     */
    public function __construct(Connection $connection, Logger $logger)
    {
        $this->connection = $connection;
        $this->logger = $logger;
    }
    
    /**
     * Log an activity
     * 
     * @param string $action
     * @param string $entity_type
     * @param int|null $entity_id
     * @param int|null $user_id
     * @param string|null $description
     * @param array $details
     * @return void
     */
    public function log(
        string $action,
        string $entity_type,
        ?int $entity_id = null,
        ?int $user_id = null,
        ?string $description = null,
        array $details = []
    ): void {
        // Get user ID from session if not provided
        if ($user_id === null && isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        // Insert into activities table
        $this->connection->insert('activities', [
            'user_id' => $user_id,
            'action' => $action,
            'entity_type' => $entity_type,
            'entity_id' => $entity_id,
            'description' => $description,
            'details' => json_encode($details),
            'ip_address' => $ip_address,
            'user_agent' => $user_agent,
            'created_at' => $timestamp,
        ]);
        
        $this->logger->info(
            "Activity: {$action} on {$entity_type}",
            array_merge(['description' => $description], $details),
            'application'
        );
    }
    
    /**
     * Get recent activities
     * 
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getRecent(int $limit = 50, int $offset = 0): array
    {
        // Query activities table
        $pdo = $this->connection->getPDO();
        $stmt = $pdo->prepare('SELECT * FROM activities ORDER BY created_at DESC LIMIT ? OFFSET ?');
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get activities for a specific entity
     * 
     * @param string $entity_type
     * @param int $entity_id
     * @return array
     */
    public function getForEntity(string $entity_type, int $entity_id): array
    {
        // Query activities table for specific entity
        $pdo = $this->connection->getPDO();
        $stmt = $pdo->prepare('SELECT * FROM activities WHERE entity_type = ? AND entity_id = ? ORDER BY created_at DESC');
        $stmt->execute([$entity_type, $entity_id]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
