<?php

declare(strict_types=1);

namespace SRP\Utils;

/**
 * HTTP Client Utility
 * Centralized HTTP request handling with circuit breaker pattern
 */
class HttpClient
{
    private static array $circuitBreakers = [];
    private const CIRCUIT_BREAKER_THRESHOLD = 5; // Failures before opening circuit
    private const CIRCUIT_BREAKER_TIMEOUT = 60; // Seconds to wait before retry

    /**
     * Send HTTP GET request
     *
     * @param string $url Target URL
     * @param array $headers Additional headers
     * @param int $timeout Timeout in seconds
     * @param bool $verifySsl Verify SSL certificate
     * @return array Response with ['success' => bool, 'body' => string, 'code' => int, 'error' => string]
     */
    public static function get(
        string $url,
        array $headers = [],
        int $timeout = 5,
        bool $verifySsl = true
    ): array {
        return self::request('GET', $url, null, $headers, $timeout, $verifySsl);
    }

    /**
     * Send HTTP POST request
     *
     * @param string $url Target URL
     * @param mixed $data Request body (will be JSON encoded if array)
     * @param array $headers Additional headers
     * @param int $timeout Timeout in seconds
     * @param bool $verifySsl Verify SSL certificate
     * @return array Response with ['success' => bool, 'body' => string, 'code' => int, 'error' => string]
     */
    public static function post(
        string $url,
        mixed $data = null,
        array $headers = [],
        int $timeout = 5,
        bool $verifySsl = true
    ): array {
        return self::request('POST', $url, $data, $headers, $timeout, $verifySsl);
    }

    /**
     * Send HTTP request with circuit breaker pattern
     *
     * @param string $method HTTP method
     * @param string $url Target URL
     * @param mixed $data Request body
     * @param array $headers Additional headers
     * @param int $timeout Timeout in seconds
     * @param bool $verifySsl Verify SSL certificate
     * @return array
     */
    private static function request(
        string $method,
        string $url,
        mixed $data = null,
        array $headers = [],
        int $timeout = 5,
        bool $verifySsl = true
    ): array {
        // Check circuit breaker
        if (self::isCircuitOpen($url)) {
            return [
                'success' => false,
                'body' => '',
                'code' => 0,
                'error' => 'Circuit breaker open: service unavailable'
            ];
        }

        // Initialize cURL
        $ch = curl_init($url);

        // Prepare request body
        $bodyContent = '';
        if ($data !== null) {
            if (is_array($data)) {
                $bodyContent = json_encode($data);
                $headers[] = 'Content-Type: application/json';
            } else {
                $bodyContent = (string) $data;
            }
        }

        // Set cURL options
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => min($timeout, 3),
            CURLOPT_SSL_VERIFYPEER => $verifySsl,
            CURLOPT_SSL_VERIFYHOST => $verifySsl ? 2 : 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_HTTPHEADER => $headers,
        ]);

        if ($bodyContent !== '') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $bodyContent);
        }

        // Execute request
        $responseBody = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        // Check for errors
        if ($responseBody === false || $error !== '') {
            self::recordFailure($url);
            return [
                'success' => false,
                'body' => '',
                'code' => $httpCode,
                'error' => $error ?: 'Unknown cURL error'
            ];
        }

        // Success
        self::recordSuccess($url);

        return [
            'success' => true,
            'body' => $responseBody,
            'code' => $httpCode,
            'error' => ''
        ];
    }

    /**
     * Send HTTP request using file_get_contents (fallback for simple requests)
     *
     * @param string $url Target URL
     * @param array $headers Additional headers
     * @param int $timeout Timeout in seconds
     * @return array
     */
    public static function getSimple(string $url, array $headers = [], int $timeout = 5): array
    {
        // Check circuit breaker
        if (self::isCircuitOpen($url)) {
            return [
                'success' => false,
                'body' => '',
                'error' => 'Circuit breaker open'
            ];
        }

        // Create stream context
        $context = stream_context_create([
            'http' => [
                'timeout' => $timeout,
                'ignore_errors' => true,
                'header' => implode("\r\n", $headers)
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true
            ]
        ]);

        // Send request
        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            self::recordFailure($url);
            return [
                'success' => false,
                'body' => '',
                'error' => 'Request failed'
            ];
        }

        self::recordSuccess($url);

        return [
            'success' => true,
            'body' => $response,
            'error' => ''
        ];
    }

    /**
     * Check if circuit breaker is open for a URL
     *
     * @param string $url
     * @return bool
     */
    private static function isCircuitOpen(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST) ?: $url;

        if (!isset(self::$circuitBreakers[$host])) {
            return false;
        }

        $breaker = self::$circuitBreakers[$host];

        // Check if timeout has passed
        if (time() - $breaker['last_failure'] > self::CIRCUIT_BREAKER_TIMEOUT) {
            // Reset circuit breaker
            unset(self::$circuitBreakers[$host]);
            return false;
        }

        return $breaker['failures'] >= self::CIRCUIT_BREAKER_THRESHOLD;
    }

    /**
     * Record a failed request
     *
     * @param string $url
     * @return void
     */
    private static function recordFailure(string $url): void
    {
        $host = parse_url($url, PHP_URL_HOST) ?: $url;

        if (!isset(self::$circuitBreakers[$host])) {
            self::$circuitBreakers[$host] = [
                'failures' => 0,
                'last_failure' => 0
            ];
        }

        self::$circuitBreakers[$host]['failures']++;
        self::$circuitBreakers[$host]['last_failure'] = time();

        // Log if circuit breaker opens
        if (self::$circuitBreakers[$host]['failures'] === self::CIRCUIT_BREAKER_THRESHOLD) {
            error_log("Circuit breaker opened for $host after " . self::CIRCUIT_BREAKER_THRESHOLD . " failures");
        }
    }

    /**
     * Record a successful request
     *
     * @param string $url
     * @return void
     */
    private static function recordSuccess(string $url): void
    {
        $host = parse_url($url, PHP_URL_HOST) ?: $url;

        // Reset circuit breaker on success
        if (isset(self::$circuitBreakers[$host])) {
            unset(self::$circuitBreakers[$host]);
        }
    }

    /**
     * Reset circuit breaker for a host
     *
     * @param string $host
     * @return void
     */
    public static function resetCircuitBreaker(string $host): void
    {
        unset(self::$circuitBreakers[$host]);
    }

    /**
     * Get circuit breaker status for all hosts
     *
     * @return array
     */
    public static function getCircuitBreakerStatus(): array
    {
        return self::$circuitBreakers;
    }
}
