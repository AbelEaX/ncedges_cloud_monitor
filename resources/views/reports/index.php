<?php
/**
 * Reports and Analytics View
 */
$themeService = app(\App\Infrastructure\Logging\ThemeService::class);
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars(config('app.locale', 'en')); ?>" data-theme="<?= htmlspecialchars($themeService->getCurrentTheme()); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analytics - <?= htmlspecialchars(config('app.name', 'Monitor')); ?></title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            font-family: var(--font-family, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif);
            background: var(--bg-color);
            color: var(--text-color);
        }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        header {
            margin-bottom: 30px;
            background: var(--surface-color);
            padding: 24px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid var(--border-color);
        }
        h1 { font-size: 28px; }
        .filters {
            display: flex;
            gap: 10px;
        }
        .btn {
            padding: 10px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
            font-weight: bold;
        }
        .btn-primary {
            background: var(--primary-color);
            color: #000000;
        }
        .btn-primary:hover {
            opacity: 0.9;
        }
        .btn-secondary {
            background: var(--muted-color);
            color: white;
        }
        .btn-secondary:hover {
            opacity: 0.9;
        }
        select {
            padding: 8px 12px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 14px;
            background: var(--bg-color);
            color: var(--text-color);
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .card {
            background: var(--surface-color);
            padding: 24px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }
        .card h3 {
            font-size: 12px;
            color: var(--muted-color);
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        .card-value {
            font-size: 32px;
            font-weight: bold;
            color: var(--text-color);
            margin-bottom: 5px;
        }
        .card-change {
            font-size: 12px;
        }
        .change-positive {
            color: #10b981;
        }
        .change-negative {
            color: #ef4444;
        }
        .chart-container {
            background: var(--surface-color);
            padding: 24px;
            border-radius: 8px;
            margin-bottom: 30px;
            border: 1px solid var(--border-color);
        }
        .chart-container h2 {
            font-size: 18px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
        }
        .chart-placeholder {
            background: var(--bg-color);
            border: 2px dashed var(--border-color);
            border-radius: 4px;
            padding: 40px;
            text-align: center;
            color: var(--muted-color);
        }
        .table-section {
            background: var(--surface-color);
            padding: 24px;
            border-radius: 8px;
            margin-bottom: 30px;
            border: 1px solid var(--border-color);
        }
        .table-section h2 {
            font-size: 18px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: var(--bg-color);
            padding: 12px;
            text-align: left;
            font-weight: 600;
            border-bottom: 1px solid var(--border-color);
            font-size: 13px;
            color: var(--text-color);
        }
        td {
            padding: 12px;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-color);
        }
        tr:hover td {
            background: rgba(100, 100, 100, 0.05);
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }
        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }
        .badge-error {
            background: #fee2e2;
            color: #7f1d1d;
        }
        .status-bar {
            display: flex;
            height: 8px;
            border-radius: 4px;
            overflow: hidden;
            background: #e5e7eb;
        }
        .status-bar-item {
            height: 100%;
        }
        .online { background: #10b981; }
        .offline { background: #ef4444; }
        .warning { background: #f59e0b; }
        .empty-state {
            text-align: center;
            padding: 40px;
            color: var(--muted-color);
        }
        .theme-toggle-btn { background: none; border: none; cursor: pointer; font-size: 18px; color: var(--text-color); margin-right: 15px; }
        [data-theme="dark"] .light-icon { display: inline !important; }
        [data-theme="dark"] .dark-icon { display: none !important; }
        [data-theme="light"] .dark-icon { display: inline !important; }
        [data-theme="light"] .light-icon { display: none !important; }
    </style>
</head>
<body>
    <?= component('nav', ['user' => $user ?? null]) ?>
    <div class="container">
        <header>
            <div style="display: flex; align-items: center; gap: 15px;">
                <div>
                    <h1>Reports & Analytics</h1>
                    <p style="color: var(--muted-color); margin-top: 5px;">Monitor performance and view detailed analytics</p>
                </div>
            </div>
            <div class="filters">
                <select id="timeRange" onchange="updateReports()">
                    <option value="24h">Last 24 Hours</option>
                    <option value="7d" selected>Last 7 Days</option>
                    <option value="30d">Last 30 Days</option>
                    <option value="90d">Last 90 Days</option>
                </select>
                <button class="btn btn-primary" onclick="exportReport('pdf')">📥 Export PDF</button>
                <button class="btn btn-secondary" onclick="exportReport('csv')">📥 Export CSV</button>
            </div>
        </header>

        <!-- Key Metrics -->
        <div class="grid">
            <div class="card">
                <h3>Total Servers</h3>
                <div class="card-value" id="totalServers">-</div>
                <div class="card-change">Monitored</div>
            </div>
            <div class="card">
                <h3>Online Servers</h3>
                <div class="card-value" id="onlineServers">-</div>
                <div class="card-change change-positive">All systems operational</div>
            </div>
            <div class="card">
                <h3>Average Uptime</h3>
                <div class="card-value" id="avgUptime">-</div>
                <div class="card-change">Last 7 days</div>
            </div>
            <div class="card">
                <h3>Alerts</h3>
                <div class="card-value" id="alertCount">-</div>
                <div class="card-change">This period</div>
            </div>
        </div>

        <!-- Server Status Overview Chart -->
        <div class="chart-container">
            <h2>Server Status Overview</h2>
            <div class="chart-placeholder">
                <div id="statusChart" style="min-height: 300px; display: flex; align-items: center; justify-content: center;">
                    <p>Loading chart...</p>
                </div>
            </div>
        </div>

        <!-- Uptime Statistics -->
        <div class="table-section">
            <h2>Server Uptime Statistics</h2>
            <table id="uptimeTable">
                <thead>
                    <tr>
                        <th>Server</th>
                        <th>Last 24h</th>
                        <th>Last 7d</th>
                        <th>Last 30d</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="uptimeTableBody">
                    <tr>
                        <td colspan="5" class="empty-state">
                            <p>Loading uptime data...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Alert History -->
        <div class="table-section">
            <h2>Recent Alerts</h2>
            <table id="alertsTable">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Server</th>
                        <th>Alert Type</th>
                        <th>Message</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="alertsTableBody">
                    <tr>
                        <td colspan="5" class="empty-state">
                            <p>Loading alerts...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Activity Timeline -->
        <?php if (config('app.features.activity_timeline_enabled', true)): ?>
        <div class="table-section">
            <h2>Activity Timeline</h2>
            <table id="activityTable">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody id="activityTableBody">
                    <tr>
                        <td colspan="4" class="empty-state">
                            <p>Loading activity timeline...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <script>
        // Load reports on page load
        document.addEventListener('DOMContentLoaded', updateReports);

        function updateReports() {
            const timeRange = document.getElementById('timeRange').value;

            // Load metrics
            Promise.all([
                fetch(`/api/reports/metrics.php?range=${timeRange}`).then(r => r.json()),
                fetch(`/api/reports/uptime.php?range=${timeRange}`).then(r => r.json()),
                fetch(`/api/reports/alerts.php?range=${timeRange}`).then(r => r.json()),
                fetch(`/api/reports/activity.php?range=${timeRange}`).then(r => r.json()),
            ])
            .then(([metrics, uptime, alerts, activity]) => {
                if (metrics.success) displayMetrics(metrics.data);
                if (uptime.success) displayUptime(uptime.data);
                if (alerts.success) displayAlerts(alerts.data);
                if (activity.success) displayActivity(activity.data);
                
                if (metrics.success) renderChart(metrics.data);
            })
            .catch(error => console.error('Error loading reports:', error));
        }
        
        let statusChartInstance = null;
        
        function renderChart(metrics) {
            const container = document.getElementById('statusChart');
            if (!document.getElementById('statusChartCanvas')) {
                container.innerHTML = '<canvas id="statusChartCanvas" style="max-height: 300px; width: 100%;"></canvas>';
            }
            const ctx = document.getElementById('statusChartCanvas').getContext('2d');
            
            if (statusChartInstance) {
                statusChartInstance.destroy();
            }
            
            const html = document.documentElement;
            const isDark = html.getAttribute('data-theme') === 'dark';
            const textColor = isDark ? '#e0e0e0' : '#333333';
            
            statusChartInstance = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Online', 'Offline'],
                    datasets: [{
                        data: [metrics.online_servers, metrics.offline_servers],
                        backgroundColor: ['#10b981', '#ef4444'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { 
                            position: 'bottom',
                            labels: { color: textColor }
                        }
                    }
                }
            });
        }

        function displayMetrics(data) {
            document.getElementById('totalServers').textContent = data.total_servers || 0;
            document.getElementById('onlineServers').textContent = data.online_servers || 0;
            document.getElementById('avgUptime').textContent = (data.avg_uptime || 0).toFixed(2) + '%';
            document.getElementById('alertCount').textContent = data.alert_count || 0;
        }

        function displayUptime(data) {
            const tbody = document.getElementById('uptimeTableBody');
            if (!data || data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="empty-state">No data available</td></tr>';
                return;
            }

            tbody.innerHTML = data.map(row => `
                <tr>
                    <td><strong>${row.server_name}</strong></td>
                    <td>
                        <div class="status-bar">
                            <div class="status-bar-item online" style="width: ${row.uptime_24h}%"></div>
                            <div class="status-bar-item offline" style="width: ${100 - row.uptime_24h}%"></div>
                        </div>
                        ${row.uptime_24h.toFixed(2)}%
                    </td>
                    <td>
                        <div class="status-bar">
                            <div class="status-bar-item online" style="width: ${row.uptime_7d}%"></div>
                            <div class="status-bar-item offline" style="width: ${100 - row.uptime_7d}%"></div>
                        </div>
                        ${row.uptime_7d.toFixed(2)}%
                    </td>
                    <td>
                        <div class="status-bar">
                            <div class="status-bar-item online" style="width: ${row.uptime_30d}%"></div>
                            <div class="status-bar-item offline" style="width: ${100 - row.uptime_30d}%"></div>
                        </div>
                        ${row.uptime_30d.toFixed(2)}%
                    </td>
                    <td><span class="badge badge-${row.current_status === 'online' ? 'success' : 'error'}">${row.current_status === 'online' ? 'Online' : 'Offline'}</span></td>
                </tr>
            `).join('');
        }

        function timeAgo(dateParam) {
            if (!dateParam) return '';
            const date = new Date(dateParam);
            const today = new Date();
            const seconds = Math.round((today - date) / 1000);
            const minutes = Math.round(seconds / 60);
            const hours = Math.round(minutes / 60);
            
            const isToday = today.toDateString() === date.toDateString();
            const isYesterday = new Date(today - 86400000).toDateString() === date.toDateString();
            
            const timeStr = date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            
            if (seconds < 5) return 'Just now';
            if (seconds < 60) return `${seconds} seconds ago`;
            if (seconds < 90) return 'A minute ago';
            if (minutes < 60) return `${minutes} minutes ago`;
            if (hours < 4) return `${hours} hours ago`;
            if (isToday) return `Today at ${timeStr}`;
            if (isYesterday) return `Yesterday at ${timeStr}`;
            return `${date.toLocaleDateString([], {month: 'short', day: 'numeric'})} at ${timeStr}`;
        }

        function displayAlerts(data) {
            const tbody = document.getElementById('alertsTableBody');
            if (!data || data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="empty-state">No alerts</td></tr>';
                return;
            }

            tbody.innerHTML = data.slice(0, 10).map(row => {
                const badgeClass = row.severity === 'critical' ? 'error' : 'warning';
                return `
                    <tr>
                        <td>${timeAgo(row.created_at)}</td>
                        <td>${row.server_name}</td>
                        <td>${row.alert_type}</td>
                        <td>${row.message}</td>
                        <td><span class="badge badge-${badgeClass}">${row.severity}</span></td>
                    </tr>
                `;
            }).join('');
        }

        function displayActivity(data) {
            const tbody = document.getElementById('activityTableBody');
            if (!data || data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="empty-state">No activity</td></tr>';
                return;
            }

            tbody.innerHTML = data.slice(0, 10).map(row => `
                <tr>
                    <td>${timeAgo(row.created_at)}</td>
                    <td>${row.user_name}</td>
                    <td>${row.action}</td>
                    <td>${row.details}</td>
                </tr>
            `).join('');
        }

        function exportReport(format) {
            const timeRange = document.getElementById('timeRange').value;
            window.location.href = `/api/reports/export.php?format=${format}&range=${timeRange}`;
        }

        function toggleTheme() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', newTheme);
            document.cookie = "theme=" + newTheme + "; path=/; max-age=31536000";
            
            // Re-render chart to update colors based on the new theme
            updateReports();
        }
    </script>
</body>
</html>
