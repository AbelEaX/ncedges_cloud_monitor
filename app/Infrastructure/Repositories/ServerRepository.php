<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\Server;
use App\Domain\Repositories\ServerRepository as ServerRepositoryInterface;
use App\Infrastructure\Database\Connection;

/**
 * Server Repository Implementation
 * 
 * Implements ServerRepository interface using PDO.
 */
class ServerRepository implements ServerRepositoryInterface
{
    /**
     * Database connection
     * 
     * @var Connection
     */
    protected Connection $connection;
    
    /**
     * Constructor
     * 
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }
    
    /**
     * Find server by ID
     * 
     * @param int $id
     * @return Server|null
     */
    public function findById(int $id): ?Server
    {
        $row = $this->connection->fetchOne(
            'SELECT * FROM servers WHERE id = ?',
            [$id]
        );
        
        return $row ? $this->hydrate($row) : null;
    }
    
    /**
     * Find all active servers
     * 
     * @return array
     */
    public function findActive(): array
    {
        $rows = $this->connection->fetchAll(
            'SELECT * FROM servers WHERE is_active = 1 ORDER BY name'
        );
        return array_map(fn($row) => $this->hydrate($row), $rows);
    }
    
    /**
     * Find all servers
     * 
     * @return array
     */
    public function findAll(): array
    {
        $rows = $this->connection->fetchAll(
            'SELECT * FROM servers ORDER BY name'
        );
        return array_map(fn($row) => $this->hydrate($row), $rows);
    }
    
    /**
     * Create a new server
     * 
     * @param Server $server
     * @return int
     */
    public function create(Server $server): int
    {
        return $this->connection->insert('servers', [
            'name' => $server->name,
            'host' => $server->host,
            'port' => $server->port,
            'description' => $server->description,
            'status' => $server->status,
            'group_name' => $server->group_name,
            'is_active' => $server->is_active ? 1 : 0,
        ]);
    }
    
    /**
     * Update server
     * 
     * @param Server $server
     * @return bool
     */
    public function update(Server $server): bool
    {
        $count = $this->connection->update(
            'servers',
            [
                'name' => $server->name,
                'host' => $server->host,
                'port' => $server->port,
                'description' => $server->description,
                'status' => $server->status,
                'group_name' => $server->group_name,
                'is_active' => $server->is_active ? 1 : 0,
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            'id = ?',
            [$server->id]
        );
        
        return $count > 0;
    }
    
    /**
     * Delete server
     * 
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $count = $this->connection->delete('servers', 'id = ?', [$id]);
        return $count > 0;
    }
    
    /**
     * Hydrate Server entity from database row
     * 
     * @param array $row
     * @return Server
     */
    protected function hydrate(array $row): Server
    {
        return new Server(
            id: (int) $row['id'],
            name: $row['name'],
            host: $row['host'],
            port: (int) $row['port'],
            description: $row['description'],
            status: $row['status'],
            group_name: $row['group_name'],
            is_active: (bool) $row['is_active'],
            last_check_at: $row['last_check_at'] ? new \DateTime($row['last_check_at']) : null,
            last_status_change_at: $row['last_status_change_at'] ? new \DateTime($row['last_status_change_at']) : null,
            alert_sent: (bool) ($row['alert_sent'] ?? false),
            created_at: $row['created_at'] ? new \DateTime($row['created_at']) : null,
            updated_at: $row['updated_at'] ? new \DateTime($row['updated_at']) : null,
        );
    }
}
