<?php

declare(strict_types=1);

namespace SRP\Controllers;

use SRP\Middleware\Session;
use SRP\Models\Settings;
use SRP\Models\TrafficLog;
use SRP\Models\Validator;
use SRP\Utils\CorsHandler;
use SRP\Utils\RequestBody;
use SRP\Utils\Csrf;

/**
 * API Controller (Production-Ready)
 *
 * Internal admin API untuk dashboard
 *
 * Features:
 * - Session-based authentication
 * - CSRF protection via Csrf helper
 * - Input validation via Validator
 * - CORS handling
 */
class ApiController
{
    private const ALLOWED_ORIGINS = [
        'http://localhost',
        'http://localhost:8000',
        'http://localhost:3000',
        'https://localhost',
    ];

    /**
     * Handle data request (GET, POST, DELETE)
     */
    public static function handleDataRequest(): void
    {
        Session::start();

        // Authenticate user
        if (!Session::isAuthenticated()) {
            CorsHandler::errorResponse('Unauthorized', 401, self::ALLOWED_ORIGINS);
        }

        // Handle CORS
        if (CorsHandler::handle(self::ALLOWED_ORIGINS, ['GET', 'POST', 'DELETE', 'OPTIONS'])) {
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
                    self::getData();
                    break;
                case 'POST':
                    // CSRF protection untuk POST
                    Csrf::protect();
                    self::postData();
                    break;
                case 'DELETE':
                    // CSRF protection untuk DELETE
                    Csrf::protect();
                    self::deleteLogs();
                    break;
                default:
                    CorsHandler::errorResponse('Method not allowed', 405, self::ALLOWED_ORIGINS);
            }
        } catch (\Throwable $e) {
            error_log('ApiController error: ' . $e->getMessage());
            $statusCode = ($e->getCode() >= 100 && $e->getCode() <= 599) ? $e->getCode() : 500;
            CorsHandler::errorResponse($e->getMessage(), $statusCode, self::ALLOWED_ORIGINS);
        }
    }

    /**
     * Get dashboard data (settings + logs)
     *
     * @return never
     */
    private static function getData(): never
    {
        try {
            // Get limit dari query string (default 50, max 200)
            $limit = Validator::sanitizeInt($_GET['limit'] ?? '50', 50, 1, 200);

            // Get config dan logs
            $cfg = Settings::get();
            $logs = TrafficLog::getAll($limit);

            CorsHandler::jsonResponse([
                'ok'   => true,
                'cfg'  => $cfg,
                'logs' => $logs,
            ], 200, self::ALLOWED_ORIGINS);
        } catch (\PDOException $e) {
            error_log('Database error in getData: ' . $e->getMessage());
            CorsHandler::errorResponse('Database error occurred', 500, self::ALLOWED_ORIGINS);
        } catch (\Throwable $e) {
            error_log('Error in getData: ' . $e->getMessage());
            CorsHandler::errorResponse('Failed to load dashboard data', 500, self::ALLOWED_ORIGINS);
        }
    }

    /**
     * Update settings (POST)
     *
     * @return never
     */
    private static function postData(): never
    {
        // Parse JSON body
        try {
            $data = RequestBody::parseJson(true, 10240);
        } catch (\RuntimeException $e) {
            CorsHandler::errorResponse($e->getMessage(), 400, self::ALLOWED_ORIGINS);
        }

        // Validate required fields
        try {
            RequestBody::validateRequired($data, ['system_on', 'redirect_url']);
        } catch (\RuntimeException $e) {
            CorsHandler::errorResponse($e->getMessage(), 400, self::ALLOWED_ORIGINS);
        }

        // Sanitize input
        $systemOn = (bool) ($data['system_on']);
        $redirectUrl = $data['redirect_url']; // Can be array or string
        $filterMode = Validator::sanitizeString($data['country_filter_mode'] ?? 'all', 20);
        $filterList = Validator::sanitizeString($data['country_filter_list'] ?? '', 10000);

        // Validate filter mode (whitelist)
        if (!in_array($filterMode, ['all', 'whitelist', 'blacklist'], true)) {
            CorsHandler::errorResponse('Invalid country_filter_mode. Must be: all, whitelist, or blacklist', 400, self::ALLOWED_ORIGINS);
        }

        // Update settings
        try {
            Settings::update($systemOn, $redirectUrl, $filterMode, $filterList);

            // Set flash message
            Session::setFlash('success', 'Settings updated successfully.');

            CorsHandler::jsonResponse(['ok' => true], 200, self::ALLOWED_ORIGINS);
        } catch (\InvalidArgumentException $e) {
            CorsHandler::errorResponse('Invalid input: ' . $e->getMessage(), 400, self::ALLOWED_ORIGINS);
        } catch (\Throwable $e) {
            error_log('Error in postData: ' . $e->getMessage());
            CorsHandler::errorResponse('Failed to update settings', 500, self::ALLOWED_ORIGINS);
        }
    }

    /**
     * Delete all traffic logs (DELETE)
     *
     * @return never
     */
    private static function deleteLogs(): never
    {
        try {
            $count = TrafficLog::clearAll();

            // Set flash message
            Session::setFlash('success', "Successfully deleted {$count} traffic log(s).");

            CorsHandler::jsonResponse(['ok' => true, 'deleted' => $count], 200, self::ALLOWED_ORIGINS);
        } catch (\Throwable $e) {
            error_log('Error in deleteLogs: ' . $e->getMessage());
            CorsHandler::errorResponse('Failed to delete logs', 500, self::ALLOWED_ORIGINS);
        }
    }
}
