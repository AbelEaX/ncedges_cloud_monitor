<?php
/**
 * SMTP Configuration
 * 
 * Single Source of Truth for email/SMTP settings.
 * All mail-related configuration centralized here.
 * Uses environment variables to avoid hardcoding sensitive data.
 */

return [
    // Default Mail Driver
    'driver' => env('MAIL_DRIVER', 'smtp'),
    
    // SMTP Configuration
    'smtp' => [
        'host' => env('MAIL_HOST', 'mail-gw.ncedges.com'),
        'port' => env('MAIL_PORT', 465),
        'username' => env('MAIL_USERNAME', 'webadmin@ncedges.com'),
        'password' => env('MAIL_PASSWORD', ''),
        'encryption' => env('MAIL_ENCRYPTION', 'ssl'),
        'timeout' => env('MAIL_TIMEOUT', 10),
    ],
    
    // Sender Information
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'webadmin@ncedges.com'),
        'name' => env('MAIL_FROM_NAME', 'Monitor System'),
    ],
    
    // Reply To Address
    'reply_to' => [
        'address' => env('MAIL_REPLY_TO_ADDRESS', 'support@ncedges.com'),
        'name' => env('MAIL_REPLY_TO_NAME', 'Support'),
    ],
    
    // Alert Recipient Configuration
    'alerts' => [
        'primary_recipient' => env('ALERT_EMAIL', 'webadmin@ncedges.com'),
        'cc' => explode(',', env('ALERT_EMAIL_CC', '')),
        'bcc' => explode(',', env('ALERT_EMAIL_BCC', '')),
    ],
    
    // Connection Settings
    'verify_ssl' => env('MAIL_VERIFY_SSL', true),
    'keep_alive' => env('MAIL_KEEP_ALIVE', false),
];
