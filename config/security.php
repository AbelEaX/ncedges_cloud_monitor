<?php
/**
 * Security Configuration
 * 
 * Application security settings including authentication, authorization, and access control.
 */

return [
    // Authentication
    'auth' => [
        'driver' => env('AUTH_DRIVER', 'database'),
        'session_timeout' => env('AUTH_SESSION_TIMEOUT', 3600),
        'remember_me_duration' => env('REMEMBER_ME_DURATION', 604800),
    ],
    
    // Password Policy
    'password' => [
        'min_length' => env('PASSWORD_MIN_LENGTH', 8),
        'require_uppercase' => env('PASSWORD_REQUIRE_UPPERCASE', true),
        'require_numbers' => env('PASSWORD_REQUIRE_NUMBERS', true),
        'require_special' => env('PASSWORD_REQUIRE_SPECIAL', true),
        'expiration_days' => env('PASSWORD_EXPIRATION_DAYS', 0),
    ],
    
    // RBAC - Roles
    'roles' => [
        'admin' => [
            'label' => 'Administrator',
            'description' => 'Full access to all features',
        ],
        'operator' => [
            'label' => 'Operator',
            'description' => 'Manage servers, limited user access',
        ],
        'viewer' => [
            'label' => 'Viewer',
            'description' => 'Read-only access',
        ],
    ],
    
    // RBAC - Default Permissions
    'permissions' => [
        'server.view',
        'server.create',
        'server.update',
        'server.delete',
        'user.view',
        'user.create',
        'user.update',
        'user.delete',
        'settings.view',
        'settings.update',
        'audit.view',
        'audit.export',
    ],
    
    // CORS Settings
    'cors' => [
        'enabled' => env('CORS_ENABLED', false),
        'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', 'http://localhost')),
        'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'],
        'allowed_headers' => ['Content-Type', 'Authorization'],
    ],
    
    // Encryption
    'encryption' => [
        'algorithm' => env('ENCRYPTION_ALGORITHM', 'AES-256-CBC'),
        'iv_length' => 16,
    ],
    
    // Two-Factor Authentication
    'two_factor' => [
        'enabled' => env('TWO_FACTOR_ENABLED', false),
        'provider' => env('TWO_FACTOR_PROVIDER', 'google_authenticator'),
    ],
    
    // Rate Limiting
    'rate_limit' => [
        'enabled' => env('RATE_LIMIT_ENABLED', true),
        'login_attempts' => env('RATE_LIMIT_LOGIN_ATTEMPTS', 5),
        'login_window' => env('RATE_LIMIT_LOGIN_WINDOW', 300),
    ],
];
