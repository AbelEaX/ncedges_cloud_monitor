<?php
require 'bootstrap/app.php';
$db = app(\App\Infrastructure\Database\Connection::class);
$servers = $db->fetchAll('SELECT * FROM servers');
print_r($servers);
