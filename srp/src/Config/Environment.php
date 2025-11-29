<?php
declare(strict_types=1);

namespace SRP\Config;

class Environment
{
    private static bool $loaded = false;

    public static function load(): void
    {
        if (self::$loaded) {
            return;
        }

        $baseDir = dirname(__DIR__, 3);
        $baseFile = $baseDir . '/.env';
        self::loadEnvFile($baseFile, false);

        $envName = getenv('SRP_ENV') ?: ($_ENV['SRP_ENV'] ?? '');
        $envName = trim((string)$envName);
        if ($envName !== '') {
            $namedFile = sprintf('%s/.env.%s', $baseDir, $envName);
            self::loadEnvFile($namedFile, true);
        }

        $explicitFile = getenv('SRP_ENV_FILE') ?: ($_ENV['SRP_ENV_FILE'] ?? '');
        $explicitFile = trim((string)$explicitFile);
        if ($explicitFile !== '') {
            $path = str_contains($explicitFile, '/') ? $explicitFile : sprintf('%s/%s', $baseDir, $explicitFile);
            self::loadEnvFile($path, true);
        }

        self::$loaded = true;
    }

    public static function get(string $key, ?string $default = null): string
    {
        $value = $_ENV[$key] ?? getenv($key);
        if ($value === false || $value === null || $value === '') {
            return $default ?? '';
        }

        return (string)$value;
    }

    /**
     * Get environment variable sebagai boolean
     *
     * @param string $key
     * @param bool $default
     * @return bool
     */
    public static function getBool(string $key, bool $default = false): bool
    {
        $value = self::get($key);
        if ($value === '') {
            return $default;
        }

        return in_array(strtolower($value), ['true', '1', 'yes', 'on'], true);
    }

    /**
     * Get environment variable sebagai integer
     *
     * @param string $key
     * @param int $default
     * @return int
     */
    public static function getInt(string $key, int $default = 0): int
    {
        $value = self::get($key);
        if ($value === '') {
            return $default;
        }

        return (int)$value;
    }

    private static function loadEnvFile(string $path, bool $override = false): void
    {
        static $fileLoadedKeys = [];

        if (!is_readable($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
            $key = trim($key);
            if ($key === '') {
                continue;
            }

            $value = trim($value);
            $hasSystemValue = getenv($key) !== false && !isset($fileLoadedKeys[$key]);

            if ($hasSystemValue) {
                continue;
            }

            if (!$override && isset($fileLoadedKeys[$key])) {
                continue;
            }

            $fileLoadedKeys[$key] = true;
            $_ENV[$key] = $value;
            putenv("{$key}={$value}");
        }
    }

    // ================================================================
    // Multi-Domain Configuration Helpers
    // ================================================================

    /**
     * Get brand domain URL (trackng.app)
     *
     * @return string
     */
    public static function getBrandUrl(): string
    {
        return self::get('APP_URL', 'https://trackng.app');
    }

    /**
     * Get panel URL (panel.trackng.app)
     *
     * @return string
     */
    public static function getPanelUrl(): string
    {
        return self::get('APP_PANEL_URL', 'https://panel.trackng.app');
    }

    /**
     * Get tracking redirect URL (t.qvtrk.com)
     *
     * @return string
     */
    public static function getTrackingRedirectUrl(): string
    {
        return self::get('TRACKING_REDIRECT_URL', 'https://t.qvtrk.com');
    }

    /**
     * Get tracking decision API URL (api.qvtrk.com)
     *
     * @return string
     */
    public static function getTrackingDecisionApi(): string
    {
        return self::get('TRACKING_DECISION_API', 'https://api.qvtrk.com');
    }

    /**
     * Get tracking postback URL (postback.qvtrk.com)
     *
     * @return string
     */
    public static function getTrackingPostbackUrl(): string
    {
        return self::get('TRACKING_POSTBACK_URL', 'https://postback.qvtrk.com');
    }

    /**
     * Get tracking primary domain (qvtrk.com)
     *
     * @return string
     */
    public static function getTrackingPrimaryDomain(): string
    {
        return self::get('TRACKING_PRIMARY_DOMAIN', 'qvtrk.com');
    }

    /**
     * Check if current request adalah dari brand domain
     *
     * @return bool
     */
    public static function isBrandDomain(): bool
    {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $brandDomains = [
            'trackng.app',
            'panel.trackng.app',
            'www.trackng.app'
        ];

        return in_array($host, $brandDomains, true);
    }

    /**
     * Check if current request adalah dari tracking domain
     *
     * @return bool
     */
    public static function isTrackingDomain(): bool
    {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $trackingPrimary = self::getTrackingPrimaryDomain();

        // Check primary domain dan subdomains
        if ($host === $trackingPrimary) {
            return true;
        }

        if (str_ends_with($host, '.' . $trackingPrimary)) {
            return true;
        }

        // Check additional tracking domains
        $additionalDomains = self::get('TRACKING_ADDITIONAL_DOMAINS', '');
        if ($additionalDomains !== '') {
            $domains = array_map('trim', explode(',', $additionalDomains));
            foreach ($domains as $domain) {
                if ($host === $domain || str_ends_with($host, '.' . $domain)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get current domain type (brand atau tracking)
     *
     * @return string 'brand'|'tracking'|'unknown'
     */
    public static function getCurrentDomainType(): string
    {
        if (self::isBrandDomain()) {
            return 'brand';
        }

        if (self::isTrackingDomain()) {
            return 'tracking';
        }

        return 'unknown';
    }

    /**
     * Check if CloudFlare headers should be trusted
     *
     * @return bool
     */
    public static function trustCloudFlareHeaders(): bool
    {
        return self::getBool('TRUST_CF_HEADERS', false);
    }

    /**
     * Get rate limit untuk tracking domain
     *
     * @return array{max: int, window: int}
     */
    public static function getTrackingRateLimit(): array
    {
        return [
            'max' => self::getInt('TRACKING_RATE_LIMIT_MAX', 120),
            'window' => self::getInt('TRACKING_RATE_LIMIT_WINDOW', 60),
        ];
    }

    /**
     * Get rate limit untuk panel domain
     *
     * @return array{max: int, window: int}
     */
    public static function getPanelRateLimit(): array
    {
        return [
            'max' => self::getInt('PANEL_RATE_LIMIT_MAX', 300),
            'window' => self::getInt('PANEL_RATE_LIMIT_WINDOW', 60),
        ];
    }

    /**
     * Check if brand landing page enabled
     *
     * @return bool
     */
    public static function isBrandLandingEnabled(): bool
    {
        return self::getBool('BRAND_ENABLE_LANDING_PAGE', true);
    }

    /**
     * Check if brand documentation enabled
     *
     * @return bool
     */
    public static function isBrandDocsEnabled(): bool
    {
        return self::getBool('BRAND_ENABLE_DOCUMENTATION', true);
    }

    /**
     * Check if tracking VPN check enabled
     *
     * @return bool
     */
    public static function isTrackingVpnCheckEnabled(): bool
    {
        return self::getBool('TRACKING_ENABLE_VPN_CHECK', true);
    }

    /**
     * Check if tracking geo filter enabled
     *
     * @return bool
     */
    public static function isTrackingGeoFilterEnabled(): bool
    {
        return self::getBool('TRACKING_ENABLE_GEO_FILTER', true);
    }

    /**
     * Check if tracking device filter enabled
     *
     * @return bool
     */
    public static function isTrackingDeviceFilterEnabled(): bool
    {
        return self::getBool('TRACKING_ENABLE_DEVICE_FILTER', true);
    }
}
