<?php
$data = ['username' => 'admin', 'password' => 'admin'];
$options = [
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/x-www-form-urlencoded',
        'content' => http_build_query($data)
    ]
];
$context = stream_context_create($options);
$result = file_get_contents('http://localhost:8000/api/auth/login', false, $context);
echo $result;
