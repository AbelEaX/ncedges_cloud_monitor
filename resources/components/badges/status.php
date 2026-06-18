<?php
/**
 * Status Badge Component
 * 
 * Usage: <?= component('badges.status', ['status' => 'online']) ?>
 * 
 * Statuses: online, offline, warning, critical, maintenance
 */
$status = $status ?? 'unknown';
$statusConfig = config('monitoring.statuses.' . $status) ?? [
    'label' => ucfirst($status),
    'color' => '#757575',
];
?>
<span 
    class="badge badge-<?= $status; ?>" 
    style="
        background: <?= $statusConfig['color']; ?>22;
        color: <?= $statusConfig['color']; ?>;
        padding: 4px 8px;
        border-radius: 3px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        display: inline-block;
    "
    title="<?= $statusConfig['label']; ?>"
>
    <?= $statusConfig['label']; ?>
</span>
