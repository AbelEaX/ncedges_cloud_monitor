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
<html lang="<?= htmlspecialchars(config('app.locale', 'en')); ?>" data-theme="<?= htmlspecialchars($themeService->getCurrentTheme()); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?= htmlspecialchars($config['name']); ?></title>
    <?= $themeService->getStyleTag(); ?>
    <style>
        :root {
            --bg-color: var(--background, #f5f5f5);
            --surface-color: var(--surface, #ffffff);
            --border-color: var(--border, #e0e0e0);
            --text-color: var(--text, #333333);
            --primary-color: var(--primary, #ec1d63);
            --success-color: var(--success, #10b981);
            --danger-color: var(--danger, #ef4444);
            --warning-color: var(--warning, #f59e0b);
            --info-color: var(--info, #3b82f6);
            --muted-color: var(--muted, #666666);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: var(--font-family, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif);
            font-size: var(--base-size, 12px);
            line-height: var(--line-height, 1.5);
            background: var(--bg-color);
            color: var(--text-color);
        }
        
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        
        header {
            margin-bottom: 30px;
            background: var(--surface-color);
            padding: 24px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        h1 { font-size: 28px; }
        
        .content-header {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .content-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-color);
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
            background: var(--surface-color);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 24px;
            text-align: center;
        }
        
        .stat-card-number {
            font-size: 32px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 8px;
        }
        
        .stat-card-label {
            font-size: 12px;
            color: var(--muted-color);
            text-transform: uppercase;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <?= component('nav', ['user' => $user ?? null]) ?>
    
    <div class="container">
        <header>
            <div>
                <h1>Infrastructure Overview</h1>
                <p style="color: var(--muted-color); font-size: 14px; margin-top: 5px;">
                    <?= $totalServers; ?> servers monitored
                </p>
            </div>
            <div style="text-align: right; color: var(--muted-color); font-size: 13px;">
                Refreshed: <span id="refresh-time" style="font-family: monospace;"><?= date('H:i:s'); ?></span>
            </div>
        </header>
        
        <!-- Content -->
        <div class="content">
            
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
                background: var(--surface-color);
                border: 1px solid var(--border-color);
                border-radius: 8px;
                padding: 40px;
                text-align: center;
                color: var(--muted-color);
            ">
                <p style="margin: 0; font-size: 14px;">No servers configured yet</p>
                <a href="/servers" style="color: var(--primary-color); text-decoration: none; font-weight: 600; margin-top: 12px; display: inline-block;">
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
