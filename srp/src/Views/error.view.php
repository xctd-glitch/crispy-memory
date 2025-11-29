<?php
/**
 * Custom Error Page View
 *
 * Variables available:
 * @var int $code HTTP status code
 * @var string $title Error title
 * @var string $message Error message
 * @var string $details Additional details (only in development)
 * @var bool $showBack Show back button
 * @var string $backUrl URL for back button
 * @var string $nonce CSP nonce (optional)
 */

declare(strict_types=1);

// Default values
$code = $code ?? 500;
$title = $title ?? 'Error';
$message = $message ?? 'An unexpected error occurred.';
$details = $details ?? '';
$showBack = $showBack ?? true;
$backUrl = $backUrl ?? '/';
$nonce = $nonce ?? '';

// Error configurations
$errorConfig = [
    400 => [
        'icon' => '🚫',
        'color' => '#e67e22',
        'title' => 'Bad Request',
        'description' => 'The server could not understand your request.',
    ],
    401 => [
        'icon' => '🔐',
        'color' => '#9b59b6',
        'title' => 'Unauthorized',
        'description' => 'You need to login to access this resource.',
    ],
    403 => [
        'icon' => '⛔',
        'color' => '#e74c3c',
        'title' => 'Forbidden',
        'description' => 'You don\'t have permission to access this resource.',
    ],
    404 => [
        'icon' => '🔍',
        'color' => '#3498db',
        'title' => 'Page Not Found',
        'description' => 'The page you\'re looking for doesn\'t exist or has been moved.',
    ],
    405 => [
        'icon' => '🚷',
        'color' => '#e67e22',
        'title' => 'Method Not Allowed',
        'description' => 'The request method is not supported for this resource.',
    ],
    408 => [
        'icon' => '⏱️',
        'color' => '#f39c12',
        'title' => 'Request Timeout',
        'description' => 'The server timed out waiting for the request.',
    ],
    429 => [
        'icon' => '🚦',
        'color' => '#e74c3c',
        'title' => 'Too Many Requests',
        'description' => 'You\'ve made too many requests. Please try again later.',
    ],
    500 => [
        'icon' => '⚠️',
        'color' => '#e74c3c',
        'title' => 'Internal Server Error',
        'description' => 'Something went wrong on our end. We\'re working to fix it.',
    ],
    502 => [
        'icon' => '🔌',
        'color' => '#9b59b6',
        'title' => 'Bad Gateway',
        'description' => 'The server received an invalid response from an upstream server.',
    ],
    503 => [
        'icon' => '🔧',
        'color' => '#f39c12',
        'title' => 'Service Unavailable',
        'description' => 'The service is temporarily unavailable. Please try again later.',
    ],
    504 => [
        'icon' => '⏳',
        'color' => '#e67e22',
        'title' => 'Gateway Timeout',
        'description' => 'The server didn\'t respond in time. Please try again.',
    ],
];

// Get error config or use default
$config = $errorConfig[$code] ?? [
    'icon' => '❌',
    'color' => '#e74c3c',
    'title' => 'Error',
    'description' => 'An unexpected error occurred.',
];

// Override title and message if provided
if ($title !== 'Error') {
    $config['title'] = $title;
}
if ($message !== 'An unexpected error occurred.') {
    $config['description'] = $message;
}

// Escape function
$e = fn(string $str): string => htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= $e($config['title']) ?> - Error <?= $code ?></title>
    <link rel="icon" href="/assets/icons/favicon.ico">
    <style<?= $nonce ? ' nonce="' . $e($nonce) . '"' : '' ?>>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #fff;
        }

        .error-container {
            text-align: center;
            max-width: 600px;
            width: 100%;
        }

        .error-icon {
            font-size: 80px;
            margin-bottom: 20px;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-20px); }
            60% { transform: translateY(-10px); }
        }

        .error-code {
            font-size: 120px;
            font-weight: 800;
            color: <?= $config['color'] ?>;
            line-height: 1;
            margin-bottom: 10px;
            text-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }

        .error-title {
            font-size: 32px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #fff;
        }

        .error-message {
            font-size: 18px;
            color: rgba(255,255,255,0.7);
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .error-details {
            background: rgba(255,255,255,0.1);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 30px;
            text-align: left;
            font-family: 'Consolas', 'Monaco', monospace;
            font-size: 13px;
            color: rgba(255,255,255,0.6);
            overflow-x: auto;
            max-height: 200px;
            overflow-y: auto;
        }

        .error-details code {
            white-space: pre-wrap;
            word-break: break-all;
        }

        .error-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 14px 28px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: <?= $config['color'] ?>;
            color: #fff;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            filter: brightness(1.1);
        }

        .btn-secondary {
            background: rgba(255,255,255,0.1);
            color: #fff;
            border: 1px solid rgba(255,255,255,0.2);
        }

        .btn-secondary:hover {
            background: rgba(255,255,255,0.2);
            transform: translateY(-2px);
        }

        .error-footer {
            margin-top: 50px;
            color: rgba(255,255,255,0.4);
            font-size: 14px;
        }

        .error-footer a {
            color: rgba(255,255,255,0.6);
            text-decoration: none;
        }

        .error-footer a:hover {
            color: #fff;
        }

        /* Decorative elements */
        .decoration {
            position: fixed;
            border-radius: 50%;
            background: <?= $config['color'] ?>;
            opacity: 0.1;
            pointer-events: none;
        }

        .decoration-1 {
            width: 300px;
            height: 300px;
            top: -100px;
            right: -100px;
        }

        .decoration-2 {
            width: 200px;
            height: 200px;
            bottom: -50px;
            left: -50px;
        }

        .decoration-3 {
            width: 150px;
            height: 150px;
            top: 50%;
            left: 10%;
            opacity: 0.05;
        }

        @media (max-width: 480px) {
            .error-code {
                font-size: 80px;
            }

            .error-title {
                font-size: 24px;
            }

            .error-message {
                font-size: 16px;
            }

            .error-icon {
                font-size: 60px;
            }

            .btn {
                padding: 12px 20px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="decoration decoration-1"></div>
    <div class="decoration decoration-2"></div>
    <div class="decoration decoration-3"></div>

    <div class="error-container">
        <div class="error-icon"><?= $config['icon'] ?></div>
        <div class="error-code"><?= $code ?></div>
        <h1 class="error-title"><?= $e($config['title']) ?></h1>
        <p class="error-message"><?= $e($config['description']) ?></p>

        <?php if (!empty($details)): ?>
        <div class="error-details">
            <code><?= $e($details) ?></code>
        </div>
        <?php endif; ?>

        <div class="error-actions">
            <?php if ($showBack): ?>
            <a href="<?= $e($backUrl) ?>" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                Go Back
            </a>
            <?php endif; ?>

            <a href="/" class="btn btn-secondary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
                Home
            </a>

            <button onclick="location.reload()" class="btn btn-secondary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M23 4v6h-6M1 20v-6h6"/>
                    <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>
                </svg>
                Retry
            </button>
        </div>

        <div class="error-footer">
            <p>Error Code: <?= $code ?> | Request ID: <?= substr(md5(uniqid((string)mt_rand(), true)), 0, 8) ?></p>
            <p style="margin-top: 5px;">
                <a href="/">Smart Redirect Platform</a>
            </p>
        </div>
    </div>
</body>
</html>
