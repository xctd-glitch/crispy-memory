<?php

declare(strict_types=1);

namespace SRP\Controllers;

use SRP\Middleware\Session;
use SRP\Models\Settings;
use SRP\Models\TrafficLog;
use SRP\Models\Validator;
use SRP\Utils\CorsHandler;

/**
 * Dashboard API Controller
 *
 * Provides combined dashboard data (settings + logs)
 * This is used for the main dashboard view
 */
class DashboardApiController
{
    private const ALLOWED_ORIGINS = [
        'http://localhost',
        'http://localhost:8000',
        'http://localhost:3000',
        'https://localhost',
    ];

    /**
     * Handle dashboard data request (GET only)
     */
    public static function handle(): void
    {
        Session::start();

        // Authenticate user
        if (!Session::isAuthenticated()) {
            CorsHandler::errorResponse('Unauthorized', 401, self::ALLOWED_ORIGINS);
        }

        // Handle CORS
        if (CorsHandler::handle(self::ALLOWED_ORIGINS, ['GET', 'OPTIONS'])) {
            exit; // OPTIONS request handled
        }

        // Set headers
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

        // Only GET is allowed
        $method = $_SERVER['REQUEST_METHOD'] ?? '';

        if ($method !== 'GET') {
            CorsHandler::errorResponse('Method not allowed', 405, self::ALLOWED_ORIGINS);
        }

        try {
            self::getDashboardData();
        } catch (\Throwable $e) {
            error_log('DashboardApiController error: ' . $e->getMessage());
            $statusCode = ($e->getCode() >= 100 && $e->getCode() <= 599) ? $e->getCode() : 500;
            CorsHandler::errorResponse($e->getMessage(), $statusCode, self::ALLOWED_ORIGINS);
        }
    }

    /**
     * Get combined dashboard data (settings + logs)
     *
     * @return never
     */
    private static function getDashboardData(): never
    {
        try {
            // Get limit from query string (default 50, max 200)
            $limit = Validator::sanitizeInt($_GET['limit'] ?? '50', 50, 1, 200);

            // Get settings and logs
            $cfg = Settings::get();
            $logs = TrafficLog::getAll($limit);

            // Get traffic statistics
            $stats = TrafficLog::getStats();
            $decisionCounts = TrafficLog::countByDecision();

            CorsHandler::jsonResponse([
                'ok'   => true,
                'cfg'  => $cfg,
                'logs' => $logs,
                'stats' => array_merge($stats, [
                    'decision_counts' => $decisionCounts
                ])
            ], 200, self::ALLOWED_ORIGINS);
        } catch (\PDOException $e) {
            error_log('Database error in getDashboardData: ' . $e->getMessage());
            CorsHandler::errorResponse('Database error occurred', 500, self::ALLOWED_ORIGINS);
        } catch (\Throwable $e) {
            error_log('Error in getDashboardData: ' . $e->getMessage());
            CorsHandler::errorResponse('Failed to load dashboard data', 500, self::ALLOWED_ORIGINS);
        }
    }
}