<?php
/**
 * Danger Button Component
 * 
 * Usage: <?= component('buttons.danger', ['text' => 'Delete', 'confirmText' => 'Are you sure?']) ?>
 */
$text = $text ?? 'Delete';
$href = $href ?? '#';
$class = $class ?? '';
$disabled = $disabled ?? false;
$confirmText = $confirmText ?? '';
$onClick = $onClick ?? '';

if ($confirmText) {
    $onClick = "if(confirm('{$confirmText}')) { " . ($onClick ?: "window.location.href='{$href}'") . " }";
}
?>
<button 
    type="button" 
    class="btn btn-danger <?= $class; ?>" 
    <?= $onClick ? "onclick=\"{$onClick}\"" : ''; ?>
    <?= $disabled ? 'disabled' : ''; ?>
    style="
        background: var(--danger);
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 4px;
        cursor: <?= $disabled ? 'not-allowed' : 'pointer'; ?>;
        font-weight: 600;
        font-size: 12px;
        opacity: <?= $disabled ? '0.5' : '1'; ?>;
    "
>
    <?= $text; ?>
</button>
