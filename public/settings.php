<?php

/**
 * Settings Management Page
 */

require __DIR__ . '/../bootstrap/app.php';

$auth = app(\App\Infrastructure\Authentication\AuthenticationService::class);
if (!$auth->isAuthenticated()) {
    header('Location: /login.php');
    exit;
}

if (!$auth->hasPermission('settings.view')) {
    header('HTTP/1.1 403 Forbidden');
    echo 'Access denied';
    exit;
}

$audit = app(\App\Infrastructure\Logging\AuditService::class);
$audit->log('view', 'settings_page', null, $auth->user()->id, ['message' => 'Viewed settings page']);

header('Content-Type: text/html; charset=utf-8');
include __DIR__ . '/../resources/views/settings/index.php';
