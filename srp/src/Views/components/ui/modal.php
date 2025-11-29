<?php
/**
 * Modal Component
 *
 * @param string $id Modal ID (required for Alpine.js)
 * @param string $title Modal title
 * @param string $content Modal content (HTML allowed)
 * @param array $actions Array of action buttons
 * @param string $size Modal size (sm, md, lg, xl)
 * @param bool $closeButton Show close button
 * @param array $attributes Additional attributes
 */

$id = $id ?? uniqid('modal_');
$title = $title ?? '';
$content = $content ?? '';
$actions = $actions ?? [];
$size = $size ?? 'md';
$closeButton = $closeButton ?? true;
$attributes = $attributes ?? [];

// Build attribute string
$attrString = '';
foreach ($attributes as $key => $val) {
    $attrString .= ' ' . htmlspecialchars($key, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') . '="' . htmlspecialchars($val, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') . '"';
}

// Size classes
$sizeClasses = [
    'sm' => 'max-w-md',
    'md' => 'max-w-lg',
    'lg' => 'max-w-2xl',
    'xl' => 'max-w-4xl'
];
$modalSizeClass = $sizeClasses[$size] ?? $sizeClasses['md'];
?>

<!-- Modal Template -->
<template x-if="<?= htmlspecialchars($id, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?>Open">
    <div class="modal-backdrop" @click="<?= htmlspecialchars($id, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?>Open = false">
        <div class="modal-container">
            <div class="modal <?= htmlspecialchars($modalSizeClass, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?>" @click.stop <?= $attrString ?>>
                <?php if ($title || $closeButton): ?>
                <div class="modal-header">
                    <?php if ($title): ?>
                    <h3 class="modal-title"><?= htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?></h3>
                    <?php endif; ?>

                    <?php if ($closeButton): ?>
                    <button type="button" class="modal-close" @click="<?= htmlspecialchars($id, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?>Open = false" aria-label="Close modal">
                        <?php
                        $name = 'x';
                        $size = 20;
                        require __DIR__ . '/icon.php';
                        ?>
                    </button>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <div class="modal-content">
                    <?= $content ?>
                </div>

                <?php if ($actions): ?>
                <div class="modal-footer">
                    <?php foreach ($actions as $action): ?>
                    <?php
                    $actionType = $action['type'] ?? 'button';
                    $actionVariant = $action['variant'] ?? 'secondary';
                    $actionSize = $action['size'] ?? 'md';
                    $actionLabel = $action['label'] ?? '';
                    $actionClick = $action['@click'] ?? '';
                    $actionAttrs = $action['attributes'] ?? [];

                    // Handle close action
                    if ($actionClick === 'close') {
                        $actionClick = htmlspecialchars($id, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') . 'Open = false';
                    }

                    // Include button component
                    require __DIR__ . '/button.php';
                    ?>
                    <div class="modal-action">
                        <?php
                        // Render button with modal-specific attributes
                        $buttonAttrs = array_merge($actionAttrs, [
                            '@click' => $actionClick
                        ]);

                        require __DIR__ . '/button.php';
                        ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</template>

<!-- Helper to open modal -->
<script nonce="<?= htmlspecialchars($GLOBALS['cspNonce'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?>">
// Initialize modal state if not exists
if (typeof window.<?= htmlspecialchars($id, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?>Open === 'undefined') {
    window.<?= htmlspecialchars($id, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?>Open = false;
}

// Helper function to open modal
window.open<?= htmlspecialchars($id, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?> = function() {
    window.<?= htmlspecialchars($id, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?>Open = true;
    // Trigger Alpine.js reactivity
    if (window.Alpine) {
        window.Alpine.store('modals', { ...window.Alpine.store('modals'), <?= htmlspecialchars($id, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?>: true });
    }
};

// Helper function to close modal
window.close<?= htmlspecialchars($id, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?> = function() {
    window.<?= htmlspecialchars($id, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?>Open = false;
    // Trigger Alpine.js reactivity
    if (window.Alpine) {
        window.Alpine.store('modals', { ...window.Alpine.store('modals'), <?= htmlspecialchars($id, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?>: false });
    }
};
</script>