<?php
/**
 * Card Component
 *
 * @param string $title Card title
 * @param string $description Card description
 * @param string $content Card content (HTML allowed)
 * @param array $actions Array of action buttons
 * @param string $class Additional CSS classes
 * @param bool $collapsible Allow card to be collapsed
 * @param bool $collapsed Initial collapsed state
 * @param array $attributes Additional attributes
 */

$title = $title ?? '';
$description = $description ?? '';
$content = $content ?? '';
$actions = $actions ?? [];
$class = $class ?? '';
$collapsible = $collapsible ?? false;
$collapsed = $collapsed ?? false;
$attributes = $attributes ?? [];

// Build attribute string
$attrString = '';
foreach ($attributes as $key => $val) {
    $attrString .= ' ' . htmlspecialchars($key, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') . '="' . htmlspecialchars($val, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') . '"';
}

// Generate unique ID for collapsible functionality
$cardId = uniqid('card_');
?>

<div class="card <?= htmlspecialchars($class, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?>" <?= $attrString ?>>
    <?php if ($title || $description || $actions || $collapsible): ?>
    <div class="card-header">
        <div class="card-header-content">
            <?php if ($title): ?>
            <h3 class="card-title">
                <?php if ($collapsible): ?>
                <button
                    type="button"
                    class="card-collapse-trigger"
                    aria-expanded="<?= $collapsed ? 'false' : 'true' ?>"
                    aria-controls="<?= $cardId ?>"
                    onclick="this.setAttribute('aria-expanded', this.getAttribute('aria-expanded') === 'true' ? 'false' : 'true'); document.getElementById('<?= $cardId ?>').classList.toggle('hidden');"
                >
                    <span class="card-collapse-icon">
                        <?php
                        $name = 'chevron-down';
                        $size = 16;
                        require __DIR__ . '/icon.php';
                        ?>
                    </span>
                    <?= htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?>
                </button>
                <?php else: ?>
                <?= htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?>
                <?php endif; ?>
            </h3>
            <?php endif; ?>

            <?php if ($description): ?>
            <p class="card-description"><?= htmlspecialchars($description, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?></p>
            <?php endif; ?>
        </div>

        <?php if ($actions): ?>
        <div class="card-actions">
            <?php foreach ($actions as $action): ?>
            <?php
            $actionClass = $action['class'] ?? 'btn btn-sm btn-secondary';
            $actionAttrs = $action['attributes'] ?? [];
            $actionAttrString = '';
            foreach ($actionAttrs as $key => $val) {
                $actionAttrString .= ' ' . htmlspecialchars($key, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') . '="' . htmlspecialchars($val, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') . '"';
            }
            ?>
            <?php if (isset($action['href'])): ?>
            <a href="<?= htmlspecialchars($action['href'], ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?>" class="<?= htmlspecialchars($actionClass, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?>" <?= $actionAttrString ?>>
                <?= htmlspecialchars($action['label'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?>
            </a>
            <?php else: ?>
            <button type="<?= htmlspecialchars($action['type'] ?? 'button', ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?>" class="<?= htmlspecialchars($actionClass, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?>" <?= $actionAttrString ?>>
                <?= htmlspecialchars($action['label'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?>
            </button>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="card-content<?= $collapsible && $collapsed ? ' hidden' : '' ?>" <?= $collapsible ? 'id="' . $cardId . '"' : '' ?>>
        <?= $content ?>
    </div>
</div>