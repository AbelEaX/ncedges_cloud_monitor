<?php
/**
 * Logging Configuration
 * 
 * Centralized logging settings for all application logs.
 * Supports multiple channels and log levels.
 */

return [
    // Default Log Channel
    'default' => env('LOG_CHANNEL', 'stack'),
    
    // Log Level
    'level' => env('LOG_LEVEL', 'debug'),
    
    // Log Channels
    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['single', 'daily'],
            'ignore_exceptions_from_channels' => [],
        ],
        
        'single' => [
            'driver' => 'single',
            'path' => dirname(__DIR__) . '/storage/logs/application.log',
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        
        'daily' => [
            'driver' => 'daily',
            'path' => dirname(__DIR__) . '/storage/logs/application.log',
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
        ],
        
        'security' => [
            'driver' => 'single',
            'path' => dirname(__DIR__) . '/storage/logs/security.log',
            'level' => 'info',
        ],
        
        'audit' => [
            'driver' => 'single',
            'path' => dirname(__DIR__) . '/storage/logs/audit.log',
            'level' => 'info',
        ],
        
        'monitoring' => [
            'driver' => 'single',
            'path' => dirname(__DIR__) . '/storage/logs/monitoring.log',
            'level' => 'info',
        ],
        
        'notifications' => [
            'driver' => 'single',
            'path' => dirname(__DIR__) . '/storage/logs/notifications.log',
            'level' => 'debug',
        ],
        
        'authentication' => [
            'driver' => 'single',
            'path' => dirname(__DIR__) . '/storage/logs/authentication.log',
            'level' => 'info',
        ],
    ],
    
    // Logged Exceptions
    'dont_report' => [
        // Exceptions to ignore
    ],
    
    // Log Retention
    'retention' => [
        'days' => env('LOG_RETENTION_DAYS', 30),
    ],
];
