<?php
$title = "✓ Server Recovered";
$headerClass = "success";

ob_start();
?>
<p>Hello,</p>
<p>Good news! The monitoring system has detected that the previously unreachable server is now back online and responding normally.</p>

<div class="detail-box">
    <div class="detail-row">
        <span class="detail-label">Server Name:</span>
        <span class="detail-value"><?= htmlspecialchars($server['name'] ?? 'Unknown') ?></span>
    </div>
    <div class="detail-row">
        <span class="detail-label">Host:</span>
        <span class="detail-value"><?= htmlspecialchars($server['host'] ?? 'Unknown') ?></span>
    </div>
    <div class="detail-row">
        <span class="detail-label">Port:</span>
        <span class="detail-value"><?= htmlspecialchars($server['port'] ?? 'Unknown') ?></span>
    </div>
    <div class="detail-row">
        <span class="detail-label">Time Detected:</span>
        <span class="detail-value"><?= date('Y-m-d H:i:s T') ?></span>
    </div>
</div>

<p>You can view current metrics in the monitoring dashboard.</p>
<a href="<?= rtrim(config('app.url'), '/') ?>/servers" class="button">View Dashboard</a>
<?php
$slot = ob_get_clean();
require __DIR__ . '/layout.php';
?>
