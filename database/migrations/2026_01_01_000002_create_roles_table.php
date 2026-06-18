<?php

namespace Database\Migrations;

/**
 * Create Roles Table
 * 
 * Migration: 2026_01_01_000002_create_roles_table
 */
class CreateRolesTable extends Migration
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
        CREATE TABLE IF NOT EXISTS roles (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(255) NOT NULL UNIQUE,
            description TEXT,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        )
        SQL;
        
        $pdo->exec($sql);
        
        // Insert default roles
        $pdo->exec("INSERT OR IGNORE INTO roles (id, name, description) VALUES 
            (1, 'admin', 'Administrator with full access'),
            (2, 'operator', 'Server operator with limited user management'),
            (3, 'viewer', 'Read-only access')
        ");
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
        
        $pdo->exec('DROP TABLE IF EXISTS roles');
    }
}
