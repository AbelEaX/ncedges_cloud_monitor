<?php

namespace Database\Migrations;

/**
 * Create Settings Table
 * 
 * Migration: 2026_01_01_000009_create_settings_table
 */
class CreateSettingsTable extends Migration
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
        CREATE TABLE IF NOT EXISTS settings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            key VARCHAR(255) NOT NULL UNIQUE,
            value TEXT,
            type VARCHAR(50),
            description TEXT,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        )
        SQL;
        
        $pdo->exec($sql);
        
        // Insert default settings
        $defaults = [
            ['key' => 'app_name', 'value' => 'Nova Cloud Hosting', 'type' => 'string'],
            ['key' => 'check_timeout', 'value' => '3', 'type' => 'integer'],
            ['key' => 'alert_after', 'value' => '300', 'type' => 'integer'],
            ['key' => 'timezone', 'value' => 'Africa/Kampala', 'type' => 'string'],
        ];
        
        foreach ($defaults as $setting) {
            $pdo->exec("INSERT OR IGNORE INTO settings (key, value, type) 
                VALUES ('{$setting['key']}', '{$setting['value']}', '{$setting['type']}')");
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
        
        $pdo->exec('DROP TABLE IF EXISTS settings');
    }
}
