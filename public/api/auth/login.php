<?php
/**
 * API: User Login
 */
require __DIR__ . '/../../bootstrap/app.php';

use App\Presentation\Responses\ApiResponse;

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ApiResponse::error('Method not allowed', [], 405);
}

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    ApiResponse::validationError([
        'username' => !$username ? 'Username is required' : null,
        'password' => !$password ? 'Password is required' : null,
    ]);
}

$auth = app(\App\Infrastructure\Authentication\AuthenticationService::class);

if (!$auth->authenticate($username, $password)) {
    ApiResponse::error('Invalid credentials', [], 401);
}

// Log the login
$audit = app(\App\Infrastructure\Logging\AuditService::class);
$user = $auth->getUser();
$audit->logLogin($user['id'], $user['username']);

// Redirect to dashboard
header('Location: /dashboard');
exit;
