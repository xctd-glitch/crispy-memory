<?php
/**
 * Icon Component
 *
 * @param string $name Icon name
 * @param int $size Icon size (default 16)
 * @param string $class Additional CSS classes
 * @param array $attributes Additional attributes
 */

$name = $name ?? '';
$size = $size ?? 16;
$class = $class ?? '';
$attributes = $attributes ?? [];

// Build attribute string
$attrString = '';
foreach ($attributes as $key => $val) {
    $attrString .= ' ' . htmlspecialchars($key, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') . '="' . htmlspecialchars($val, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') . '"';
}

// Icon definitions
$icons = [
    // UI Icons
    'loading' => '<path d="M8 2v4M8 10v4M4.93 4.93l2.83 2.83M9.24 9.24l2.83 2.83M2 8h4M10 8h4M4.93 11.07l2.83-2.83M9.24 6.76l2.83-2.83"/>',
    'plus' => '<path d="M8 3v10M3 8h10"/>',
    'minus' => '<path d="M3 8h10"/>',
    'check' => '<path d="M3 8l3 3 8-8"/>',
    'x' => '<path d="M15 5L5 15M5 5l10 10"/>',
    'edit' => '<path d="M11 2l3 3L5 14H2v-3L11 2z"/>',
    'trash' => '<path d="M3 6h10M6 6V4a1 1 0 011-1h2a1 1 0 011 1v2M5 6v6a1 1 0 001 1h4a1 1 0 001-1V6"/>',
    'download' => '<path d="M8 10V2M4 8l4 4 4-4M2 14h12"/>',
    'upload' => '<path d="M8 6V14M4 10l4-4 4 4M2 2h12"/>',
    'search' => '<circle cx="7" cy="7" r="4"/><path d="M14 14l-4.35-4.35"/>',
    'filter' => '<path d="M2 3h12l-5 7v4l-2-1V10L2 3z"/>',
    'settings' => '<circle cx="8" cy="8" r="2"/><path d="M6.5 2l.3 1.5a5.5 5.5 0 00-1 .6L4.4 3.3l-1 1.7 1.1.8a5.5 5.5 0 000 1.2l-1.1.8 1 1.7 1.4-.8a5.5 5.5 0 001 .6L6.5 14h2l.3-1.5a5.5 5.5 0 001-.6l1.4.8 1-1.7-1.1-.8a5.5 5.5 0 000-1.2l1.1-.8-1-1.7-1.4.8a5.5 5.5 0 00-1-.6L9.5 2h-2z"/>',

    // Navigation Icons
    'arrow-left' => '<path d="M10 12L4 8l6-4v8z"/>',
    'arrow-right' => '<path d="M6 4l6 4-6 4V4z"/>',
    'arrow-up' => '<path d="M4 10l4-6 4 6H4z"/>',
    'arrow-down' => '<path d="M12 6l-4 6-4-6h8z"/>',
    'chevron-left' => '<path d="M10 4L4 8l6 4"/>',
    'chevron-right' => '<path d="M6 4l6 4-6 4"/>',
    'chevron-up' => '<path d="M4 10l4-4 4 4"/>',
    'chevron-down' => '<path d="M4 6l4 4 4-4"/>',
    'menu' => '<path d="M4 6h8M4 10h8M4 14h8"/>',
    'dots-vertical' => '<circle cx="8" cy="4" r="1"/><circle cx="8" cy="8" r="1"/><circle cx="8" cy="12" r="1"/>',
    'dots-horizontal' => '<circle cx="4" cy="8" r="1"/><circle cx="8" cy="8" r="1"/><circle cx="12" cy="8" r="1"/>',

    // Status Icons
    'info' => '<circle cx="8" cy="8" r="7"/><path d="M8 12V8M8 4h0"/>',
    'warning' => '<path d="M8 2L1 14h14L8 2z"/><path d="M8 10V8M8 12h0"/>',
    'error' => '<circle cx="8" cy="8" r="7"/><path d="M10 6L6 10M6 6l4 4"/>',
    'success' => '<circle cx="8" cy="8" r="7"/><path d="M4 8l2.5 2.5L12 5"/>',
    'question' => '<circle cx="8" cy="8" r="7"/><path d="M6 6a2 2 0 014 0c0 1.5-2 1.5-2 3M8 12h0"/>',

    // Action Icons
    'copy' => '<rect x="5" y="5" width="8" height="10" rx="1"/><path d="M3 3h8a1 1 0 011 1v8"/>',
    'paste' => '<path d="M6 2h4v2h4v10H2V4h4V2z"/><rect x="6" y="1" width="4" height="3" rx="1"/>',
    'cut' => '<circle cx="5" cy="5" r="2"/><circle cx="11" cy="11" r="2"/><path d="M5 7l6 6M11 7L8 10"/>',
    'save' => '<path d="M2 2h10l2 2v10H2V2z"/><path d="M10 2v5H5V2"/>',
    'share' => '<path d="M11 7V3l4 4-4 4v-4H7a2 2 0 00-2 2v3"/>',
    'link' => '<path d="M7 9H5a2 2 0 010-4h2M9 7h2a2 2 0 010 4H9M5 7h6"/>',
    'external-link' => '<path d="M10 2h4v4M14 2L7 9M12 14H2V4h5"/>',
    'refresh' => '<path d="M2 8a6 6 0 0110.5-4M14 8a6 6 0 01-10.5 4"/><path d="M12 2v4h4M4 14v-4H0"/>',
    'sync' => '<path d="M4 4v3a4 4 0 008 0V4M12 12V9a4 4 0 00-8 0v3"/><path d="M4 7L1 4l3-3M12 9l3 3-3 3"/>',

    // Media Icons
    'play' => '<path d="M5 4l8 4-8 4V4z"/>',
    'pause' => '<rect x="5" y="4" width="2" height="8"/><rect x="9" y="4" width="2" height="8"/>',
    'stop' => '<rect x="4" y="4" width="8" height="8"/>',
    'volume' => '<path d="M8 4L5 7H2v2h3l3 3V4z"/><path d="M11 5v6M13 3v10"/>',
    'volume-mute' => '<path d="M8 4L5 7H2v2h3l3 3V4z"/><path d="M11 6l4 4M15 6l-4 4"/>',

    // File Icons
    'file' => '<path d="M3 2h7l3 3v9H3V2z"/><path d="M10 2v3h3"/>',
    'folder' => '<path d="M2 4h4l2-2h6v10H2V4z"/>',
    'folder-open' => '<path d="M2 4h4l2-2h6v2H4l-2 8h12l2-6"/>',
    'image' => '<rect x="2" y="3" width="12" height="10" rx="1"/><circle cx="5" cy="6" r="1"/><path d="M2 10l3-3 2 2 3-3 4 4"/>',
    'document' => '<path d="M3 2h7l3 3v9H3V2z"/><path d="M10 2v3h3M5 8h6M5 10h6M5 12h4"/>',

    // Communication Icons
    'mail' => '<rect x="2" y="4" width="12" height="8" rx="1"/><path d="M2 4l6 4 6-4"/>',
    'phone' => '<path d="M4 2a1 1 0 00-1 1l1 4a8 8 0 008 0l1-4a1 1 0 00-1-1H4z"/>',
    'message' => '<path d="M2 3h12v8H5l-3 3V3z"/>',
    'bell' => '<path d="M8 2a3 3 0 013 3v3l1 2H4l1-2V5a3 3 0 013-3zM6 12a2 2 0 104 0"/>',
    'notification' => '<circle cx="12" cy="4" r="2" fill="currentColor"/>',

    // User Icons
    'user' => '<circle cx="8" cy="5" r="3"/><path d="M2 14c0-2 2-4 6-4s6 2 6 4"/>',
    'users' => '<circle cx="5" cy="5" r="2"/><circle cx="11" cy="5" r="2"/><path d="M1 12c0-1.5 1.5-3 4-3 1 0 2 .2 2.5.5M9 12c0-1.5 1.5-3 4-3s4 1.5 4 3"/>',
    'logout' => '<path d="M7 14V2h6v12H7zM3 8h4M3 8l2-2M3 8l2 2"/>',
    'login' => '<path d="M9 2h4v12H9M3 8h8M7 6l2 2-2 2"/>',

    // Utility Icons
    'grid' => '<rect x="3" y="3" width="4" height="4"/><rect x="9" y="3" width="4" height="4"/><rect x="3" y="9" width="4" height="4"/><rect x="9" y="9" width="4" height="4"/>',
    'list' => '<path d="M5 4h8M5 8h8M5 12h8"/><circle cx="2" cy="4" r="0.5"/><circle cx="2" cy="8" r="0.5"/><circle cx="2" cy="12" r="0.5"/>',
    'calendar' => '<rect x="2" y="3" width="12" height="11" rx="1"/><path d="M2 6h12M5 2v2M11 2v2"/>',
    'clock' => '<circle cx="8" cy="8" r="6"/><path d="M8 4v4l2 2"/>',
    'location' => '<circle cx="8" cy="6" r="3"/><path d="M8 1a5 5 0 00-5 5c0 4 5 8 5 8s5-4 5-8a5 5 0 00-5-5z"/>',
    'globe' => '<circle cx="8" cy="8" r="6"/><ellipse cx="8" cy="8" rx="2" ry="6"/><path d="M2 8h12"/>',
    'lock' => '<rect x="3" y="7" width="10" height="7" rx="1"/><path d="M5 7V5a3 3 0 016 0v2"/>',
    'unlock' => '<rect x="3" y="7" width="10" height="7" rx="1"/><path d="M5 7V5a3 3 0 013-3"/>',
    'key' => '<circle cx="5" cy="8" r="3"/><path d="M8 8h6l-1 2 1 2"/>',
    'shield' => '<path d="M8 2L3 5v4c0 3.3 2.2 5 5 5s5-1.7 5-5V5L8 2z"/>',

    // Brand/Custom Icons
    'fox' => '<path d="M4 2L2 6v6c0 2 2 2 2 2h8s2 0 2-2V6l-2-4-2 2-2-1-2 1L4 2z"/><circle cx="5" cy="8" r="1"/><circle cx="11" cy="8" r="1"/><path d="M8 10v1"/>',
];

// Get icon path
$iconPath = $icons[$name] ?? '';

if (!$iconPath) {
    // Icon not found, return empty
    return;
}

// Add animate-spin class for loading icon
if ($name === 'loading') {
    $class .= ' animate-spin';
}
?>

<svg
    width="<?= (int)$size ?>"
    height="<?= (int)$size ?>"
    viewBox="0 0 16 16"
    fill="none"
    stroke="currentColor"
    stroke-width="2"
    stroke-linecap="round"
    stroke-linejoin="round"
    class="icon icon-<?= htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?> <?= htmlspecialchars($class, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8') ?>"
    <?= $attrString ?>
>
    <?= $iconPath ?>
</svg>