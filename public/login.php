<?php
/**
 * Login Page
 */
require __DIR__ . '/../bootstrap/app.php';

$auth = app(\App\Infrastructure\Authentication\AuthenticationService::class);

if ($auth->isAuthenticated()) {
    header('Location: /dashboard');
    exit;
}

// Display login form
try {
    echo view('auth.login');
} catch (Exception $e) {
    // Fallback if view fails
    echo '<h1>Login</h1><p>Error loading view: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
