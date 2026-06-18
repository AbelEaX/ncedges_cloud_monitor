<?php

namespace Database\Migrations;

/**
 * Migration Base Class
 * 
 * Provides interface for creating and rolling back migrations.
 */
abstract class Migration
{
    /**
     * Run the migration
     * 
     * @return void
     */
    abstract public function up(): void;
    
    /**
     * Rollback the migration
     * 
     * @return void
     */
    abstract public function down(): void;
}
