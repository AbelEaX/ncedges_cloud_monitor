<?php
/**
 * Alert Component
 * 
 * Usage: <?= component('alerts.success', ['message' => 'Operation successful!']) ?>
 * Usage: <?= component('alerts.error', ['message' => 'Error occurred']) ?>
 * Usage: <?= component('alerts.warning', ['message' => 'Please note...']) ?>
 * Usage: <?= component('alerts.info', ['message' => 'Information']) ?>
 */
$message = $message ?? '';
$dismissible = $dismissible ?? true;
$class = $class ?? '';
?>
<div 
    class="alert alert-<?= $type ?? 'info'; ?> <?= $class; ?>" 
    style="
        padding: 12px 16px;
        border-radius: 4px;
        margin-bottom: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: <?= $this->getAlertBackground($type ?? 'info'); ?>;
        color: <?= $this->getAlertTextColor($type ?? 'info'); ?>;
        border-left: 4px solid <?= $this->getAlertBorderColor($type ?? 'info'); ?>;
    "
    role="alert"
>
    <span><?= htmlspecialchars($message); ?></span>
    <?php if ($dismissible): ?>
    <button 
        type="button" 
        onclick="this.parentElement.remove()"
        style="background: none; border: none; cursor: pointer; color: inherit; font-size: 18px;"
    >
        ×
    </button>
    <?php endif; ?>
</div>

<?php
/**
 * Helper methods for alert styling
 */
function getAlertBackground($type) {
    return match($type) {
        'success' => 'rgba(102, 187, 106, 0.1)',
        'error' => 'rgba(239, 83, 80, 0.1)',
        'danger' => 'rgba(239, 83, 80, 0.1)',
        'warning' => 'rgba(255, 193, 7, 0.1)',
        'info' => 'rgba(41, 182, 246, 0.1)',
        default => 'rgba(41, 182, 246, 0.1)',
    };
}

function getAlertTextColor($type) {
    return match($type) {
        'success' => 'var(--success)',
        'error' => 'var(--danger)',
        'danger' => 'var(--danger)',
        'warning' => 'var(--warning)',
        'info' => 'var(--info)',
        default => 'var(--info)',
    };
}

function getAlertBorderColor($type) {
    return match($type) {
        'success' => 'var(--success)',
        'error' => 'var(--danger)',
        'danger' => 'var(--danger)',
        'warning' => 'var(--warning)',
        'info' => 'var(--info)',
        default => 'var(--info)',
    };
}
?>
