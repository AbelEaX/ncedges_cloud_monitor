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

        // Return full config or specific setting
        if ($setting === null) {
            return $configs[$file] ?? $default;
        }

        return $configs[$file][$setting] ?? $default;
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
