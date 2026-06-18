<?php
/**
 * Primary Button Component
 * 
 * Usage: <?= component('buttons.primary', ['text' => 'Click Me', 'href' => '#']) ?>
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
    class="btn btn-primary <?= $class; ?>" 
    <?= $href !== '#' ? "onclick=\"window.location.href='{$href}'\"" : ''; ?>
    <?= $onClick ? "onclick=\"{$onClick}\"" : ''; ?>
    <?= $disabled ? 'disabled' : ''; ?>
    style="
        background: var(--primary);
        color: #000;
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
