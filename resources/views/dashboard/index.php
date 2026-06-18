<?php
/**
 * Dashboard View
 * 
 * Main monitoring dashboard
 */
$config = config('app');
$monitoring = config('monitoring');
$themeService = app(\App\Infrastructure\Logging\ThemeService::class);

// Get server data (placeholder)
$servers = [];
$totalServers = count($servers);
$onlineCount = 0;
foreach ($servers as $s) {
    if ($s['status'] === 'online') $onlineCount++;
}
$offlineCount = $totalServers - $onlineCount;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?= htmlspecialchars($config['name']); ?></title>
    <?= $themeService->getStyleTag(); ?>
    <style>
        :root {
            --bg: #1a1a1a;
            --sidebar: #1a1a1a;
            --accent: #ffc107;
            --card: #282828;
            --border: #444444;
            --text: #e0e0e0;
            --muted: #a0a0a0;
            --success: #66bb6a;
            --danger: #ef5350;
            --warning: #ffc107;
            --info: #29b6f6;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 12px;
            background: var(--bg);
            color: var(--text);
            display: flex;
            height: 100vh;
            overflow: hidden;
        }
        
        /* Header */
        .header {
            background: var(--card);
            padding: 15px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
        }
        
        .header h1 {
            font-size: 16px;
            margin: 0;
            color: var(--accent);
        }
        
        .header-right {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .logout-link {
            color: var(--danger);
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
        }
        
        /* Main Layout */
        .main-container {
            display: flex;
            flex: 1;
            width: 100%;
            overflow: hidden;
        }
        
        /* Sidebar */
        .sidebar {
            width: 200px;
            background: var(--sidebar);
            border-right: 1px solid var(--border);
            padding: 20px 15px;
            overflow-y: auto;
            flex-shrink: 0;
        }
        
        .sidebar h2 {
            font-size: 11px;
            text-transform: uppercase;
            color: var(--accent);
            margin-bottom: 12px;
            border-bottom: 2px solid var(--accent);
            padding-bottom: 8px;
            letter-spacing: 1px;
        }
        
        .nav-item {
            display: block;
            padding: 8px 12px;
            color: var(--muted);
            text-decoration: none;
            border-radius: 4px;
            margin-bottom: 4px;
            font-size: 12px;
            transition: all 0.2s;
        }
        
        .nav-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text);
        }
        
        .nav-item.active {
            background: rgba(255, 193, 7, 0.2);
            color: var(--accent);
            font-weight: 600;
        }
        
        .sidebar-stats {
            margin-top: auto;
            padding-top: 15px;
            border-top: 1px solid var(--border);
        }
        
        .stat {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 11px;
        }
        
        .stat-value {
            font-weight: 700;
            color: var(--accent);
        }
        
        /* Content */
        .content {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
        }
        
        .content-header {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .content-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--text);
        }
        
        /* Grid */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 16px;
            text-align: center;
        }
        
        .stat-card-number {
            font-size: 28px;
            font-weight: 700;
            color: var(--accent);
            margin-bottom: 4px;
        }
        
        .stat-card-label {
            font-size: 11px;
            color: var(--muted);
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1><?= htmlspecialchars($config['name']); ?></h1>
        <div class="header-right">
            <span style="font-size: 11px; color: var(--muted);">
                Refreshed: <span id="refresh-time"><?= date('H:i:s'); ?></span>
            </span>
            <a href="/api/auth/logout" class="logout-link">Logout</a>
        </div>
    </div>
    
    <!-- Main Container -->
    <div class="main-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>Navigation</h2>
            <a href="/dashboard" class="nav-item active">Dashboard</a>
            <a href="/servers" class="nav-item">Manage Servers</a>
            <a href="/reports" class="nav-item">Reports</a>
            <a href="/settings" class="nav-item">Settings</a>
            
            <div class="sidebar-stats">
                <h2>Statistics</h2>
                <div class="stat">
                    <span>Total Servers</span>
                    <span class="stat-value" id="stat-total"><?= $totalServers; ?></span>
                </div>
                <div class="stat">
                    <span>Online</span>
                    <span class="stat-value" style="color: var(--success);" id="stat-online"><?= $onlineCount; ?></span>
                </div>
                <div class="stat">
                    <span>Offline</span>
                    <span class="stat-value" style="color: var(--danger);" id="stat-offline"><?= $offlineCount; ?></span>
                </div>
            </div>
        </div>
        
        <!-- Content -->
        <div class="content">
            <div class="content-header">
                <div>
                    <h2 class="content-title">Infrastructure Overview</h2>
                    <p style="color: var(--muted); font-size: 11px; margin-top: 4px;">
                        <?= $totalServers; ?> servers monitored
                    </p>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-number"><?= $totalServers; ?></div>
                    <div class="stat-card-label">Total Assets</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-number" style="color: var(--success);"><?= $onlineCount; ?></div>
                    <div class="stat-card-label">Online</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-number" style="color: var(--danger);"><?= $offlineCount; ?></div>
                    <div class="stat-card-label">Offline</div>
                </div>
            </div>
            
            <!-- Servers Grid -->
            <?php if (empty($servers)): ?>
            <div style="
                background: var(--card);
                border: 1px solid var(--border);
                border-radius: 4px;
                padding: 40px;
                text-align: center;
                color: var(--muted);
            ">
                <p style="margin: 0; font-size: 14px;">No servers configured yet</p>
                <a href="/servers" style="color: var(--accent); text-decoration: none; font-weight: 600; margin-top: 12px; display: inline-block;">
                    Add Servers
                </a>
            </div>
            <?php else: ?>
            <div class="grid" id="servers-grid">
                <?php foreach ($servers as $server): ?>
                    <!-- Server cards will be rendered here -->
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Auto-refresh every 30 seconds
        <?php if (config('monitoring.refresh.interval')): ?>
        setInterval(() => {
            location.reload();
        }, <?= config('monitoring.refresh.interval') * 1000; ?>);
        <?php endif; ?>
        
        // Update refresh time
        setInterval(() => {
            const now = new Date();
            document.getElementById('refresh-time').textContent = 
                String(now.getHours()).padStart(2, '0') + ':' +
                String(now.getMinutes()).padStart(2, '0') + ':' +
                String(now.getSeconds()).padStart(2, '0');
        }, 1000);
    </script>
</body>
</html>
