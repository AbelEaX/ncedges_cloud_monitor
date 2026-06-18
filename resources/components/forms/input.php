<?php
/**
 * Form Input Component
 * 
 * Usage: <?= component('forms.input', ['name' => 'email', 'type' => 'email', 'label' => 'Email']) ?>
 */
$name = $name ?? '';
$label = $label ?? ucfirst($name);
$type = $type ?? 'text';
$value = $value ?? '';
$required = $required ?? false;
$placeholder = $placeholder ?? '';
$class = $class ?? '';
?>
<div class="form-group" style="margin-bottom: 16px;">
    <label style="
        display: block;
        margin-bottom: 4px;
        font-size: 12px;
        font-weight: 600;
        color: var(--text);
        text-transform: uppercase;
    ">
        <?= htmlspecialchars($label); ?>
        <?php if ($required): ?><span style="color: var(--danger);">*</span><?php endif; ?>
    </label>
    <input 
        type="<?= $type; ?>"
        name="<?= htmlspecialchars($name); ?>"
        value="<?= htmlspecialchars($value); ?>"
        placeholder="<?= htmlspecialchars($placeholder); ?>"
        <?php if ($required): ?>required<?php endif; ?>
        class="form-input <?= $class; ?>"
        style="
            width: 100%;
            padding: 8px 12px;
            background: var(--surface);
            border: 1px solid var(--border);
            color: var(--text);
            border-radius: 4px;
            font-size: 12px;
            box-sizing: border-box;
        "
    />
</div>
