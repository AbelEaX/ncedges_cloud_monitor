<?php

namespace Database\Migrations;

/**
 * Create Audit Logs Table
 * 
 * Migration: 2026_01_01_000008_create_audit_logs_table
 */
class CreateAuditLogsTable extends Migration
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
        CREATE TABLE IF NOT EXISTS audit_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            action VARCHAR(100) NOT NULL,
            entity_type VARCHAR(100),
            entity_id INTEGER,
            details JSON,
            severity VARCHAR(50) NOT NULL DEFAULT 'info',
            ip_address VARCHAR(45),
            user_agent TEXT,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        )
        SQL;
        
        $pdo->exec($sql);
        
        // Create indexes for queries
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_audit_user ON audit_logs(user_id)');
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_audit_action ON audit_logs(action)');
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_audit_created ON audit_logs(created_at)');
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_audit_severity ON audit_logs(severity)');
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
        
        $pdo->exec('DROP TABLE IF EXISTS audit_logs');
    }
}
