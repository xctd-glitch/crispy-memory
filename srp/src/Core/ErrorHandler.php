<?php

declare(strict_types=1);

namespace SRP\Core;

/**
 * Production-Ready Error Handler
 *
 * Handles all PHP errors and exceptions:
 * - Returns JSON for AJAX/API requests
 * - Renders custom error page for browser requests
 * - Logs all errors without exposing details to users
 */
class ErrorHandler
{
    private static bool $registered = false;

    /** @var string|null Path to custom error view */
    private static ?string $errorViewPath = null;

    /**
     * Register error handlers
     */
    public static function register(): void
    {
        if (self::$registered) {
            return;
        }

        // Set error handler
        set_error_handler([self::class, 'handleError']);

        // Set exception handler
        set_exception_handler([self::class, 'handleException']);

        // Register shutdown function untuk fatal errors
        register_shutdown_function([self::class, 'handleShutdown']);

        // Try to find error view path
        self::$errorViewPath = self::findErrorViewPath();

        self::$registered = true;
    }

    /**
     * Find the error view file path
     */
    private static function findErrorViewPath(): ?string
    {
        $possiblePaths = [
            __DIR__ . '/../Views/error.view.php',
            dirname(__DIR__) . '/Views/error.view.php',
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Handle PHP errors
     */
    public static function handleError(int $severity, string $message, string $file, int $line): bool
    {
        if (!(error_reporting() & $severity)) {
            // Error reporting is disabled for this error
            return false;
        }

        // Log error details (JANGAN expose ke user)
        error_log(sprintf(
            '[%s] PHP Error [%s]: %s in %s:%d',
            date('Y-m-d H:i:s'),
            self::severityToString($severity),
            $message,
            $file,
            $line
        ));

        // Convert to exception
        throw new \ErrorException($message, 0, $severity, $file, $line);
    }

    /**
     * Handle uncaught exceptions
     */
    public static function handleException(\Throwable $e): void
    {
        // Log full error details
        error_log(sprintf(
            '[%s] Uncaught %s: %s in %s:%d%sStack trace:%s%s',
            date('Y-m-d H:i:s'),
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            PHP_EOL,
            PHP_EOL,
            $e->getTraceAsString()
        ));

        // Determine HTTP status code
        $httpCode = 500;
        if ($e->getCode() >= 400 && $e->getCode() < 600) {
            $httpCode = $e->getCode();
        }

        self::sendErrorResponse($e->getMessage(), $httpCode, $e);
    }

    /**
     * Handle fatal errors
     */
    public static function handleShutdown(): void
    {
        $error = error_get_last();
        if ($error && ($error['type'] & (E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_PARSE))) {
            // Log fatal error
            error_log(sprintf(
                '[%s] Fatal Error [%s]: %s in %s:%d',
                date('Y-m-d H:i:s'),
                self::severityToString($error['type']),
                $error['message'],
                $error['file'],
                $error['line']
            ));

            self::sendErrorResponse('A system error occurred', 500);
        }
    }

    /**
     * Send error response as JSON or HTML based on request type
     *
     * @param string $message Error message
     * @param int $code HTTP status code
     * @param \Throwable|null $exception Original exception
     */
    private static function sendErrorResponse(string $message, int $code = 500, ?\Throwable $exception = null): void
    {
        // Clean any output buffer
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Determine if this is AJAX/API request
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        $acceptsJson = isset($_SERVER['HTTP_ACCEPT']) &&
                      strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $isJsonRequest = strpos($contentType, 'application/json') !== false;

        // Set appropriate status code
        $httpCode = ($code >= 400 && $code < 600) ? $code : 500;
        http_response_code($httpCode);

        // Check environment
        $isProduction = ($_ENV['SRP_ENV'] ?? $_ENV['APP_ENV'] ?? 'production') === 'production';
        $errorMessage = $isProduction ? self::getGenericMessage($httpCode) : $message;

        // Return JSON for AJAX/API requests
        if ($isAjax || $acceptsJson || $isJsonRequest) {
            self::sendJsonError($errorMessage, $httpCode, $isProduction, $exception);
            return;
        }

        // Render HTML error page
        self::sendHtmlError($errorMessage, $httpCode, $isProduction, $exception);
    }

    /**
     * Send JSON error response
     */
    private static function sendJsonError(
        string $message,
        int $code,
        bool $isProduction,
        ?\Throwable $exception
    ): void {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, no-store, must-revalidate');

        $response = [
            'ok' => false,
            'error' => $message,
            'code' => self::getErrorCode($code),
        ];

        // Add debug info in development
        if (!$isProduction && $exception !== null) {
            $response['debug'] = [
                'exception' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ];
        }

        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Send HTML error page
     */
    private static function sendHtmlError(
        string $message,
        int $code,
        bool $isProduction,
        ?\Throwable $exception
    ): void {
        header('Content-Type: text/html; charset=utf-8');
        header('Cache-Control: no-cache, no-store, must-revalidate');

        // Prepare view variables
        $title = 'Error';
        $details = '';
        $showBack = true;
        $backUrl = '/';
        $nonce = '';

        // Get CSP nonce if Session class is available
        if (class_exists('\SRP\Middleware\Session')) {
            try {
                $nonce = \SRP\Middleware\Session::getCspNonce() ?? '';
            } catch (\Throwable $e) {
                // Ignore
            }
        }

        // Show details in development
        if (!$isProduction && $exception !== null) {
            $details = sprintf(
                "%s\n\nFile: %s:%d\n\nStack Trace:\n%s",
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine(),
                $exception->getTraceAsString()
            );
        }

        // Try to use custom error view
        if (self::$errorViewPath !== null && file_exists(self::$errorViewPath)) {
            include self::$errorViewPath;
            exit;
        }

        // Fallback: inline HTML
        self::sendFallbackHtml($code, $message);
    }

    /**
     * Send fallback HTML error page
     */
    private static function sendFallbackHtml(int $code, string $message): void
    {
        $colors = [
            400 => '#e67e22',
            401 => '#9b59b6',
            403 => '#e74c3c',
            404 => '#3498db',
            500 => '#e74c3c',
            502 => '#9b59b6',
            503 => '#f39c12',
        ];

        $color = $colors[$code] ?? '#e74c3c';
        $e = fn(string $str): string => htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');

        echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Error {$code}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #1a1a2e, #16213e);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #fff;
        }
        .container { text-align: center; max-width: 500px; }
        .code { font-size: 100px; font-weight: 800; color: {$color}; line-height: 1; }
        h1 { font-size: 24px; margin: 20px 0 10px; }
        p { color: rgba(255,255,255,0.7); margin-bottom: 30px; }
        a {
            display: inline-block;
            padding: 12px 24px;
            background: {$color};
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            margin: 5px;
        }
        a:hover { filter: brightness(1.1); }
    </style>
</head>
<body>
    <div class="container">
        <div class="code">{$code}</div>
        <h1>Error</h1>
        <p>{$e($message)}</p>
        <a href="/">Go Home</a>
    </div>
</body>
</html>
HTML;
        exit;
    }

    /**
     * Get generic error message for production
     */
    private static function getGenericMessage(int $code): string
    {
        $messages = [
            400 => 'The request could not be understood by the server.',
            401 => 'Authentication is required to access this resource.',
            403 => 'You do not have permission to access this resource.',
            404 => 'The requested resource could not be found.',
            405 => 'The request method is not allowed for this resource.',
            408 => 'The request timed out. Please try again.',
            429 => 'Too many requests. Please slow down.',
            500 => 'An internal server error occurred.',
            502 => 'The server received an invalid response.',
            503 => 'The service is temporarily unavailable.',
            504 => 'The server did not respond in time.',
        ];

        return $messages[$code] ?? 'An unexpected error occurred. Please try again later.';
    }

    /**
     * Get error code string
     */
    private static function getErrorCode(int $httpCode): string
    {
        $codes = [
            400 => 'BAD_REQUEST',
            401 => 'UNAUTHORIZED',
            403 => 'FORBIDDEN',
            404 => 'NOT_FOUND',
            405 => 'METHOD_NOT_ALLOWED',
            408 => 'REQUEST_TIMEOUT',
            429 => 'TOO_MANY_REQUESTS',
            500 => 'INTERNAL_ERROR',
            502 => 'BAD_GATEWAY',
            503 => 'SERVICE_UNAVAILABLE',
            504 => 'GATEWAY_TIMEOUT',
        ];

        return $codes[$httpCode] ?? 'SYSTEM_ERROR';
    }

    /**
     * Convert error severity to string
     */
    public static function severityToString(int $severity): string
    {
        $severities = [
            E_ERROR             => 'E_ERROR',
            E_WARNING           => 'E_WARNING',
            E_PARSE             => 'E_PARSE',
            E_NOTICE            => 'E_NOTICE',
            E_CORE_ERROR        => 'E_CORE_ERROR',
            E_CORE_WARNING      => 'E_CORE_WARNING',
            E_COMPILE_ERROR     => 'E_COMPILE_ERROR',
            E_COMPILE_WARNING   => 'E_COMPILE_WARNING',
            E_USER_ERROR        => 'E_USER_ERROR',
            E_USER_WARNING      => 'E_USER_WARNING',
            E_USER_NOTICE       => 'E_USER_NOTICE',
            E_STRICT            => 'E_STRICT',
            E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
            E_DEPRECATED        => 'E_DEPRECATED',
            E_USER_DEPRECATED   => 'E_USER_DEPRECATED',
        ];

        return $severities[$severity] ?? 'UNKNOWN';
    }

    /**
     * Manually trigger an error page
     *
     * @param int $code HTTP status code
     * @param string $message Custom message
     */
    public static function abort(int $code, string $message = ''): never
    {
        $defaultMessages = [
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            429 => 'Too Many Requests',
            500 => 'Internal Server Error',
            503 => 'Service Unavailable',
        ];

        $message = $message ?: ($defaultMessages[$code] ?? 'Error');

        self::sendErrorResponse($message, $code);
        exit;
    }
}
