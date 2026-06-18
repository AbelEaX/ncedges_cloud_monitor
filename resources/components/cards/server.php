<?php
/**
 * Server Card Component
 * 
 * Reusable card for displaying server information
 * Usage: <?= component('cards.server', ['server' => $server, 'metrics' => $metrics]) ?>
 */
$server = $server ?? null;
$metrics = $metrics ?? [];

if (!$server) {
    return '';
}
?>
<div class="card" style="
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 4px;
    padding: 16px;
    border-left: 4px solid <?= $server->getStatusColor(); ?>;
    margin-bottom: 12px;
">
    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">
        <div>
            <h3 style="margin: 0 0 4px 0; font-size: 14px; color: var(--text);">
                <?= htmlspecialchars($server->name); ?>
            </h3>
            <p style="margin: 0; font-size: 11px; color: var(--muted);">
                <?= htmlspecialchars($server->host); ?>:<?= $server->port; ?>
            </p>
        </div>
        <div>
            <?= component('badges.status', ['status' => $server->status]); ?>
        </div>
    </div>
    
    <?php if (!empty($metrics)): ?>
    <div style="
        display: flex;
        gap: 12px;
        font-size: 11px;
        color: var(--muted);
        padding-top: 12px;
        border-top: 1px solid var(--border);
    ">
        <div>
            <span>Last Check:</span><br>
            <strong style="color: var(--text);"><?= date('H:i:s', strtotime($metrics['checked_at'] ?? '')); ?></strong>
        </div>
        <div>
            <span>Response Time:</span><br>
            <strong style="color: var(--text);"><?= $metrics['response_time'] ?? 'N/A'; ?>ms</strong>
        </div>
    </div>
    <?php endif; ?>
</div>
