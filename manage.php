<?php
session_start();
$config = require __DIR__ . '/config.php';
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$serversFile = __DIR__ . '/servers.json';
$statusFile = __DIR__ . '/status.json';
$servers = file_exists($serversFile) ? json_decode(file_get_contents($serversFile), true) : [];
$currentStatus = file_exists($statusFile) ? json_decode(file_get_contents($statusFile), true) : [];

$editIndex = isset($_GET['edit']) ? (int)$_GET['edit'] : -1;
$editServer = ($editIndex >= 0 && isset($servers[$editIndex])) ? $servers[$editIndex] : null;

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $name = trim($_POST['name'] ?? '');
        $host = trim($_POST['host'] ?? '');
        $port = (int)($_POST['port'] ?? 0);
        if ($name && filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME | FILTER_VALIDATE_IP) && $port > 0) {
            $servers[] = ['name' => $name, 'host' => $host, 'port' => $port];
            file_put_contents($serversFile, json_encode($servers, JSON_PRETTY_PRINT));
            $message = "Asset added successfully.";
        }
    } elseif (isset($_POST['update'])) {
        $index = (int)$_POST['index'];
        $name = trim($_POST['name'] ?? '');
        $host = trim($_POST['host'] ?? '');
        $port = (int)($_POST['port'] ?? 0);
        if (isset($servers[$index]) && $name && $host && $port) {
            $servers[$index] = ['name' => $name, 'host' => $host, 'port' => $port];
            file_put_contents($serversFile, json_encode($servers, JSON_PRETTY_PRINT));
            $message = "Asset updated successfully.";
        }
    } elseif (isset($_POST['delete'])) {
        $index = (int)$_POST['index'];
        if (isset($servers[$index])) {
            array_splice($servers, $index, 1);
            file_put_contents($serversFile, json_encode($servers, JSON_PRETTY_PRINT));
            $message = "Asset removed.";
        }
    } elseif (isset($_POST['trigger_check'])) {
        // Execute the cron_check logic immediately
        shell_exec('php ' . __DIR__ . '/cron_check.php');
        $message = "Health check executed manually.";
    }
    header('Location: manage.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Assets - <?= $config['company_name']; ?></title>
    <style>
        :root{--bg:#1a1a1a;--sidebar:#1a1a1a;--accent:#ffc107;--card:#282828;--border:#444444;--text:#e0e0e0;--red:#ef5350}
        body{margin:0;font:12px Inter,sans-serif;background:var(--bg);display:flex;height:100vh;overflow:hidden}
        .sidebar{width:180px;background:var(--sidebar);color:var(--text);padding:15px;flex-shrink:0;border-right:1px solid var(--border)}
        .sidebar h2{font-size:13px;color:var(--text);margin:0 0 15px 0;text-transform:uppercase;border-bottom:2px solid var(--accent);padding-bottom:5px}
        .nav-item{padding:8px 10px;border-radius:4px;color:var(--muted);text-decoration:none;display:block;font-size:11px}
        .nav-item.active{background:rgba(255,255,255,0.1);color:var(--text);font-weight:600}
        .main{flex:1;overflow-y:auto;padding:15px}
        .card{background:var(--card);border:1px solid var(--border);border-radius:4px;padding:12px;margin-bottom:15px;color:var(--text)}
        h1,h3{font-size:14px;margin:0 0 10px 0;color:var(--text)}
        table{width:100%;border-collapse:collapse;font-size:11px;color:var(--text)}
        th,td{text-align:left;padding:6px;border-bottom:1px solid var(--border)}
        th{font-size:10px;text-transform:uppercase;color:var(--text)}
        input{padding:6px;border:1px solid var(--border);border-radius:4px;margin-right:5px;font-size:11px;background:var(--bg);color:var(--text)}
        button{padding:6px 12px;border-radius:4px;border:none;cursor:pointer;font-weight:600;font-size:11px}
        .btn-add{background:var(--accent);color:#000}.btn-del{background:rgba(239,83,80,0.2);color:var(--red)}
        .btn-edit{background:var(--border);color:var(--text);text-decoration:none;padding:6px 10px;border-radius:4px;font-size:11px;font-weight:600;display:inline-block;margin-right:5px}
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Monitor Portal</h2>
        <a href="index.php" class="nav-item">Dashboard</a>
        <a href="manage.php" class="nav-item active">Manage Assets</a>
        <a href="reports.php" class="nav-item">Reports</a>
        <a href="settings.php" class="nav-item">Settings</a>
    </div>
    <div class="main">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <h1>Infrastructure Management</h1>
            <form method="post">
                <button type="submit" name="trigger_check" class="btn-add" style="background:var(--accent); color:var(--sidebar)">Run Health Checks Now</button>
            </form>
        </div>
        
        <div class="card">
            <h3><?= $editServer ? "Edit Asset: " . htmlspecialchars($editServer['name']) : "Add New Asset" ?></h3>
            <form method="post">
                <?php if ($editServer): ?>
                    <input type="hidden" name="index" value="<?= $editIndex ?>">
                <?php endif; ?>
                <input name="name" placeholder="Service Name" required value="<?= $editServer ? htmlspecialchars($editServer['name']) : '' ?>">
                <input name="host" placeholder="Host/IP" required value="<?= $editServer ? htmlspecialchars($editServer['host']) : '' ?>">
                <input name="port" type="number" placeholder="Port" required style="width:80px;" value="<?= $editServer ? $editServer['port'] : '' ?>">
                <?php if ($editServer): ?>
                    <button type="submit" name="update" class="btn-add">Update Asset</button>
                    <a href="manage.php" style="font-size:12px; margin-left:10px; color:var(--muted); text-decoration:none;">Cancel</a>
                <?php else: ?>
                    <button type="submit" name="add" class="btn-add">Add Asset</button>
                <?php endif; ?>
            </form>
        </div>
        <div class="card">
            <h3>Configured Assets</h3>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Endpoint</th>
                        <th>Monitoring Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($servers as $i => $s): 
                        $key = md5($s['host'] . $s['port']);
                        $missing = !isset($currentStatus[$key]);
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($s['name']); ?></strong></td>
                        <td><?= htmlspecialchars($s['host']) . ':' . $s['port']; ?></td>
                        <td>
                            <?php if($missing): ?>
                                <span style="color:var(--accent)">⚠️ Waiting for first health check...</span>
                            <?php else: ?>
                                <span style="color:#22c55e">✓ Active in logs</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="manage.php?edit=<?= $i ?>" class="btn-edit">Edit</a>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="index" value="<?= $i ?>">
                                <button type="submit" name="delete" class="btn-del">Remove</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>