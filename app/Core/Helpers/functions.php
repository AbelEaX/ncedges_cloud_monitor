<?php
/**
 * Helper Functions
 * 
 * Global helper functions for common application tasks.
 */

if (!function_exists('env')) {
    /**
     * Get an environment variable
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function env(string $key, $default = null) {
        // Try to get from $_ENV first
        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }
        
        // Try to get from getenv()
        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }
        
        // Return default or null
        return $default;
    }
}

if (!function_exists('config')) {
    /**
     * Get a configuration value
     *
     * @param string $key (e.g., 'app.name', 'database.default')
     * @param mixed $default
     * @return mixed
     */
    function config(string $key, $default = null) {
        static $configs = [];
        static $dbSettings = null;
        static $loadingDb = false;

        [$file, $setting] = array_pad(explode('.', $key, 2), 2, null);

        // Load config file if not loaded
        if (!isset($configs[$file])) {
            // Use CONFIG_PATH constant if defined, otherwise try to locate it
            if (defined('CONFIG_PATH')) {
                $configPath = CONFIG_PATH . "/{$file}.php";
            } else {
                $baseDir = dirname(dirname(dirname(__DIR__)));
                $configPath = $baseDir . "/config/{$file}.php";
            }

            if (file_exists($configPath)) {
                $configs[$file] = require $configPath;
            } else {
                return $default;
            }
        }

        // Load database settings to override PHP configuration if applicable
        if ($dbSettings === null && !$loadingDb && $file !== 'database' && function_exists('app')) {
            $loadingDb = true;
            try {
                $container = app();
                if ($container && $container->bound(\App\Infrastructure\Database\Connection::class)) {
                    $connection = $container->resolve(\App\Infrastructure\Database\Connection::class);
                    // Check if settings table exists to avoid crash during migrations
                    $tableExists = $connection->fetchOne(
                        "SELECT name FROM sqlite_master WHERE type='table' AND name='settings'"
                    );
                    if ($tableExists) {
                        $rows = $connection->fetchAll('SELECT key, value, type FROM settings');
                        $dbSettings = [];
                        foreach ($rows as $row) {
                            $value = $row['value'];
                            $type = $row['type'];
                            $dbSettings[$row['key']] = match ($type) {
                                'integer', 'int' => (int) $value,
                                'boolean', 'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
                                'float', 'double' => (float) $value,
                                default => $value,
                            };
                        }
                    }
                }
            } catch (\Exception $e) {
                // Ignore errors if database/connection is not fully initialized yet
            }
            $loadingDb = false;
        }

        // Merge database settings into configurations if loaded
        if ($dbSettings !== null) {
            foreach ($dbSettings as $dbKey => $dbValue) {
                [$dbFile, $dbSettingKey] = array_pad(explode('.', $dbKey, 2), 2, null);
                if ($dbSettingKey !== null && isset($configs[$dbFile])) {
                    $keys = explode('.', $dbSettingKey);
                    $temp = &$configs[$dbFile];
                    foreach ($keys as $i => $nestedKey) {
                        if ($i === count($keys) - 1) {
                            $temp[$nestedKey] = $dbValue;
                        } else {
                            if (!isset($temp[$nestedKey]) || !is_array($temp[$nestedKey])) {
                                $temp[$nestedKey] = [];
                            }
                            $temp = &$temp[$nestedKey];
                        }
                    }
                }
            }
        }

        // Return full config or specific setting
        if ($setting === null) {
            return $configs[$file] ?? $default;
        }

        // Return nested array key if requested (e.g., 'smtp.smtp.host')
        $keys = explode('.', $setting);
        $value = $configs[$file];
        foreach ($keys as $k) {
            if (is_array($value) && isset($value[$k])) {
                $value = $value[$k];
            } else {
                return $default;
            }
        }

        return $value;
    }
}

if (!function_exists('app')) {
    /**
     * Get the service container instance
     * 
     * @param string|null $abstract
     * @return \App\Core\Services\Container|mixed
     */
    function app(?string $abstract = null) {
        global $container;
        
        if ($abstract === null) {
            return $container;
        }
        
        return $container->resolve($abstract);
    }
}

if (!function_exists('now')) {
    /**
     * Get current DateTime instance
     * 
     * @return \DateTime
     */
    function now(): \DateTime {
        return new \DateTime('now', new \DateTimeZone(config('app.timezone')));
    }
}

if (!function_exists('json_response')) {
    /**
     * Return a standardized JSON response
     * 
     * @param bool $success
     * @param string|null $message
     * @param mixed $data
     * @param array $errors
     * @return string
     */
    function json_response(bool $success, ?string $message = null, $data = null, array $errors = []): string {
        return json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data,
            'errors' => $errors,
        ]);
    }
}

if (!function_exists('view')) {
    /**
     * Render a view file
     * 
     * @param string $path
     * @param array $data
     * @return string
     */
    function view(string $path, array $data = []): string {
        $viewPath = config('app.base_path') . '/resources/views/' . str_replace('.', '/', $path) . '.php';
        
        if (!file_exists($viewPath)) {
            throw new \Exception("View not found: {$path}");
        }
        
        // Extract data to variables
        extract($data, EXTR_SKIP);
        
        // Start output buffering
        ob_start();
        require $viewPath;
        return ob_get_clean();
    }
}

if (!function_exists('component')) {
    /**
     * Render a component
     * 
     * @param string $name (e.g., 'buttons.primary')
     * @param array $props
     * @return string
     */
    function component(string $name, array $props = []): string {
        $componentPath = config('app.base_path') . '/resources/components/' . str_replace('.', '/', $name) . '.php';
        
        if (!file_exists($componentPath)) {
            throw new \Exception("Component not found: {$name}");
        }
        
        // Extract props to variables
        extract($props, EXTR_SKIP);
        
        // Start output buffering
        ob_start();
        require $componentPath;
        return ob_get_clean();
    }
}

if (!function_exists('log_info')) {
    /**
     * Log an info level message
     * 
     * @param string $message
     * @param array $context
     * @return void
     */
    function log_info(string $message, array $context = []): void {
        $logger = app(\App\Infrastructure\Logging\Logger::class);
        $logger->info($message, $context);
    }
}

if (!function_exists('log_error')) {
    /**
     * Log an error level message
     * 
     * @param string $message
     * @param array $context
     * @return void
     */
    function log_error(string $message, array $context = []): void {
        $logger = app(\App\Infrastructure\Logging\Logger::class);
        $logger->error($message, $context);
    }
}

if (!function_exists('dd')) {
    /**
     * Dump and die
     * 
     * @param mixed $data
     * @return void
     */
    function dd(...$data): void {
        foreach ($data as $item) {
            echo '<pre>';
            var_dump($item);
            echo '</pre>';
        }
        die;
    }
}
