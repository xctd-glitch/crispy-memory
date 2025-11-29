<?php
/**
 * Form Select Component
 *
 * @param string $id Select ID
 * @param string $name Select name
 * @param string $label Label text
 * @param array $options Array of options [value => label] or [group => [value => label]]
 * @param string|array $selected Selected value(s)
 * @param bool $required Is field required
 * @param bool $multiple Allow multiple selection
 * @param string $placeholder Placeholder text
 * @param string $help Help text
 * @param string $error Error message
 * @param array $attributes Additional attributes
 */

$id = $id ?? '';
$name = $name ?? $id;
$label = $label ?? '';
$options = $options ?? [];
$selected = $selected ?? '';
$required = $required ?? false;
$multiple = $multiple ?? false;
$placeholder = $placeholder ?? '-- Select --';
$help = $help ?? '';
$error = $error ?? '';
$attributes = $attributes ?? [];

// Convert single selected value to array for easier checking
$selectedValues = is_array($selected) ? $selected : [$selected];

// Build attribute string
$attrString = '';
foreach ($attributes as $key => $val) {
    $attrString .= ' ' . htmlspecialchars($key, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') . '="' . htmlspecialchars($val, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') . '"';
}

// Helper function to check if option is selected
$isSelected = function($value) use ($selectedValues) {
    return in_array($value, $selectedValues);
};

// Helper function to render options
$renderOptions = function($options) use ($isSelected) {
    $html = '';
    foreach ($options as $value => $label) {
        if (is_array($label)) {
            // Optgroup
            $html .= '<optgroup label="' . htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') . '">';
            foreach ($label as $subValue => $subLabel) {
                $selected = $isSelected($subValue) ? ' selected' : '';
                $html .= '<option value="' . htmlspecialchars($subValue, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') . '"' . $selected . '>';
                $html .= htmlspecialchars($subLabel, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
                $html .= '</option>';
            }
            $html .= '</optgroup>';
        } else {
            // Regular option
            $selected = $isSelected($value) ? ' selected' : '';
            $html .= '<option value="' . htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') . '"' . $selected . '>';
            $html .= htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
            $html .= '</option>';
        }
    }
    return $html;
};
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

    <select
        id="<?= htmlspecialchars($id, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?>"
        name="<?= htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?><?= $multiple ? '[]' : '' ?>"
        <?php if ($required): ?>required<?php endif; ?>
        <?php if ($multiple): ?>multiple<?php endif; ?>
        class="select<?= $error ? ' select-error' : '' ?>"
        <?= $attrString ?>
    >
        <?php if (!$multiple && $placeholder): ?>
        <option value=""><?= htmlspecialchars($placeholder, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?></option>
        <?php endif; ?>

        <?= $renderOptions($options) ?>
    </select>

    <?php if ($help): ?>
    <p class="form-help"><?= htmlspecialchars($help, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if ($error): ?>
    <p class="form-error"><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?></p>
    <?php endif; ?>
</div>