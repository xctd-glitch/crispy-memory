<?php

declare(strict_types=1);

namespace SRP\Models;

use PDO;
use PDOException;
use SRP\Utils\HttpClient;
use Throwable;

/**
 * Environment Configuration Model (Production-Ready)
 *
 * Manages .env file configuration dengan validasi ketat
 */
class EnvConfig
{
    private static string $envFilePath;

    /**
     * Initialize dengan env file path
     *
     * @return void
     */
    private static function init(): void
    {
        if (!isset(self::$envFilePath)) {
            self::$envFilePath = dirname(__DIR__, 2) . '/.env';
        }
    }

    /**
     * Get all environment configuration
     * Priority: database > .env file
     *
     * @return array<string, string>
     */
    public static function getAll(): array
    {
        self::init();

        // Try to get from database first
        try {
            $dbConfig = self::getFromDatabase();

            // Get comprehensive config with all fields
            $allKeys = self::getAllConfigKeys();
            $result = [];

            foreach ($allKeys as $key => $defaultValue) {
                $result[$key] = $dbConfig[$key] ?? (getenv($key) ?: $defaultValue);
            }

            return $result;
        } catch (Throwable $e) {
            error_log('EnvConfig database read error, fallback to .env: ' . $e->getMessage());

            // Fallback ke .env only jika database error
            $allKeys = self::getAllConfigKeys();
            $result = [];

            foreach ($allKeys as $key => $defaultValue) {
                $result[$key] = getenv($key) ?: $defaultValue;
            }

            return $result;
        }
    }

    /**
     * Get all configuration keys with default values
     *
     * @return array<string, string>
     */
    private static function getAllConfigKeys(): array
    {
        return [
            // Database Configuration
            'DB_HOST' => 'localhost',
            'DB_PORT' => '3306',
            'DB_NAME' => '',
            'DB_USER' => '',
            'DB_PASS' => '',
            'DB_CHARSET' => 'utf8mb4',

            // Domain Configuration
            'APP_URL' => 'https://trackng.app',
            'APP_PANEL_URL' => 'https://panel.trackng.app',
            'TRACKING_PRIMARY_DOMAIN' => 'qvtrk.com',
            'TRACKING_DOMAIN' => 'https://qvtrk.com',
            'TRACKING_REDIRECT_URL' => 'https://t.qvtrk.com',
            'TRACKING_DECISION_API' => 'https://api.qvtrk.com',
            'TRACKING_POSTBACK_URL' => 'https://postback.qvtrk.com',

            // API Keys
            'API_KEY_INTERNAL' => '',
            'API_KEY_EXTERNAL' => '',

            // SRP API
            'SRP_API_URL' => 'https://api.qvtrk.com/decision.php',
            'SRP_API_KEY' => '',

            // Application Settings
            'APP_NAME' => 'Smart Redirect Platform',
            'APP_TIMEZONE' => 'UTC',
            'APP_ENV' => 'production',
            'SRP_ENV' => 'production',
            'APP_DEBUG' => 'false',
            'MAINTENANCE_MODE' => 'false',
            'MAINTENANCE_MESSAGE' => 'System under maintenance. Please try again later.',

            // Session & Security
            'SESSION_LIFETIME' => '7200',
            'SESSION_NAME' => 'SRP_SESSION',
            'SESSION_SECRET' => '',
            'RATE_LIMIT_ATTEMPTS' => '5',
            'RATE_LIMIT_WINDOW' => '900',
            'SECURE_COOKIES' => 'true',
            'HTTP_ONLY' => 'true',
            'SAME_SITE' => 'Strict',
            'TRUST_CF_HEADERS' => 'true',
            'TRUST_PROXY_HEADERS' => 'false',

            // Feature Flags - Brand Domain
            'BRAND_ENABLE_LANDING_PAGE' => 'true',
            'BRAND_ENABLE_DOCUMENTATION' => 'true',
            'BRAND_ENABLE_API_DOCS' => 'true',

            // Feature Flags - Tracking Domain
            'TRACKING_ENABLE_VPN_CHECK' => 'true',
            'TRACKING_ENABLE_GEO_FILTER' => 'true',
            'TRACKING_ENABLE_DEVICE_FILTER' => 'true',
            'TRACKING_ENABLE_AUTO_MUTE' => 'true',
            'RATE_LIMIT_TRACKING_ENABLED' => 'true',
            'TRACKING_RATE_LIMIT_MAX' => '120',
            'TRACKING_RATE_LIMIT_WINDOW' => '60',
            'PANEL_RATE_LIMIT_MAX' => '300',
            'PANEL_RATE_LIMIT_WINDOW' => '60',

            // Postback Configuration
            'POSTBACK_TIMEOUT' => '5',
            'POSTBACK_MAX_RETRIES' => '3',
            'POSTBACK_RETRY_DELAY' => '60',
            'DEFAULT_PAYOUT' => '0.00',
            'POSTBACK_HMAC_SECRET' => '',
            'POSTBACK_API_KEY' => '',
            'POSTBACK_REQUIRE_API_KEY' => 'true',
            'POSTBACK_FORWARD_ENABLED' => 'false',
            'POSTBACK_FORWARD_URL' => '',

            // Path Configuration
            'APP_ROOT' => '',
            'LOG_PATH' => '',
            'ERROR_LOG_PATH' => '',

            // External Services
            'VPN_CHECK_URL' => 'https://blackbox.ipinfo.app/lookup/',
            'VPN_CHECK_TIMEOUT' => '2',

            // Logging
            'LOG_LEVEL' => 'warning',
            'LOG_CHANNEL' => 'file',

            // Debug Features
            'ENABLE_DEBUG_BAR' => 'false',
            'ENABLE_QUERY_LOG' => 'false',
            'ENABLE_PERFORMANCE_MONITOR' => 'false',
            'ENABLE_ERROR_REPORTING' => 'false',
        ];
    }

    /**
     * Get configuration from database
     *
     * @return array<string, string>
     */
    private static function getFromDatabase(): array
    {
        $db = \SRP\Config\Database::getConnection();

        $stmt = \SRP\Config\Database::execute(
            'SELECT config_key, config_value FROM env_config WHERE is_editable = 1',
            []
        );

        $config = [];
        while ($row = $stmt->fetch()) {
            $config[$row['config_key']] = $row['config_value'] ?? '';
        }

        return $config;
    }

    /**
     * Save configuration to database
     *
     * @param array<string, string> $newConfig
     * @param int $userId
     * @return bool
     */
    private static function saveToDatabase(array $newConfig, int $userId = 1): bool
    {
        try {
            $timestamp = time();

            foreach ($newConfig as $key => $value) {
                if (!self::isValidEnvKey($key)) {
                    continue;
                }

                // Sanitize value
                $value = trim(str_replace(["\r", "\n"], '', (string)$value));

                // Upsert ke database
                \SRP\Config\Database::execute(
                    'INSERT INTO env_config (config_key, config_value, config_type, is_editable, updated_at, updated_by)
                     VALUES (?, ?, ?, 1, ?, ?)
                     ON DUPLICATE KEY UPDATE
                        config_value = VALUES(config_value),
                        updated_at = VALUES(updated_at),
                        updated_by = VALUES(updated_by)',
                    [
                        $key,
                        $value,
                        self::getConfigType($key),
                        $timestamp,
                        $userId
                    ]
                );
            }

            return true;
        } catch (Throwable $e) {
            error_log('EnvConfig saveToDatabase error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get config type berdasarkan key
     *
     * @param string $key
     * @return string
     */
    private static function getConfigType(string $key): string
    {
        // Integer types
        if (in_array($key, ['SESSION_LIFETIME', 'RATE_LIMIT_ATTEMPTS', 'RATE_LIMIT_WINDOW'], true)) {
            return 'integer';
        }

        // Boolean types
        if (in_array($key, ['APP_DEBUG'], true)) {
            return 'boolean';
        }

        // Default: string
        return 'string';
    }

    /**
     * Update environment configuration dengan validasi ketat
     * Save ke database DAN .env file
     *
     * @param array<string, string> $newConfig
     * @return bool
     */
    public static function update(array $newConfig): bool
    {
        self::init();

        try {
            // 1. Save to database first
            $dbSuccess = self::saveToDatabase($newConfig);

            if (!$dbSuccess) {
                error_log('EnvConfig: Failed to save to database');
                // Continue to .env update anyway
            }

            // 2. Save to .env file (backup untuk fallback)
            $envContent = '';
            if (file_exists(self::$envFilePath)) {
                $content = file_get_contents(self::$envFilePath);
                if ($content === false) {
                    throw new \RuntimeException('Failed to read .env file');
                }
                $envContent = $content;
            }

            // Parse existing env file
            $envVars = self::parseEnvFile($envContent);

            // Merge dengan new config (dengan validasi)
            foreach ($newConfig as $key => $value) {
                // Validate key (whitelist pattern)
                if (!self::isValidEnvKey($key)) {
                    error_log("Invalid env key skipped: {$key}");
                    continue;
                }

                // Sanitasi value (trim dan hapus newlines)
                $value = trim(str_replace(["\r", "\n"], '', (string)$value));

                // Update atau add
                $envVars[$key] = $value;
            }

            // Write back to file
            $newContent = self::buildEnvContent($envVars);

            // Backup old file
            if (file_exists(self::$envFilePath)) {
                $backupPath = self::$envFilePath . '.backup.' . time();
                if (!copy(self::$envFilePath, $backupPath)) {
                    error_log("Failed to create backup: {$backupPath}");
                }
            }

            // Write new content
            if (file_put_contents(self::$envFilePath, $newContent) === false) {
                throw new \RuntimeException('Failed to write .env file');
            }

            // Update current environment
            foreach ($newConfig as $key => $value) {
                if (self::isValidEnvKey($key)) {
                    putenv("{$key}={$value}");
                    $_ENV[$key] = $value;
                }
            }

            return true;
        } catch (Throwable $e) {
            error_log("EnvConfig update error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Test database connection dengan PDO (BUKAN mysqli)
     *
     * @param string $host
     * @param string $database
     * @param string $username
     * @param string $password
     * @return array{success: bool, message: string}
     */
    public static function testDatabaseConnection(
        string $host,
        string $database,
        string $username,
        string $password
    ): array {
        try {
            // Sanitasi input
            $host = Validator::sanitizeString($host, 255);
            $database = Validator::sanitizeString($database, 64);
            $username = Validator::sanitizeString($username, 32);

            // Password tidak di-sanitasi karena bisa contain special chars

            // Validate hostname format
            if (!preg_match('/^[a-z0-9.-]+$/i', $host)) {
                return [
                    'success' => false,
                    'message' => 'Invalid hostname format'
                ];
            }

            // Build DSN
            $dsn = "mysql:host={$host};dbname={$database};charset=utf8mb4";

            // Attempt PDO connection dengan timeout
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);

            // Test query
            $pdo->query('SELECT 1');

            // Close connection
            $pdo = null;

            return [
                'success' => true,
                'message' => 'Connection successful'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage()
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Test SRP API connection dengan HttpClient
     *
     * @param string $apiUrl
     * @param string $apiKey
     * @return array{success: bool, message: string, response?: array<string, mixed>}
     */
    public static function testSrpConnection(string $apiUrl, string $apiKey): array
    {
        try {
            // Validate URL
            if (!filter_var($apiUrl, FILTER_VALIDATE_URL)) {
                return [
                    'success' => false,
                    'message' => 'Invalid API URL format'
                ];
            }

            // Build test payload
            $testPayload = [
                'click_id' => 'TEST_' . time(),
                'country_code' => 'XX',
                'user_agent' => 'web',
                'ip_address' => '127.0.0.1',
                'user_lp' => 'test'
            ];

            // Send request menggunakan HttpClient
            $response = HttpClient::post(
                $apiUrl,
                $testPayload,
                [
                    'Content-Type: application/json',
                    'X-API-Key: ' . $apiKey,
                ],
                10,
                true
            );

            if (!$response['success']) {
                return [
                    'success' => false,
                    'message' => 'Request failed: ' . ($response['error'] ?? 'Unknown error')
                ];
            }

            $httpCode = $response['code'];

            if ($httpCode === 401 || $httpCode === 403) {
                return [
                    'success' => false,
                    'message' => 'Authentication failed. Check API key.'
                ];
            }

            if ($httpCode !== 200) {
                return [
                    'success' => false,
                    'message' => "HTTP error: {$httpCode}"
                ];
            }

            $data = json_decode($response['body'], true);

            if (!is_array($data)) {
                return [
                    'success' => false,
                    'message' => 'Invalid JSON response'
                ];
            }

            return [
                'success' => true,
                'message' => 'API connection successful',
                'response' => $data
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Parse .env file content
     *
     * @param string $content
     * @return array<string, string>
     */
    private static function parseEnvFile(string $content): array
    {
        $vars = [];
        $lines = explode("\n", $content);

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip empty lines dan comments
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            // Parse KEY=VALUE
            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Remove quotes jika ada
                $value = trim($value, '"\'');

                if (self::isValidEnvKey($key)) {
                    $vars[$key] = $value;
                }
            }
        }

        return $vars;
    }

    /**
     * Build .env file content dari array
     *
     * @param array<string, string> $vars
     * @return string
     */
    private static function buildEnvContent(array $vars): string
    {
        $content = "# ===================================================================\n";
        $content .= "# SRP Application Environment Configuration\n";
        $content .= "# Last updated: " . date('Y-m-d H:i:s') . "\n";
        $content .= "# ===================================================================\n\n";

        // Database section
        $content .= "# Database Configuration\n";
        $content .= "DB_HOST=" . ($vars['DB_HOST'] ?? 'localhost') . "\n";
        $content .= "DB_NAME=" . ($vars['DB_NAME'] ?? '') . "\n";
        $content .= "DB_USER=" . ($vars['DB_USER'] ?? '') . "\n";
        $content .= "DB_PASS=" . ($vars['DB_PASS'] ?? '') . "\n\n";

        // SRP API section
        $content .= "# SRP API Configuration\n";
        $content .= "SRP_API_URL=" . ($vars['SRP_API_URL'] ?? 'https://api.qvtrk.com/decision.php') . "\n";
        $content .= "SRP_API_KEY=" . ($vars['SRP_API_KEY'] ?? '') . "\n\n";

        // Application section
        $content .= "# Application Configuration\n";
        $content .= "APP_ENV=" . ($vars['APP_ENV'] ?? 'production') . "\n";
        $content .= "APP_DEBUG=" . ($vars['APP_DEBUG'] ?? 'false') . "\n\n";

        // Session section
        $content .= "# Session Configuration\n";
        $content .= "SESSION_LIFETIME=" . ($vars['SESSION_LIFETIME'] ?? '3600') . "\n\n";

        // Rate limiting section
        $content .= "# Rate Limiting Configuration\n";
        $content .= "RATE_LIMIT_ATTEMPTS=" . ($vars['RATE_LIMIT_ATTEMPTS'] ?? '5') . "\n";
        $content .= "RATE_LIMIT_WINDOW=" . ($vars['RATE_LIMIT_WINDOW'] ?? '900') . "\n\n";

        // Any other vars not in standard sections
        $standardKeys = [
            'DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS',
            'SRP_API_URL', 'SRP_API_KEY',
            'APP_ENV', 'APP_DEBUG',
            'SESSION_LIFETIME',
            'RATE_LIMIT_ATTEMPTS', 'RATE_LIMIT_WINDOW'
        ];

        $otherVars = array_diff_key($vars, array_flip($standardKeys));
        if (!empty($otherVars)) {
            $content .= "# Other Configuration\n";
            foreach ($otherVars as $key => $value) {
                if (self::isValidEnvKey($key)) {
                    $content .= "{$key}={$value}\n";
                }
            }
        }

        return $content;
    }

    /**
     * Validate environment key dengan whitelist pattern
     *
     * @param string $key
     * @return bool
     */
    private static function isValidEnvKey(string $key): bool
    {
        // Allow only uppercase alphanumeric dan underscore
        // Must start dengan letter atau underscore
        return (bool) preg_match('/^[A-Z_][A-Z0-9_]*$/', $key);
    }

    /**
     * Get configuration groups untuk UI
     *
     * @return array<string, array<string, mixed>>
     */
    public static function getConfigGroups(): array
    {
        $all = self::getAll();

        return [
            'database' => [
                'label' => 'Database Configuration',
                'icon' => 'database',
                'fields' => [
                    'DB_HOST' => [
                        'label' => 'Database Host',
                        'type' => 'text',
                        'value' => $all['DB_HOST'],
                        'placeholder' => 'localhost'
                    ],
                    'DB_NAME' => [
                        'label' => 'Database Name',
                        'type' => 'text',
                        'value' => $all['DB_NAME'],
                        'placeholder' => 'srp_database'
                    ],
                    'DB_USER' => [
                        'label' => 'Database Username',
                        'type' => 'text',
                        'value' => $all['DB_USER'],
                        'placeholder' => 'root'
                    ],
                    'DB_PASS' => [
                        'label' => 'Database Password',
                        'type' => 'password',
                        'value' => $all['DB_PASS'],
                        'placeholder' => '••••••••'
                    ]
                ]
            ],
            'srp_api' => [
                'label' => 'SRP API Configuration',
                'icon' => 'api',
                'fields' => [
                    'SRP_API_URL' => [
                        'label' => 'API URL',
                        'type' => 'url',
                        'value' => $all['SRP_API_URL'],
                        'placeholder' => 'https://api.qvtrk.com/decision.php'
                    ],
                    'SRP_API_KEY' => [
                        'label' => 'API Key',
                        'type' => 'password',
                        'value' => $all['SRP_API_KEY'],
                        'placeholder' => 'Enter your API key'
                    ]
                ]
            ],
            'application' => [
                'label' => 'Application Settings',
                'icon' => 'settings',
                'fields' => [
                    'APP_ENV' => [
                        'label' => 'Environment',
                        'type' => 'select',
                        'value' => $all['APP_ENV'],
                        'options' => [
                            'development' => 'Development',
                            'staging' => 'Staging',
                            'production' => 'Production'
                        ]
                    ],
                    'APP_DEBUG' => [
                        'label' => 'Debug Mode',
                        'type' => 'select',
                        'value' => $all['APP_DEBUG'],
                        'options' => [
                            'true' => 'Enabled',
                            'false' => 'Disabled'
                        ]
                    ],
                    'SESSION_LIFETIME' => [
                        'label' => 'Session Lifetime (seconds)',
                        'type' => 'number',
                        'value' => $all['SESSION_LIFETIME'],
                        'placeholder' => '3600'
                    ]
                ]
            ],
            'security' => [
                'label' => 'Security Settings',
                'icon' => 'shield',
                'fields' => [
                    'RATE_LIMIT_ATTEMPTS' => [
                        'label' => 'Rate Limit Attempts',
                        'type' => 'number',
                        'value' => $all['RATE_LIMIT_ATTEMPTS'],
                        'placeholder' => '5'
                    ],
                    'RATE_LIMIT_WINDOW' => [
                        'label' => 'Rate Limit Window (seconds)',
                        'type' => 'number',
                        'value' => $all['RATE_LIMIT_WINDOW'],
                        'placeholder' => '900'
                    ]
                ]
            ]
        ];
    }

    /**
     * Sync configuration dari .env file ke database
     * Gunakan untuk migration pertama kali
     *
     * @return bool
     */
    public static function syncFromEnvToDatabase(): bool
    {
        self::init();

        try {
            // Get all defined config keys with defaults
            $allKeys = self::getAllConfigKeys();
            $envConfig = [];

            // Read each key from environment or use default
            foreach ($allKeys as $key => $defaultValue) {
                $envValue = getenv($key);
                $envConfig[$key] = ($envValue !== false && $envValue !== '') ? $envValue : $defaultValue;
            }

            // Save to database
            $success = self::saveToDatabase($envConfig, 1);

            if ($success) {
                error_log('EnvConfig: Successfully synced ' . count($envConfig) . ' keys from .env to database');
            } else {
                error_log('EnvConfig: Failed to sync from .env to database');
            }

            return $success;
        } catch (Throwable $e) {
            error_log('EnvConfig syncFromEnvToDatabase error: ' . $e->getMessage());
            return false;
        }
    }
}
