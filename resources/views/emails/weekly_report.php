<?php
$title = "Weekly Health Report";
$headerClass = "info";

ob_start();
?>
<h2>System Health Summary</h2>
<p>Here is your automated weekly overview of the monitoring system.</p>

<div style="display: flex; gap: 15px; margin: 24px 0;">
    <!-- Metric 1 -->
    <div style="flex: 1; background: #1e293b; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 20px; text-align: center;">
        <div style="font-size: 11px; text-transform: uppercase; color: #94a3b8; font-weight: 600; margin-bottom: 8px;">Uptime</div>
        <div style="font-size: 24px; font-weight: 700; color: #f8fafc;"><?= number_format($metrics['avg_uptime'] ?? 100, 2) ?>%</div>
    </div>
    <!-- Metric 2 -->
    <div style="flex: 1; background: #1e293b; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 20px; text-align: center;">
        <div style="font-size: 11px; text-transform: uppercase; color: #94a3b8; font-weight: 600; margin-bottom: 8px;">Servers</div>
        <div style="font-size: 24px; font-weight: 700; color: #f8fafc;"><?= (int)($metrics['total_servers'] ?? 0) ?></div>
    </div>
    <!-- Metric 3 -->
    <div style="flex: 1; background: #1e293b; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 20px; text-align: center;">
        <div style="font-size: 11px; text-transform: uppercase; color: #94a3b8; font-weight: 600; margin-bottom: 8px;">Alerts</div>
        <div style="font-size: 24px; font-weight: 700; color: <?= ($metrics['alert_count'] > 0) ? '#fca5a5' : '#6ee7b7' ?>;"><?= (int)($metrics['alert_count'] ?? 0) ?></div>
    </div>
</div>

<?php if (!empty($offline_servers)): ?>
<h3 style="color: #fca5a5; font-size: 16px; margin-top: 32px; margin-bottom: 16px;">Currently Offline Servers</h3>
<div class="detail-box" style="padding: 0; overflow: hidden;">
    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px;">
        <tr style="background: rgba(255,255,255,0.02); border-bottom: 1px solid rgba(255,255,255,0.05);">
            <th style="padding: 12px 16px; color: #94a3b8; font-weight: 600; text-transform: uppercase; font-size: 11px;">Name</th>
            <th style="padding: 12px 16px; color: #94a3b8; font-weight: 600; text-transform: uppercase; font-size: 11px;">Host</th>
        </tr>
        <?php foreach ($offline_servers as $s): ?>
        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
            <td style="padding: 12px 16px; color: #f8fafc;"><?= htmlspecialchars($s['name']) ?></td>
            <td style="padding: 12px 16px; color: #93c5fd; font-family: monospace;"><?= htmlspecialchars($s['host']) ?>:<?= htmlspecialchars($s['port']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php else: ?>
<div class="detail-box" style="text-align: center; border-color: rgba(16, 185, 129, 0.2);">
    <span style="font-size: 24px; display: block; margin-bottom: 8px;">🎉</span>
    <strong style="color: #6ee7b7;">All Systems Go</strong>
    <p style="margin-top: 8px; font-size: 13px;">There are no offline servers at the moment.</p>
</div>
<?php endif; ?>

<div style="text-align: center; margin-top: 32px;">
    <a href="<?= rtrim(config('app.url') ?? 'http://localhost:8000', '/') ?>/reports" class="button">View Full Report</a>
</div>
<?php
$slot = ob_get_clean();
require __DIR__ . '/layout.php';
?>
