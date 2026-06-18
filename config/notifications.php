<?php
/**
 * Notifications Configuration
 * 
 * Centralized notification system settings.
 * Supports multiple channels: Email, SMS, Push, In-App
 * Future-ready for extensible notification architecture.
 */

return [
    // Enabled Channels
    'channels' => [
        'email' => [
            'enabled' => env('NOTIFICATION_EMAIL_ENABLED', true),
            'queue' => false,
        ],
        'sms' => [
            'enabled' => env('NOTIFICATION_SMS_ENABLED', false),
            'provider' => env('SMS_PROVIDER', 'africastalking'),
            'queue' => false,
        ],
        'push' => [
            'enabled' => env('NOTIFICATION_PUSH_ENABLED', false),
            'provider' => env('PUSH_PROVIDER', 'firebase'),
            'queue' => false,
        ],
        'in_app' => [
            'enabled' => env('NOTIFICATION_IN_APP_ENABLED', true),
            'queue' => false,
        ],
    ],
    
    // Throttling Configuration
    'throttle' => [
        'enabled' => env('NOTIFICATION_THROTTLE_ENABLED', true),
        'minutes' => env('NOTIFICATION_THROTTLE_MINUTES', 30),
        'max_notifications' => env('NOTIFICATION_THROTTLE_MAX', 3),
    ],
    
    // Notification Types/Events
    'events' => [
        'server_created',
        'server_updated',
        'server_deleted',
        'server_down',
        'server_recovered',
        'critical_threshold_reached',
        'warning_threshold_reached',
        'user_created',
        'user_deleted',
        'user_login',
        'user_logout',
        'settings_changed',
    ],
    
    // Email Notification Templates
    'templates' => [
        'server_down' => 'emails.notifications.server-down',
        'server_recovered' => 'emails.notifications.server-recovered',
        'critical_alert' => 'emails.notifications.critical-alert',
        'test_email' => 'emails.notifications.test',
    ],
    
    // Default Recipients
    'default_recipients' => [
        'administrators' => env('NOTIFY_ADMIN_EMAIL', ''),
    ],
    
    // Retry Configuration
    'retry' => [
        'enabled' => env('NOTIFICATION_RETRY_ENABLED', true),
        'max_attempts' => env('NOTIFICATION_MAX_ATTEMPTS', 3),
        'delay_seconds' => env('NOTIFICATION_RETRY_DELAY', 300),
    ],
];
