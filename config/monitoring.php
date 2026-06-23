<?php
/**
 * Monitoring Configuration
 * 
 * Server monitoring, health checks, and metrics configuration.
 * All monitoring-related settings centralized here.
 */

return [
    // Refresh Intervals (in seconds)
    'refresh' => [
        'interval' => env('MONITOR_REFRESH_INTERVAL', 30),
        'slow_check_interval' => env('MONITOR_SLOW_CHECK_INTERVAL', 60),
        'fast_check_interval' => env('MONITOR_FAST_CHECK_INTERVAL', 15),
    ],
    
    // Health Check Configuration
    'health_check' => [
        'enabled' => env('HEALTH_CHECK_ENABLED', true),
        'timeout' => env('HEALTH_CHECK_TIMEOUT', 3),
        'retries' => env('HEALTH_CHECK_RETRIES', 1),
        'retry_delay' => env('HEALTH_CHECK_RETRY_DELAY', 2),
    ],
    
    // Alert Thresholds
    'thresholds' => [
        'alert_after_seconds' => env('ALERT_AFTER_SECONDS', 300),
        'critical_after_seconds' => env('CRITICAL_AFTER_SECONDS', 900),
        'recovery_grace_period' => env('RECOVERY_GRACE_PERIOD', 30),
    ],
    
    // Metrics Collection
    'metrics' => [
        'enabled' => env('METRICS_ENABLED', true),
        'retention_days' => env('METRICS_RETENTION_DAYS', 90),
        'collection_interval' => env('METRICS_COLLECTION_INTERVAL', 300),
    ],
    
    // Status Definitions
    'statuses' => [
        'online' => [
            'label' => 'Online',
            'color' => '#66bb6a',
            'badge' => 'success',
        ],
        'warning' => [
            'label' => 'Warning',
            'color' => '#ffc107',
            'badge' => 'warning',
        ],
        'critical' => [
            'label' => 'Critical',
            'color' => '#ef5350',
            'badge' => 'danger',
        ],
        'offline' => [
            'label' => 'Offline',
            'color' => '#757575',
            'badge' => 'secondary',
        ],
        'maintenance' => [
            'label' => 'Maintenance',
            'color' => '#29b6f6',
            'badge' => 'info',
        ],
        'pending' => [
            'label' => 'Pending',
            'color' => '#9e9e9e',
            'badge' => 'secondary',
        ],
        'in_progress' => [
            'label' => 'Checking...',
            'color' => '#ab47bc',
            'badge' => 'info',
        ],
    ],
    
    // Server Groups (Optional)
    'groups' => [
        'infrastructure' => 'Infrastructure',
        'applications' => 'Applications',
        'backup' => 'Backup Systems',
    ],
];
