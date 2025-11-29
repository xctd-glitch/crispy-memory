<?php
declare(strict_types=1);

/**
 * Konfigurasi terpusat untuk navigasi tab dashboard
 * @var array<int, array{id: string, label: string, icon: string, badge?: bool}>
 */
$tabsConfig = [
    [
        'id' => 'overview',
        'label' => 'Overview',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>',
    ],
    [
        'id' => 'routing',
        'label' => 'Routing Config',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>',
    ],
    [
        'id' => 'env-config',
        'label' => 'Environment',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>',
    ],
    [
        'id' => 'postback',
        'label' => 'Postback',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>',
    ],
    [
        'id' => 'statistics',
        'label' => 'Statistics',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>',
    ],
    [
        'id' => 'logs',
        'label' => 'Traffic Logs',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>',
        'badge' => true,
    ],
    [
        'id' => 'api-docs',
        'label' => 'API Docs',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>',
    ],
];

/**
 * Render ikon SVG untuk tab
 * @param string $iconPath SVG path dari konfigurasi tab
 * @return string HTML untuk SVG icon
 */
function renderTabIcon(string $iconPath): string
{
    return sprintf(
        '<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">%s</svg>',
        $iconPath
    );
}

/**
 * Render tombol tab
 * @param array{id: string, label: string, icon: string, badge?: bool} $tab
 * @return string HTML untuk tombol tab
 */
function renderTabButton(array $tab): string
{
    $tabId = htmlspecialchars($tab['id'], ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
    $tabLabel = htmlspecialchars($tab['label'], ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
    $hasBadge = $tab['badge'] ?? false;

    $badgeHtml = $hasBadge
        ? '<span class="ml-1 rounded-full bg-muted px-1.5 py-0.5 text-[9px] font-medium" x-text="logs.length"></span>'
        : '';

    return sprintf(
        '<button
            @click="activeTab = \'%s\'"
            class="shrink-0 py-3 px-2 border-b-2 font-medium text-xs transition-colors whitespace-nowrap"
            :class="activeTab === \'%s\'
                ? \'border-primary text-primary\'
                : \'border-transparent text-muted-foreground hover:text-foreground hover:border-border\'"
            type="button">
            <div class="flex items-center gap-1.5">
                %s
                <span class="hidden md:inline">%s</span>
                %s
            </div>
        </button>',
        $tabId,
        $tabId,
        renderTabIcon($tab['icon']),
        $tabLabel,
        $badgeHtml
    );
}
?>

<!-- Tabs Navigation dengan Horizontal Scroll -->
<div class="border-b">
    <div class="max-w-4xl mx-auto px-5">
        <div class="relative -mx-5 px-5 overflow-x-auto scroll-smooth" style="scrollbar-width: thin;">
            <nav class="flex gap-2" aria-label="Dashboard tabs" role="tablist">
                <?php foreach ($tabsConfig as $tab): ?>
                    <?= renderTabButton($tab); ?>
                <?php endforeach; ?>
            </nav>
        </div>
    </div>
</div>
