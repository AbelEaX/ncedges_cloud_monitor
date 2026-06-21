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
<html lang="en">
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
        nav {
            background: var(--surface-color);
            padding: 0 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 1px solid var(--border-color);
        }
        .nav-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 60px;
        }
        .nav-logo {
            font-size: 20px;
            font-weight: bold;
            color: var(--primary-color);
        }
        .nav-links {
            display: flex;
            gap: 30px;
            align-items: center;
        }
        .nav-links a {
            text-decoration: none;
            color: var(--muted-color);
            font-size: 14px;
            transition: color 0.3s ease;
        }
        .nav-links a:hover {
            color: var(--primary-color);
        }
        .nav-user {
            display: flex;
            align-items: center;
            gap: 15px;
            color: var(--text-color);
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-color);
            color: #000000;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        .logout-btn {
            background: none;
            border: none;
            color: var(--danger-color);
            cursor: pointer;
            font-size: 14px;
            text-decoration: underline;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        .welcome {
            background: var(--surface-color);
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
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
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            text-decoration: none;
            color: inherit;
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
            border-left: 4px solid var(--primary-color);
        }
        .section-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
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
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
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
            padding: 20px;
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
    <nav>
        <div class="nav-content">
            <div class="nav-logo">📡 Monitor</div>
            <div class="nav-links">
                <a href="/dashboard">Dashboard</a>
                <a href="/servers">Servers</a>
                <a href="/settings">Settings</a>
                <a href="/reports">Reports</a>
            </div>
            <div class="nav-user">
                <div class="user-avatar"><?php echo strtoupper(substr($user->getUsername() ?? 'U', 0, 1)); ?></div>
                <div style="font-size: 14px;">
                    <div style="font-weight: 500;"><?php echo htmlspecialchars($user->getUsername() ?? 'User'); ?></div>
                    <div style="color: var(--muted-color); font-size: 12px;"><?php echo ucfirst($user->getRole()); ?></div>
                </div>
                <form action="/api/auth/logout" method="POST" style="margin: 0;">
                    <button type="submit" class="logout-btn">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="welcome">
            <h1>Welcome, <?php echo htmlspecialchars($user->getUsername() ?? 'Admin'); ?>! 👋</h1>
            <p>Monitor your cloud infrastructure in real-time</p>
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

        <div class="dashboard-info">
            <h2>Quick Status</h2>
            <div class="info-grid">
                <div class="info-box">
                    <h3>Total Servers</h3>
                    <div class="value"><?php echo $totalServers; ?></div>
                </div>
                <div class="info-box">
                    <h3>Online</h3>
                    <div class="value" style="color: #10b981;"><?php echo $onlineServers; ?></div>
                </div>
                <div class="info-box">
                    <h3>Offline</h3>
                    <div class="value" style="color: #ef4444;"><?php echo $offlineServers; ?></div>
                </div>
                <div class="info-box">
                    <h3>Alerts</h3>
                    <div class="value" style="color: #f59e0b;"><?php echo $alertsCount; ?></div>
                </div>
            </div>
        </div>

        <div style="text-align: center; margin-top: 40px; color: #9ca3af; font-size: 13px;">
            <p>Monitor v1.0.0 | Built with Clean Architecture | Last updated: <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
    </div>
</body>
</html>
