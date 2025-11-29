<?php

/**
 * Tracking Domain Index Page
 * Simple status page atau 404 response
 */

declare(strict_types=1);

// Set headers
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

// Return simple status
http_response_code(200);

echo json_encode([
    'status' => 'active',
    'service' => 'SRP Tracking API',
    'version' => '2.0',
    'endpoints' => [
        'decision' => '/decision.php (internal, API key required)',
        'redirect' => '/r.php (public, rate limited)',
        'postback' => '/postback-receiver.php (API key required)',
        'external' => '/api-external.php (API key required)',
    ],
    'documentation' => 'https://trackng.app/docs',
], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);

exit;
