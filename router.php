<?php
/**
 * Development Router for PHP Built-in Server
 * 
 * Routes requests to the public directory
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH));

// Serve static files directly
if (preg_match('/\.(?:css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$/', $uri)) {
    $file = __DIR__ . '/public' . $uri;
    if (file_exists($file)) {
        $mime_types = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon'
        ];
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (isset($mime_types[$ext])) {
            header('Content-Type: ' . $mime_types[$ext]);
        }
        readfile($file);
        return;
    }
}

// Route to public directory
$requestUri = $uri;

// Map routes to files
$routes = [
    '/' => '/index.php',
    '/login' => '/login.php',
    '/dashboard' => '/dashboard.php',
    '/servers' => '/servers.php',
    '/settings' => '/settings.php',
    '/reports' => '/reports.php',
];

// Check if route exists
if (isset($routes[$requestUri])) {
    require __DIR__ . '/public' . $routes[$requestUri];
    return;
}

// Check for API routes
if (strpos($requestUri, '/api/') === 0) {
    $apiFile = __DIR__ . '/public' . $requestUri . '.php';
    if (file_exists($apiFile)) {
        require $apiFile;
        return;
    }
}

// Try to serve the file directly from public directory
$file = __DIR__ . '/public' . $requestUri;
if (file_exists($file) && is_file($file)) {
    require $file;
    return;
}

// 404 - File not found
http_response_code(404);
echo "404 - Page not found: $requestUri";
