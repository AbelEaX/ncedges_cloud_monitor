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
echo view('auth.login');
