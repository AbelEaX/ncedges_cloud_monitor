<?php

namespace App\Infrastructure\Database;

use PDO;
use PDOException;

/**
 * Database Connection Manager
 * 
 * Manages database connections using PDO or FileDatabase fallback.
 * Supports SQLite, MySQL, PostgreSQL, and JSON file-based storage.
 */
class Connection
{
    /**
     * Database connection (PDO or FileDatabase)
     * 
     * @var PDO|FileDatabase
     */
    protected $pdo;
    
    /**
     * Database configuration
     * 
     * @var array
     */
    protected array $config;
    
    /**
     * Whether using FileDatabase fallback
     * 
     * @var bool
     */
    protected bool $usingFileDatabase = false;
    
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
        
        // Check if PDO drivers are available
        if (empty(PDO::getAvailableDrivers())) {
            // Fall back to FileDatabase if no PDO drivers are available
            $this->useFileDatabase($driver);
            return;
        }
        
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
     * Use FileDatabase as fallback when PDO drivers are not available
     * 
     * @param array $driver
     * @return void
     */
    protected function useFileDatabase(array $driver): void
    {
        $databasePath = dirname(__DIR__, 3) . '/storage/database';
        $this->pdo = new FileDatabase($databasePath);
        $this->usingFileDatabase = true;
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
     * Get PDO instance or FileDatabase
     * 
     * @return PDO|FileDatabase
     */
    public function getPDO()
    {
        return $this->pdo;
    }
    
    /**
     * Execute a query
     * 
     * @param string $query
     * @param array $params
     * @return \PDOStatement|FileDatabaseStatement
     */
    public function query(string $query, array $params = [])
    {
        if ($this->usingFileDatabase) {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt;
        }
        
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
        if ($this->usingFileDatabase) {
            // For FileDatabase, we need to handle SELECT queries differently
            if (preg_match('/SELECT\s+\*\s+FROM\s+(\w+)/i', $query, $matches)) {
                $table = strtolower($matches[1]);
                $data = $this->pdo->select($table);
                return $data[0] ?? null;
            }
            return null;
        }
        
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
        if ($this->usingFileDatabase) {
            // For FileDatabase, we need to handle SELECT queries differently
            if (preg_match('/SELECT\s+\*\s+FROM\s+(\w+)/i', $query, $matches)) {
                $table = strtolower($matches[1]);
                return $this->pdo->select($table);
            }
            return [];
        }
        
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
        if ($this->usingFileDatabase) {
            $this->pdo->insert(strtolower($table), $data);
            return "1";
        }
        
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
        if ($this->usingFileDatabase) {
            // FileDatabase doesn't support UPDATE, return 0
            return 0;
        }
        
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
        if ($this->usingFileDatabase) {
            // Parse simple WHERE clause for FileDatabase
            if (preg_match('/(\w+)\s*=\s*\?/i', $where, $matches)) {
                $column = $matches[1];
                $this->pdo->delete(strtolower($table), $column, $params[0]);
                return 1;
            }
            return 0;
        }
        
        $query = "DELETE FROM {$table} WHERE {$where}";
        return $this->query($query, $params)->rowCount();
    }
}
