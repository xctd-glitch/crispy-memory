<?php
$pageTitle = 'SRP Traffic Control';
require __DIR__ . '/components/header.php';
?>
<div x-data="dash" x-cloak>
<header class="sticky top-0 z-50 w-full border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
    <div class="flex h-12 max-w-4xl mx-auto items-center px-5">
        <div class="mr-3 hidden md:flex">
            <a href="/" class="mr-4 flex items-center space-x-2">
                <img src="/assets/icons/fox.svg" alt="Fox head logo" class="h-8 w-8" width="32" height="32">
                <div class="flex flex-col leading-tight">
                <span class="font-semibold text-sm tracking-tight">SRP Smart Redirect Platform</span>
                 <span class="text-[11px] text-muted-foreground">No "smart" buzzword without actual routing logic.</span>
                 </div>
            </a>
        </div>

        <button @click="mobileMenuOpen = !mobileMenuOpen" class="mr-2 md:hidden btn btn-ghost btn-icon" aria-label="Toggle navigation">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>

        <div class="flex md:hidden items-center space-x-2">
            <img src="/assets/icons/fox.svg" alt="Fox head logo" class="h-4 w-4" width="16" height="16">
            <span class="font-semibold text-xs tracking-tight">SRP</span>
        </div>

        <div class="flex flex-1 items-center justify-end space-x-2">
            <div class="flex items-center space-x-2 rounded-md px-2 sm:px-2.5 py-1 transition-colors duration-200"
                 :class="cfg.system_on ? (muteStatus.isMuted ? 'bg-amber-500 text-white shadow-sm' : 'bg-primary text-primary-foreground shadow-sm') : 'border'">
                <div class="h-1.5 w-1.5 rounded-full transition-all duration-200"
                     :class="cfg.system_on ? (muteStatus.isMuted ? 'bg-white animate-pulse' : 'bg-emerald-500 animate-pulse') : 'bg-gray-400'"></div>
                <span class="text-[11px] font-medium hidden sm:inline"
                      x-text="cfg.system_on ? (muteStatus.isMuted ? 'Muted' : 'Active') : 'Offline'"></span>
            </div>

            <button type="button"
                    @click="toggleAutoRefresh()"
                    class="btn btn-ghost btn-icon"
                    :title="autoRefreshEnabled ? 'Pause auto-refresh' : 'Resume auto-refresh'"
                    aria-label="Toggle auto-refresh">
                <svg x-show="autoRefreshEnabled" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <svg x-show="!autoRefreshEnabled" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </button>

            <form method="post" action="/logout.php" class="hidden sm:block">
                <input type="hidden" name="_csrf_token"
                       value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8'); ?>">
                <button type="submit" class="btn btn-secondary btn-sm">Logout</button>
            </form>

            <form method="post" action="/logout.php" class="sm:hidden">
                <input type="hidden" name="_csrf_token"
                       value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8'); ?>">
                <button type="submit" class="btn btn-ghost btn-icon" aria-label="Logout">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v1" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</header>

<!-- Toast & Confirm Modal -->
<?php require __DIR__ . '/components/toast.php'; ?>

<main class="flex-1 w-full">
    <?php require __DIR__ . '/components/dashboard-content.php'; ?>
</main>
</div>

<!-- Load external JavaScript file -->
<script src="/assets/js/dashboard.js" nonce="<?= htmlspecialchars($cspNonce ?? '', ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8'); ?>"></script>

<?php require __DIR__ . '/components/footer.php'; ?>