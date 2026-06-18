<?php

namespace Database\Migrations;

/**
 * Create Server Metrics Table
 * 
 * Migration: 2026_01_01_000005_create_server_metrics_table
 */
class CreateServerMetricsTable extends Migration
{
    /**
     * Run the migration
     * 
     * @return void
     */
    public function up(): void
    {
        $connection = app(\App\Infrastructure\Database\Connection::class);
        $pdo = $connection->getPDO();
        
        $sql = <<<SQL
        CREATE TABLE IF NOT EXISTS server_metrics (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            server_id INTEGER NOT NULL,
            response_time INTEGER,
            status VARCHAR(50),
            checked_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (server_id) REFERENCES servers(id) ON DELETE CASCADE
        )
        SQL;
        
        $pdo->exec($sql);
        
        // Create index for queries
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_metrics_server ON server_metrics(server_id)');
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_metrics_checked ON server_metrics(checked_at)');
    }
    
    /**
     * Rollback the migration
     * 
     * @return void
     */
    public function down(): void
    {
        $connection = app(\App\Infrastructure\Database\Connection::class);
        $pdo = $connection->getPDO();
        
        $pdo->exec('DROP TABLE IF EXISTS server_metrics');
    }
}
