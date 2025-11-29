<!doctype html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8'); ?>">
    <meta name="theme-color" content="#18181b">
    <meta name="color-scheme" content="dark light">
    <meta name="apple-mobile-web-app-title" content="SRP - Device-aware routing">
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/png" href="/assets/icons/favicon-96x96.png" sizes="96x96">
    <link rel="icon" type="image/svg+xml" href="/assets/icons/favicon.svg">
    <link rel="shortcut icon" href="/assets/icons/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/icons/apple-touch-icon.png">
    <title><?= htmlspecialchars($pageTitle ?? 'SRP Traffic Control', ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8'); ?></title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script nonce="<?= htmlspecialchars($cspNonce ?? '', ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8'); ?>">
        tailwind.config = {
            theme: {
                container: {
                    center: true,
                    padding: "1.25rem",
                    screens: { "2xl": "1400px" }
                },
                extend: {
                    colors: {
                        border: "hsl(240 5.9% 90%)",
                        input: "hsl(240 5.9% 90%)",
                        ring: "hsl(240 5.9% 10%)",
                        background: "hsl(0 0% 100%)",
                        foreground: "hsl(240 10% 3.9%)",
                        primary: {
                            DEFAULT: "hsl(240 5.9% 10%)",
                            foreground: "hsl(0 0% 98%)"
                        },
                        secondary: {
                            DEFAULT: "hsl(240 4.8% 95.9%)",
                            foreground: "hsl(240 5.9% 10%)"
                        },
                        destructive: {
                            DEFAULT: "hsl(0 84.2% 60.2%)",
                            foreground: "hsl(0 0% 98%)"
                        },
                        muted: {
                            DEFAULT: "hsl(240 4.8% 95.9%)",
                            foreground: "hsl(240 3.8% 46.1%)"
                        },
                        accent: {
                            DEFAULT: "hsl(240 4.8% 95.9%)",
                            foreground: "hsl(240 5.9% 10%)"
                        },
                        popover: {
                            DEFAULT: "hsl(0 0% 100%)",
                            foreground: "hsl(240 10% 3.9%)"
                        },
                        card: {
                            DEFAULT: "hsl(0 0% 100%)",
                            foreground: "hsl(240 10% 3.9%)"
                        }
                    },
                    borderRadius: {
                        lg: "0.3rem",
                        md: "calc(0.3rem - 2px)",
                        sm: "calc(0.3rem - 4px)"
                    }
                }
            }
        };
    </script>

    <!-- Main stylesheet berisi .card, .btn, .input, dll -->
    <link rel="stylesheet" type="text/css" href="/assets/style.css" id="preload-stylesheet"/>

    <script src="/pwa/register-sw.js" defer></script>
</head>
<body class="min-h-screen bg-main font-sans antialiased text-sm flex flex-col">

<header class="sticky top-0 z-50 w-full border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
    <div class="flex h-12 max-w-4xl mx-auto items-center px-5">
        <div class="flex items-center space-x-2">
            <img src="/assets/icons/fox.svg" alt="Fox head logo" class="h-8 w-8" width="32" height="32">
            <div class="flex flex-col leading-tight">
                <span class="text-sm font-semibold tracking-tight">Smart Redirect Platform</span>
                <span class="text-[11px] text-muted-foreground">
                    No "smart" buzzword without actual routing logic.
                </span>
            </div>
        </div>
    </div>
</header>

<?php if (!empty($errorMessage)): ?>
    <div class="fixed top-4 right-4 z-50 max-w-xs">
        <div
            id="login-toast"
            class="px-3 py-2 text-[11px] flex items-start gap-2 rounded-md border border-destructive/60 bg-destructive/10 text-destructive transition-opacity transition-transform duration-200"
        >
            <div class="mt-[2px]">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 8v4m0 4h.01M12 3a9 9 0 110 18 9 9 0 010-18z"
                    ></path>
                </svg>
            </div>
            <div class="flex-1">
                <p class="font-medium">Login failed</p>
                <p class="mt-0.5">
                    <?= htmlspecialchars($errorMessage, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8'); ?>
                </p>
            </div>
        </div>
    </div>
    <script nonce="<?= htmlspecialchars($cspNonce ?? '', ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8'); ?>">
        document.addEventListener('DOMContentLoaded', function () {
            var t = document.getElementById('login-toast');
            if (!t) {
                return;
            }
            setTimeout(function () {
                t.classList.add('opacity-0', 'translate-y-2');
            }, 4000);
        });
    </script>
<?php endif; ?>

<main class="flex-1 flex items-start pt-6 pb-6 md:pt-8 md:pb-8">
    <div class="container">
        <div class="flex justify-center">
            <div class="card max-w-sm w-full px-4 py-4 sm:px-5 sm:py-5">
                <!-- Header -->
                <div class="mb-8">
                    <div class="flex items-center gap-2 mb-3">
                        <img
                            src="/assets/icons/favicon.ico"
                            alt="SRP logo"
                            class="h-8 w-8"
                            loading="lazy"
                        >
                        <div class="flex flex-col">
                            <span class="text-xs font-semibold tracking-[0.16em] text-muted-foreground uppercase">
                                SRP Control
                            </span>
                            <span class="text-sm font-medium">
                                Smart Redirect Platform
                            </span>
                        </div>
                    </div>
                    <h1 class="hero-title text-xl font-semibold mb-1">
                        Sign in to SRP
                    </h1>
                    <p class="text-xs text-muted-foreground">
                        Use your SRP account to manage routing, domains, and postback flows.
                    </p>
                </div>

                <!-- Form -->
                <form method="post" autocomplete="off" class="space-y-3">
                    <input
                        type="hidden"
                        name="_csrf_token"
                        value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8'); ?>"
                    >

                    <div>
                        <label for="username" class="form-label">
                            Username
                        </label>
                        <input
                            id="username"
                            name="username"
                            type="text"
                            class="input"
                            autocomplete="username"
                            maxlength="191"
                            required
                            placeholder="username"
                        >
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label for="password" class="form-label">
                                Password
                            </label>
                        </div>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            class="input"
                            autocomplete="current-password"
                            minlength="8"
                            required
                            placeholder="••••••••"
                        >
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="inline-flex items-center gap-2 text-xs text-muted-foreground cursor-pointer">
                            <input
                                type="checkbox"
                                name="remember"
                                class="h-3.5 w-3.5 rounded border border-border"
                                value="1"
                                checked
                            >
                            <span>Keep me signed in on this device</span>
                        </label>
                    </div>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center w-full mt-1 rounded-[0.3rem]
                               border border-transparent bg-primary px-3 py-2 text-xs font-medium
                               text-primary-foreground hover:bg-primary/90
                               focus:outline-none focus-visible:ring-1 focus-visible:ring-ring
                               focus-visible:ring-offset-1 focus-visible:ring-offset-[hsl(210_20%_97%)]"
                    >
                        <svg class="h-3.5 w-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M5 12h14M12 5l7 7-7 7"
                            ></path>
                        </svg>
                        <span>Sign In</span>
                    </button>

                    <p class="form-help text-xs text-muted-foreground mt-1">
                        Do not share your credentials. All access is logged.
                    </p>
                </form>
            </div>
        </div>
    </div>
</main>

<footer class="border-t py-4 md:py-5">
    <div class="max-w-4xl mx-auto px-5">
        <div class="flex flex-col items-center justify-between gap-3 md:flex-row">
            <p class="text-center text-[11px] text-muted-foreground">
                &copy; <?= date('Y'); ?> SRP Control. All rights reserved.
            </p>
        </div>
    </div>
</footer>

<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</body>
</html>