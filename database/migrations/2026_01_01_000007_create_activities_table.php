<?php

namespace Database\Migrations;

/**
 * Create Activities Table (Activity Timeline)
 * 
 * Migration: 2026_01_01_000007_create_activities_table
 */
class CreateActivitiesTable extends Migration
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
        CREATE TABLE IF NOT EXISTS activities (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            action VARCHAR(100) NOT NULL,
            entity_type VARCHAR(100),
            entity_id INTEGER,
            description TEXT,
            details JSON,
            ip_address VARCHAR(45),
            user_agent TEXT,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        )
        SQL;
        
        $pdo->exec($sql);
        
        // Create indexes
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_activities_user ON activities(user_id)');
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_activities_action ON activities(action)');
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_activities_created ON activities(created_at)');
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
        
        $pdo->exec('DROP TABLE IF EXISTS activities');
    }
}
