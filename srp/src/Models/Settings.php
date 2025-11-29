<?php

declare(strict_types=1);

namespace SRP\Models;

use SRP\Config\Database;
use InvalidArgumentException;
use PDO;

/**
 * Settings Model (Production-Ready dengan PDO Prepared Statements)
 *
 * Semua query menggunakan prepared statements untuk keamanan
 */
class Settings
{
    private static ?array $cache = null;
    private static int $cacheTime = 0;
    private const CACHE_TTL = 3; // Cache untuk 3 detik

    /**
     * Get settings dari database dengan caching
     *
     * @param bool $forceRefresh
     * @return array<string, mixed>
     */
    public static function get(bool $forceRefresh = false): array
    {
        // Return cached data jika masih valid
        if (!$forceRefresh && self::$cache !== null && (time() - self::$cacheTime) < self::CACHE_TTL) {
            return self::$cache;
        }

        // Query dengan prepared statement
        $row = Database::fetchRow(
            'SELECT redirect_url, system_on, country_filter_mode, country_filter_list, updated_at,
                    total_decision_a, total_decision_b, stats_reset_at,
                    postback_enabled, postback_url, default_payout
             FROM settings
             WHERE id = ?',
            [1]
        );

        $data = $row ?: self::getDefaults();

        // Parse redirect_url dari JSON string ke array
        if (isset($data['redirect_url']) && $data['redirect_url'] !== '') {
            $decoded = json_decode($data['redirect_url'], true);
            if (is_array($decoded)) {
                $data['redirect_url'] = $decoded;
            } else {
                // Backward compatibility: convert single URL string ke array
                $data['redirect_url'] = [$data['redirect_url']];
            }
        } else {
            $data['redirect_url'] = [];
        }

        // Update cache
        self::$cache = $data;
        self::$cacheTime = time();

        return $data;
    }

    /**
     * Clear settings cache
     *
     * @return void
     */
    public static function clearCache(): void
    {
        self::$cache = null;
        self::$cacheTime = 0;
    }

    /**
     * Update settings dengan validasi dan prepared statements
     *
     * @param bool $on
     * @param array<int, string>|string $urls
     * @param string $filterMode
     * @param string $filterList
     * @return void
     * @throws InvalidArgumentException
     */
    public static function update(bool $on, array|string $urls, string $filterMode = 'all', string $filterList = ''): void
    {
        // Handle both array dan string input untuk backward compatibility
        if (is_string($urls)) {
            $urls = [$urls];
        }

        if (!is_array($urls)) {
            throw new InvalidArgumentException('redirect_url must be string or array');
        }

        $validatedUrls = [];
        foreach ($urls as $url) {
            $safeUrl = self::validateUrl((string)$url);
            if ($safeUrl !== '') {
                $validatedUrls[] = $safeUrl;
            }
        }

        // Store as JSON string
        $urlsJson = json_encode($validatedUrls);

        // Validate filter mode (whitelist)
        if (!in_array($filterMode, ['all', 'whitelist', 'blacklist'], true)) {
            throw new InvalidArgumentException('Invalid country filter mode');
        }

        // Validate dan clean country codes
        $countries = [];
        if ($filterList !== '') {
            $parts = explode(',', $filterList);
            foreach ($parts as $code) {
                $code = strtoupper(trim($code));
                if ($code !== '' && Validator::isValidCountryCode($code)) {
                    $countries[] = $code;
                }
            }
        }
        $cleanList = implode(',', array_unique($countries));

        // Update dengan prepared statement
        Database::execute(
            'UPDATE settings
               SET system_on = ?,
                   redirect_url = ?,
                   country_filter_mode = ?,
                   country_filter_list = ?,
                   updated_at = UNIX_TIMESTAMP()
             WHERE id = ?',
            [$on ? 1 : 0, $urlsJson, $filterMode, $cleanList, 1]
        );

        // Clear cache setelah update
        self::clearCache();
    }

    /**
     * Get country filter configuration
     *
     * @return array{mode: string, list: array<int, string>}
     */
    public static function getCountryFilter(): array
    {
        $cfg = self::get();
        $list = $cfg['country_filter_list'] !== '' ? explode(',', $cfg['country_filter_list']) : [];

        return [
            'mode' => $cfg['country_filter_mode'] ?? 'all',
            'list' => array_map('strtoupper', $list),
        ];
    }

    /**
     * Get default settings
     *
     * @return array<string, mixed>
     */
    private static function getDefaults(): array
    {
        return [
            'redirect_url' => [],
            'system_on' => 0,
            'country_filter_mode' => 'all',
            'country_filter_list' => '',
            'updated_at' => 0,
            'total_decision_a' => 0,
            'total_decision_b' => 0,
            'stats_reset_at' => 0,
            'postback_enabled' => 1, // Always enabled
            'postback_url' => '',
            'default_payout' => 0.00,
        ];
    }

    /**
     * Update postback configuration dengan prepared statements
     *
     * @param bool $enabled
     * @param string $url
     * @param float $payout
     * @return void
     * @throws InvalidArgumentException
     */
    public static function updatePostback(bool $enabled, string $url, float $payout): void
    {
        $safeUrl = trim($url);

        // Jika enabling postback, require valid URL
        if ($enabled && $safeUrl === '') {
            throw new InvalidArgumentException('Postback URL is required when enabling postback');
        }

        // Validate URL dengan placeholder substitution
        if ($safeUrl !== '') {
            $testUrl = str_replace(
                ['{country}', '{traffic_type}', '{payout}'],
                ['US', 'WAP', '1.00'],
                $safeUrl
            );

            if (!filter_var($testUrl, FILTER_VALIDATE_URL)) {
                throw new InvalidArgumentException('Invalid postback URL format');
            }
        }

        // Update dengan prepared statement
        Database::execute(
            'UPDATE settings
               SET postback_enabled = ?,
                   postback_url = ?,
                   default_payout = ?,
                   updated_at = UNIX_TIMESTAMP()
             WHERE id = ?',
            [$enabled ? 1 : 0, $safeUrl, $payout, 1]
        );

        // Clear cache setelah update
        self::clearCache();
    }

    /**
     * Check dan reset stats jika sudah waktunya (daily reset)
     *
     * @return void
     */
    public static function checkAndResetStatsIfNeeded(): void
    {
        // Get stats_reset_at dengan prepared statement
        $row = Database::fetchRow('SELECT stats_reset_at FROM settings WHERE id = ?', [1]);

        $statsResetAt = $row ? (int)$row['stats_reset_at'] : 0;
        $now = time();

        // Jika stats_reset_at adalah 0 (never reset), set ke now
        if ($statsResetAt === 0) {
            Database::execute('UPDATE settings SET stats_reset_at = UNIX_TIMESTAMP() WHERE id = ?', [1]);
            return;
        }

        // Get today's midnight timestamp (00:00:00)
        $todayMidnight = strtotime('today midnight');
        $lastResetDate = date('Y-m-d', $statsResetAt);
        $currentDate = date('Y-m-d', $now);

        // Reset daily at midnight - check jika kita di hari baru
        if ($lastResetDate !== $currentDate && $now >= $todayMidnight) {
            // Reset stats untuk hari baru dengan prepared statement
            Database::execute(
                'UPDATE settings
                   SET total_decision_a = 0,
                       total_decision_b = 0,
                       stats_reset_at = UNIX_TIMESTAMP()
                 WHERE id = ?',
                [1]
            );
        }
    }

    /**
     * Increment Decision A counter (prepared statement)
     *
     * @return void
     */
    public static function incrementDecisionA(): void
    {
        Database::execute(
            'UPDATE settings SET total_decision_a = total_decision_a + 1 WHERE id = ?',
            [1]
        );
        self::clearCache();
    }

    /**
     * Increment Decision B counter (prepared statement)
     *
     * @return void
     */
    public static function incrementDecisionB(): void
    {
        Database::execute(
            'UPDATE settings SET total_decision_b = total_decision_b + 1 WHERE id = ?',
            [1]
        );
        self::clearCache();
    }

    /**
     * Validate URL dengan strict checks
     *
     * @param string $url
     * @return string
     * @throws InvalidArgumentException
     */
    private static function validateUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Invalid redirect_url format');
        }

        $parsed = parse_url($url);
        if (!isset($parsed['scheme']) || $parsed['scheme'] !== 'https') {
            throw new InvalidArgumentException('Redirect URL must use HTTPS');
        }

        if (!isset($parsed['host']) || !preg_match('/^[a-z0-9.-]+$/i', $parsed['host'])) {
            throw new InvalidArgumentException('Invalid redirect_url host');
        }

        if (strlen($url) > 2048) {
            throw new InvalidArgumentException('Redirect URL too long');
        }

        return $url;
    }
}
