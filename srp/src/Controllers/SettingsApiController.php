<?php

declare(strict_types=1);

namespace SRP\Controllers;

use SRP\Middleware\Session;
use SRP\Models\Settings;
use SRP\Models\Validator;
use SRP\Utils\CorsHandler;
use SRP\Utils\RequestBody;
use SRP\Utils\Csrf;

/**
 * Settings API Controller
 *
 * Handles all settings-related API operations
 * Extracted from ApiController for better separation of concerns
 */
class SettingsApiController
{
    private const ALLOWED_ORIGINS = [
        'http://localhost',
        'http://localhost:8000',
        'http://localhost:3000',
        'https://localhost',
    ];

    /**
     * Handle settings request (GET, PUT)
     */
    public static function handle(): void
    {
        Session::start();

        // Authenticate user
        if (!Session::isAuthenticated()) {
            CorsHandler::errorResponse('Unauthorized', 401, self::ALLOWED_ORIGINS);
        }

        // Handle CORS
        if (CorsHandler::handle(self::ALLOWED_ORIGINS, ['GET', 'PUT', 'OPTIONS'])) {
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
                    self::getSettings();
                    break;
                case 'PUT':
                    // CSRF protection for PUT
                    Csrf::protect();
                    self::updateSettings();
                    break;
                default:
                    CorsHandler::errorResponse('Method not allowed', 405, self::ALLOWED_ORIGINS);
            }
        } catch (\Throwable $e) {
            error_log('SettingsApiController error: ' . $e->getMessage());
            $statusCode = ($e->getCode() >= 100 && $e->getCode() <= 599) ? $e->getCode() : 500;
            CorsHandler::errorResponse($e->getMessage(), $statusCode, self::ALLOWED_ORIGINS);
        }
    }

    /**
     * Get current settings
     *
     * @return never
     */
    private static function getSettings(): never
    {
        try {
            $settings = Settings::get();

            CorsHandler::jsonResponse([
                'ok' => true,
                'data' => $settings,
            ], 200, self::ALLOWED_ORIGINS);
        } catch (\PDOException $e) {
            error_log('Database error in getSettings: ' . $e->getMessage());
            CorsHandler::errorResponse('Database error occurred', 500, self::ALLOWED_ORIGINS);
        } catch (\Throwable $e) {
            error_log('Error in getSettings: ' . $e->getMessage());
            CorsHandler::errorResponse('Failed to load settings', 500, self::ALLOWED_ORIGINS);
        }
    }

    /**
     * Update settings (PUT)
     *
     * @return never
     */
    private static function updateSettings(): never
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

            CorsHandler::jsonResponse([
                'ok' => true,
                'message' => 'Settings updated successfully'
            ], 200, self::ALLOWED_ORIGINS);
        } catch (\InvalidArgumentException $e) {
            CorsHandler::errorResponse('Invalid input: ' . $e->getMessage(), 400, self::ALLOWED_ORIGINS);
        } catch (\Throwable $e) {
            error_log('Error in updateSettings: ' . $e->getMessage());
            CorsHandler::errorResponse('Failed to update settings', 500, self::ALLOWED_ORIGINS);
        }
    }
}