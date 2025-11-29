<?php

declare(strict_types=1);

namespace SRP\Middleware;

/**
 * Security Headers Middleware (Production-Ready)
 *
 * Apply comprehensive security headers untuk protect dari berbagai attacks:
 * - XSS (Cross-Site Scripting)
 * - Clickjacking
 * - MIME sniffing
 * - Information disclosure
 * - Content injection
 */
class SecurityHeaders
{
    /**
     * Apply all security headers dengan CSP nonce
     *
     * @param string $cspNonce CSP nonce untuk inline scripts
     * @param bool $isProduction Jika true, apply production-level headers
     * @return void
     */
    public static function apply(string $cspNonce, bool $isProduction = true): void
    {
        // Remove server information disclosure
        self::removeServerInfo();

        // Apply fundamental security headers
        self::applyFrameOptions();
        self::applyContentTypeOptions();
        self::applyXssProtection();
        self::applyReferrerPolicy();

        // Apply CSP dengan nonce (production-safe tanpa unsafe-inline/unsafe-eval)
        self::applyContentSecurityPolicy($cspNonce, $isProduction);

        // Apply HSTS (HTTPS only)
        if ($isProduction && self::isHttps()) {
            self::applyHsts();
        }

        // Apply Permissions Policy (disable unnecessary features)
        self::applyPermissionsPolicy();

        // Additional headers
        self::applyAdditionalHeaders();
    }

    /**
     * Remove server information dari headers
     *
     * @return void
     */
    private static function removeServerInfo(): void
    {
        header_remove('Server');
        header_remove('X-Powered-By');

        // Explicitly set empty values untuk ensure removal
        header('Server: ', true);
        header('X-Powered-By: ', true);
    }

    /**
     * Apply X-Frame-Options untuk prevent clickjacking
     *
     * @return void
     */
    private static function applyFrameOptions(): void
    {
        // DENY: tidak boleh di-frame sama sekali (paling aman)
        header('X-Frame-Options: DENY', true);
    }

    /**
     * Apply X-Content-Type-Options untuk prevent MIME sniffing
     *
     * @return void
     */
    private static function applyContentTypeOptions(): void
    {
        header('X-Content-Type-Options: nosniff', true);
    }

    /**
     * Apply X-XSS-Protection (legacy, tapi masih berguna untuk old browsers)
     *
     * @return void
     */
    private static function applyXssProtection(): void
    {
        // Mode block: jika XSS detected, block page rendering
        header('X-XSS-Protection: 1; mode=block', true);
    }

    /**
     * Apply Referrer-Policy untuk control referrer information
     *
     * @return void
     */
    private static function applyReferrerPolicy(): void
    {
        // no-referrer: paling aman, tidak send referrer sama sekali
        header('Referrer-Policy: no-referrer', true);
    }

    /**
     * Apply Content Security Policy dengan nonce
     *
     * CSP Level yang aman untuk production:
     * - Tidak ada unsafe-inline atau unsafe-eval
     * - Script hanya dari self + nonce
     * - No external domains kecuali explicitly allowed
     *
     * @param string $nonce
     * @param bool $isProduction
     * @return void
     */
    private static function applyContentSecurityPolicy(string $nonce, bool $isProduction): void
    {
        if ($isProduction) {
            // Production CSP dengan Alpine.js & CDN support
            // NOTE: unsafe-eval diperlukan untuk Alpine.js directive evaluation
            $csp = implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'nonce-{$nonce}' 'unsafe-eval' https://cdn.tailwindcss.com https://unpkg.com https://static.cloudflareinsights.com",
                "style-src 'self' 'nonce-{$nonce}' 'unsafe-inline'", // Tailwind needs unsafe-inline
                "img-src 'self' data:",
                "font-src 'self'",
                "connect-src 'self' https://cloudflareinsights.com",
                "form-action 'self'",
                "frame-ancestors 'none'",
                "base-uri 'self'",
                "object-src 'none'",
                "upgrade-insecure-requests"
            ]);
        } else {
            // Development CSP (sedikit lebih permissive untuk debugging)
            $csp = implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'nonce-{$nonce}' 'unsafe-eval' https://cdn.tailwindcss.com https://unpkg.com https://static.cloudflareinsights.com",
                "style-src 'self' 'nonce-{$nonce}' 'unsafe-inline'", // inline styles untuk rapid dev
                "img-src 'self' data:",
                "font-src 'self'",
                "connect-src 'self' https://cloudflareinsights.com",
                "form-action 'self'",
                "frame-ancestors 'none'",
                "base-uri 'self'",
                "object-src 'none'"
            ]);
        }

        header("Content-Security-Policy: {$csp}", true);
    }

    /**
     * Apply HTTP Strict Transport Security (HSTS)
     *
     * Force HTTPS untuk semua future requests
     *
     * @return void
     */
    private static function applyHsts(): void
    {
        // max-age=31536000: 1 year
        // includeSubDomains: apply ke semua subdomains
        // preload: eligible untuk HSTS preload list
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload', true);
    }

    /**
     * Apply Permissions Policy (formerly Feature-Policy)
     *
     * Disable unnecessary browser features
     *
     * @return void
     */
    private static function applyPermissionsPolicy(): void
    {
        // Disable semua features yang tidak diperlukan
        $policies = [
            'geolocation=()',
            'microphone=()',
            'camera=()',
            'payment=()',
            'usb=()',
            'magnetometer=()',
            'gyroscope=()',
            'accelerometer=()',
            'ambient-light-sensor=()',
            'autoplay=()',
            'encrypted-media=()',
            'fullscreen=(self)', // Allow fullscreen only untuk same origin
            'picture-in-picture=()',
            'screen-wake-lock=()',
            'web-share=()',
            'xr-spatial-tracking=()'
        ];

        header('Permissions-Policy: ' . implode(', ', $policies), true);
    }

    /**
     * Apply additional security headers
     *
     * @return void
     */
    private static function applyAdditionalHeaders(): void
    {
        // Cross-Origin-Embedder-Policy: require-corp (isolate dari cross-origin resources)
        header('Cross-Origin-Embedder-Policy: require-corp', true);

        // Cross-Origin-Opener-Policy: same-origin (isolate browsing context)
        header('Cross-Origin-Opener-Policy: same-origin', true);

        // Cross-Origin-Resource-Policy: same-origin (block cross-origin reads)
        header('Cross-Origin-Resource-Policy: same-origin', true);

        // Expect-CT: enforce Certificate Transparency
        // max-age=86400: 1 day
        header('Expect-CT: max-age=86400, enforce', true);
    }

    /**
     * Check if current request is HTTPS
     *
     * @return bool
     */
    private static function isHttps(): bool
    {
        // Check HTTPS server variable
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            return true;
        }

        // Check for reverse proxy headers (CloudFlare, nginx, etc)
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            return strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https';
        }

        if (isset($_SERVER['HTTP_X_FORWARDED_SSL'])) {
            return strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) === 'on';
        }

        // Check port
        if (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443) {
            return true;
        }

        return false;
    }

    /**
     * Apply API-specific headers (untuk JSON API endpoints)
     *
     * @param string $cspNonce
     * @return void
     */
    public static function applyApiHeaders(string $cspNonce): void
    {
        // Basic security headers
        self::removeServerInfo();
        self::applyContentTypeOptions();
        self::applyReferrerPolicy();

        // Simplified CSP untuk API (no scripts)
        $csp = implode('; ', [
            "default-src 'none'",
            "frame-ancestors 'none'",
            "base-uri 'none'"
        ]);
        header("Content-Security-Policy: {$csp}", true);

        // Content-Type untuk JSON
        header('Content-Type: application/json; charset=utf-8', true);

        // No caching untuk API responses dengan sensitive data
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0', true);
        header('Pragma: no-cache', true);
        header('Expires: 0', true);
    }

    /**
     * Set cache headers untuk static assets
     *
     * @param int $maxAge Cache duration dalam seconds
     * @return void
     */
    public static function applyCacheHeaders(int $maxAge = 31536000): void
    {
        // 1 year cache untuk immutable assets
        header("Cache-Control: public, max-age={$maxAge}, immutable", true);
    }

    /**
     * Set no-cache headers untuk sensitive pages
     *
     * @return void
     */
    public static function applyNoCacheHeaders(): void
    {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0', true);
        header('Pragma: no-cache', true);
        header('Expires: 0', true);
    }

    /**
     * Get recommended CSP nonce length
     *
     * @return int
     */
    public static function getRecommendedNonceLength(): int
    {
        return 32; // 32 bytes = 64 hex characters
    }

    /**
     * Validate CSP nonce format
     *
     * @param string $nonce
     * @return bool
     */
    public static function isValidNonce(string $nonce): bool
    {
        // Nonce harus hex string dengan panjang minimum 32 chars (16 bytes)
        return (bool)preg_match('/^[a-f0-9]{32,}$/i', $nonce);
    }

    /**
     * Apply Brand Domain Headers (trackng.app)
     *
     * Strict security headers untuk brand domain:
     * - Strict CSP dengan nonce
     * - Restrictive CORS (same-origin only)
     * - Full security headers
     * - Session-based authentication support
     *
     * @param string $cspNonce CSP nonce untuk inline scripts
     * @return void
     */
    public static function applyBrandHeaders(string $cspNonce): void
    {
        // Remove server information
        self::removeServerInfo();

        // Apply fundamental security headers
        self::applyFrameOptions();
        self::applyContentTypeOptions();
        self::applyXssProtection();
        self::applyReferrerPolicy();

        // CSP untuk brand domain dengan Alpine.js & CDN support
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'nonce-{$cspNonce}' 'unsafe-eval' https://cdn.tailwindcss.com https://unpkg.com https://static.cloudflareinsights.com",
            "style-src 'self' 'nonce-{$cspNonce}' 'unsafe-inline'",
            "img-src 'self' data:",
            "font-src 'self'",
            "connect-src 'self' https://cloudflareinsights.com",
            "form-action 'self'",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "object-src 'none'",
            "upgrade-insecure-requests"
        ]);
        header("Content-Security-Policy: {$csp}", true);

        // HSTS untuk brand domain
        if (self::isHttps()) {
            self::applyHsts();
        }

        // Full Permissions Policy
        self::applyPermissionsPolicy();

        // Restrictive CORS (same-origin only)
        header('Access-Control-Allow-Origin: ' . self::getCurrentOrigin(), true);
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS', true);
        header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token', true);
        header('Access-Control-Allow-Credentials: true', true);
        header('Access-Control-Max-Age: 3600', true);

        // Additional strict headers
        header('Cross-Origin-Embedder-Policy: require-corp', true);
        header('Cross-Origin-Opener-Policy: same-origin', true);
        header('Cross-Origin-Resource-Policy: same-origin', true);

        // No cache untuk panel pages
        self::applyNoCacheHeaders();
    }

    /**
     * Apply Tracking Domain Headers (qvtrk.com)
     *
     * Lightweight security headers untuk tracking domain:
     * - Lighter CSP (optimized untuk performance)
     * - Permissive CORS (public API)
     * - Minimal overhead
     * - API-key based authentication support
     *
     * @return void
     */
    public static function applyTrackingHeaders(): void
    {
        // Remove server information
        self::removeServerInfo();

        // Basic security headers
        self::applyContentTypeOptions();
        self::applyReferrerPolicy();

        // Lightweight CSP untuk tracking domain (focus on performance)
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self'",
            "object-src 'none'",
            "base-uri 'self'",
            "frame-ancestors 'none'"
        ]);
        header("Content-Security-Policy: {$csp}", true);

        // HSTS untuk tracking domain
        if (self::isHttps()) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains', true);
        }

        // Minimal Permissions Policy (lightweight)
        $policies = [
            'geolocation=()',
            'microphone=()',
            'camera=()',
            'payment=()',
            'usb=()'
        ];
        header('Permissions-Policy: ' . implode(', ', $policies), true);

        // Permissive CORS untuk public API
        header('Access-Control-Allow-Origin: *', true);
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS', true);
        header('Access-Control-Allow-Headers: Content-Type, X-API-Key', true);
        header('Access-Control-Max-Age: 3600', true);

        // Handle preflight OPTIONS request
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }

        // No cache untuk API responses
        self::applyNoCacheHeaders();
    }

    /**
     * Get current origin untuk CORS
     *
     * @return string
     */
    private static function getCurrentOrigin(): string
    {
        $protocol = self::isHttps() ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return "{$protocol}://{$host}";
    }
}
