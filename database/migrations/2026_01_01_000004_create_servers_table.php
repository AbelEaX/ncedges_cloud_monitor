<?php

namespace Database\Migrations;

/**
 * Create Servers Table
 * 
 * Migration: 2026_01_01_000004_create_servers_table
 */
class CreateServersTable extends Migration
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
        CREATE TABLE IF NOT EXISTS servers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(255) NOT NULL,
            host VARCHAR(255) NOT NULL,
            port INTEGER NOT NULL DEFAULT 443,
            description TEXT,
            status VARCHAR(50) NOT NULL DEFAULT 'unknown',
            group_name VARCHAR(100),
            is_active BOOLEAN NOT NULL DEFAULT 1,
            last_check_at DATETIME,
            last_status_change_at DATETIME,
            alert_sent BOOLEAN DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        )
        SQL;
        
        $pdo->exec($sql);
        
        // Create index for active servers
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_servers_active ON servers(is_active)');
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_servers_status ON servers(status)');
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
        
        $pdo->exec('DROP TABLE IF EXISTS servers');
    }
}
