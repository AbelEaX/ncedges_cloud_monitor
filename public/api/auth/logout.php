<?php
/**
 * API: User Logout
 */
require dirname(__DIR__, 3) . '/bootstrap/app.php';

use App\Presentation\Responses\ApiResponse;

$auth = app(\App\Infrastructure\Authentication\AuthenticationService::class);
$audit = app(\App\Infrastructure\Logging\AuditService::class);

if ($auth->isAuthenticated()) {
    $user = $auth->getUser();
    $audit->logLogout($user['id'], $user['username']);
}

$auth->logout();

// Clear session cookie completely
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Redirect to login
header('Location: /login');
exit;
