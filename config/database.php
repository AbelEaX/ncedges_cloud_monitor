<?php
/**
 * Database Configuration
 * 
 * Supports multiple database connections.
 * Uses environment variables for sensitive data.
 */

return [
    // Default Database Connection
    'default' => env('DB_CONNECTION', 'sqlite'),
    
    // Database Connections
    'connections' => [
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => env('DB_DATABASE', dirname(__DIR__) . '/database/monitor.db'),
            'prefix' => env('DB_PREFIX', ''),
        ],
        
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', 3306),
            'database' => env('DB_DATABASE', 'monitor'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => env('DB_PREFIX', ''),
            'strict' => true,
            'engine' => null,
        ],
        
        'postgresql' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', 5432),
            'database' => env('DB_DATABASE', 'monitor'),
            'username' => env('DB_USERNAME', 'postgres'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => env('DB_PREFIX', ''),
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],
    ],
    
    // Migration Settings
    'migrations' => [
        'table' => 'migrations',
        'directory' => 'database/migrations',
    ],
];
