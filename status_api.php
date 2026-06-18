<?php
date_default_timezone_set('Africa/Kampala');
$config = require __DIR__ . '/config.php';
$statusFile = __DIR__ . '/status.json';

session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('HTTP/1.1 401 Unauthorized');
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if (!file_exists($statusFile)) {
    echo json_encode([]);
    exit;
}

$currentStatus = json_decode(file_get_contents($statusFile), true);

header('Content-Type: application/json');
echo json_encode([
    'timestamp' => date('Y-m-d H:i:s'),
    'servers'   => $currentStatus
]);
