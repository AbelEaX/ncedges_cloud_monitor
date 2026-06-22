<?php
$title = "✓ Server Restored";
$headerClass = "success";

ob_start();
?>
<h2>Server Back Online</h2>
<p>Good news! The monitoring system has detected that the previously unreachable server is now back online and responding normally.</p>

<div class="detail-box">
    <div class="detail-row">
        <span class="detail-label">Status</span>
        <span class="detail-value"><span class="status-badge badge-green">Online</span></span>
    </div>
    <div class="detail-row">
        <span class="detail-label">Server Name</span>
        <span class="detail-value"><?= htmlspecialchars($server['name'] ?? 'Unknown') ?></span>
    </div>
    <div class="detail-row">
        <span class="detail-label">Host</span>
        <span class="detail-value" style="font-family: monospace; color: #93c5fd;"><?= htmlspecialchars($server['host'] ?? 'Unknown') ?></span>
    </div>
    <div class="detail-row">
        <span class="detail-label">Port</span>
        <span class="detail-value"><?= htmlspecialchars($server['port'] ?? 'Unknown') ?></span>
    </div>
    <div class="detail-row">
        <span class="detail-label">Time Detected</span>
        <span class="detail-value"><?= date('Y-m-d H:i:s T') ?></span>
    </div>
</div>

<p>You can view current metrics and the incident timeline in the monitoring dashboard.</p>

<div style="text-align: center;">
    <a href="<?= rtrim(config('app.url') ?? 'http://localhost:8000', '/') ?>/servers" class="button">View Dashboard</a>
</div>
<?php
$slot = ob_get_clean();
require __DIR__ . '/layout.php';
?>
