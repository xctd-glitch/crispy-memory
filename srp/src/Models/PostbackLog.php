<?php

declare(strict_types=1);

namespace SRP\Models;

use SRP\Config\Database;
use SRP\Utils\HttpClient;
use InvalidArgumentException;

/**
 * Postback Log Model (PDO-based, Production-Ready)
 *
 * Semua query menggunakan PDO prepared statements
 */
class PostbackLog
{
    private static ?array $cache = null;
    private static int $cacheTime = 0;
    private const CACHE_TTL = 5; // Cache untuk 5 detik

    /**
     * Create postback log entry dengan validasi
     *
     * @param array<string, mixed> $data
     * @return void
     * @throws InvalidArgumentException
     */
    public static function create(array $data): void
    {
        // Validasi required fields
        $requiredFields = ['country_code', 'traffic_type', 'payout', 'postback_url'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                throw new InvalidArgumentException("Missing required field: {$field}");
            }
        }

        // Sanitasi dan validasi
        $countryCode = Validator::sanitizeString($data['country_code'], 10);
        $trafficType = Validator::sanitizeString($data['traffic_type'], 50);
        $payout = (float)$data['payout'];
        $postbackUrl = Validator::sanitizeString($data['postback_url'], 2048);
        $responseCode = isset($data['response_code']) ? (int)$data['response_code'] : null;
        $responseBody = isset($data['response_body'])
            ? Validator::sanitizeString($data['response_body'], 500)
            : null;
        $success = !empty($data['success']) ? 1 : 0;

        // Validate country code
        if ($countryCode !== '' && !Validator::isValidCountryCode($countryCode)) {
            throw new InvalidArgumentException('Invalid country code');
        }

        // Insert dengan PDO prepared statement
        Database::execute(
            'INSERT INTO postback_logs
             (ts, country_code, traffic_type, payout, postback_url, response_code, response_body, success)
             VALUES (UNIX_TIMESTAMP(), ?, ?, ?, ?, ?, ?, ?)',
            [
                $countryCode,
                $trafficType,
                $payout,
                $postbackUrl,
                $responseCode,
                $responseBody,
                $success
            ]
        );

        // Clear cache setelah insert
        self::clearCache();
    }

    /**
     * Get recent postback logs dengan caching
     *
     * @param int $limit
     * @param bool $forceRefresh
     * @return array<int, array<string, mixed>>
     */
    public static function getRecent(int $limit = 20, bool $forceRefresh = false): array
    {
        // Return cached data jika masih valid
        if (!$forceRefresh && self::$cache !== null && (time() - self::$cacheTime) < self::CACHE_TTL) {
            return self::$cache;
        }

        // Batasi limit
        $limit = max(1, min(100, $limit));

        // Query dengan PDO prepared statement
        $logs = Database::fetchAll(
            'SELECT id, ts, country_code, traffic_type, payout, postback_url,
                    response_code, response_body, success
             FROM postback_logs
             ORDER BY ts DESC
             LIMIT ?',
            [$limit]
        );

        // Update cache
        self::$cache = $logs;
        self::$cacheTime = time();

        return $logs;
    }

    /**
     * Clear postback logs cache
     *
     * @return void
     */
    public static function clearCache(): void
    {
        self::$cache = null;
        self::$cacheTime = 0;
    }

    /**
     * Send postback dengan HttpClient dan log hasilnya
     *
     * @param string $country
     * @param string $trafficType
     * @param float $payout
     * @param string $urlTemplate
     * @return bool
     */
    public static function sendPostback(
        string $country,
        string $trafficType,
        float $payout,
        string $urlTemplate
    ): bool {
        // Validasi input
        if (!Validator::isValidCountryCode($country)) {
            error_log("Invalid country code for postback: {$country}");
            return false;
        }

        // Replace placeholders dengan URL encoding
        $url = str_replace(
            ['{country}', '{traffic_type}', '{payout}'],
            [
                urlencode(strtoupper($country)),
                urlencode($trafficType),
                number_format($payout, 2, '.', '')
            ],
            $urlTemplate
        );

        // Validate final URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            error_log("Invalid postback URL after substitution: {$url}");
            return false;
        }

        // Send HTTP request menggunakan HttpClient dengan circuit breaker
        $response = HttpClient::get($url, [], 5, true);

        $responseCode = $response['code'] ?? null;
        $responseBody = substr($response['body'] ?? $response['error'] ?? '', 0, 500);
        $success = $response['success'] && $responseCode >= 200 && $responseCode < 300;

        // Log postback attempt
        try {
            self::create([
                'country_code' => strtoupper($country),
                'traffic_type' => $trafficType,
                'payout' => $payout,
                'postback_url' => $url,
                'response_code' => $responseCode,
                'response_body' => $responseBody,
                'success' => $success,
            ]);
        } catch (InvalidArgumentException $e) {
            error_log("Failed to log postback: " . $e->getMessage());
        }

        return $success;
    }

    /**
     * Get postback logs by success status
     *
     * @param bool $success
     * @param int $limit
     * @return array<int, array<string, mixed>>
     */
    public static function getByStatus(bool $success, int $limit = 20): array
    {
        $limit = max(1, min(100, $limit));

        return Database::fetchAll(
            'SELECT * FROM postback_logs WHERE success = ? ORDER BY ts DESC LIMIT ?',
            [$success ? 1 : 0, $limit]
        );
    }

    /**
     * Get postback statistics
     *
     * @return array<string, int|float>
     */
    public static function getStats(): array
    {
        $row = Database::fetchRow(
            'SELECT
                COUNT(*) as total,
                SUM(CASE WHEN success = 1 THEN 1 ELSE 0 END) as successful,
                SUM(CASE WHEN success = 0 THEN 1 ELSE 0 END) as failed,
                SUM(payout) as total_payout
             FROM postback_logs'
        );

        if (!$row || $row['total'] == 0) {
            return [
                'total' => 0,
                'successful' => 0,
                'failed' => 0,
                'success_rate' => 0.0,
                'total_payout' => 0.0
            ];
        }

        $total = (int)$row['total'];
        $successful = (int)$row['successful'];

        return [
            'total' => $total,
            'successful' => $successful,
            'failed' => (int)$row['failed'],
            'success_rate' => $total > 0 ? round(($successful / $total) * 100, 2) : 0.0,
            'total_payout' => (float)$row['total_payout']
        ];
    }

    /**
     * Clean up old postback logs
     *
     * @param int $retentionDays
     * @return int Number of deleted rows
     */
    public static function cleanup(int $retentionDays = 30): int
    {
        $retentionDays = max(1, min(365, $retentionDays));
        $cutoffTimestamp = time() - ($retentionDays * 86400);

        $stmt = Database::execute(
            'DELETE FROM postback_logs WHERE ts < ?',
            [$cutoffTimestamp]
        );

        $count = $stmt->rowCount();

        if ($count > 0) {
            self::clearCache();
        }

        return $count;
    }
}
