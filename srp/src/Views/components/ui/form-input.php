<?php
/**
 * Form Input Component
 *
 * @param string $id Input ID
 * @param string $name Input name
 * @param string $label Label text
 * @param string $type Input type (text, email, url, number, etc.)
 * @param string $value Current value
 * @param string $placeholder Placeholder text
 * @param bool $required Is field required
 * @param string $help Help text
 * @param array $attributes Additional attributes
 */

$id = $id ?? '';
$name = $name ?? $id;
$label = $label ?? '';
$type = $type ?? 'text';
$value = $value ?? '';
$placeholder = $placeholder ?? '';
$required = $required ?? false;
$help = $help ?? '';
$attributes = $attributes ?? [];
$error = $error ?? '';

// Build attribute string
$attrString = '';
foreach ($attributes as $key => $val) {
    $attrString .= ' ' . htmlspecialchars($key, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') . '="' . htmlspecialchars($val, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') . '"';
}
?>

<div class="form-group">
    <?php if ($label): ?>
    <label for="<?= htmlspecialchars($id, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?>" class="form-label">
        <?= htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?>
        <?php if ($required): ?>
        <span class="text-destructive">*</span>
        <?php endif; ?>
    </label>
    <?php endif; ?>

    <input
        type="<?= htmlspecialchars($type, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?>"
        id="<?= htmlspecialchars($id, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?>"
        name="<?= htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?>"
        value="<?= htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?>"
        <?php if ($placeholder): ?>
        placeholder="<?= htmlspecialchars($placeholder, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?>"
        <?php endif; ?>
        <?php if ($required): ?>required<?php endif; ?>
        class="input<?= $error ? ' input-error' : '' ?>"
        <?= $attrString ?>
    >

    <?php if ($help): ?>
    <p class="form-help"><?= htmlspecialchars($help, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if ($error): ?>
    <p class="form-error"><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?></p>
    <?php endif; ?>
</div>