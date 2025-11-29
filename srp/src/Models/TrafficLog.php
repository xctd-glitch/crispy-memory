<?php

declare(strict_types=1);

namespace SRP\Models;

use SRP\Config\Database;
use InvalidArgumentException;

/**
 * Traffic Log Model (PDO-based, Production-Ready)
 *
 * Semua query menggunakan PDO prepared statements
 */
class TrafficLog
{
    private static ?array $cache = null;
    private static int $cacheTime = 0;
    private const CACHE_TTL = 2; // Cache untuk 2 detik

    /**
     * Create traffic log entry dengan validasi ketat
     *
     * @param array<string, mixed> $data
     * @return void
     * @throws InvalidArgumentException
     */
    public static function create(array $data): void
    {
        // Validasi dan sanitasi input (TIDAK pakai htmlspecialchars untuk database)
        // htmlspecialchars hanya untuk output ke HTML
        $ip = Validator::sanitizeString($data['ip'] ?? '', 45);
        $ua = Validator::sanitizeString($data['ua'] ?? '', 500);
        $cid = Validator::sanitizeString($data['cid'] ?? '', 100);
        $cc = Validator::sanitizeString($data['cc'] ?? '', 10);
        $lp = Validator::sanitizeString($data['lp'] ?? '', 100);
        $decision = $data['decision'] ?? '';

        // Validate decision (whitelist)
        if (!in_array($decision, ['A', 'B'], true)) {
            throw new InvalidArgumentException('Invalid decision value');
        }

        // Validate IP jika ada
        if ($ip !== '' && !Validator::isValidIp($ip)) {
            throw new InvalidArgumentException('Invalid IP address');
        }

        // Insert dengan PDO prepared statement
        Database::execute(
            'INSERT INTO logs (ts, ip, ua, click_id, country_code, user_lp, decision)
             VALUES (UNIX_TIMESTAMP(), ?, ?, ?, ?, ?, ?)',
            [
                $ip,
                $ua,
                $cid !== '' ? $cid : null,
                $cc !== '' ? $cc : null,
                $lp !== '' ? $lp : null,
                $decision
            ]
        );

        // Clear cache setelah insert
        self::clearCache();
    }

    /**
     * Get all logs dengan limit dan caching
     *
     * @param int $limit
     * @param int $offset
     * @param bool $forceRefresh
     * @return array<int, array<string, mixed>>
     */
    public static function getAll(int $limit = 50, int $offset = 0, bool $forceRefresh = false): array
    {
        // Return cached data jika masih valid (only for offset 0)
        if ($offset === 0 && !$forceRefresh && self::$cache !== null && (time() - self::$cacheTime) < self::CACHE_TTL) {
            return self::$cache;
        }

        // Batasi limit antara 1-200
        $limit = max(1, min(200, $limit));
        $offset = max(0, $offset);

        // Hanya ambil logs dari hari ini (last 24 hours)
        $todayStart = strtotime('today midnight');

        // Query dengan PDO prepared statement
        $logs = Database::fetchAll(
            'SELECT * FROM logs WHERE ts >= ? ORDER BY id DESC LIMIT ? OFFSET ?',
            [$todayStart, $limit, $offset]
        );

        // Update cache only for first page
        if ($offset === 0) {
            self::$cache = $logs;
            self::$cacheTime = time();
        }

        return $logs;
    }

    /**
     * Get total count of logs (today only)
     *
     * @return int
     */
    public static function getCount(): int
    {
        $todayStart = strtotime('today midnight');

        $row = Database::fetchRow(
            'SELECT COUNT(*) as count FROM logs WHERE ts >= ?',
            [$todayStart]
        );

        return isset($row['count']) ? (int)$row['count'] : 0;
    }

    /**
     * Clear traffic logs cache
     *
     * @return void
     */
    public static function clearCache(): void
    {
        self::$cache = null;
        self::$cacheTime = 0;
    }

    /**
     * Clear all logs dengan prepared statement
     *
     * @return int Number of deleted rows
     */
    public static function clearAll(): int
    {
        $stmt = Database::execute('DELETE FROM logs');
        $count = $stmt->rowCount();

        self::clearCache();
        return $count;
    }

    /**
     * Delete specific logs by IDs
     *
     * @param array<int> $ids
     * @return int Number of deleted rows
     */
    public static function deleteByIds(array $ids): int
    {
        if (empty($ids)) {
            return 0;
        }

        // Filter to ensure all IDs are integers
        $ids = array_filter($ids, 'is_numeric');
        $ids = array_map('intval', $ids);

        if (empty($ids)) {
            return 0;
        }

        // Build placeholders for IN clause
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';

        $stmt = Database::execute(
            "DELETE FROM logs WHERE id IN ({$placeholders})",
            $ids
        );

        $count = $stmt->rowCount();

        if ($count > 0) {
            self::clearCache();
        }

        return $count;
    }

    /**
     * Auto cleanup old logs berdasarkan retention period
     *
     * @param int $retentionDays
     * @return int Number of deleted rows
     */
    public static function autoCleanup(int $retentionDays = 1): int
    {
        // Default retention adalah 1 hari (daily reset)
        $retentionDays = max(1, min(365, $retentionDays));
        $cutoffTimestamp = time() - ($retentionDays * 86400);

        $stmt = Database::execute(
            'DELETE FROM logs WHERE ts < ?',
            [$cutoffTimestamp]
        );

        $count = $stmt->rowCount();

        if ($count > 0) {
            self::clearCache();
        }

        return $count;
    }

    /**
     * Clean up logs older than today (keep only today's data)
     *
     * @return int Number of deleted rows
     */
    public static function cleanupOldLogs(): int
    {
        $todayStart = strtotime('today midnight');

        $stmt = Database::execute(
            'DELETE FROM logs WHERE ts < ?',
            [$todayStart]
        );

        $count = $stmt->rowCount();

        if ($count > 0) {
            self::clearCache();
        }

        return $count;
    }

    /**
     * Get statistics tentang logs
     *
     * @return array<string, int>
     */
    public static function getStats(): array
    {
        $row = Database::fetchRow(
            'SELECT COUNT(*) as total, MIN(ts) as oldest, MAX(ts) as newest FROM logs'
        );

        if (!$row || $row['total'] == 0) {
            return [
                'total' => 0,
                'oldest_days' => 0,
                'newest_days' => 0,
                'size_estimate' => 0
            ];
        }

        return [
            'total' => (int)$row['total'],
            'oldest_days' => $row['oldest'] ? (int)((time() - $row['oldest']) / 86400) : 0,
            'newest_days' => $row['newest'] ? (int)((time() - $row['newest']) / 86400) : 0,
            'size_estimate' => (int)$row['total'] * 500
        ];
    }

    /**
     * Get logs by country code
     *
     * @param string $countryCode
     * @param int $limit
     * @return array<int, array<string, mixed>>
     */
    public static function getByCountry(string $countryCode, int $limit = 50): array
    {
        $limit = max(1, min(200, $limit));

        return Database::fetchAll(
            'SELECT * FROM logs WHERE country_code = ? ORDER BY id DESC LIMIT ?',
            [strtoupper($countryCode), $limit]
        );
    }

    /**
     * Get logs by decision type
     *
     * @param string $decision
     * @param int $limit
     * @return array<int, array<string, mixed>>
     */
    public static function getByDecision(string $decision, int $limit = 50): array
    {
        if (!in_array($decision, ['A', 'B'], true)) {
            return [];
        }

        $limit = max(1, min(200, $limit));

        return Database::fetchAll(
            'SELECT * FROM logs WHERE decision = ? ORDER BY id DESC LIMIT ?',
            [$decision, $limit]
        );
    }

    /**
     * Count logs by decision for today
     *
     * @return array{A: int, B: int}
     */
    public static function countByDecision(): array
    {
        $todayStart = strtotime('today midnight');

        $rows = Database::fetchAll(
            'SELECT decision, COUNT(*) as count FROM logs WHERE ts >= ? GROUP BY decision',
            [$todayStart]
        );

        $counts = ['A' => 0, 'B' => 0];

        foreach ($rows as $row) {
            if (isset($row['decision']) && isset($row['count'])) {
                $counts[$row['decision']] = (int)$row['count'];
            }
        }

        return $counts;
    }
}
