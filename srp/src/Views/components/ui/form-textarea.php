<?php
/**
 * Form Textarea Component
 *
 * @param string $id Textarea ID
 * @param string $name Textarea name
 * @param string $label Label text
 * @param string $value Current value
 * @param string $placeholder Placeholder text
 * @param bool $required Is field required
 * @param int $rows Number of rows
 * @param int $maxlength Maximum length
 * @param string $help Help text
 * @param string $error Error message
 * @param array $attributes Additional attributes
 */

$id = $id ?? '';
$name = $name ?? $id;
$label = $label ?? '';
$value = $value ?? '';
$placeholder = $placeholder ?? '';
$required = $required ?? false;
$rows = $rows ?? 4;
$maxlength = $maxlength ?? null;
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
    <?php if ($label): ?>
    <label for="<?= htmlspecialchars($id, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?>" class="form-label">
        <?= htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?>
        <?php if ($required): ?>
        <span class="text-destructive">*</span>
        <?php endif; ?>
    </label>
    <?php endif; ?>

    <textarea
        id="<?= htmlspecialchars($id, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?>"
        name="<?= htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?>"
        rows="<?= (int)$rows ?>"
        <?php if ($placeholder): ?>
        placeholder="<?= htmlspecialchars($placeholder, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?>"
        <?php endif; ?>
        <?php if ($maxlength): ?>
        maxlength="<?= (int)$maxlength ?>"
        <?php endif; ?>
        <?php if ($required): ?>required<?php endif; ?>
        class="textarea<?= $error ? ' textarea-error' : '' ?>"
        <?= $attrString ?>
    ><?= htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?></textarea>

    <?php if ($help): ?>
    <p class="form-help"><?= htmlspecialchars($help, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if ($error): ?>
    <p class="form-error"><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if ($maxlength): ?>
    <p class="form-counter" x-data>
        <span x-text="$refs.textarea_<?= htmlspecialchars($id, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?>?.value.length || 0">0</span> / <?= (int)$maxlength ?>
    </p>
    <script nonce="<?= htmlspecialchars($GLOBALS['cspNonce'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?>">
        // Add x-ref to textarea for character counting
        document.getElementById('<?= htmlspecialchars($id, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?>').setAttribute('x-ref', 'textarea_<?= htmlspecialchars($id, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?>');
    </script>
    <?php endif; ?>
</div>