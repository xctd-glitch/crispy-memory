<!doctype html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome | SRP Traffic Control</title>
    <meta name="theme-color" content="#18181b">
    <meta name="color-scheme" content="dark light">
    <meta name="apple-mobile-web-app-title" content="SRP - Device-aware routing">

    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/png" href="/assets/icons/favicon-96x96.png" sizes="96x96">
    <link rel="icon" type="image/svg+xml" href="/assets/icons/favicon.svg">
    <link rel="shortcut icon" href="/assets/icons/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/icons/apple-touch-icon.png">

    <!-- Tailwind via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script nonce="__CSP_NONCE__">
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
                        background: "hsl(210 20% 97%)",
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

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        :root {
            --background: hsl(210 20% 97%);
            --foreground: hsl(240 10% 3.9%);

            --card-bg: hsl(0 0% 100%);
            --card-foreground: var(--foreground);

            --border: hsl(240 5.9% 90%);
            --input: hsl(240 5.9% 90%);
            --ring: hsl(240 5.9% 10%);

            --primary: hsl(240 5.9% 10%);
            --primary-foreground: hsl(0 0% 98%);

            --secondary: hsl(240 4.8% 95.9%);
            --secondary-foreground: hsl(240 5.9% 10%);

            --muted: hsl(240 4.8% 95.9%);
            --muted-foreground: hsl(240 3.8% 46.1%);

            --destructive: hsl(0 84.2% 60.2%);
            --destructive-foreground: hsl(0 0% 98%);

            --primary-soft-bg: rgba(15, 23, 42, 0.04);
        }

        html,
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-feature-settings: "cv02", "cv03", "cv04", "cv11";
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
            font-smooth: always;
        }

        body {
            background-color: var(--background);
            color: var(--foreground);
        }

        ::-webkit-scrollbar {
            width: 7px;
            height: 7px;
        }

        ::-webkit-scrollbar-track {
            background: hsl(240 4.8% 96.5%);
        }

        ::-webkit-scrollbar-thumb {
            background: hsl(240 5.9% 88%);
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: hsl(240 3.8% 46.1%);
        }

        .text-muted-foreground {
            color: var(--muted-foreground);
        }

        .bg-main {
            background-color: var(--background);
        }

        .bg-card {
            background-color: var(--card-bg);
        }

        .border-border {
            border-color: var(--border);
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
            border-radius: 0.3rem;
            font-size: .8125rem;
            font-weight: 500;
            padding: 0.45rem 0.9rem;
            border-width: 1px;
            border-style: solid;
            outline: 2px solid transparent;
            outline-offset: 2px;
            transition:
                background-color 150ms ease-out,
                color 150ms ease-out,
                border-color 150ms ease-out;
            cursor: pointer;
        }

        .btn:disabled {
            opacity: .5;
            pointer-events: none;
        }

        .btn-primary {
            background-color: var(--primary);
            color: var(--primary-foreground);
            border-color: transparent;
        }

        .btn-primary:hover {
            background-color: hsl(240 5.9% 14%);
        }

        .btn-ghost {
            background-color: transparent;
            color: var(--foreground);
            border-color: var(--border);
        }

        .btn-ghost:hover {
            background-color: var(--secondary);
            color: var(--secondary-foreground);
        }

        .btn-link {
            border: none;
            background: transparent;
            color: var(--muted-foreground);
            font-size: .75rem;
            text-decoration: none;
            text-underline-offset: 3px;
            cursor: pointer;
        }

        .btn-link:hover {
            color: var(--foreground);
            text-decoration: underline;
        }

        /* Card */
        .card {
            border-radius: 0.3rem;
            border-width: 1px;
            border-style: solid;
            border-color: var(--border);
            background-color: var(--card-bg);
            color: var(--card-foreground);
            transition:
                background-color 150ms ease-out,
                border-color 150ms ease-out;
        }

        .card--muted-hover:hover {
            background-color: var(--primary-soft-bg);
            border-color: var(--primary);
        }

        /* Badge */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            border-radius: 0.3rem;
            border-width: 1px;
            border-style: solid;
            border-color: var(--border);
            background-color: var(--secondary);
            color: var(--secondary-foreground);
            font-size: 0.7rem;
            font-weight: 500;
            padding: 0.15rem 0.55rem;
        }

        .badge-soft {
            background-color: var(--muted);
            color: var(--muted-foreground);
        }

        .badge-pill {
            border-radius: .3rem;
        }

        .status-dot {
            width: 0.45rem;
            height: 0.45rem;
            border-radius: 999px;
            background-color: #16a34a;
        }

        .status-dot--muted {
            background-color: #9ca3af;
        }

        /* Offline pill style used as "routing engine" indicator */
        .offline-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            border-radius: 999px;
            background-color: var(--muted);
            color: var(--muted-foreground);
            padding: 0.2rem 0.65rem;
            font-size: 0.7rem;
        }

        .offline-pill span {
            font-weight: 500;
        }

        /* Subtle divider */
        .divider {
            height: 1px;
            width: 100%;
            background-color: var(--border);
        }

        /* Hero title */
        .hero-title {
            letter-spacing: -0.03em;
        }

        /* Simple list */
        .feature-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .feature-list li {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8rem;
            color: var(--muted-foreground);
        }

        .feature-bullet {
            width: 0.45rem;
            height: 0.45rem;
            border-radius: 999px;
            background-color: var(--primary);
        }
    </style>
</head>
<body class="min-h-screen bg-main font-sans antialiased text-sm flex flex-col">

<header class="sticky top-0 z-50 w-full border-b border-border bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
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

<div class="flex-1 flex items-center">
    <div class="container py-4 md:py-8">
        <!-- MAIN GRID -->
        <main class="grid gap-8 md:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)] items-start">
            <!-- LEFT: HERO -->
            <section>
                <div class="inline-flex items-center gap-2 rounded bg-card border border-border px-3 py-1 mb-4">
                    <span class="status-dot status-dot--muted"></span>
                    <span class="text-[0.7rem] text-muted-foreground">
                        Welcome · First hop into SRP
                    </span>
                </div>

                <h1 class="hero-title text-3xl sm:text-4xl font-semibold mb-3">
                    Device-aware routing<br>
                    in a muted, predictable UI.
                </h1>

                <p class="text-sm text-muted-foreground max-w-xl mb-6">
                    SRP keeps your traffic under control: clean redirects, security layers,
                    and clear reporting. This dashboard is the quiet control room that makes
                    your CPA experiments slightly less chaotic.
                </p>

                <div class="flex flex-wrap items-center gap-3 mb-6">
                    <a href="/" class="btn btn-primary">
                        Dashboard
                    </a>
                    <a href="/docs" class="btn btn-ghost">
                        View docs
                    </a>
                    <button type="button" class="btn-link">
                        Or stay here and overthink your funnel →
                    </button>
                </div>

                <ul class="feature-list space-y-2">
                    <li>
                        <span class="feature-bullet"></span>
                        <span>Muted visual noise, louder insights.</span>
                    </li>
                    <li>
                        <span class="feature-bullet"></span>
                        <span>Device, geo &amp; rules-based redirection in one place.</span>
                    </li>
                    <li>
                        <span class="feature-bullet"></span>
                        <span>Offline-ready shell for unstable connections.</span>
                    </li>
                </ul>
            </section>

            <!-- RIGHT: PANELS -->
            <section class="space-y-3 mt-4 md:mt-0">
                <!-- Card: Environment -->
                <div class="card card--muted-hover p-3">
                    <div class="flex items-center justify-between mb-2">
                        <div class="text-xs font-semibold text-muted-foreground tracking-[0.14em] uppercase">
                            Environment
                        </div>
                        <span class="badge badge-pill">
                            <span class="status-dot"></span>
                            <span>Healthy</span>
                        </span>
                    </div>
                    <div class="flex items-baseline justify-between gap-2 mb-2">
                        <div>
                            <div class="text-sm font-medium">
                                Production routing
                            </div>
                            <div class="text-xs text-muted-foreground">
                                Connected to tracking domain
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs text-muted-foreground">
                                Active domains
                            </div>
                            <div class="text-sm font-semibold">
                                4
                            </div>
                        </div>
                    </div>
                    <div class="divider my-2"></div>
                    <div class="flex items-center justify-between text-[0.7rem] text-muted-foreground">
                        <span>Next step: verify DNS &amp; SSL</span>
                        <span>~30 sec</span>
                    </div>
                </div>

                <!-- Card: Traffic snapshot -->
                <div class="card card--muted-hover p-3">
                    <div class="flex items-center justify-between mb-2">
                        <div class="text-xs font-semibold text-muted-foreground tracking-[0.14em] uppercase">
                            Traffic snapshot
                        </div>
                        <span class="badge badge-pill badge-soft">
                            Muted metrics
                        </span>
                    </div>
                    <div class="flex items-center justify-between mb-2">
                        <div>
                            <div class="text-2xl font-semibold leading-none">
                                0
                            </div>
                            <div class="text-[0.7rem] text-muted-foreground">
                                clicks in the last 15 minutes
                            </div>
                        </div>
                        <div class="flex flex-col items-end gap-1 text-[0.7rem] text-muted-foreground">
                            <span>Conv. rate · 0.00%</span>
                            <span>Blocked · 0</span>
                            <span>Clean · 0</span>
                        </div>
                    </div>
                    <div class="divider my-2"></div>
                    <div class="flex items-center justify-between text-[0.7rem] text-muted-foreground">
                        <span>Live data will appear once tracking is wired.</span>
                        <span>/postback &amp; /click</span>
                    </div>
                </div>

                <!-- Card: Offline & PWA -->
                <div class="card card--muted-hover p-3">
                    <div class="flex items-center justify-between mb-2">
                        <div class="text-xs font-semibold text-muted-foreground tracking-[0.14em] uppercase">
                            Offline shell
                        </div>
                        <span class="badge badge-pill badge-soft">
                            PWA-ready
                        </span>
                    </div>
                    <p class="text-[0.78rem] text-muted-foreground mb-3">
                        This welcome page is cached as part of the app shell. When the
                        connection drops, users will see a muted offline screen instead
                        of a broken funnel.
                    </p>
                    <div class="flex items-center justify-between text-[0.7rem] text-muted-foreground">
                        <span>Pair with <code class="text-[0.68rem] px-1 py-0.5 bg-muted rounded">/offline.html</code></span>
                        <span>Service worker: required</span>
                    </div>
                </div>
            </section>
        </main>

        <!-- FOOTER -->
        <footer class="mt-10 mb-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 text-[0.7rem] text-muted-foreground">
            <div>
                SRP Smart Redirect Platform ·
                <span class="text-foreground">&copy; <?= date('Y'); ?> SRP Control. All rights reserved.</span>
            </div>
            <div class="flex items-center gap-3">
                <span>v2 · Device-aware routing</span>
                <span class="hidden sm:inline-block text-xs">No "smart" buzzword without actual routing logic.</span>
            </div>
        </footer>
    </div>
</div>
</body>
</html>
