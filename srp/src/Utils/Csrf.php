<?php

declare(strict_types=1);

namespace SRP\Utils;

use SRP\Middleware\Session;

/**
 * CSRF Protection Utility (Production-Ready)
 *
 * Simplified CSRF token validation untuk Controllers
 */
class Csrf
{
    /**
     * Validate CSRF token dari request atau throw 403
     *
     * Check token dari:
     * 1. POST body (_csrf_token)
     * 2. Header (X-CSRF-Token)
     *
     * @param bool $throwOnFailure Jika true, throw 403 dan exit. Jika false, return bool
     * @return bool True jika valid (hanya jika $throwOnFailure = false)
     */
    public static function validate(bool $throwOnFailure = true): bool
    {
        $providedToken = self::getTokenFromRequest();

        if ($providedToken === null) {
            if ($throwOnFailure) {
                self::fail('CSRF token not provided');
            }
            return false;
        }

        $isValid = Session::validateCsrfToken($providedToken);

        if (!$isValid && $throwOnFailure) {
            self::fail('Invalid CSRF token');
        }

        return $isValid;
    }

    /**
     * Get CSRF token dari request (POST body atau header)
     *
     * @return string|null
     */
    private static function getTokenFromRequest(): ?string
    {
        // Check POST body first
        if (isset($_POST['_csrf_token']) && $_POST['_csrf_token'] !== '') {
            return (string)$_POST['_csrf_token'];
        }

        // Check custom header (untuk AJAX requests)
        if (isset($_SERVER['HTTP_X_CSRF_TOKEN']) && $_SERVER['HTTP_X_CSRF_TOKEN'] !== '') {
            return (string)$_SERVER['HTTP_X_CSRF_TOKEN'];
        }

        return null;
    }

    /**
     * Fail dengan 403 response dan exit
     *
     * @param string $reason
     * @return never
     */
    private static function fail(string $reason): never
    {
        error_log("CSRF validation failed: {$reason} from IP " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));

        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode([
            'ok' => false,
            'error' => 'CSRF validation failed'
        ]);

        exit;
    }

    /**
     * Get CSRF token untuk embedding di forms
     *
     * @return string
     */
    public static function getToken(): string
    {
        return Session::getCsrfToken();
    }

    /**
     * Render hidden input field dengan CSRF token
     *
     * @return string HTML input element
     */
    public static function field(): string
    {
        $token = self::getToken();
        return '<input type="hidden" name="_csrf_token" value="' .
               htmlspecialchars($token, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '">';
    }

    /**
     * Render meta tag dengan CSRF token (untuk AJAX)
     *
     * @return string HTML meta element
     */
    public static function metaTag(): string
    {
        $token = self::getToken();
        return '<meta name="csrf-token" content="' .
               htmlspecialchars($token, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '">';
    }

    /**
     * Check if current request method requires CSRF validation
     *
     * @return bool
     */
    public static function requiresValidation(): bool
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        return in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true);
    }

    /**
     * Middleware-style CSRF check (auto-validate untuk POST/PUT/PATCH/DELETE)
     *
     * Call di awal controller untuk auto-protect semua state-changing operations
     *
     * @return void
     */
    public static function protect(): void
    {
        if (self::requiresValidation()) {
            self::validate(throwOnFailure: true);
        }
    }
}
