<?php

declare(strict_types=1);

namespace SRP\Utils;

use SRP\Config\Environment;
use SRP\Models\Validator;

/**
 * IP Detection Utility
 *
 * Centralized IP detection with CloudFlare and proxy support
 * Prevents code duplication across controllers
 */
class IpDetector
{
    /**
     * Priority order for IP detection headers
     */
    private const IP_HEADERS = [
        'HTTP_CF_CONNECTING_IP',     // CloudFlare
        'HTTP_TRUE_CLIENT_IP',        // Enterprise proxies
        'HTTP_X_REAL_IP',            // Nginx proxy
        'HTTP_X_FORWARDED_FOR',      // Standard proxy
        'REMOTE_ADDR'                // Direct connection
    ];

    /**
     * Trusted proxy IPs (loaded from environment)
     *
     * @var array<string>
     */
    private static ?array $trustedProxies = null;

    /**
     * Get real client IP address with proxy support
     *
     * @param bool $validatePublic Whether to check if IP is public (not private/reserved)
     * @return string
     */
    public static function getClientIp(bool $validatePublic = false): string
    {
        // Load trusted proxies from environment
        if (self::$trustedProxies === null) {
            $proxies = Environment::get('TRUSTED_PROXIES', '');
            self::$trustedProxies = !empty($proxies) ? explode(',', $proxies) : [];
        }

        // Check if we should trust proxy headers
        $trustProxyHeaders = self::shouldTrustProxyHeaders();

        foreach (self::IP_HEADERS as $header) {
            // Skip proxy headers if not trusted
            if (!$trustProxyHeaders && $header !== 'REMOTE_ADDR') {
                continue;
            }

            if (!empty($_SERVER[$header])) {
                $ip = self::extractIpFromHeader($_SERVER[$header]);

                if ($ip !== null) {
                    // Validate if IP should be public
                    if ($validatePublic && !Validator::isPublicIp($ip)) {
                        continue;
                    }

                    return $ip;
                }
            }
        }

        // Fallback for testing/development (ONLY in development mode)
        if (Environment::get('APP_ENV') === 'development' &&
            isset($_GET['ip_address']) &&
            Validator::isValidIp((string)$_GET['ip_address'])) {
            return (string)$_GET['ip_address'];
        }

        return '0.0.0.0';
    }

    /**
     * Get client IP with country code (CloudFlare aware)
     *
     * @return array{ip: string, country: string}
     */
    public static function getClientIpWithCountry(): array
    {
        $ip = self::getClientIp();
        $country = self::getCountryCode();

        return [
            'ip' => $ip,
            'country' => $country
        ];
    }

    /**
     * Get country code from CloudFlare or fallback
     *
     * @return string
     */
    public static function getCountryCode(): string
    {
        // CloudFlare provides country code
        if (!empty($_SERVER['HTTP_CF_IPCOUNTRY'])) {
            $country = strtoupper(trim((string)$_SERVER['HTTP_CF_IPCOUNTRY']));

            if (Validator::isValidCountryCode($country)) {
                return $country;
            }
        }

        // Fallback from query parameter (testing)
        if (!empty($_GET['country_code'])) {
            $country = strtoupper(trim((string)$_GET['country_code']));

            if (Validator::isValidCountryCode($country)) {
                return $country;
            }
        }

        return 'XX'; // Unknown
    }

    /**
     * Check if current request is from CloudFlare
     *
     * @return bool
     */
    public static function isFromCloudFlare(): bool
    {
        return !empty($_SERVER['HTTP_CF_RAY']) ||
               !empty($_SERVER['HTTP_CF_CONNECTING_IP']) ||
               !empty($_SERVER['HTTP_CF_IPCOUNTRY']);
    }

    /**
     * Check if IP is from trusted proxy
     *
     * @param string $ip
     * @return bool
     */
    public static function isTrustedProxy(string $ip): bool
    {
        if (empty(self::$trustedProxies)) {
            return false;
        }

        foreach (self::$trustedProxies as $trustedRange) {
            if (self::isIpInRange($ip, trim($trustedRange))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extract IP from header value (handles comma-separated lists)
     *
     * @param string $headerValue
     * @return string|null
     */
    private static function extractIpFromHeader(string $headerValue): ?string
    {
        $headerValue = trim($headerValue);

        // Handle X-Forwarded-For comma-separated list
        if (str_contains($headerValue, ',')) {
            $ips = explode(',', $headerValue);

            // Look for first valid public IP
            foreach ($ips as $potentialIp) {
                $potentialIp = trim($potentialIp);

                if (Validator::isValidIp($potentialIp)) {
                    // Skip private/reserved IPs in proxy chain
                    if (Validator::isPublicIp($potentialIp)) {
                        return $potentialIp;
                    }
                }
            }

            // If no public IP found, use first valid IP
            foreach ($ips as $potentialIp) {
                $potentialIp = trim($potentialIp);

                if (Validator::isValidIp($potentialIp)) {
                    return $potentialIp;
                }
            }
        } else {
            // Single IP value
            if (Validator::isValidIp($headerValue)) {
                return $headerValue;
            }
        }

        return null;
    }

    /**
     * Check if we should trust proxy headers
     *
     * @return bool
     */
    private static function shouldTrustProxyHeaders(): bool
    {
        // Always trust if explicitly configured
        if (Environment::getBool('TRUST_PROXY_HEADERS', false)) {
            return true;
        }

        // Trust CloudFlare headers if configured
        if (Environment::getBool('TRUST_CF_HEADERS', false) && self::isFromCloudFlare()) {
            return true;
        }

        // Check if remote address is from trusted proxy
        $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        if (self::isTrustedProxy($remoteAddr)) {
            return true;
        }

        // Development environment
        if (Environment::get('APP_ENV') === 'development') {
            return true;
        }

        return false;
    }

    /**
     * Check if IP is in CIDR range
     *
     * @param string $ip
     * @param string $cidr
     * @return bool
     */
    private static function isIpInRange(string $ip, string $cidr): bool
    {
        // Handle single IP (no CIDR notation)
        if (!str_contains($cidr, '/')) {
            return $ip === $cidr;
        }

        // IPv4 CIDR check
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            [$subnet, $mask] = explode('/', $cidr);

            if (!filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                return false;
            }

            $subnet = ip2long($subnet);
            $ip = ip2long($ip);
            $mask = -1 << (32 - (int)$mask);
            $subnet &= $mask;

            return ($ip & $mask) === $subnet;
        }

        // IPv6 CIDR check
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            [$subnet, $maskBits] = explode('/', $cidr);

            if (!filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                return false;
            }

            // Convert to binary for comparison
            $subnetBinary = inet_pton($subnet);
            $ipBinary = inet_pton($ip);

            if ($subnetBinary === false || $ipBinary === false) {
                return false;
            }

            $maskBits = (int)$maskBits;
            $bytesToCheck = intval($maskBits / 8);
            $bitsToCheck = $maskBits % 8;

            // Check full bytes
            for ($i = 0; $i < $bytesToCheck; $i++) {
                if ($subnetBinary[$i] !== $ipBinary[$i]) {
                    return false;
                }
            }

            // Check remaining bits
            if ($bitsToCheck > 0 && $bytesToCheck < 16) {
                $mask = 0xFF << (8 - $bitsToCheck);
                $subnetByte = ord($subnetBinary[$bytesToCheck]);
                $ipByte = ord($ipBinary[$bytesToCheck]);

                if (($subnetByte & $mask) !== ($ipByte & $mask)) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Get request fingerprint for rate limiting
     *
     * @return string
     */
    public static function getRequestFingerprint(): string
    {
        $ip = self::getClientIp();
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

        // Create fingerprint from IP + User Agent hash
        return md5($ip . '|' . $ua);
    }

    /**
     * Check if IP is localhost/development
     *
     * @param string $ip
     * @return bool
     */
    public static function isLocalhost(string $ip): bool
    {
        return in_array($ip, ['127.0.0.1', '::1', 'localhost'], true);
    }

    /**
     * Get all request headers for debugging
     *
     * @return array<string, string>
     */
    public static function getAllHeaders(): array
    {
        $headers = [];

        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $headerName = str_replace('_', '-', substr($key, 5));
                $headers[$headerName] = (string)$value;
            }
        }

        return $headers;
    }
}