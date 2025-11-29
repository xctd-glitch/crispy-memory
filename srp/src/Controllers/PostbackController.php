<?php

declare(strict_types=1);

namespace SRP\Controllers;

use SRP\Config\Database;
use SRP\Middleware\Session;
use SRP\Models\Settings;
use SRP\Models\PostbackLog;
use SRP\Models\Validator;
use SRP\Utils\Csrf;

/**
 * Postback Controller (Production-Ready)
 *
 * Handles postback configuration and testing
 *
 * Features:
 * - Session-based authentication
 * - CSRF protection via Csrf helper
 * - Input validation via Validator
 * - PDO untuk all database queries
 */
class PostbackController
{
    /**
     * Handle postback request (GET atau POST)
     */
    public static function handleRequest(): void
    {
        Session::start();

        header('Content-Type: application/json; charset=utf-8');

        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // Handle GET requests (load postback logs)
        if ($method === 'GET') {
            self::getPostbackLogs();
            return;
        }

        // Handle POST requests
        if ($method === 'POST') {
            self::handlePostRequest();
            return;
        }

        http_response_code(405);
        echo json_encode(['ok' => false, 'error' => 'Method not allowed'], JSON_THROW_ON_ERROR);
        exit;
    }

    /**
     * Get postback logs (GET request)
     *
     * @return void
     */
    private static function getPostbackLogs(): void
    {
        // Check authentication
        if (!Session::isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['ok' => false, 'error' => 'Authentication required'], JSON_THROW_ON_ERROR);
            exit;
        }

        // Check if requesting received postbacks
        $action = Validator::sanitizeString($_GET['action'] ?? 'logs', 20);

        if ($action === 'received') {
            self::getReceivedPostbacks();
            return;
        }

        if ($action === 'stats') {
            self::getDailyStats();
            return;
        }

        // Get sent postbacks (default)
        try {
            // Get limit from query string (default 20, max 100)
            $limit = Validator::sanitizeInt($_GET['limit'] ?? '20', 20, 1, 100);

            $logs = PostbackLog::getRecent($limit);
            echo json_encode(['ok' => true, 'logs' => $logs], JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            // Log detailed error untuk debugging
            error_log('PostbackController::getPostbackLogs error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());

            // Check if it's a database table error
            $errorMessage = 'Failed to load postback logs';
            if (strpos($e->getMessage(), "doesn't exist") !== false ||
                strpos($e->getMessage(), "Base table or view not found") !== false ||
                strpos($e->getMessage(), "1146") !== false) {
                error_log('CRITICAL: Table postback_logs does not exist!');
                error_log('Run this SQL: CREATE TABLE postback_logs (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, ts INT UNSIGNED NOT NULL, country_code VARCHAR(10) NOT NULL, traffic_type VARCHAR(50) NOT NULL, payout DECIMAL(10,2) NOT NULL, postback_url TEXT NOT NULL, response_code INT NULL, response_body TEXT NULL, success TINYINT(1) NOT NULL DEFAULT 0, INDEX idx_postback_ts (ts), INDEX idx_postback_success (success), INDEX idx_postback_country (country_code)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
                $errorMessage = 'Database table missing. Run URGENT_FIX_NOW.sql';
            }

            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => $errorMessage], JSON_THROW_ON_ERROR);
        }
        exit;
    }

    /**
     * Get daily/weekly payout statistics
     *
     * Week start: Monday 07:00 UTC+7 (Monday 00:00 UTC)
     * Week boundary offset: -7 hours (25200 seconds)
     *
     * @return void
     */
    private static function getDailyStats(): void
    {
        try {
            // Get parameters
            $days = Validator::sanitizeInt($_GET['days'] ?? '30', 30, 1, 365);
            $view = Validator::sanitizeString($_GET['view'] ?? 'daily', 10);

            // Validate view
            if (!in_array($view, ['daily', 'weekly'], true)) {
                $view = 'daily';
            }

            // Build query based on view
            if ($view === 'weekly') {
                // Weekly aggregation: Week starts Monday 07:00 UTC+7
                // Offset: ts - 25200 (7 hours = 7*3600 seconds)
                // Formula: Get Monday of (timestamp - 7 hours)
                $stmt = Database::execute(
                    'SELECT
                        DATE_SUB(
                            DATE(FROM_UNIXTIME(ts - 25200)),
                            INTERVAL WEEKDAY(FROM_UNIXTIME(ts - 25200)) DAY
                        ) as date,
                        DATE_ADD(
                            DATE_SUB(
                                DATE(FROM_UNIXTIME(ts - 25200)),
                                INTERVAL WEEKDAY(FROM_UNIXTIME(ts - 25200)) DAY
                            ),
                            INTERVAL 6 DAY
                        ) as week_end,
                        COUNT(*) as total_postbacks,
                        SUM(payout) as total_payout,
                        AVG(payout) as avg_payout,
                        MIN(payout) as min_payout,
                        MAX(payout) as max_payout,
                        COUNT(DISTINCT traffic_type) as unique_traffic_types,
                        COUNT(DISTINCT country_code) as unique_countries,
                        COUNT(DISTINCT network) as unique_networks
                    FROM postback_received
                    WHERE ts >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL ? DAY))
                    GROUP BY DATE_SUB(
                        DATE(FROM_UNIXTIME(ts - 25200)),
                        INTERVAL WEEKDAY(FROM_UNIXTIME(ts - 25200)) DAY
                    )
                    ORDER BY date DESC',
                    [$days]
                );
            } else {
                // Daily aggregation (default)
                $stmt = Database::execute(
                    'SELECT
                        DATE(FROM_UNIXTIME(ts)) as date,
                        COUNT(*) as total_postbacks,
                        SUM(payout) as total_payout,
                        AVG(payout) as avg_payout,
                        MIN(payout) as min_payout,
                        MAX(payout) as max_payout,
                        COUNT(DISTINCT traffic_type) as unique_traffic_types,
                        COUNT(DISTINCT country_code) as unique_countries,
                        COUNT(DISTINCT network) as unique_networks
                    FROM postback_received
                    WHERE ts >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL ? DAY))
                    GROUP BY DATE(FROM_UNIXTIME(ts))
                    ORDER BY date DESC',
                    [$days]
                );
            }

            $stats = [];
            while ($row = $stmt->fetch()) {
                $record = [
                    'date' => $row['date'],
                    'total_postbacks' => (int) $row['total_postbacks'],
                    'total_payout' => (float) $row['total_payout'],
                    'avg_payout' => (float) $row['avg_payout'],
                    'min_payout' => (float) $row['min_payout'],
                    'max_payout' => (float) $row['max_payout'],
                    'unique_traffic_types' => (int) $row['unique_traffic_types'],
                    'unique_countries' => (int) $row['unique_countries'],
                    'unique_networks' => (int) ($row['unique_networks'] ?? 0)
                ];

                // Add week_end for weekly view
                if ($view === 'weekly' && isset($row['week_end'])) {
                    $record['week_end'] = $row['week_end'];
                }

                $stats[] = $record;
            }

            // Calculate totals
            $totalPostbacks = array_sum(array_column($stats, 'total_postbacks'));
            $totalPayout = array_sum(array_column($stats, 'total_payout'));
            $avgDailyPayout = count($stats) > 0 ? $totalPayout / count($stats) : 0;

            echo json_encode([
                'ok' => true,
                'view' => $view,
                'stats' => $stats,
                'summary' => [
                    'total_postbacks' => $totalPostbacks,
                    'total_payout' => round($totalPayout, 2),
                    'avg_daily_payout' => round($avgDailyPayout, 2),
                    'days_count' => count($stats),
                    'period_days' => $days
                ]
            ], JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            error_log('Error loading daily stats: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Failed to load daily statistics'], JSON_THROW_ON_ERROR);
        }
        exit;
    }

    /**
     * Get received postbacks (dari affiliate networks)
     *
     * @return void
     */
    private static function getReceivedPostbacks(): void
    {
        try {
            // Get limit from query string (default 50, max 200)
            $limit = Validator::sanitizeInt($_GET['limit'] ?? '50', 50, 1, 200);

            // Query dengan PDO - include network column
            $stmt = Database::execute(
                'SELECT
                    id,
                    ts,
                    status,
                    country_code,
                    traffic_type,
                    payout,
                    click_id,
                    network,
                    ip_address,
                    query_string
                FROM postback_received
                ORDER BY ts DESC
                LIMIT ?',
                [$limit]
            );

            $logs = [];
            while ($row = $stmt->fetch()) {
                $logs[] = $row;
            }

            echo json_encode(['ok' => true, 'logs' => $logs], JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            error_log('Error loading received postbacks: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Failed to load received postbacks'], JSON_THROW_ON_ERROR);
        }
        exit;
    }

    /**
     * Handle POST request
     *
     * @return void
     */
    private static function handlePostRequest(): void
    {
        // Check authentication
        if (!Session::isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['ok' => false, 'error' => 'Authentication required'], JSON_THROW_ON_ERROR);
            exit;
        }

        // Validate CSRF token
        if (!Csrf::validate(throwOnFailure: false)) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'CSRF token validation failed'], JSON_THROW_ON_ERROR);
            exit;
        }

        // Parse JSON body
        $raw = file_get_contents('php://input');
        if ($raw === false || $raw === '') {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'No data provided'], JSON_THROW_ON_ERROR);
            exit;
        }

        try {
            $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Invalid JSON payload'], JSON_THROW_ON_ERROR);
            exit;
        }

        if (!is_array($data)) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Invalid JSON payload'], JSON_THROW_ON_ERROR);
            exit;
        }

        // Check if this is a test postback request
        $action = Validator::sanitizeString($data['action'] ?? '', 20);

        if ($action === 'test') {
            self::handleTestPostback($data);
            return;
        }

        // Save postback configuration (default action)
        self::savePostbackConfig($data);
    }

    /**
     * Handle test postback request
     *
     * @param array<string, mixed> $data
     * @return void
     */
    private static function handleTestPostback(array $data): void
    {
        try {
            $cfg = Settings::get();

            if (empty($cfg['postback_url'])) {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'Postback URL is not configured'], JSON_THROW_ON_ERROR);
                exit;
            }

            // Sanitize test parameters
            $country = Validator::sanitizeString($data['country'] ?? 'US', 10);
            $country = strtoupper($country);

            // Validate country code
            if (!Validator::isValidCountryCode($country)) {
                $country = 'US'; // Fallback to US
            }

            $trafficType = Validator::sanitizeString($data['traffic_type'] ?? 'WAP', 50);
            $payout = (float) ($data['payout'] ?? $cfg['default_payout'] ?? 0.00);

            // Validate payout range
            if ($payout < 0 || $payout > 10000) {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'Invalid payout value'], JSON_THROW_ON_ERROR);
                exit;
            }

            // Send test postback
            $success = PostbackLog::sendPostback($country, $trafficType, $payout, $cfg['postback_url']);

            if ($success) {
                echo json_encode(['ok' => true, 'message' => 'Test postback sent successfully'], JSON_THROW_ON_ERROR);
            } else {
                echo json_encode(['ok' => false, 'error' => 'Test postback failed'], JSON_THROW_ON_ERROR);
            }
        } catch (\Throwable $e) {
            error_log('Error in handleTestPostback: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_THROW_ON_ERROR);
        }
        exit;
    }

    /**
     * Save postback configuration
     *
     * @param array<string, mixed> $data
     * @return void
     */
    private static function savePostbackConfig(array $data): void
    {
        try {
            $url = Validator::sanitizeString($data['postback_url'] ?? '', 2048);
            $payout = (float) ($data['default_payout'] ?? 0.00);

            // Validate payout range
            if ($payout < 0 || $payout > 10000) {
                http_response_code(400);
                echo json_encode(['ok' => false, 'error' => 'Invalid payout value (must be between 0 and 10000)'], JSON_THROW_ON_ERROR);
                exit;
            }

            // Update settings (always enabled)
            Settings::updatePostback(true, $url, $payout);

            // Set flash message
            Session::setFlash('success', 'Postback configuration saved successfully.');

            echo json_encode(['ok' => true], JSON_THROW_ON_ERROR);
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            error_log('Error in savePostbackConfig: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Failed to save postback configuration'], JSON_THROW_ON_ERROR);
        }
        exit;
    }
}
