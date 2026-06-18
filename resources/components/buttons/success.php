<?php
/**
 * Success Button Component
 * 
 * Usage: <?= component('buttons.success', ['text' => 'Save']) ?>
 */
$type = $type ?? 'button';
$text = $text ?? 'Button';
$href = $href ?? '#';
$class = $class ?? '';
$disabled = $disabled ?? false;
$onClick = $onClick ?? '';
?>
<button 
    type="<?= $type; ?>" 
    class="btn btn-success <?= $class; ?>" 
    <?= $href !== '#' ? "onclick=\"window.location.href='{$href}'\"" : ''; ?>
    <?= $onClick ? "onclick=\"{$onClick}\"" : ''; ?>
    <?= $disabled ? 'disabled' : ''; ?>
    style="
        background: var(--success);
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
