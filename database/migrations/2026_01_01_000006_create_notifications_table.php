<?php

namespace Database\Migrations;

/**
 * Create Notifications Table
 * 
 * Migration: 2026_01_01_000006_create_notifications_table
 */
class CreateNotificationsTable extends Migration
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
        CREATE TABLE IF NOT EXISTS notifications (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            type VARCHAR(100) NOT NULL,
            recipient VARCHAR(255) NOT NULL,
            subject VARCHAR(255),
            message TEXT,
            channel VARCHAR(50),
            status VARCHAR(50) NOT NULL DEFAULT 'pending',
            sent_at DATETIME,
            read_at DATETIME,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        )
        SQL;
        
        $pdo->exec($sql);
        
        // Create index for queries
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_notifications_status ON notifications(status)');
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_notifications_recipient ON notifications(recipient)');
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
        
        $pdo->exec('DROP TABLE IF EXISTS notifications');
    }
}
