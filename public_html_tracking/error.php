<?php

/**
 * Tracking Domain Error Handler
 *
 * Minimal error page optimized for API/tracking traffic.
 * Returns JSON for API requests, simple HTML for browsers.
 */

declare(strict_types=1);

// Get error code from Apache or query parameter
$code = 500;

if (isset($_SERVER['REDIRECT_STATUS'])) {
    $code = (int) $_SERVER['REDIRECT_STATUS'];
}

if (isset($_GET['code'])) {
    $code = (int) $_GET['code'];
}

// Validate code range
if ($code < 400 || $code > 599) {
    $code = 500;
}

http_response_code($code);

// Error messages
$errorMessages = [
    400 => 'Bad Request',
    401 => 'Unauthorized',
    403 => 'Forbidden',
    404 => 'Not Found',
    405 => 'Method Not Allowed',
    408 => 'Request Timeout',
    429 => 'Too Many Requests',
    500 => 'Internal Server Error',
    502 => 'Bad Gateway',
    503 => 'Service Unavailable',
    504 => 'Gateway Timeout',
];

$message = $errorMessages[$code] ?? 'Error';

// Check if this is an API/AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

$acceptsJson = isset($_SERVER['HTTP_ACCEPT']) &&
              strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;

$hasApiKey = !empty($_SERVER['HTTP_X_API_KEY']);

$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
$isJsonRequest = strpos($contentType, 'application/json') !== false;

// Return JSON for API requests
if ($isAjax || $acceptsJson || $isJsonRequest || $hasApiKey) {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, no-store, must-revalidate');

    echo json_encode([
        'ok' => false,
        'error' => $message,
        'code' => $code,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    exit;
}

// Simple HTML for browser requests
$e = fn(string $str): string => htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Error <?= $code ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #0f172a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
        }
        .c { text-align: center; }
        .n { font-size: 80px; font-weight: 800; color: #ef4444; }
        h1 { font-size: 20px; margin: 15px 0; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="c">
        <div class="n"><?= $code ?></div>
        <h1><?= $e($message) ?></h1>
    </div>
</body>
</html>
