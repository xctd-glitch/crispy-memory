<?php

declare(strict_types=1);

namespace SRP\Utils;

/**
 * CORS Handler Utility
 * Centralized CORS (Cross-Origin Resource Sharing) handling
 */
class CorsHandler
{
    /**
     * Handle CORS preflight and set appropriate headers
     *
     * @param array $allowedOrigins List of allowed origin domains
     * @param array $allowedMethods HTTP methods to allow (default: GET, POST, OPTIONS)
     * @param array $allowedHeaders HTTP headers to allow
     * @param int $maxAge Cache duration for preflight requests (default: 86400 = 24 hours)
     * @return bool True if this was a preflight OPTIONS request and was handled
     */
    public static function handle(
        array $allowedOrigins = [],
        array $allowedMethods = ['GET', 'POST', 'OPTIONS'],
        array $allowedHeaders = ['Content-Type', 'X-API-Key', 'X-CSRF-Token', 'X-Requested-With'],
        int $maxAge = 86400
    ): bool {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // Check if origin is allowed
        $isOriginAllowed = self::isOriginAllowed($origin, $allowedOrigins);

        // Set CORS headers
        if ($isOriginAllowed) {
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Access-Control-Allow-Credentials: true');
        } elseif (self::isLocalEnvironment()) {
            // Allow all origins in local development
            header('Access-Control-Allow-Origin: *');
        }

        // Handle preflight OPTIONS request
        if ($requestMethod === 'OPTIONS') {
            header('Access-Control-Allow-Methods: ' . implode(', ', $allowedMethods));
            header('Access-Control-Allow-Headers: ' . implode(', ', $allowedHeaders));
            header('Access-Control-Max-Age: ' . $maxAge);
            header('Content-Length: 0');
            header('Content-Type: text/plain');
            http_response_code(204);
            return true; // Preflight request handled
        }

        return false; // Not a preflight request
    }

    /**
     * Check if origin is in the allowed list
     *
     * @param string $origin The origin to check
     * @param array $allowedOrigins List of allowed origins
     * @return bool
     */
    private static function isOriginAllowed(string $origin, array $allowedOrigins): bool
    {
        if (empty($origin)) {
            return false;
        }

        // Exact match
        if (in_array($origin, $allowedOrigins, true)) {
            return true;
        }

        // Wildcard subdomain matching (e.g., *.example.com)
        foreach ($allowedOrigins as $allowed) {
            if (str_starts_with($allowed, '*.')) {
                $domain = substr($allowed, 2);
                if (str_ends_with($origin, $domain)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if running in local development environment
     *
     * @return bool
     */
    private static function isLocalEnvironment(): bool
    {
        $serverName = $_SERVER['SERVER_NAME'] ?? '';
        $appEnv = getenv('APP_ENV') ?: 'production';

        return in_array($serverName, ['localhost', '127.0.0.1'], true)
            || $appEnv === 'development';
    }

    /**
     * Send JSON response with CORS headers
     *
     * @param array $data Response data
     * @param int $statusCode HTTP status code
     * @param array $allowedOrigins Allowed origins for CORS
     * @return never
     */
    public static function jsonResponse(
        array $data,
        int $statusCode = 200,
        array $allowedOrigins = [],
        bool $compress = true
    ): never {
        // Handle CORS
        self::handle($allowedOrigins);

        // Set headers
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');

        // Encode JSON
        $json = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

        // Enable gzip compression if supported and beneficial
        if ($compress && self::shouldCompress($json)) {
            $acceptEncoding = $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '';

            if (str_contains($acceptEncoding, 'gzip')) {
                $compressed = gzencode($json, 6); // Level 6 balance between speed and compression
                if ($compressed !== false && strlen($compressed) < strlen($json)) {
                    header('Content-Encoding: gzip');
                    header('Content-Length: ' . strlen($compressed));
                    echo $compressed;
                    exit;
                }
            }
        }

        // Send uncompressed response
        header('Content-Length: ' . strlen($json));
        echo $json;
        exit;
    }

    /**
     * Check if response should be compressed
     */
    private static function shouldCompress(string $content): bool
    {
        // Only compress if content is larger than 1KB
        return strlen($content) > 1024 && function_exists('gzencode');
    }

    /**
     * Send error response with CORS headers
     *
     * @param string $error Error message
     * @param int $statusCode HTTP status code
     * @param array $allowedOrigins Allowed origins for CORS
     * @return never
     */
    public static function errorResponse(
        string $error,
        int $statusCode = 400,
        array $allowedOrigins = []
    ): never {
        self::jsonResponse([
            'ok' => false,
            'error' => $error
        ], $statusCode, $allowedOrigins);
    }
}
