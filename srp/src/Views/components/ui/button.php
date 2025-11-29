<?php
/**
 * Button Component
 *
 * @param string $label Button text
 * @param string $type Button type (button, submit, reset)
 * @param string $variant Button variant (primary, secondary, destructive, ghost, link)
 * @param string $size Button size (sm, md, lg, icon)
 * @param bool $disabled Is button disabled
 * @param bool $loading Show loading state
 * @param string $icon Icon name (if using icon button)
 * @param string $href Link URL (converts to anchor tag)
 * @param array $attributes Additional attributes
 */

$label = $label ?? '';
$type = $type ?? 'button';
$variant = $variant ?? 'primary';
$size = $size ?? 'md';
$disabled = $disabled ?? false;
$loading = $loading ?? false;
$icon = $icon ?? '';
$href = $href ?? '';
$attributes = $attributes ?? [];

// Build class string
$classes = ['btn'];
$classes[] = 'btn-' . $variant;
$classes[] = 'btn-' . $size;
if ($disabled || $loading) {
    $classes[] = 'btn-disabled';
}
if ($loading) {
    $classes[] = 'btn-loading';
}

// Add custom classes from attributes
if (isset($attributes['class'])) {
    $classes[] = $attributes['class'];
    unset($attributes['class']);
}

$classString = implode(' ', $classes);

// Build attribute string
$attrString = '';
foreach ($attributes as $key => $val) {
    $attrString .= ' ' . htmlspecialchars($key, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') . '="' . htmlspecialchars($val, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') . '"';
}

// Helper function to render icon
$renderIcon = function($iconName) {
    ob_start();
    $name = $iconName;
    $size = 16;
    require __DIR__ . '/icon.php';
    return ob_get_clean();
};
?>

<?php if ($href): ?>
<a href="<?= htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?>" class="<?= htmlspecialchars($classString, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?>" <?= $attrString ?>>
    <?php if ($loading): ?>
    <span class="btn-icon"><?= $renderIcon('loading') ?></span>
    <?php elseif ($icon && $size === 'icon'): ?>
    <?= $renderIcon($icon) ?>
    <?php elseif ($icon): ?>
    <span class="btn-icon"><?= $renderIcon($icon) ?></span>
    <?php endif; ?>
    <?php if ($label && $size !== 'icon'): ?>
    <span class="btn-label"><?= htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?></span>
    <?php endif; ?>
</a>
<?php else: ?>
<button type="<?= htmlspecialchars($type, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?>" class="<?= htmlspecialchars($classString, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?>" <?= ($disabled || $loading) ? 'disabled' : '' ?> <?= $attrString ?>>
    <?php if ($loading): ?>
    <span class="btn-icon"><?= $renderIcon('loading') ?></span>
    <?php elseif ($icon && $size === 'icon'): ?>
    <?= $renderIcon($icon) ?>
    <?php elseif ($icon): ?>
    <span class="btn-icon"><?= $renderIcon($icon) ?></span>
    <?php endif; ?>
    <?php if ($label && $size !== 'icon'): ?>
    <span class="btn-label"><?= htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?></span>
    <?php endif; ?>
</button>
<?php endif; ?>