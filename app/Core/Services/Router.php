<?php

namespace App\Core\Services;

/**
 * Simple Router
 * 
 * Routes HTTP requests to appropriate handlers.
 */
class Router
{
    /**
     * Routes configuration
     * 
     * @var array
     */
    protected array $routes = [];
    
    /**
     * Register a GET route
     * 
     * @param string $path
     * @param callable|string $handler
     * @return void
     */
    public function get(string $path, $handler): void
    {
        $this->register('GET', $path, $handler);
    }
    
    /**
     * Register a POST route
     * 
     * @param string $path
     * @param callable|string $handler
     * @return void
     */
    public function post(string $path, $handler): void
    {
        $this->register('POST', $path, $handler);
    }
    
    /**
     * Register a route
     * 
     * @param string $method
     * @param string $path
     * @param callable|string $handler
     * @return void
     */
    protected function register(string $method, string $path, $handler): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
        ];
    }
    
    /**
     * Dispatch a request
     * 
     * @param string $method
     * @param string $uri
     * @return void
     */
    public function dispatch(string $method, string $uri): void
    {
        // Remove query string
        $path = parse_url($uri, PHP_URL_PATH);
        
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            
            if ($this->matches($route['path'], $path)) {
                $this->call($route['handler']);
                return;
            }
        }
        
        // Not found
        http_response_code(404);
        echo 'Not Found';
    }
    
    /**
     * Check if route path matches request path
     * 
     * @param string $pattern
     * @param string $path
     * @return bool
     */
    protected function matches(string $pattern, string $path): bool
    {
        // Direct match
        if ($pattern === $path) {
            return true;
        }
        
        // Convert pattern to regex
        $regex = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $pattern);
        $regex = "/^{$regex}$/";
        
        return preg_match($regex, $path) === 1;
    }
    
    /**
     * Call the handler
     * 
     * @param callable|string $handler
     * @return void
     */
    protected function call($handler): void
    {
        if (is_callable($handler)) {
            call_user_func($handler);
        } elseif (is_string($handler)) {
            include $handler;
        }
    }
}
