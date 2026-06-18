<?php

/**
 * Bootstrap Application
 * 
 * This file bootstraps the entire application.
 * It sets up error handling, loads configurations, initializes the service container,
 * registers all services, and prepares the application for handling requests.
 */

// Define application base path
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('RESOURCES_PATH', BASE_PATH . '/resources');
define('CONFIG_PATH', BASE_PATH . '/config');
define('STORAGE_PATH', BASE_PATH . '/storage');

// Load environment variables from .env file
$envFile = BASE_PATH . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) continue; // Skip comments
        if (strpos($line, '=') === false) continue;

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        // Remove quotes if present
        if (in_array($value[0] ?? null, ['"', "'"])) {
            $value = substr($value, 1, -1);
        }

        // Only set if not already defined
        if (!getenv($key)) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

// Enable error reporting in development
$isDevelopment = getenv('APP_ENV') === 'development' || getenv('APP_ENV') === 'dev';
if ($isDevelopment) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
}

// Register autoloader
// Try to load composer autoloader, otherwise use simple PSR-4 autoloader
if (file_exists(BASE_PATH . '/vendor/composer/autoload_classmap.php')) {
    require BASE_PATH . '/vendor/autoload.php';
} else {
    require APP_PATH . '/autoloader.php';
}

// Load helper functions
require APP_PATH . '/Core/Helpers/functions.php';

// Create service container instance
$container = new \App\Core\Services\Container();

// Store global reference for the app() helper
global $container;

// Register core services
registerCoreServices($container);

/**
 * Register all core application services
 * 
 * @param \App\Core\Services\Container $container
 * @return void
 */
function registerCoreServices(\App\Core\Services\Container $container): void
{
    // Database Connection
    $container->singleton(
        \App\Infrastructure\Database\Connection::class,
        function ($c) {
            return new \App\Infrastructure\Database\Connection(config('database'));
        }
    );
    
    // Logger Service
    $container->singleton(
        \App\Infrastructure\Logging\Logger::class,
        function ($c) {
            return new \App\Infrastructure\Logging\Logger(config('logging'));
        }
    );
    
    // Authentication Service
    $container->singleton(
        \App\Infrastructure\Authentication\AuthenticationService::class,
        function ($c) {
            return new \App\Infrastructure\Authentication\AuthenticationService(
                $c->resolve(\App\Infrastructure\Database\Connection::class),
                config('security')
            );
        }
    );
    
    // Mail Service
    $container->singleton(
        \App\Infrastructure\Mail\MailService::class,
        function ($c) {
            return new \App\Infrastructure\Mail\MailService(
                config('smtp'),
                $c->resolve(\App\Infrastructure\Logging\Logger::class)
            );
        }
    );
    
    // Notification Manager
    $container->singleton(
        \App\Infrastructure\Notifications\NotificationManager::class,
        function ($c) {
            return new \App\Infrastructure\Notifications\NotificationManager(
                config('notifications'),
                $c->resolve(\App\Infrastructure\Mail\MailService::class),
                $c->resolve(\App\Infrastructure\Logging\Logger::class)
            );
        }
    );
    
    // Monitoring Service
    $container->singleton(
        \App\Infrastructure\Monitoring\MonitoringService::class,
        function ($c) {
            return new \App\Infrastructure\Monitoring\MonitoringService(
                $c->resolve(\App\Infrastructure\Database\Connection::class),
                $c->resolve(\App\Infrastructure\Notifications\NotificationManager::class),
                $c->resolve(\App\Infrastructure\Logging\Logger::class),
                config('monitoring')
            );
        }
    );
    
    // Audit Service
    $container->singleton(
        \App\Infrastructure\Logging\AuditService::class,
        function ($c) {
            return new \App\Infrastructure\Logging\AuditService(
                $c->resolve(\App\Infrastructure\Database\Connection::class),
                $c->resolve(\App\Infrastructure\Logging\Logger::class)
            );
        }
    );
    
    // Theme Service
    $container->singleton(
        \App\Infrastructure\Logging\ThemeService::class,
        function ($c) {
            return new \App\Infrastructure\Logging\ThemeService(config('theme'));
        }
    );
}

// Set timezone
date_default_timezone_set(config('app.timezone'));

// Start session if not already started
if (session_status() === PHP_SESSION_NONE && !defined('PHPUNIT_TESTSUITE')) {
    session_start();
}

return $container;
