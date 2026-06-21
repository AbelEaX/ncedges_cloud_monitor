<?php
header('Content-Type: text/plain');
echo "PDO drivers: " . implode(', ', PDO::getAvailableDrivers()) . "\n";
echo "sqlite3 extension: " . (extension_loaded('sqlite3') ? 'yes' : 'no') . "\n";
echo "pdo_sqlite extension: " . (extension_loaded('pdo_sqlite') ? 'yes' : 'no') . "\n";
echo "using ini: " . php_ini_loaded_file() . "\n";
