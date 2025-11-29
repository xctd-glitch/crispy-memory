<?php

declare(strict_types=1);

namespace SRP\Controllers;

use SRP\Config\Environment;
use SRP\Middleware\Session;
use SRP\Middleware\SecurityHeaders;
use SRP\Models\Validator;
use SRP\Utils\Csrf;
use SRP\Utils\IpDetector;

/**
 * Authentication Controller (Production-Ready)
 *
 * Features:
 * - CSRF protection untuk login dan logout
 * - Session fingerprinting via Session middleware
 * - Flash messages untuk error/success feedback
 * - SecurityHeaders dengan CSP nonce
 * - HANYA support bcrypt password hash (NO plain text!)
 */
class AuthController
{

    /**
     * Display login page
     */
    public static function login(): void
    {
        Session::start();

        // Redirect authenticated users to dashboard
        if (Session::isAuthenticated()) {
            header('Location: /index.php');
            exit;
        }

        // Apply security headers dengan CSP nonce
        $cspNonce = Session::getCspNonce();
        $isProduction = (Environment::get('APP_ENV') ?? 'production') === 'production';
        SecurityHeaders::apply($cspNonce, $isProduction);
        SecurityHeaders::applyNoCacheHeaders();

        $errorMessage = null;

        // Handle POST (login attempt)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errorMessage = self::handleLoginAttempt();

            // If no error, user was redirected to dashboard
            // If we're still here, login failed
        }

        // Get flash messages (e.g., from logout)
        $flashMessages = Session::getFlashMessages();

        // Get CSRF token for form
        $csrfToken = Session::getCsrfToken();

        require __DIR__ . '/../Views/login.view.php';
    }

    /**
     * Handle logout request
     */
    public static function logout(): void
    {
        Session::start();

        // Logout harus POST dengan CSRF token
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login.php');
            exit;
        }

        // Validate CSRF token
        Csrf::validate(throwOnFailure: true);

        // Destroy session
        Session::destroy();

        // Set flash message (akan muncul di halaman login berikutnya)
        Session::start(); // Restart session untuk flash message
        Session::setFlash('success', 'You have been logged out successfully.');

        header('Location: /login.php');
        exit;
    }

    /**
     * Handle login attempt dengan validation
     *
     * @return string|null Error message jika login gagal, null jika sukses (redirected)
     */
    private static function handleLoginAttempt(): ?string
    {
        $clientIp = IpDetector::getClientIp();

        // Step 1: Validate CSRF token
        if (!Csrf::validate(throwOnFailure: false)) {
            return 'Invalid session token. Please refresh the page and try again.';
        }

        // Step 2: Sanitize input
        $username = Validator::sanitizeString($_POST['username'] ?? '', 100);
        $password = (string) ($_POST['password'] ?? ''); // Don't sanitize password!
        $remember = isset($_POST['remember']) && $_POST['remember'] === '1';

        // Step 3: Validate input length
        if ($username === '' || $password === '') {
            return 'Username and password are required.';
        }

        if (strlen($password) > 255) {
            return 'Invalid password length.';
        }

        // Step 4: Get admin credentials dari .env
        $adminUser = Validator::sanitizeString(Environment::get('SRP_ADMIN_USER'), 100);
        $adminHash = trim(Environment::get('SRP_ADMIN_PASSWORD_HASH'));

        // Step 5: Validate credentials configured
        if ($adminUser === '' || $adminHash === '') {
            error_log('Admin credentials not configured in .env');
            return 'Authentication system is not configured. Please contact administrator.';
        }

        // Step 6: Validate username (constant-time comparison)
        if (!Validator::hashEquals($adminUser, $username)) {
            error_log("Failed login attempt for user: {$username} from IP: {$clientIp}");
            return 'Invalid credentials provided.';
        }

        // Step 7: Verify password hash
        $passwordValid = false;

        // ONLY support bcrypt hash (production-safe)
        if ($adminHash !== '' && str_starts_with($adminHash, '$2')) {
            $passwordValid = password_verify($password, $adminHash);
        } else {
            error_log('Invalid password hash format in .env (must be bcrypt)');
            return 'Authentication system configuration error. Please contact administrator.';
        }

        // Step 8: Handle success/failure
        if ($passwordValid) {
            // Set authenticated session (dengan session regeneration)
            Session::setAuthenticated(1); // adminId = 1 (single admin system)

            // Regenerate CSRF token after login
            Session::regenerateCsrfToken();

            // Handle "Remember Me" option
            if ($remember) {
                $params = session_get_cookie_params();
                setcookie(session_name(), session_id(), [
                    'expires'  => time() + 60 * 60 * 24 * 30, // 30 days
                    'path'     => $params['path'],
                    'domain'   => $params['domain'],
                    'secure'   => $params['secure'],
                    'httponly' => true,
                    'samesite' => 'Strict',
                ]);
            }

            // Log successful login
            error_log("Successful login for user: {$username} from IP: {$clientIp}");

            // Set flash message
            Session::setFlash('success', 'Login successful. Welcome back!');

            // Redirect to dashboard
            header('Location: /index.php');
            exit;
        }

        // Login failed
        error_log("Failed login attempt (wrong password) for user: {$username} from IP: {$clientIp}");

        return 'Invalid credentials provided.';
    }

}
