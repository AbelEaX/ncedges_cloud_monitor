<?php

namespace App\Infrastructure\Database;

/**
 * File-Based Database Simulator
 *
 * Simple implementation using JSON files for development when
 * proper database drivers are not available.
 */

class FileDatabase
{
    private string $dataDir;
    private array $tables = [];

    public function __construct(string $dataDir)
    {
        $this->dataDir = $dataDir;
        if (!is_dir($dataDir)) {
            mkdir($dataDir, 0755, true);
        }
    }

    public function exec(string $sql): void
    {
        // Parse and execute simple CREATE TABLE statements
        if (preg_match('/CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS\s+(\w+)\s*\(/i', $sql, $matches)) {
            $table = strtolower($matches[1]);
            $this->createTable($table);
        }
    }

    public function prepare(string $sql): FileDatabaseStatement
    {
        return new FileDatabaseStatement($this, $sql);
    }

    private function createTable(string $name): void
    {
        $file = $this->dataDir . "/$name.json";
        if (!file_exists($file)) {
            file_put_contents($file, json_encode(['data' => [], 'sequence' => 0]));
        }
    }

    public function insert(string $table, array $values): void
    {
        $file = $this->dataDir . "/$table.json";
        $this->ensureFile($file);

        $data = json_decode(file_get_contents($file), true);
        $data['sequence']++;
        $values['id'] = $data['sequence'];
        $data['data'][] = $values;

        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    public function select(string $table): array
    {
        $file = $this->dataDir . "/$table.json";
        $this->ensureFile($file);

        $data = json_decode(file_get_contents($file), true);
        return $data['data'] ?? [];
    }

    public function selectWhere(string $table, string $column, $value): array
    {
        $all = $this->select($table);
        return array_filter($all, fn($row) => $row[$column] === $value);
    }

    public function delete(string $table, string $column, $value): void
    {
        $file = $this->dataDir . "/$table.json";
        $this->ensureFile($file);

        $data = json_decode(file_get_contents($file), true);
        $data['data'] = array_filter($data['data'], fn($row) => $row[$column] !== $value);
        $data['data'] = array_values($data['data']); // Re-index

        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private function ensureFile(string $file): void
    {
        if (!file_exists($file)) {
            file_put_contents($file, json_encode(['data' => [], 'sequence' => 0]));
        }
    }
}

class FileDatabaseStatement
{
    private FileDatabase $db;
    private string $sql;
    private array $values = [];

    public function __construct(FileDatabase $db, string $sql)
    {
        $this->db = $db;
        $this->sql = $sql;
    }

    public function execute(array $values = []): void
    {
        $this->values = $values;

        // Handle INSERT
        if (preg_match('/INSERT\s+INTO\s+(\w+)\s*\((.*?)\)\s*VALUES\s*\((.*?)\)/i', $this->sql, $matches)) {
            $table = strtolower($matches[1]);
            $columns = array_map('trim', explode(',', $matches[2]));
            $valuesPart = $matches[3];
            
            // Parse VALUES part - handle both ? placeholders and function calls
            $valueItems = array_map('trim', explode(',', $valuesPart));
            $record = [];
            $valueIndex = 0;
            
            foreach ($columns as $i => $column) {
                $valueItem = $valueItems[$i];
                if ($valueItem === '?') {
                    // Use the provided value
                    $record[$column] = $this->values[$valueIndex] ?? null;
                    $valueIndex++;
                } elseif (preg_match('/NOW\(\)/i', $valueItem)) {
                    // Handle NOW() function
                    $record[$column] = date('Y-m-d H:i:s');
                } elseif (preg_match('/CURRENT_TIMESTAMP/i', $valueItem)) {
                    // Handle CURRENT_TIMESTAMP
                    $record[$column] = date('Y-m-d H:i:s');
                } else {
                    // Handle literal values
                    $record[$column] = $valueItem;
                }
            }
            
            $this->db->insert($table, $record);
            return;
        }

        // Handle DELETE
        if (preg_match('/DELETE\s+FROM\s+(\w+)\s+WHERE\s+(\w+)\s*=\s*\?/i', $this->sql, $matches)) {
            $table = strtolower($matches[1]);
            $column = $matches[2];
            $this->db->delete($table, $column, $this->values[0]);
            return;
        }
    }

    public function fetch(int $fetchMode = PDO::FETCH_ASSOC): ?array
    {
        // Not implemented for file database
        return null;
    }

    public function fetchAll(int $fetchMode = PDO::FETCH_ASSOC): array
    {
        // Not implemented for file database
        return [];
    }
}

// Create helper function for testing
function createTestDatabase()
{
    return new FileDatabase(__DIR__ . '/storage/database');
}
