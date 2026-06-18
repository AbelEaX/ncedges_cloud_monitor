<?php
session_start();
$config = require __DIR__ . '/config.php';
if (!isset($_SESSION['logged_in'])) { header('Location: login.php'); exit; }

$settingsFile = __DIR__ . '/settings.json';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newSettings = [
        'company_name' => $_POST['company_name'],
        'alert_email' => $_POST['alert_email'],
        'check_timeout' => (int)$_POST['timeout'],
        'alert_after' => (int)$_POST['alert_after'] * 60, // Convert minutes to seconds
        'smtp' => [
            'host' => $_POST['smtp_host'],
            'port' => (int)$_POST['smtp_port'],
            'username' => $_POST['smtp_user'],
            'password' => $_POST['smtp_pass'],
            'secure' => $_POST['smtp_secure']
        ]
    ];
    file_put_contents($settingsFile, json_encode($newSettings, JSON_PRETTY_PRINT));
    $message = "Settings updated successfully!";
    $config = require __DIR__ . '/config.php'; // Reload
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Settings - <?= $config['company_name']; ?></title>
    <style>
        :root{--bg:#1a1a1a;--sidebar:#1a1a1a;--accent:#ffc107;--card:#282828;--border:#444444;--text:#e0e0e0}
        body{margin:0;font:12px Inter,sans-serif;background:var(--bg);display:flex;height:100vh;overflow:hidden}
        .sidebar{width:180px;background:var(--sidebar);color:var(--text);padding:15px;flex-shrink:0;border-right:1px solid var(--border)}
        .sidebar h2{font-size:13px;color:var(--text);margin:0 0 15px 0;text-transform:uppercase;border-bottom:2px solid var(--accent);padding-bottom:5px}
        .nav-item{padding:8px 10px;border-radius:4px;color:var(--muted);text-decoration:none;display:block;font-size:11px}
        .nav-item.active{background:rgba(255,255,255,0.1);color:var(--text);font-weight:600}
        .main{flex:1;overflow-y:auto;padding:15px}
        .card{background:var(--card);border:1px solid var(--border);border-radius:4px;padding:15px;max-width:500px;margin-bottom:15px;color:var(--text)}
        .form-group{margin-bottom:12px}
        label{display:block;font-size:10px;font-weight:700;margin-bottom:4px;color:var(--accent);text-transform:uppercase}
        input,select{width:100%;padding:8px;border:1px solid var(--border);border-radius:4px;box-sizing:border-box;font-size:11px;background:var(--bg);color:var(--text)}
        button{background:var(--accent);color:#000;padding:8px 16px;border:none;border-radius:4px;cursor:pointer;font-weight:700;font-size:11px}
        .msg{background:#e6f2e6;color:#15803d;padding:8px;border-radius:4px;margin-bottom:12px;font-size:11px}
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Monitor Portal</h2>
        <a href="index.php" class="nav-item">Dashboard</a>
        <a href="manage.php" class="nav-item">Manage Assets</a>
        <a href="reports.php" class="nav-item">Reports</a>
        <a href="settings.php" class="nav-item active">Settings</a>
    </div>
    <div class="main">
        <h1 style="font-size:16px; margin:0 0 15px 0;">System Settings</h1>
        <?php if($message): ?><div class="msg"><?= $message; ?></div><?php endif; ?>
        <div class="card">
            <form method="post">
                <div class="form-group">
                    <label>Company Name</label>
                    <input name="company_name" value="<?= htmlspecialchars($config['company_name']); ?>">
                </div>
                <div class="form-group">
                    <label>Alert Recipient Email</label>
                    <input name="alert_email" value="<?= htmlspecialchars($config['alert_email']); ?>">
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div class="form-group">
                        <label>Check Timeout (Sec)</label>
                        <input name="timeout" type="number" value="<?= $config['check_timeout']; ?>">
                    </div>
                    <div class="form-group">
                        <label>Alert Delay (Mins)</label>
                        <input name="alert_after" type="number" value="<?= $config['alert_after'] / 60; ?>">
                    </div>
                </div>
                <hr style="margin:15px 0; border:0; border-top:1px solid #eee;">
                <h3 style="font-size:12px; margin-bottom:10px;">SMTP Configuration</h3>
                <div class="form-group">
                    <label>SMTP Host</label>
                    <input name="smtp_host" value="<?= htmlspecialchars($config['smtp']['host']); ?>">
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                    <div class="form-group"><label>SMTP Port</label><input name="smtp_port" type="number" value="<?= $config['smtp']['port']; ?>"></div>
                    <div class="form-group"><label>Encryption</label>
                        <select name="smtp_secure">
                            <option value="ssl" <?= $config['smtp']['secure']=='ssl'?'selected':''; ?>>SSL</option>
                            <option value="tls" <?= $config['smtp']['secure']=='tls'?'selected':''; ?>>TLS</option>
                        </select>
                    </div>
                </div>
                <div class="form-group"><label>SMTP Username</label><input name="smtp_user" value="<?= htmlspecialchars($config['smtp']['username']); ?>"></div>
                <div class="form-group"><label>SMTP Password</label><input name="smtp_pass" type="password" value="<?= htmlspecialchars($config['smtp']['password']); ?>"></div>
                <button type="submit">Save Changes</button>
            </form>
        </div>
    </div>
</body>
</html>