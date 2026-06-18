<?php

/**
 * Simple PSR-4 Autoloader
 *
 * This autoloader handles the App and Database namespaces
 * for development when composer autoloader isn't available.
 */

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = dirname(__DIR__) . '/app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) === 0) {
        $relative_class = substr($class, $len);
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

        if (file_exists($file)) {
            require $file;
            return true;
        }
    }

    $prefix = 'Database\\';
    $base_dir = dirname(__DIR__) . '/database/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) === 0) {
        $relative_class = substr($class, $len);
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

        if (file_exists($file)) {
            require $file;
            return true;
        }
    }

    return false;
});
