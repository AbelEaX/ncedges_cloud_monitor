<?php
/**
 * Confirmation Modal Component
 * 
 * Usage: <?= component('modals.confirmation', [
 *     'title' => 'Confirm Delete',
 *     'message' => 'Are you sure?',
 *     'confirmText' => 'Delete',
 *     'cancelText' => 'Cancel',
 *     'confirmAction' => 'window.location.href=/delete/1'
 * ]) ?>
 */
$id = 'modal-' . uniqid();
$title = $title ?? 'Confirm';
$message = $message ?? '';
$confirmText = $confirmText ?? 'Confirm';
$cancelText = $cancelText ?? 'Cancel';
$confirmAction = $confirmAction ?? '';
$cancelAction = $cancelAction ?? "document.getElementById('{$id}').remove()";
?>
<div id="<?= $id; ?>" class="modal-overlay" style="
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
">
    <div class="modal" style="
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 24px;
        max-width: 500px;
        width: 90%;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    ">
        <h2 style="margin: 0 0 12px 0; font-size: 16px; color: var(--text);">
            <?= htmlspecialchars($title); ?>
        </h2>
        <p style="margin: 0 0 20px 0; color: var(--muted); font-size: 14px;">
            <?= htmlspecialchars($message); ?>
        </p>
        <div style="display: flex; gap: 12px; justify-content: flex-end;">
            <button 
                onclick="<?= $cancelAction; ?>"
                style="
                    background: var(--border);
                    color: var(--text);
                    border: none;
                    padding: 8px 16px;
                    border-radius: 4px;
                    cursor: pointer;
                    font-size: 12px;
                    font-weight: 600;
                "
            >
                <?= htmlspecialchars($cancelText); ?>
            </button>
            <button 
                onclick="<?= $confirmAction; ?>"
                style="
                    background: var(--danger);
                    color: white;
                    border: none;
                    padding: 8px 16px;
                    border-radius: 4px;
                    cursor: pointer;
                    font-size: 12px;
                    font-weight: 600;
                "
            >
                <?= htmlspecialchars($confirmText); ?>
            </button>
        </div>
    </div>
</div>
