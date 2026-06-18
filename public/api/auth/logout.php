<?php
/**
 * API: User Logout
 */
require __DIR__ . '/../../bootstrap/app.php';

use App\Presentation\Responses\ApiResponse;

$auth = app(\App\Infrastructure\Authentication\AuthenticationService::class);
$audit = app(\App\Infrastructure\Logging\AuditService::class);

if ($auth->isAuthenticated()) {
    $user = $auth->getUser();
    $audit->logLogout($user['id'], $user['username']);
}

$auth->logout();

// Redirect to login
header('Location: /login');
exit;
