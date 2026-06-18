<?php

/**
 * Database Migration Runner
 *
 * Usage: php database/migrate.php [command] [options]
 *
 * Commands:
 *   up       - Run all pending migrations
 *   down     - Rollback last migration
 *   refresh  - Rollback all and run all migrations
 *   status   - Show migration status
 */

require dirname(__DIR__) . '/bootstrap/app.php';

class MigrationRunner
{
    private \App\Infrastructure\Database\Connection $connection;
    private string $migrationsPath;
    private \App\Infrastructure\Logging\Logger $logger;

    public function __construct()
    {
        $this->connection = app(\App\Infrastructure\Database\Connection::class);
        $this->logger = app(\App\Infrastructure\Logging\Logger::class);
        $this->migrationsPath = dirname(__DIR__) . '/database/migrations';

        $this->ensureMigrationsTable();
    }

    /**
     * Ensure migrations table exists
     */
    private function ensureMigrationsTable(): void
    {
        $pdo = $this->connection->getPDO();
        $sql = <<<SQL
        CREATE TABLE IF NOT EXISTS migrations (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            migration VARCHAR(255) NOT NULL UNIQUE,
            batch INTEGER NOT NULL,
            executed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        )
        SQL;

        $pdo->exec($sql);
    }

    /**
     * Run all pending migrations
     */
    public function up(): void
    {
        $pending = $this->getPendingMigrations();

        if (empty($pending)) {
            echo "[✓] No pending migrations\n";
            return;
        }

        $batch = $this->getNextBatch();

        foreach ($pending as $migration) {
            try {
                $this->runMigration($migration, 'up');
                $this->recordMigration($migration, $batch);
                echo "[✓] Migrated: $migration\n";
            } catch (Exception $e) {
                echo "[✗] Failed to migrate $migration: " . $e->getMessage() . "\n";
                $this->logger->error("Migration failed: $migration", ['error' => $e->getMessage()]);
                throw $e;
            }
        }

        echo "\n[✓] Successfully migrated " . count($pending) . " migration(s)\n";
    }

    /**
     * Rollback last migration batch
     */
    public function down(): void
    {
        $lastBatch = $this->getLastBatch();

        if ($lastBatch === null) {
            echo "[✓] No migrations to rollback\n";
            return;
        }

        $pdo = $this->connection->getPDO();
        $stmt = $pdo->prepare('SELECT migration FROM migrations WHERE batch = ? ORDER BY id DESC');
        $stmt->execute([$lastBatch]);
        $migrations = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($migrations as $migration) {
            try {
                $this->runMigration($migration, 'down');
                $this->removeMigration($migration);
                echo "[✓] Rolled back: $migration\n";
            } catch (Exception $e) {
                echo "[✗] Failed to rollback $migration: " . $e->getMessage() . "\n";
                throw $e;
            }
        }

        echo "\n[✓] Successfully rolled back " . count($migrations) . " migration(s)\n";
    }

    /**
     * Refresh (rollback all and run all migrations)
     */
    public function refresh(): void
    {
        echo "[*] Refreshing database...\n\n";

        $pdo = $this->connection->getPDO();
        $stmt = $pdo->prepare('SELECT migration FROM migrations ORDER BY batch DESC, id DESC');
        $stmt->execute();
        $allMigrations = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Rollback all migrations
        foreach ($allMigrations as $migration) {
            try {
                $this->runMigration($migration, 'down');
                $this->removeMigration($migration);
                echo "[✓] Rolled back: $migration\n";
            } catch (Exception $e) {
                echo "[✗] Failed to rollback $migration: " . $e->getMessage() . "\n";
                throw $e;
            }
        }

        echo "\n[*] Running all migrations...\n\n";

        // Run all migrations
        $this->up();
    }

    /**
     * Show migration status
     */
    public function status(): void
    {
        $pdo = $this->connection->getPDO();

        echo "\n┌─ Migration Status ─────────────────────────────────────────────┐\n";
        echo "│ Batch │ Migration                              │ Executed At       │\n";
        echo "├───────┼────────────────────────────────────────┼───────────────────┤\n";

        $stmt = $pdo->prepare('SELECT batch, migration, executed_at FROM migrations ORDER BY batch DESC, id DESC');
        $stmt->execute();
        $migrations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($migrations)) {
            echo "│ - No migrations executed                                        │\n";
        } else {
            foreach ($migrations as $row) {
                $batch = str_pad($row['batch'], 5);
                $migration = str_pad($row['migration'], 38);
                $time = substr($row['executed_at'], 0, 16);
                echo "│ $batch │ $migration │ $time │\n";
            }
        }

        echo "└───────┴────────────────────────────────────────┴───────────────────┘\n";

        $pending = count($this->getPendingMigrations());
        echo "\nPending migrations: $pending\n\n";
    }

    /**
     * Get list of migration files
     */
    private function getAllMigrations(): array
    {
        $files = glob($this->migrationsPath . '/*_*.php');
        $migrations = [];

        foreach ($files as $file) {
            $filename = basename($file, '.php');
            $migrations[] = $filename;
        }

        sort($migrations);
        return $migrations;
    }

    /**
     * Get pending migrations
     */
    private function getPendingMigrations(): array
    {
        $all = $this->getAllMigrations();
        $pdo = $this->connection->getPDO();

        $stmt = $pdo->prepare('SELECT migration FROM migrations');
        $stmt->execute();
        $executed = $stmt->fetchAll(PDO::FETCH_COLUMN);

        return array_diff($all, $executed);
    }

    /**
     * Get next batch number
     */
    private function getNextBatch(): int
    {
        $pdo = $this->connection->getPDO();
        $stmt = $pdo->prepare('SELECT MAX(batch) as max_batch FROM migrations');
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return ($result['max_batch'] ?? 0) + 1;
    }

    /**
     * Get last batch number
     */
    private function getLastBatch(): ?int
    {
        $pdo = $this->connection->getPDO();
        $stmt = $pdo->prepare('SELECT MAX(batch) as max_batch FROM migrations');
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['max_batch'] ?? null;
    }

    /**
     * Run a migration
     */
    private function runMigration(string $migration, string $direction): void
    {
        $file = $this->migrationsPath . "/$migration.php";

        if (!file_exists($file)) {
            throw new Exception("Migration file not found: $file");
        }

        require $file;

        // Convert filename to class name
        $className = implode('', array_map(function($part) {
            return ucfirst(strtolower($part));
        }, preg_split('/_/', preg_replace('/^\d+_\d+_\d+_/', '', $migration))));

        $class = "Database\\Migrations\\$className";

        if (!class_exists($class)) {
            throw new Exception("Migration class not found: $class");
        }

        $migration = new $class();
        $method = $direction;
        $migration->$method();
    }

    /**
     * Record migration as executed
     */
    private function recordMigration(string $migration, int $batch): void
    {
        $pdo = $this->connection->getPDO();
        $stmt = $pdo->prepare('INSERT INTO migrations (migration, batch) VALUES (?, ?)');
        $stmt->execute([$migration, $batch]);
    }

    /**
     * Remove migration record
     */
    private function removeMigration(string $migration): void
    {
        $pdo = $this->connection->getPDO();
        $stmt = $pdo->prepare('DELETE FROM migrations WHERE migration = ?');
        $stmt->execute([$migration]);
    }
}

// Get command from arguments
$command = $argv[1] ?? 'status';

try {
    $runner = new MigrationRunner();

    switch ($command) {
        case 'up':
            $runner->up();
            break;
        case 'down':
            $runner->down();
            break;
        case 'refresh':
            $runner->refresh();
            break;
        case 'status':
            $runner->status();
            break;
        default:
            echo "Unknown command: $command\n";
            echo "Available commands: up, down, refresh, status\n";
            exit(1);
    }
} catch (Exception $e) {
    echo "\n[✗] Error: " . $e->getMessage() . "\n";
    exit(1);
}
