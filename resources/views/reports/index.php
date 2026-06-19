<?php
/**
 * Reports and Analytics View
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analytics - Monitor</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f5f5f5;
            color: #333;
            padding: 20px;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        header {
            margin-bottom: 30px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
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
        }
        .btn-primary {
            background: #3b82f6;
            color: white;
        }
        .btn-primary:hover {
            background: #2563eb;
        }
        .btn-secondary {
            background: #e5e7eb;
            color: #333;
        }
        .btn-secondary:hover {
            background: #d1d5db;
        }
        select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .card h3 {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        .card-value {
            font-size: 32px;
            font-weight: bold;
            color: #1f2937;
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
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .chart-container h2 {
            font-size: 18px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e5e7eb;
        }
        .chart-placeholder {
            background: #f9fafb;
            border: 2px dashed #d1d5db;
            border-radius: 4px;
            padding: 40px;
            text-align: center;
            color: #6b7280;
        }
        .table-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .table-section h2 {
            font-size: 18px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e5e7eb;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: #f3f4f6;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            border-bottom: 1px solid #e5e7eb;
            font-size: 13px;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        tr:hover {
            background: #f9fafb;
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
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div style="display: flex; align-items: center; gap: 15px;">
                <a href="/dashboard.php" style="text-decoration: none; font-size: 24px; color: #6b7280;" title="Back to Dashboard">←</a>
                <div>
                    <h1>Reports & Analytics</h1>
                    <p style="color: #6b7280; margin-top: 5px;">Monitor performance and view detailed analytics</p>
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
            })
            .catch(error => console.error('Error loading reports:', error));
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
                    <td><span class="badge badge-success">Online</span></td>
                </tr>
            `).join('');
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
                        <td>${new Date(row.created_at).toLocaleString()}</td>
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
                    <td>${new Date(row.created_at).toLocaleString()}</td>
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
    </script>
</body>
</html>
