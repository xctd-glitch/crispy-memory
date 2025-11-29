<?php
/**
 * Form Checkbox Component
 *
 * @param string $id Checkbox ID
 * @param string $name Checkbox name
 * @param string $label Label text
 * @param string $value Checkbox value
 * @param bool $checked Is checked
 * @param bool $required Is field required
 * @param string $help Help text
 * @param string $error Error message
 * @param array $attributes Additional attributes
 */

$id = $id ?? '';
$name = $name ?? $id;
$label = $label ?? '';
$value = $value ?? '1';
$checked = $checked ?? false;
$required = $required ?? false;
$help = $help ?? '';
$error = $error ?? '';
$attributes = $attributes ?? [];

// Build attribute string
$attrString = '';
foreach ($attributes as $key => $val) {
    $attrString .= ' ' . htmlspecialchars($key, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') . '="' . htmlspecialchars($val, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') . '"';
}
?>

<div class="form-group">
    <label class="checkbox-label<?= $error ? ' checkbox-error' : '' ?>">
        <input
            type="checkbox"
            id="<?= htmlspecialchars($id, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?>"
            name="<?= htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?>"
            value="<?= htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?>"
            <?php if ($checked): ?>checked<?php endif; ?>
            <?php if ($required): ?>required<?php endif; ?>
            class="checkbox"
            <?= $attrString ?>
        >
        <span class="checkbox-text">
            <?= htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?>
            <?php if ($required): ?>
            <span class="text-destructive">*</span>
            <?php endif; ?>
        </span>
    </label>

    <?php if ($help): ?>
    <p class="form-help"><?= htmlspecialchars($help, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if ($error): ?>
    <p class="form-error"><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?></p>
    <?php endif; ?>
</div>