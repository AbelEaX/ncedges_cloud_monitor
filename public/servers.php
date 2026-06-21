<?php

/**
 * Server Management Page
 *
 * Displays the server management interface
 */

require __DIR__ . '/../bootstrap/app.php';

// Check authentication
$auth = app(\App\Infrastructure\Authentication\AuthenticationService::class);
if (!$auth->isAuthenticated()) {
    header('Location: /login.php');
    exit;
}

// Check permission
if (!$auth->hasPermission('server.view')) {
    header('HTTP/1.1 403 Forbidden');
    echo 'Access denied';
    exit;
}

// Log action
$audit = app(\App\Infrastructure\Logging\AuditService::class);
$audit->log('view', 'servers_page', null, $auth->user()->id, ['message' => 'Viewed servers page']);

// Render view
$servers = [];
try {
    $serverRepo = app(\App\Infrastructure\Repositories\ServerRepository::class);
    $servers = $serverRepo->findAll();
} catch (Exception $e) {
    // Database not available yet, use empty array
    $servers = [];
}

// Output the view
header('Content-Type: text/html; charset=utf-8');
include __DIR__ . '/../resources/views/servers/index.php';
