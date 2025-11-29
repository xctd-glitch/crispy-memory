<?php

declare(strict_types=1);

namespace SRP\Models;

use SRP\Utils\HttpClient;
use InvalidArgumentException;

/**
 * SRP Decision API Client (Production-Ready)
 *
 * Handles semua interaksi dengan SRP Decision API
 * dengan validasi input yang ketat
 */
class SrpClient
{
    private string $apiUrl;
    private string $apiKey;
    private bool $debugMode;

    /**
     * Constructor dengan validasi URL dan API key
     *
     * @param string|null $apiUrl
     * @param string|null $apiKey
     * @param bool $debugMode
     */
    public function __construct(?string $apiUrl = null, ?string $apiKey = null, bool $debugMode = false)
    {
        $this->apiUrl = $apiUrl ?? (getenv('SRP_API_URL') ?: 'https://api.qvtrk.com/decision.php');
        $this->apiKey = $apiKey ?? (getenv('SRP_API_KEY') ?: '');
        $this->debugMode = $debugMode;

        // Validate API URL
        if (!filter_var($this->apiUrl, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Invalid API URL');
        }

        $parsed = parse_url($this->apiUrl);
        if (!isset($parsed['scheme']) || !in_array($parsed['scheme'], ['http', 'https'], true)) {
            throw new InvalidArgumentException('API URL must use HTTP or HTTPS');
        }
    }

    /**
     * Get routing decision dari SRP API dengan validasi ketat
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>|null
     */
    public function getDecision(array $params): ?array
    {
        if (empty($this->apiKey)) {
            $this->debugLog('API key not configured');
            return null;
        }

        // Validate required parameters dengan whitelist
        $required = ['click_id', 'country_code', 'user_agent', 'ip_address'];
        foreach ($required as $field) {
            if (!isset($params[$field]) || $params[$field] === '') {
                $this->debugLog("Missing required field: {$field}");
                return null;
            }
        }

        // Sanitasi dan validasi semua parameter
        $cleanParams = [
            'click_id' => Validator::sanitizeString($params['click_id'], 100),
            'country_code' => strtoupper(Validator::sanitizeString($params['country_code'], 10)),
            'user_agent' => Validator::sanitizeString($params['user_agent'], 500),
            'ip_address' => Validator::sanitizeString($params['ip_address'], 45),
        ];

        // Optional parameter: user_lp
        if (isset($params['user_lp'])) {
            $cleanParams['user_lp'] = Validator::sanitizeString($params['user_lp'], 100);
        }

        // Validate IP address format
        if (!Validator::isValidIp($cleanParams['ip_address'])) {
            $this->debugLog('Invalid IP address format', ['ip' => $cleanParams['ip_address']]);
            return null;
        }

        // Validate country code (optional, bisa 'XX')
        if ($cleanParams['country_code'] !== 'XX' &&
            !Validator::isValidCountryCode($cleanParams['country_code'])) {
            $this->debugLog('Invalid country code', ['country' => $cleanParams['country_code']]);
            // Don't fail, just default to XX
            $cleanParams['country_code'] = 'XX';
        }

        $this->debugLog('Calling SRP API', $cleanParams);

        // Send HTTP request menggunakan HttpClient
        $response = HttpClient::post(
            $this->apiUrl,
            $cleanParams,
            [
                'Content-Type: application/json',
                'X-API-Key: ' . $this->apiKey,
                'User-Agent: SRP-Client/1.0'
            ],
            5,
            true
        );

        if (!$response['success']) {
            $this->debugLog('HTTP request failed', ['error' => $response['error']]);
            return null;
        }

        $httpCode = $response['code'];
        if ($httpCode !== 200) {
            $this->debugLog('HTTP error', [
                'code' => $httpCode,
                'body' => substr($response['body'], 0, 200)
            ]);
            return null;
        }

        $data = json_decode($response['body'], true);

        if (!is_array($data) || !isset($data['ok']) || !$data['ok']) {
            $this->debugLog('Invalid API response', [
                'response' => substr($response['body'], 0, 200)
            ]);
            return null;
        }

        $this->debugLog('API response received', [
            'decision' => $data['decision'] ?? 'unknown',
            'target' => isset($data['target']) ? substr($data['target'], 0, 50) : 'none'
        ]);

        return $data;
    }

    /**
     * Get real client IP address (CloudFlare aware) dengan validasi
     *
     * @return string
     */
    public static function getClientIP(): string
    {
        // Priority order untuk IP detection
        $ipSources = [
            'HTTP_CF_CONNECTING_IP',  // CloudFlare
            'HTTP_TRUE_CLIENT_IP',     // Enterprise proxies
            'HTTP_X_REAL_IP',          // Nginx proxy
            'HTTP_X_FORWARDED_FOR',    // Standard proxy
            'REMOTE_ADDR'              // Direct connection
        ];

        foreach ($ipSources as $source) {
            if (!empty($_SERVER[$source])) {
                $ip = trim((string)$_SERVER[$source]);

                // Handle X-Forwarded-For (bisa comma-separated list)
                if ($source === 'HTTP_X_FORWARDED_FOR' && str_contains($ip, ',')) {
                    $ips = explode(',', $ip);
                    foreach ($ips as $potentialIP) {
                        $potentialIP = trim($potentialIP);

                        // Validate IP format
                        if (!Validator::isValidIp($potentialIP)) {
                            continue;
                        }

                        // Skip private/reserved IPs
                        if (filter_var(
                            $potentialIP,
                            FILTER_VALIDATE_IP,
                            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
                        ) !== false) {
                            return $potentialIP;
                        }
                    }
                } else {
                    // Validate single IP
                    if (Validator::isValidIp($ip)) {
                        return $ip;
                    }
                }
            }
        }

        // Tidak ada fallback ke query parameter untuk security
        // IP harus dari header server yang trusted

        return '0.0.0.0';
    }

    /**
     * Detect device type dari User Agent dengan validasi
     *
     * @param string $userAgent
     * @return string
     */
    public static function detectDevice(string $userAgent): string
    {
        // Sanitasi input
        $ua = Validator::sanitizeString($userAgent, 500);

        // Handle empty user agent
        if ($ua === '') {
            return 'web';
        }

        $uaLower = strtolower($ua);

        // Check untuk bots first (sebelum mobile check untuk avoid false positives)
        if (preg_match('~bot|crawl|spider|facebook|whatsapp|telegram~i', $ua)) {
            return 'bot';
        }

        // Check untuk tablets
        if (preg_match('/tablet|ipad/i', $ua)) {
            return 'wap'; // SRP treats tablets as mobile
        }

        // Check untuk mobile devices
        if (preg_match('/mobile|android|iphone|ipod|blackberry|iemobile|opera mini|windows phone/i', $ua)) {
            return 'wap';
        }

        // Default ke desktop
        return 'web';
    }

    /**
     * Get country code dari CloudFlare atau fallback
     *
     * @return string
     */
    public static function getCountryCode(): string
    {
        // CloudFlare provides country code
        if (!empty($_SERVER['HTTP_CF_IPCOUNTRY'])) {
            $country = strtoupper(trim((string)$_SERVER['HTTP_CF_IPCOUNTRY']));

            // Validate country code
            if (Validator::isValidCountryCode($country)) {
                return $country;
            }
        }

        // Fallback dari query parameter (untuk testing)
        if (!empty($_GET['country_code'])) {
            $country = strtoupper(trim((string)$_GET['country_code']));

            if (Validator::isValidCountryCode($country)) {
                return $country;
            }
        }

        return 'XX'; // Unknown
    }

    /**
     * Build fallback URL dengan original parameters
     *
     * @param string $fallbackPath
     * @return string
     */
    public static function getFallbackUrl(string $fallbackPath = '/_meetups/'): string
    {
        // Detect protocol dengan fallback aman
        $protocol = 'https'; // Default ke HTTPS untuk security

        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            $proto = strtolower(trim((string)$_SERVER['HTTP_X_FORWARDED_PROTO']));
            if (in_array($proto, ['http', 'https'], true)) {
                $protocol = $proto;
            }
        } elseif (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            $protocol = 'https';
        }

        // Get host dengan validasi
        $host = 'localhost'; // Default

        if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
            $host = trim((string)$_SERVER['HTTP_X_FORWARDED_HOST']);
        } elseif (isset($_SERVER['HTTP_HOST'])) {
            $host = trim((string)$_SERVER['HTTP_HOST']);
        }

        // Sanitasi host (remove invalid characters)
        $host = preg_replace('/[^a-z0-9.-]/i', '', $host);

        $baseUrl = "{$protocol}://{$host}";

        // Get query string dengan sanitasi
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        $queryString = trim($queryString);

        return $baseUrl . $fallbackPath . ($queryString !== '' ? '?' . $queryString : '');
    }

    /**
     * Debug log helper
     *
     * @param string $message
     * @param array<string, mixed> $context
     * @return void
     */
    private function debugLog(string $message, array $context = []): void
    {
        if (!$this->debugMode) {
            return;
        }

        $contextStr = !empty($context) ? ' | ' . json_encode($context) : '';
        error_log("[SRP Debug] {$message}{$contextStr}");
    }
}
