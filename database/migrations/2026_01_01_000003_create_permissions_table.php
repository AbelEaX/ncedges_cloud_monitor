<?php

namespace Database\Migrations;

/**
 * Create Permissions Table
 * 
 * Migration: 2026_01_01_000003_create_permissions_table
 */
class CreatePermissionsTable extends Migration
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
        CREATE TABLE IF NOT EXISTS permissions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(255) NOT NULL UNIQUE,
            description TEXT,
            category VARCHAR(100),
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        )
        SQL;
        
        $pdo->exec($sql);
        
        // Insert default permissions
        $permissions = [
            // Server permissions
            ['server.view', 'View servers', 'server'],
            ['server.create', 'Create server', 'server'],
            ['server.update', 'Update server', 'server'],
            ['server.delete', 'Delete server', 'server'],
            
            // User permissions
            ['user.view', 'View users', 'user'],
            ['user.create', 'Create user', 'user'],
            ['user.update', 'Update user', 'user'],
            ['user.delete', 'Delete user', 'user'],
            
            // Settings permissions
            ['settings.view', 'View settings', 'settings'],
            ['settings.update', 'Update settings', 'settings'],
            
            // Audit permissions
            ['audit.view', 'View audit logs', 'audit'],
            ['audit.export', 'Export audit logs', 'audit'],
        ];
        
        foreach ($permissions as [$name, $desc, $category]) {
            $pdo->exec("INSERT OR IGNORE INTO permissions (name, description, category) 
                VALUES ('{$name}', '{$desc}', '{$category}')");
        }
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
        
        $pdo->exec('DROP TABLE IF EXISTS permissions');
    }
}
