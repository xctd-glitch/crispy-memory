<?php

declare(strict_types=1);

namespace SRP\Middleware;

use SRP\Models\Validator;

/**
 * Session Management Middleware (Production-Ready)
 *
 * Features:
 * - Secure session configuration
 * - CSP nonce generation per request
 * - CSRF token management
 * - Session fingerprinting (UA + IP hash)
 * - Session fixation protection
 * - Auto-regeneration (session ID rotates every 5 minutes)
 * - No auto logout (session persists until manual logout)
 */
class Session
{
    private const REGENERATE_INTERVAL = 300; // 5 minutes (regenerate session ID)

    /**
     * Start session dengan secure configuration
     *
     * @return void
     */
    public static function start(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            // Get session lifetime dari environment (default: 30 days)
            $lifetime = (int)($_ENV['SESSION_LIFETIME'] ?? 2592000);

            session_set_cookie_params([
                'lifetime' => $lifetime,
                'path'     => '/',
                'domain'   => '', // Current domain only
                'secure'   => self::isHttps(), // HTTPS only di production
                'httponly' => true, // Prevent JS access
                'samesite' => 'Strict', // Strict CSRF protection
            ]);

            // Secure session configuration
            ini_set('session.sid_length', '48'); // Long session ID
            ini_set('session.sid_bits_per_character', '6'); // More entropy
            ini_set('session.use_strict_mode', '1'); // Reject uninitialized session IDs
            ini_set('session.use_only_cookies', '1'); // No session ID in URL
            ini_set('session.cookie_httponly', '1');
            ini_set('session.cookie_samesite', 'Strict');

            // Use secure session save path (outside webroot jika possible)
            $savePath = sys_get_temp_dir() . '/srp_sessions';
            if (!is_dir($savePath)) {
                @mkdir($savePath, 0700, true);
            }

            // Verify directory permissions for security
            if (is_dir($savePath)) {
                $perms = fileperms($savePath);
                if (($perms & 0777) !== 0700) {
                    @chmod($savePath, 0700);
                }

                if (is_writable($savePath)) {
                    session_save_path($savePath);
                }
            }

            session_start();

            // Initialize atau validate session
            self::initializeSession();

            // Check session timeout
            self::validateSession();

            // Regenerate session ID periodically
            self::regenerateIdIfNeeded();

            // Generate fresh CSP nonce untuk setiap request
            self::regenerateCspNonce();
        }
    }

    /**
     * Initialize session dengan fingerprinting
     *
     * @return void
     */
    private static function initializeSession(): void
    {
        $now = time();

        // Set creation time jika belum ada
        if (!isset($_SESSION['CREATED'])) {
            $_SESSION['CREATED'] = $now;
        }

        // Set last activity time
        if (!isset($_SESSION['LAST_ACTIVITY'])) {
            $_SESSION['LAST_ACTIVITY'] = $now;
        }

        // Set session fingerprint untuk security
        if (!isset($_SESSION['FINGERPRINT'])) {
            $_SESSION['FINGERPRINT'] = self::generateFingerprint();
        }

        // Set initial IP jika belum ada (untuk detect session hijacking)
        if (!isset($_SESSION['INITIAL_IP'])) {
            $_SESSION['INITIAL_IP'] = self::getClientIpHash();
        }
    }

    /**
     * Validate session dan check hijacking
     *
     * Note: Auto logout disabled - session persists until browser closes or manual logout
     *
     * @return void
     */
    private static function validateSession(): void
    {
        // Update last activity time (for reference only, no timeout)
        $_SESSION['LAST_ACTIVITY'] = time();

        // Validate fingerprint untuk prevent session hijacking
        $currentFingerprint = self::generateFingerprint();
        if (isset($_SESSION['FINGERPRINT']) && $_SESSION['FINGERPRINT'] !== $currentFingerprint) {
            // Fingerprint mismatch = possible session hijacking
            error_log('Session hijacking attempt detected: fingerprint mismatch');
            self::destroy();
            return;
        }

        // Validate IP hash (soft check - IP bisa berubah di mobile networks)
        // Hanya log warning, tidak destroy session (untuk avoid false positives)
        $currentIpHash = self::getClientIpHash();
        if (isset($_SESSION['INITIAL_IP']) && $_SESSION['INITIAL_IP'] !== $currentIpHash) {
            error_log('Session IP changed (possible location change or mobile network)');
            // Update IP hash untuk mobile users
            $_SESSION['INITIAL_IP'] = $currentIpHash;
        }
    }

    /**
     * Generate session fingerprint dari User-Agent
     *
     * Tidak pakai IP karena bisa berubah (mobile networks, VPN, proxy)
     *
     * @return string
     */
    private static function generateFingerprint(): string
    {
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $acceptLang = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
        $acceptEnc = $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '';

        // Hash fingerprint components
        return hash('sha256', $ua . '|' . $acceptLang . '|' . $acceptEnc);
    }

    /**
     * Get client IP hash (untuk monitoring, bukan strict validation)
     *
     * @return string
     */
    private static function getClientIpHash(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        // Check reverse proxy headers (CloudFlare, nginx)
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ips[0]);
        }

        // Hash IP untuk privacy (tidak store raw IP)
        return hash('sha256', $ip);
    }

    /**
     * Regenerate session ID periodically untuk prevent fixation
     *
     * @return void
     */
    private static function regenerateIdIfNeeded(): void
    {
        if (!isset($_SESSION['LAST_REGENERATION'])) {
            $_SESSION['LAST_REGENERATION'] = time();
        }

        $elapsed = time() - $_SESSION['LAST_REGENERATION'];

        if ($elapsed > self::REGENERATE_INTERVAL) {
            session_regenerate_id(true); // Delete old session file
            $_SESSION['LAST_REGENERATION'] = time();
        }
    }

    /**
     * Destroy session completely (logout)
     *
     * @return void
     */
    public static function destroy(): void
    {
        $_SESSION = [];

        // Destroy session cookie
        if (isset($_COOKIE[session_name()])) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 3600,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    /**
     * Require authentication atau redirect ke login
     *
     * @return void
     */
    public static function requireAuth(): void
    {
        self::start();

        if (empty($_SESSION['srp_admin_id'])) {
            header('Location: /login.php');
            exit;
        }
    }

    /**
     * Check if user is authenticated
     *
     * @return bool
     */
    public static function isAuthenticated(): bool
    {
        self::start();
        return !empty($_SESSION['srp_admin_id']);
    }

    /**
     * Set user as authenticated
     *
     * @param int $adminId
     * @return void
     */
    public static function setAuthenticated(int $adminId): void
    {
        self::start();

        // Regenerate session ID untuk prevent session fixation attack
        session_regenerate_id(true);

        $_SESSION['srp_admin_id'] = $adminId;
        $_SESSION['AUTH_TIME'] = time();
        $_SESSION['LAST_REGENERATION'] = time();

        // Update fingerprint setelah login
        $_SESSION['FINGERPRINT'] = self::generateFingerprint();
        $_SESSION['INITIAL_IP'] = self::getClientIpHash();
    }

    // =========================================================================
    // CSRF TOKEN MANAGEMENT
    // =========================================================================

    /**
     * Get atau generate CSRF token
     *
     * @return string
     */
    public static function getCsrfToken(): string
    {
        self::start();

        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = Validator::generateToken(32);
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * Validate CSRF token dengan constant-time comparison
     *
     * @param string $providedToken
     * @return bool
     */
    public static function validateCsrfToken(string $providedToken): bool
    {
        self::start();

        $sessionToken = $_SESSION['csrf_token'] ?? '';

        if ($sessionToken === '' || $providedToken === '') {
            return false;
        }

        return Validator::hashEquals($sessionToken, $providedToken);
    }

    /**
     * Regenerate CSRF token (setelah sensitive operations)
     *
     * @return string New token
     */
    public static function regenerateCsrfToken(): string
    {
        self::start();

        $_SESSION['csrf_token'] = Validator::generateToken(32);

        return $_SESSION['csrf_token'];
    }

    // =========================================================================
    // CSP NONCE MANAGEMENT
    // =========================================================================

    /**
     * Get CSP nonce untuk current request
     *
     * Nonce di-generate ulang setiap request untuk maximum security
     *
     * @return string
     */
    public static function getCspNonce(): string
    {
        self::start();

        if (!isset($_SESSION['csp_nonce'])) {
            $_SESSION['csp_nonce'] = Validator::generateToken(
                SecurityHeaders::getRecommendedNonceLength()
            );
        }

        return $_SESSION['csp_nonce'];
    }

    /**
     * Regenerate CSP nonce untuk setiap request
     *
     * @return void
     */
    private static function regenerateCspNonce(): void
    {
        $_SESSION['csp_nonce'] = Validator::generateToken(
            SecurityHeaders::getRecommendedNonceLength()
        );
    }

    // =========================================================================
    // FLASH MESSAGES
    // =========================================================================

    /**
     * Set flash message (one-time message)
     *
     * @param string $type Type: success, error, warning, info
     * @param string $message
     * @return void
     */
    public static function setFlash(string $type, string $message): void
    {
        self::start();

        if (!isset($_SESSION['flash_messages'])) {
            $_SESSION['flash_messages'] = [];
        }

        $_SESSION['flash_messages'][] = [
            'type' => $type,
            'message' => $message
        ];
    }

    /**
     * Get dan clear flash messages
     *
     * @return array<int, array{type: string, message: string}>
     */
    public static function getFlashMessages(): array
    {
        self::start();

        $messages = $_SESSION['flash_messages'] ?? [];
        unset($_SESSION['flash_messages']);

        return $messages;
    }

    /**
     * Check if there are flash messages
     *
     * @return bool
     */
    public static function hasFlashMessages(): bool
    {
        self::start();
        return !empty($_SESSION['flash_messages']);
    }

    // =========================================================================
    // UTILITY METHODS
    // =========================================================================

    /**
     * Check if current connection is HTTPS
     *
     * @return bool
     */
    private static function isHttps(): bool
    {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            return true;
        }

        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            return strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https';
        }

        if (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443) {
            return true;
        }

        return false;
    }

    /**
     * Get session data untuk debugging (NEVER call di production)
     *
     * @return array<string, mixed>
     */
    public static function getDebugInfo(): array
    {
        if (($_ENV['APP_ENV'] ?? 'production') === 'production') {
            return ['error' => 'Debug info not available in production'];
        }

        self::start();

        return [
            'session_id' => session_id(),
            'created' => $_SESSION['CREATED'] ?? null,
            'last_activity' => $_SESSION['LAST_ACTIVITY'] ?? null,
            'last_regeneration' => $_SESSION['LAST_REGENERATION'] ?? null,
            'fingerprint' => $_SESSION['FINGERPRINT'] ?? null,
            'ip_hash' => $_SESSION['INITIAL_IP'] ?? null,
            'authenticated' => !empty($_SESSION['srp_admin_id']),
            'csrf_token_set' => isset($_SESSION['csrf_token']),
            'csp_nonce_set' => isset($_SESSION['csp_nonce']),
        ];
    }
}
