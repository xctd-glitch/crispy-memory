<?php

declare(strict_types=1);

namespace SRP\Controllers;

use SRP\Middleware\Session;
use SRP\Models\TrafficLog;
use SRP\Models\Validator;
use SRP\Utils\CorsHandler;
use SRP\Utils\Csrf;

/**
 * Traffic Log API Controller
 *
 * Handles all traffic log-related API operations
 * Extracted from ApiController for better separation of concerns
 */
class TrafficLogApiController
{
    private const ALLOWED_ORIGINS = [
        'http://localhost',
        'http://localhost:8000',
        'http://localhost:3000',
        'https://localhost',
    ];

    /**
     * Handle traffic logs request (GET, DELETE)
     */
    public static function handle(): void
    {
        Session::start();

        // Authenticate user
        if (!Session::isAuthenticated()) {
            CorsHandler::errorResponse('Unauthorized', 401, self::ALLOWED_ORIGINS);
        }

        // Handle CORS
        if (CorsHandler::handle(self::ALLOWED_ORIGINS, ['GET', 'DELETE', 'OPTIONS'])) {
            exit; // OPTIONS request handled
        }

        // Set headers
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

        // Route by method
        $method = $_SERVER['REQUEST_METHOD'] ?? '';

        try {
            switch ($method) {
                case 'GET':
                    self::getLogs();
                    break;
                case 'DELETE':
                    // CSRF protection for DELETE
                    Csrf::protect();
                    self::deleteLogs();
                    break;
                default:
                    CorsHandler::errorResponse('Method not allowed', 405, self::ALLOWED_ORIGINS);
            }
        } catch (\Throwable $e) {
            error_log('TrafficLogApiController error: ' . $e->getMessage());
            $statusCode = ($e->getCode() >= 100 && $e->getCode() <= 599) ? $e->getCode() : 500;
            CorsHandler::errorResponse($e->getMessage(), $statusCode, self::ALLOWED_ORIGINS);
        }
    }

    /**
     * Get traffic logs with pagination
     *
     * @return never
     */
    private static function getLogs(): never
    {
        try {
            // Get parameters from query string
            $limit = Validator::sanitizeInt($_GET['limit'] ?? '50', 50, 1, 200);
            $offset = Validator::sanitizeInt($_GET['offset'] ?? '0', 0, 0, PHP_INT_MAX);

            // Optional filters
            $country = isset($_GET['country']) ? Validator::sanitizeString($_GET['country'], 2, 2) : null;
            $dateFrom = isset($_GET['date_from']) ? Validator::sanitizeString($_GET['date_from'], 20) : null;
            $dateTo = isset($_GET['date_to']) ? Validator::sanitizeString($_GET['date_to'], 20) : null;

            // Get logs with filters
            $logs = TrafficLog::getAll($limit, $offset);

            // Get total count for pagination
            $totalCount = TrafficLog::getCount();

            CorsHandler::jsonResponse([
                'ok' => true,
                'data' => $logs,
                'pagination' => [
                    'limit' => $limit,
                    'offset' => $offset,
                    'total' => $totalCount,
                    'hasMore' => ($offset + $limit) < $totalCount
                ]
            ], 200, self::ALLOWED_ORIGINS);
        } catch (\PDOException $e) {
            error_log('Database error in getLogs: ' . $e->getMessage());
            CorsHandler::errorResponse('Database error occurred', 500, self::ALLOWED_ORIGINS);
        } catch (\Throwable $e) {
            error_log('Error in getLogs: ' . $e->getMessage());
            CorsHandler::errorResponse('Failed to load traffic logs', 500, self::ALLOWED_ORIGINS);
        }
    }

    /**
     * Delete traffic logs
     *
     * @return never
     */
    private static function deleteLogs(): never
    {
        try {
            // Check for specific log IDs to delete
            $logIds = $_GET['ids'] ?? null;

            if ($logIds) {
                // Delete specific logs
                $ids = array_map('intval', explode(',', $logIds));
                $count = TrafficLog::deleteByIds($ids);
            } else {
                // Delete all logs
                $count = TrafficLog::clearAll();
            }

            // Set flash message
            Session::setFlash('success', "Successfully deleted {$count} traffic log(s).");

            CorsHandler::jsonResponse([
                'ok' => true,
                'deleted' => $count,
                'message' => "Deleted {$count} traffic log(s)"
            ], 200, self::ALLOWED_ORIGINS);
        } catch (\Throwable $e) {
            error_log('Error in deleteLogs: ' . $e->getMessage());
            CorsHandler::errorResponse('Failed to delete logs', 500, self::ALLOWED_ORIGINS);
        }
    }
}