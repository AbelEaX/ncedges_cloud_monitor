<?php
$defaults = [
    'company_name' => 'Nova Cloud Hosting',
    'alert_email'  => 'webadmin@ncedges.com',
    'email_from'   => 'webadmin@ncedges.com',
    'check_timeout'=> 3,
    'alert_after'  => 300,
    'timezone'     => 'Africa/Kampala',
    'auth' => [
        'username' => 'admin',
        'password' => 'admin'
    ],
    'smtp' => [
        'host'     => 'mail-gw.ncedges.com',
        'port'     => 465,
        'username' => 'webadmin@ncedges.com',
        'password' => 'User@2026@',
        'secure'   => 'ssl'
    ],
    'servers' => [
        [
            'name' => 'Web Server 1',
            'host' => '10.10.1.2',
            'port' => 443
        ],
        [
            'name' => 'Nova Mail Server',
            'host' => '10.10.1.3',
            'port' => 6071
        ],
        [
            'name' => 'Nova Billing System',
            'host' => '10.10.1.4',
            'port' => 5067
        ],
        [
            'name' => 'London Backup',
            'host' => 'public-1.ncedges.com',
            'port' => 443
        ],
        [
            'name' => 'Document Server',
            'host' => 'cloud.ncedges.com',
            'port' => 443
        ],
        [
            'name' => 'Voice Mail Gateway',
            'host' => '10.10.1.7',
            'port' => 8090
        ],
        [
            'name' => 'Unifi Controller',
            'host' => '10.10.1.7',
            'port' => 8443
        ]
    ]
];

// Load dynamic settings if they exist
$settingsFile = __DIR__ . '/settings.json';
if (file_exists($settingsFile)) {
    $saved = json_decode(file_get_contents($settingsFile), true);
    if ($saved) {
        // Merge saved settings into defaults
        foreach ($saved as $key => $val) {
            if (isset($defaults[$key])) $defaults[$key] = $val;
        }
    }
}
return $defaults;
