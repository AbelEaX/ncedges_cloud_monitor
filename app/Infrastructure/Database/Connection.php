<?php

namespace App\Infrastructure\Database;

use PDO;
use PDOException;

/**
 * Database Connection Manager
 * 
 * Manages database connections using PDO.
 * Supports SQLite, MySQL, and PostgreSQL.
 */
class Connection
{
    /**
     * Database connection
     * 
     * @var PDO
     */
    protected PDO $pdo;
    
    /**
     * Database configuration
     * 
     * @var array
     */
    protected array $config;
    
    /**
     * Constructor
     * 
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->connect();
    }
    
    /**
     * Establish database connection
     * 
     * @return void
     * @throws PDOException
     */
    protected function connect(): void
    {
        $driver = $this->config['connections'][$this->config['default']] ?? [];
        
        $dsn = $this->buildDSN($driver);
        
        try {
            $this->pdo = new PDO(
                $dsn,
                $driver['username'] ?? null,
                $driver['password'] ?? null,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            throw new PDOException("Database connection failed: " . $e->getMessage());
        }
    }
    
    /**
     * Build DSN from driver configuration
     * 
     * @param array $driver
     * @return string
     */
    protected function buildDSN(array $driver): string
    {
        $dbDriver = $driver['driver'] ?? 'sqlite';
        
        return match($dbDriver) {
            'sqlite' => "sqlite:{$driver['database']}",
            'mysql' => "mysql:host={$driver['host']};port={$driver['port']};dbname={$driver['database']};charset={$driver['charset']}",
            'pgsql' => "pgsql:host={$driver['host']};port={$driver['port']};dbname={$driver['database']}",
            default => throw new PDOException("Unsupported database driver: {$dbDriver}"),
        };
    }
    
    /**
     * Get PDO instance
     * 
     * @return PDO
     */
    public function getPDO(): PDO
    {
        return $this->pdo;
    }
    
    /**
     * Execute a query
     * 
     * @param string $query
     * @param array $params
     * @return \PDOStatement
     */
    public function query(string $query, array $params = []): \PDOStatement
    {
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt;
    }
    
    /**
     * Get single row
     * 
     * @param string $query
     * @param array $params
     * @return array|null
     */
    public function fetchOne(string $query, array $params = []): ?array
    {
        return $this->query($query, $params)->fetch();
    }
    
    /**
     * Get all rows
     * 
     * @param string $query
     * @param array $params
     * @return array
     */
    public function fetchAll(string $query, array $params = []): array
    {
        return $this->query($query, $params)->fetchAll();
    }
    
    /**
     * Insert a record
     * 
     * @param string $table
     * @param array $data
     * @return string
     */
    public function insert(string $table, array $data): string
    {
        $columns = implode(',', array_keys($data));
        $placeholders = implode(',', array_fill(0, count($data), '?'));
        
        $query = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $this->query($query, array_values($data));
        
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Update records
     * 
     * @param string $table
     * @param array $data
     * @param string $where
     * @param array $params
     * @return int
     */
    public function update(string $table, array $data, string $where, array $params = []): int
    {
        $set = implode(',', array_map(fn($k) => "{$k}=?", array_keys($data)));
        $query = "UPDATE {$table} SET {$set} WHERE {$where}";
        
        return $this->query($query, array_merge(array_values($data), $params))->rowCount();
    }
    
    /**
     * Delete records
     * 
     * @param string $table
     * @param string $where
     * @param array $params
     * @return int
     */
    public function delete(string $table, string $where, array $params = []): int
    {
        $query = "DELETE FROM {$table} WHERE {$where}";
        return $this->query($query, $params)->rowCount();
    }
}
