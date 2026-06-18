<?php
/**
 * Application Configuration
 * 
 * Single Source of Truth (SSOT) for all application-level settings.
 * Environment variables should be used for environment-specific values.
 */

return [
    // Application Identity
    'name' => env('APP_NAME', 'Nova Cloud Hosting'),
    'environment' => env('APP_ENV', 'production'),
    'debug' => env('APP_DEBUG', false),
    
    // Timezone
    'timezone' => env('APP_TIMEZONE', 'Africa/Kampala'),
    
    // URLs
    'url' => env('APP_URL', 'http://localhost'),
    'base_path' => dirname(__DIR__),
    'public_path' => dirname(__DIR__) . '/public',
    
    // Application Keys
    'app_key' => env('APP_KEY', 'base64:' . bin2hex(random_bytes(32))),
    'cipher' => env('APP_CIPHER', 'AES-256-CBC'),
    
    // Session Configuration
    'session' => [
        'driver' => env('SESSION_DRIVER', 'file'),
        'lifetime' => env('SESSION_LIFETIME', 120),
        'cookie' => env('SESSION_COOKIE', 'monitor_session'),
        'secure' => env('SESSION_SECURE', false),
        'http_only' => env('SESSION_HTTP_ONLY', true),
        'same_site' => env('SESSION_SAME_SITE', 'Lax'),
    ],
    
    // Cache Configuration
    'cache' => [
        'driver' => env('CACHE_DRIVER', 'file'),
        'ttl' => env('CACHE_TTL', 3600),
    ],
    
    // Locale
    'locale' => env('APP_LOCALE', 'en'),
    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),
    
    // Features
    'features' => [
        'notifications_enabled' => env('NOTIFICATIONS_ENABLED', true),
        'audit_logging_enabled' => env('AUDIT_LOGGING_ENABLED', true),
        'activity_timeline_enabled' => env('ACTIVITY_TIMELINE_ENABLED', true),
        'auto_refresh_enabled' => env('AUTO_REFRESH_ENABLED', true),
    ],
    
    // Pagination
    'pagination' => [
        'per_page' => 25,
    ],
];
