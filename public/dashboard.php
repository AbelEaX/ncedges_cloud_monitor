<?php

/**
 * Dashboard & Navigation Hub
 *
 * Main entry point showing all available sections
 */

require __DIR__ . '/../bootstrap/app.php';

$auth = app(\App\Infrastructure\Authentication\AuthenticationService::class);
if (!$auth->isAuthenticated()) {
    header('Location: /login.php');
    exit;
}

$user = $auth->user();
$themeService = app(\App\Infrastructure\Logging\ThemeService::class);
$audit = app(\App\Infrastructure\Logging\AuditService::class);
$audit->log('view', 'dashboard', null, $user->getId(), ['message' => 'Viewed dashboard']);

// Fetch servers statistics dynamically
$totalServers = 0;
$onlineServers = 0;
$offlineServers = 0;
$alertsCount = 0;

try {
    $serverRepo = app(\App\Infrastructure\Repositories\ServerRepository::class);
    $servers = $serverRepo->findAll();
    $totalServers = count($servers);
    foreach ($servers as $server) {
        if ($server->status === 'online') {
            $onlineServers++;
        } elseif ($server->status === 'offline') {
            $offlineServers++;
        }
    }
} catch (\Exception $e) {
    // Fallback if repository fails
}

try {
    $connection = app(\App\Infrastructure\Database\Connection::class);
    $alertsCountRow = $connection->fetchOne("SELECT COUNT(*) as cnt FROM notifications");
    $alertsCount = $alertsCountRow ? (int) $alertsCountRow['cnt'] : 0;
} catch (\Exception $e) {
    // Fallback
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= htmlspecialchars($themeService->getCurrentTheme()); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Monitor</title>
    <?= $themeService->getStyleTag(); ?>
    <style>
        :root {
            --bg-color: var(--background, #f5f5f5);
            --surface-color: var(--surface, #ffffff);
            --border-color: var(--border, #e0e0e0);
            --text-color: var(--text, #333333);
            --primary-color: var(--primary, #3b82f6);
            --success-color: var(--success, #10b981);
            --danger-color: var(--danger, #ef4444);
            --warning-color: var(--warning, #f59e0b);
            --info-color: var(--info, #3b82f6);
            --muted-color: var(--muted, #6b7280);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: var(--font-family, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif);
            font-size: var(--base-size, 12px);
            line-height: var(--line-height, 1.5);
            background: var(--bg-color);
            color: var(--text-color);
        }
        .theme-toggle-btn { background: none; border: none; cursor: pointer; font-size: 18px; color: var(--text-color); margin-right: 15px; }
        [data-theme="dark"] .light-icon { display: inline !important; }
        [data-theme="dark"] .dark-icon { display: none !important; }
        [data-theme="light"] .dark-icon { display: inline !important; }
        [data-theme="light"] .light-icon { display: none !important; }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        .welcome {
            background: var(--surface-color);
            padding: 24px;
            border-radius: 8px;
            margin-bottom: 40px;
            border: 1px solid var(--border-color);
        }
        .welcome h1 {
            font-size: 32px;
            margin-bottom: 10px;
            color: var(--text-color);
        }
        .welcome p {
            color: var(--muted-color);
            font-size: 16px;
        }
        .sections {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .section-card {
            background: var(--surface-color);
            padding: 24px;
            border-radius: 8px;
            text-decoration: none;
            color: inherit;
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
            border-left: 4px solid var(--primary-color);
        }
        .section-card:hover {
            transform: translateY(-2px);
        }
        .section-card h2 {
            font-size: 20px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--text-color);
        }
        .section-card p {
            color: var(--muted-color);
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        .section-card ul {
            list-style: none;
            font-size: 13px;
            color: var(--muted-color);
            margin-bottom: 15px;
        }
        .section-card li {
            padding: 5px 0;
            padding-left: 20px;
            position: relative;
        }
        .section-card li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: var(--success-color);
        }
        .btn {
            display: inline-block;
            padding: 10px 16px;
            background: var(--primary-color);
            color: #000000;
            font-weight: bold;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            transition: background 0.3s ease;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            opacity: 0.9;
        }
        .dashboard-info {
            background: var(--surface-color);
            padding: 24px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }
        .dashboard-info h2 {
            font-size: 20px;
            color: var(--text-color);
            margin-bottom: 20px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        .info-box {
            background: var(--bg-color);
            padding: 24px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            border-left: 3px solid var(--primary-color);
        }
        .info-box h3 {
            font-size: 12px;
            color: var(--muted-color);
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        .info-box .value {
            font-size: 28px;
            font-weight: bold;
            color: var(--text-color);
        }
    </style>
</head>
<body>
    <?= component('nav', ['user' => $user]) ?>

    <div class="container">
        <div class="welcome">
            <h1>Welcome, <?php echo htmlspecialchars($user->getUsername() ?? 'Admin'); ?>! 👋</h1>
            <p>Monitor your cloud infrastructure in real-time</p>
        </div>

        <div class="dashboard-info" style="margin-bottom: 40px;">
            <h2>Quick Status</h2>
            <div class="info-grid">
                <div class="info-box">
                    <h3>Total Servers</h3>
                    <div class="value" id="val-total"><?php echo $totalServers; ?></div>
                </div>
                <div class="info-box">
                    <h3>Online</h3>
                    <div class="value" id="val-online" style="color: #10b981;"><?php echo $onlineServers; ?></div>
                </div>
                <div class="info-box">
                    <h3>Offline</h3>
                    <div class="value" id="val-offline" style="color: #ef4444;"><?php echo $offlineServers; ?></div>
                </div>
                <div class="info-box">
                    <h3>Alerts</h3>
                    <div class="value" id="val-alerts" style="color: #f59e0b;"><?php echo $alertsCount; ?></div>
                </div>
            </div>
        </div>

        <div class="sections">
            <!-- Servers Section -->
            <a href="/servers.php" class="section-card">
                <h2>🖥️ Server Management</h2>
                <p>Manage and monitor all your servers</p>
                <ul>
                    <li>View server status</li>
                    <li>Add/edit/delete servers</li>
                    <li>Track server metrics</li>
                </ul>
                <button class="btn">Manage Servers →</button>
            </a>

            <!-- Settings Section -->
            <?php if ($auth->hasPermission('settings.view')): ?>
            <a href="/settings.php" class="section-card">
                <h2>⚙️ Settings</h2>
                <p>Configure application settings</p>
                <ul>
                    <li>General settings</li>
                    <li>Email (SMTP) configuration</li>
                    <li>Notification preferences</li>
                </ul>
                <button class="btn">Configure Settings →</button>
            </a>
            <?php endif; ?>

            <!-- Reports Section -->
            <a href="/reports.php" class="section-card">
                <h2>📊 Reports & Analytics</h2>
                <p>View performance metrics and reports</p>
                <ul>
                    <li>Uptime statistics</li>
                    <li>Alert history</li>
                    <li>Export reports</li>
                </ul>
                <button class="btn">View Reports →</button>
            </a>
        </div>

        <div style="text-align: center; margin-top: 40px; color: #9ca3af; font-size: 13px;">
            <p>Monitor v1.0.0 | Built with Clean Architecture | Last updated: <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
    </div>
    <script>
        function toggleTheme() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', newTheme);
            document.cookie = "theme=" + newTheme + "; path=/; max-age=31536000";
        }
        
        let isRefreshing = false;
        function refreshDashboard() {
            if (isRefreshing) return;
            isRefreshing = true;
            
            // Add slight opacity to indicate loading
            document.querySelector('.info-grid').style.opacity = '0.7';
            
            fetch('/api/reports/metrics.php?range=7d')
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        document.getElementById('val-total').textContent = res.data.total_servers;
                        document.getElementById('val-online').textContent = res.data.online_servers;
                        document.getElementById('val-offline').textContent = res.data.offline_servers;
                        document.getElementById('val-alerts').textContent = res.data.alert_count;
                    }
                })
                .finally(() => {
                    document.querySelector('.info-grid').style.opacity = '1';
                    isRefreshing = false;
                });
        }
        
        setInterval(refreshDashboard, 15000);
    </script>
</body>
</html>
