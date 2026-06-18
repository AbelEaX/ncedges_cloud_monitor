<?php
/**
 * BACKGROUND SERVICE CHECKER
 * Run this via CLI: php cron_check.php
 */
require __DIR__ . '/helpers.php';
$config = require __DIR__ . '/config.php';
$statusFile = __DIR__ . '/status.json';

// Prevent multiple instances from running simultaneously
$lockFile = fopen(__FILE__, 'r');
if (!flock($lockFile, LOCK_EX | LOCK_NB)) {
    echo "Check already in progress. Exiting.\n";
    exit;
}

date_default_timezone_set($config['timezone']);

$previousStatus = file_exists($statusFile) ? json_decode(file_get_contents($statusFile), true) : [];
$currentStatus = [];
$now = time();

echo "Starting Health Check at " . date('Y-m-d H:i:s') . "\n";

foreach ($config['servers'] as $server) {
    $key = md5($server['host'] . $server['port']);
    $res = checkServer($server['host'], $server['port'], $config['check_timeout']);
    
    $prev = $previousStatus[$key] ?? [
        'status' => 'unknown',
        'since' => $now,
        'alert_sent' => false,
        'history' => []
    ];
    
    $history = $prev['history'] ?? [];
    $status = $res['up'] ? 'up' : 'down';

    if ($status === 'up' && $prev['status'] === 'down') {
        // Recovered
        $history[] = ['status' => 'recovered', 'timestamp' => $now];
        echo "[-] {$server['name']} recovered.\n";
    } elseif ($status === 'down' && $prev['status'] !== 'down') {
        // New Failure
        $history[] = ['status' => 'down', 'timestamp' => $now];
        echo "[!] {$server['name']} is DOWN.\n";
    }

    // Limit history to 10 entries
    if (count($history) > 10) array_shift($history);

    $alertSent = $prev['alert_sent'];
    $since = ($status === $prev['status']) ? $prev['since'] : $now;

    // Alert Logic
    if ($status === 'down') {
        $downtime = $now - $since;
        if ($downtime >= $config['alert_after'] && !$alertSent) {
            sendAlert($config, 
                "🚨 Server DOWN: {$server['name']}", 
                "{$server['name']} ({$server['host']}) has been down for " . round($downtime/60) . " mins."
            );
            $alertSent = true;
        }
    } else {
        $alertSent = false;
    }

    $currentStatus[$key] = [
        'name' => $server['name'],
        'host' => $server['host'],
        'status' => $status,
        'latency' => $res['latency'],
        'since' => $since,
        'alert_sent' => $alertSent,
        'history' => $history,
        'last_check' => $now
    ];
}

file_put_contents($statusFile, json_encode($currentStatus, JSON_PRETTY_PRINT));
echo "Health check complete. Status updated.\n";