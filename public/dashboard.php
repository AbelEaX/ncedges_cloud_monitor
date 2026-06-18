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
$audit = app(\App\Infrastructure\Logging\AuditService::class);
$audit->log('view', 'dashboard', null, $user->getId(), 'Viewed dashboard');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Monitor</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f5f5f5;
            color: #333;
        }
        nav {
            background: white;
            padding: 0 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
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
            color: #3b82f6;
        }
        .nav-links {
            display: flex;
            gap: 30px;
            align-items: center;
        }
        .nav-links a {
            text-decoration: none;
            color: #6b7280;
            font-size: 14px;
            transition: color 0.3s ease;
        }
        .nav-links a:hover {
            color: #3b82f6;
        }
        .nav-user {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #3b82f6;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        .logout-btn {
            background: none;
            border: none;
            color: #ef4444;
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
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 40px;
        }
        .welcome h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        .welcome p {
            color: #6b7280;
            font-size: 16px;
        }
        .sections {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .section-card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            text-decoration: none;
            color: inherit;
            transition: all 0.3s ease;
            border-left: 4px solid #3b82f6;
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
        }
        .section-card p {
            color: #6b7280;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        .section-card ul {
            list-style: none;
            font-size: 13px;
            color: #6b7280;
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
            color: #10b981;
        }
        .btn {
            display: inline-block;
            padding: 10px 16px;
            background: #3b82f6;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            transition: background 0.3s ease;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            background: #2563eb;
        }
        .dashboard-info {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .dashboard-info h2 {
            font-size: 20px;
            margin-bottom: 20px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        .info-box {
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            border-left: 3px solid #3b82f6;
        }
        .info-box h3 {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        .info-box .value {
            font-size: 28px;
            font-weight: bold;
            color: #1f2937;
        }
    </style>
</head>
<body>
    <nav>
        <div class="nav-content">
            <div class="nav-logo">📡 Monitor</div>
            <div class="nav-links">
                <a href="/dashboard.php">Dashboard</a>
                <a href="/servers.php">Servers</a>
                <a href="/settings.php">Settings</a>
                <a href="/reports.php">Reports</a>
            </div>
            <div class="nav-user">
                <div class="user-avatar"><?php echo strtoupper(substr($user->getFirstName() ?? 'U', 0, 1)); ?></div>
                <div style="font-size: 14px;">
                    <div style="font-weight: 500;"><?php echo htmlspecialchars(($user->getFirstName() ?? 'User') . ' ' . ($user->getLastName() ?? '')); ?></div>
                    <div style="color: #6b7280; font-size: 12px;"><?php echo ucfirst($user->getRole()); ?></div>
                </div>
                <form action="/logout.php" method="POST" style="margin: 0;">
                    <button type="submit" class="logout-btn">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="welcome">
            <h1>Welcome, <?php echo htmlspecialchars($user->getFirstName() ?? 'Admin'); ?>! 👋</h1>
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
                    <div class="value">0</div>
                </div>
                <div class="info-box">
                    <h3>Online</h3>
                    <div class="value" style="color: #10b981;">0</div>
                </div>
                <div class="info-box">
                    <h3>Offline</h3>
                    <div class="value" style="color: #ef4444;">0</div>
                </div>
                <div class="info-box">
                    <h3>Alerts</h3>
                    <div class="value" style="color: #f59e0b;">0</div>
                </div>
            </div>
        </div>

        <div style="text-align: center; margin-top: 40px; color: #9ca3af; font-size: 13px;">
            <p>Monitor v1.0.0 | Built with Clean Architecture | Last updated: <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
    </div>
</body>
</html>
