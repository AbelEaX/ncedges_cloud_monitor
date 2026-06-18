<?php
session_start();
require __DIR__ . '/helpers.php';
$config = require __DIR__ . '/config.php';
if (!isset($_SESSION['logged_in'])) { header('Location: login.php'); exit; }

$statusFile = __DIR__ . '/status.json';
$currentStatus = file_exists($statusFile) ? json_decode(file_get_contents($statusFile), true) : [];
$servers = $config['servers'];

// Flatten history for global report
$allIncidents = [];
foreach ($currentStatus as $srv) {
    if (!empty($srv['history'])) {
        foreach ($srv['history'] as $event) {
            $allIncidents[] = [
                'name' => $srv['name'],
                'status' => $event['status'],
                'timestamp' => $event['timestamp']
            ];
        }
    }
}
usort($allIncidents, function($a, $b) { return $b['timestamp'] - $a['timestamp']; });
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports - <?= $config['company_name']; ?></title>
    <style>
        :root{--bg:#000000;--sidebar:#000000;--accent:#eaaa00;--card:#111111;--border:#27272a;--text:#ffffff;--grn:#4ade80;--red:#f87171}
        body{margin:0;font:12px Inter,sans-serif;background:var(--bg);display:flex;height:100vh;overflow:hidden}
        .sidebar{width:180px;background:var(--sidebar);color:var(--text);padding:15px;flex-shrink:0;border-right:1px solid var(--border)}
        .sidebar h2{font-size:13px;color:var(--text);margin:0 0 15px 0;text-transform:uppercase;border-bottom:2px solid var(--accent);padding-bottom:5px}
        .nav-item{padding:8px 10px;border-radius:4px;color:var(--muted);text-decoration:none;display:block;font-size:11px}
        .nav-item.active{background:rgba(255,255,255,.1);color:var(--text);font-weight:600}
        .main{flex:1;overflow-y:auto;padding:15px}
        .card{background:var(--card);border:1px solid var(--border);border-radius:4px;padding:12px;margin-bottom:15px;color:var(--text)}
        h1,h3{font-size:14px;margin:0 0 10px 0;color:var(--text)}
        table{width:100%;border-collapse:collapse;font-size:11px;color:var(--text)}
        th,td{text-align:left;padding:6px;border-bottom:1px solid var(--border)}
        th{font-size:10px;text-transform:uppercase;color:var(--sidebar)}
        [class^=status-]{font-weight:700}
        .status-down{color:var(--red)}.status-up,.status-recovered{color:var(--grn)}.status-dormant{color:var(--accent)}
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Monitor Portal</h2>
        <a href="index.php" class="nav-item">Dashboard</a>
        <a href="manage.php" class="nav-item">Manage Assets</a>
        <a href="reports.php" class="nav-item active">Reports</a>
        <a href="settings.php" class="nav-item">Settings</a>
    </div>
    <div class="main">
        <h1>Portal Reports</h1>

        <div class="card">
            <h3>Live Asset Inventory</h3>
            <table>
                <thead>
                    <tr>
                        <th>Asset Name</th>
                        <th>Endpoint</th>
                        <th>Status</th>
                        <th>Last Checked</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($servers as $s): 
                        $key = md5($s['host'] . $s['port']);
                        $statusData = $currentStatus[$key] ?? null;
                        $status = $statusData ? $statusData['status'] : 'dormant';
                        $label = ($status === 'up') ? 'ACTIVE' : strtoupper($status);
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($s['name']); ?></td>
                        <td><?= htmlspecialchars($s['host']) . ':' . $s['port']; ?></td>
                        <td class="status-<?= $status; ?>"><?= $label; ?></td>
                        <td><?= isset($statusData['last_check']) ? date('Y-m-d H:i', $statusData['last_check']) : 'Never'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="card">
            <h3>Incident History Log</h3>
            <table>
                <thead><tr><th>Time</th><th>Asset Name</th><th>Event</th></tr></thead>
                <tbody>
                    <?php foreach($allIncidents as $i): ?>
                    <tr>
                        <td><?= date('Y-m-d H:i:s', $i['timestamp']); ?></td>
                        <td><?= htmlspecialchars($i['name']); ?></td>
                        <td class="status-<?= $i['status']; ?>"><?= strtoupper($i['status']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($allIncidents)): ?><tr><td colspan="3">No incidents recorded.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>