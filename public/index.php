<?php
/**
 * Public Index - Redirects to login or dashboard
 */
require __DIR__ . '/bootstrap/app.php';

$auth = app(\App\Infrastructure\Authentication\AuthenticationService::class);

if (!$auth->isAuthenticated()) {
    header('Location: /login');
    exit;
}

// Logged in - redirect to dashboard
header('Location: /dashboard');
exit;
