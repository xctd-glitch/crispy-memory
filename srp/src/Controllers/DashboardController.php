<?php

declare(strict_types=1);

namespace SRP\Controllers;

use SRP\Config\Environment;
use SRP\Middleware\Session;
use SRP\Middleware\SecurityHeaders;

/**
 * Dashboard Controller (Production-Ready)
 *
 * Features:
 * - Session-based authentication
 * - SecurityHeaders dengan CSP nonce
 * - Flash messages untuk feedback
 */
class DashboardController
{
    /**
     * Display admin dashboard
     */
    public static function index(): void
    {
        // Require authentication (auto-redirect ke login jika not authenticated)
        Session::requireAuth();

        // Get CSP nonce
        $cspNonce = Session::getCspNonce();

        // Apply security headers
        $isProduction = (Environment::get('APP_ENV') ?? 'production') === 'production';
        SecurityHeaders::apply($cspNonce, $isProduction);
        SecurityHeaders::applyNoCacheHeaders();

        // Get flash messages
        $flashMessages = Session::getFlashMessages();

        // Get CSRF token for forms
        $csrfToken = Session::getCsrfToken();

        // Render dashboard view
        require __DIR__ . '/../Views/dashboard.view.php';
    }

    /**
     * Display landing page information (public page)
     */
    public static function landing(): void
    {
        // Get CSP nonce
        $cspNonce = Session::getCspNonce();

        // Apply security headers (production-safe)
        $isProduction = (Environment::get('APP_ENV') ?? 'production') === 'production';

        if ($isProduction) {
            // Production: strict CSP
            SecurityHeaders::apply($cspNonce, true);
        } else {
            // Development: allow Tailwind CDN
            self::applyLandingSecurityHeaders($cspNonce);
        }

        // Render landing view
        require __DIR__ . '/../Views/landing.view.php';
    }

    /**
     * Apply security headers untuk landing page (development mode)
     *
     * Allows Tailwind CDN untuk development
     *
     * @param string $cspNonce
     * @return void
     */
    private static function applyLandingSecurityHeaders(string $cspNonce): void
    {
        // Remove server info
        header_remove('Server');
        header_remove('X-Powered-By');

        // Basic security headers
        header('X-Frame-Options: DENY', true);
        header('X-Content-Type-Options: nosniff', true);
        header('Referrer-Policy: no-referrer', true);
        header('X-XSS-Protection: 1; mode=block', true);

        // CSP dengan Tailwind CDN support (development only)
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'nonce-{$cspNonce}' https://cdn.tailwindcss.com",
            "style-src 'self' 'unsafe-inline'", // Tailwind needs unsafe-inline
            "img-src 'self' data:",
            "font-src 'self'",
            "connect-src 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "object-src 'none'"
        ]);

        header("Content-Security-Policy: {$csp}", true);

        // Permissions Policy
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
            'fullscreen=(self)',
            'picture-in-picture=()',
            'screen-wake-lock=()',
            'web-share=()',
            'xr-spatial-tracking=()'
        ];

        header('Permissions-Policy: ' . implode(', ', $policies), true);
    }
}
