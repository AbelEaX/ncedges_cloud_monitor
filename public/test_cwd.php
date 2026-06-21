<?php
require __DIR__ . '/../bootstrap/app.php';
header('Content-Type: text/plain');
echo "getcwd(): " . getcwd() . "\n";
echo "DB_DATABASE: " . getenv('DB_DATABASE') . "\n";
echo "resolved database: " . realpath(getenv('DB_DATABASE')) . "\n";
echo "monitor.db exists in cwd: " . (file_exists('monitor.db') ? 'yes' : 'no') . "\n";
echo "monitor.db size: " . (file_exists(getenv('DB_DATABASE')) ? filesize(getenv('DB_DATABASE')) : 0) . "\n";
