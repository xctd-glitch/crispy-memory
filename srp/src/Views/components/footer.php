<footer class="border-t py-4 md:py-5 mt-auto">
    <div class="max-w-4xl mx-auto px-5">
        <div class="flex flex-col items-center justify-between gap-3 md:flex-row">
            <p class="text-center text-[11px] text-muted-foreground">
                &copy; <?= date('Y'); ?> SRP Control. All rights reserved - xctd:-.
            </p>
        </div>
    </div>
</footer>

<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" nonce="<?= htmlspecialchars($cspNonce ?? '', ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8'); ?>" defer></script>
</body>
</html>
