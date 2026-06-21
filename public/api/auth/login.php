<?php
/**
 * API: User Login
 */
require __DIR__ . '/../../../bootstrap/app.php';

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

// Validate CSRF Token
$csrfToken = $_POST['csrf_token'] ?? '';
if (!$auth->validateCsrfToken($csrfToken)) {
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    $requestedWith = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
    $isJson = strpos($accept, 'application/json') !== false || strtolower($requestedWith) === 'xmlhttprequest';

    if ($isJson) {
        ApiResponse::error('Invalid CSRF token', [], 403);
    } else {
        header('Location: /login?error=' . urlencode('Invalid or expired session. Please try again.'));
        exit;
    }
}

if (!$auth->authenticate($username, $password)) {
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    $requestedWith = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
    $isJson = strpos($accept, 'application/json') !== false || strtolower($requestedWith) === 'xmlhttprequest';

    if ($isJson) {
        ApiResponse::error('Invalid credentials', [], 401);
    } else {
        header('Location: /login?error=' . urlencode('Invalid credentials'));
        exit;
    }
}

// Log the login
$audit = app(\App\Infrastructure\Logging\AuditService::class);
$user = $auth->getUser();
$audit->logLogin($user['id'], $user['username']);

// Redirect to dashboard
header('Location: /dashboard');
exit;
