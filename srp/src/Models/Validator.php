<?php

declare(strict_types=1);

namespace SRP\Models;

/**
 * Validator Helper Class (Production-Ready)
 *
 * Comprehensive input validation dan sanitization
 * untuk keamanan maksimal
 */
class Validator
{
    // =========================================================================
    // IP ADDRESS VALIDATION
    // =========================================================================

    /**
     * Validate IP address (IPv4 atau IPv6)
     *
     * @param string $ip
     * @return bool
     */
    public static function isValidIp(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Validate IPv4 address only
     *
     * @param string $ip
     * @return bool
     */
    public static function isValidIpv4(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
    }

    /**
     * Validate IPv6 address only
     *
     * @param string $ip
     * @return bool
     */
    public static function isValidIpv6(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
    }

    /**
     * Check if IP is public (not private/reserved)
     *
     * @param string $ip
     * @return bool
     */
    public static function isPublicIp(string $ip): bool
    {
        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) !== false;
    }

    // =========================================================================
    // STRING SANITIZATION
    // =========================================================================

    /**
     * Sanitize string dengan trim dan length limit
     *
     * @param string $input
     * @param int $maxLength
     * @return string
     */
    public static function sanitizeString(string $input, int $maxLength = 255): string
    {
        // Trim whitespace
        $clean = trim($input);

        // Remove control characters (kecuali tab, newline, carriage return)
        $clean = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $clean);

        // Limit length
        return mb_substr($clean, 0, $maxLength, 'UTF-8');
    }

    /**
     * Sanitize string untuk single line (remove newlines)
     *
     * @param string $input
     * @param int $maxLength
     * @return string
     */
    public static function sanitizeSingleLine(string $input, int $maxLength = 255): string
    {
        // Remove newlines dan carriage returns
        $clean = str_replace(["\r", "\n", "\t"], ' ', $input);

        return self::sanitizeString($clean, $maxLength);
    }

    /**
     * Escape output untuk HTML dengan settings aman
     *
     * @param string $value
     * @return string
     */
    public static function escapeHtml(string $value): string
    {
        return htmlspecialchars(
            $value,
            ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5,
            'UTF-8'
        );
    }

    /**
     * Escape output untuk HTML attribute
     *
     * @param string $value
     * @return string
     */
    public static function escapeHtmlAttr(string $value): string
    {
        // Same as escapeHtml tapi lebih explicit
        return htmlspecialchars(
            $value,
            ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5,
            'UTF-8'
        );
    }

    /**
     * Escape untuk JavaScript string (inside quotes)
     *
     * @param string $value
     * @return string
     */
    public static function escapeJs(string $value): string
    {
        // Escape quotes, backslashes, dan control characters
        $map = [
            "\\" => "\\\\",
            "'" => "\\'",
            '"' => '\\"',
            "\n" => "\\n",
            "\r" => "\\r",
            "\t" => "\\t",
            "/" => "\\/",
            "<" => "\\x3C",
            ">" => "\\x3E",
        ];

        return strtr($value, $map);
    }

    /**
     * Sanitize filename untuk prevent directory traversal
     *
     * @param string $filename
     * @param int $maxLength
     * @return string
     */
    public static function sanitizeFilename(string $filename, int $maxLength = 255): string
    {
        // Remove path separators
        $clean = str_replace(['/', '\\', '..'], '', $filename);

        // Remove control characters
        $clean = preg_replace('/[\x00-\x1F\x7F]/u', '', $clean);

        // Remove potentially dangerous characters
        $clean = preg_replace('/[<>:"|?*]/', '', $clean);

        // Trim dan limit length
        $clean = trim($clean);

        return mb_substr($clean, 0, $maxLength, 'UTF-8');
    }

    // =========================================================================
    // COUNTRY CODE VALIDATION
    // =========================================================================

    /**
     * Validate country code (ISO 3166-1 alpha-2)
     *
     * @param string $code
     * @return bool
     */
    public static function isValidCountryCode(string $code): bool
    {
        $validCodes = [
            'AD', 'AE', 'AF', 'AG', 'AI', 'AL', 'AM', 'AO', 'AQ', 'AR', 'AS', 'AT',
            'AU', 'AW', 'AX', 'AZ', 'BA', 'BB', 'BD', 'BE', 'BF', 'BG', 'BH', 'BI',
            'BJ', 'BL', 'BM', 'BN', 'BO', 'BQ', 'BR', 'BS', 'BT', 'BV', 'BW', 'BY',
            'BZ', 'CA', 'CC', 'CD', 'CF', 'CG', 'CH', 'CI', 'CK', 'CL', 'CM', 'CN',
            'CO', 'CR', 'CU', 'CV', 'CW', 'CX', 'CY', 'CZ', 'DE', 'DJ', 'DK', 'DM',
            'DO', 'DZ', 'EC', 'EE', 'EG', 'EH', 'ER', 'ES', 'ET', 'FI', 'FJ', 'FK',
            'FM', 'FO', 'FR', 'GA', 'GB', 'GD', 'GE', 'GF', 'GG', 'GH', 'GI', 'GL',
            'GM', 'GN', 'GP', 'GQ', 'GR', 'GS', 'GT', 'GU', 'GW', 'GY', 'HK', 'HM',
            'HN', 'HR', 'HT', 'HU', 'ID', 'IE', 'IL', 'IM', 'IN', 'IO', 'IQ', 'IR',
            'IS', 'IT', 'JE', 'JM', 'JO', 'JP', 'KE', 'KG', 'KH', 'KI', 'KM', 'KN',
            'KP', 'KR', 'KW', 'KY', 'KZ', 'LA', 'LB', 'LC', 'LI', 'LK', 'LR', 'LS',
            'LT', 'LU', 'LV', 'LY', 'MA', 'MC', 'MD', 'ME', 'MF', 'MG', 'MH', 'MK',
            'ML', 'MM', 'MN', 'MO', 'MP', 'MQ', 'MR', 'MS', 'MT', 'MU', 'MV', 'MW',
            'MX', 'MY', 'MZ', 'NA', 'NC', 'NE', 'NF', 'NG', 'NI', 'NL', 'NO', 'NP',
            'NR', 'NU', 'NZ', 'OM', 'PA', 'PE', 'PF', 'PG', 'PH', 'PK', 'PL', 'PM',
            'PN', 'PR', 'PS', 'PT', 'PW', 'PY', 'QA', 'RE', 'RO', 'RS', 'RU', 'RW',
            'SA', 'SB', 'SC', 'SD', 'SE', 'SG', 'SH', 'SI', 'SJ', 'SK', 'SL', 'SM',
            'SN', 'SO', 'SR', 'SS', 'ST', 'SV', 'SX', 'SY', 'SZ', 'TC', 'TD', 'TF',
            'TG', 'TH', 'TJ', 'TK', 'TL', 'TM', 'TN', 'TO', 'TR', 'TT', 'TV', 'TW',
            'TZ', 'UA', 'UG', 'UM', 'US', 'UY', 'UZ', 'VA', 'VC', 'VE', 'VG', 'VI',
            'VN', 'VU', 'WF', 'WS', 'XX', 'YE', 'YT', 'ZA', 'ZM', 'ZW'
        ];

        return in_array(strtoupper($code), $validCodes, true);
    }

    /**
     * Check if country is allowed berdasarkan settings filter
     *
     * @param string $countryCode
     * @return bool
     */
    public static function isCountryAllowed(string $countryCode): bool
    {
        $filter = Settings::getCountryFilter();
        $mode = $filter['mode'];
        $list = $filter['list'];
        $cc = strtoupper($countryCode);

        switch ($mode) {
            case 'whitelist':
                if (empty($list)) {
                    return false;
                }
                return in_array($cc, $list, true);
            case 'blacklist':
                if (empty($list)) {
                    return true;
                }
                return !in_array($cc, $list, true);
            case 'all':
            default:
                return true;
        }
    }

    // =========================================================================
    // URL VALIDATION
    // =========================================================================

    /**
     * Validate URL format
     *
     * @param string $url
     * @param bool $httpsOnly
     * @return bool
     */
    public static function isValidUrl(string $url, bool $httpsOnly = false): bool
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        if ($httpsOnly) {
            $parsed = parse_url($url);
            if (!isset($parsed['scheme']) || $parsed['scheme'] !== 'https') {
                return false;
            }
        }

        return true;
    }

    /**
     * Sanitize URL dengan validation
     *
     * @param string $url
     * @param bool $httpsOnly
     * @return string|null Returns sanitized URL or null if invalid
     */
    public static function sanitizeUrl(string $url, bool $httpsOnly = false): ?string
    {
        $clean = trim($url);

        if (!self::isValidUrl($clean, $httpsOnly)) {
            return null;
        }

        return $clean;
    }

    // =========================================================================
    // EMAIL VALIDATION
    // =========================================================================

    /**
     * Validate email address
     *
     * @param string $email
     * @return bool
     */
    public static function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Sanitize email address
     *
     * @param string $email
     * @return string|null
     */
    public static function sanitizeEmail(string $email): ?string
    {
        $clean = filter_var(trim($email), FILTER_SANITIZE_EMAIL);

        if ($clean === false || !self::isValidEmail($clean)) {
            return null;
        }

        return $clean;
    }

    // =========================================================================
    // NUMERIC VALIDATION
    // =========================================================================

    /**
     * Validate integer dengan optional range check
     *
     * @param mixed $value
     * @param int|null $min
     * @param int|null $max
     * @return bool
     */
    public static function isValidInt($value, ?int $min = null, ?int $max = null): bool
    {
        if (!is_numeric($value)) {
            return false;
        }

        $intVal = (int)$value;

        // Check if conversion was clean (no decimals lost)
        if ((string)$intVal !== (string)$value) {
            return false;
        }

        if ($min !== null && $intVal < $min) {
            return false;
        }

        if ($max !== null && $intVal > $max) {
            return false;
        }

        return true;
    }

    /**
     * Validate float dengan optional range check
     *
     * @param mixed $value
     * @param float|null $min
     * @param float|null $max
     * @return bool
     */
    public static function isValidFloat($value, ?float $min = null, ?float $max = null): bool
    {
        if (!is_numeric($value)) {
            return false;
        }

        $floatVal = (float)$value;

        if ($min !== null && $floatVal < $min) {
            return false;
        }

        if ($max !== null && $floatVal > $max) {
            return false;
        }

        return true;
    }

    /**
     * Sanitize integer dengan default value
     *
     * @param mixed $value
     * @param int $default
     * @param int|null $min
     * @param int|null $max
     * @return int
     */
    public static function sanitizeInt($value, int $default = 0, ?int $min = null, ?int $max = null): int
    {
        if (!self::isValidInt($value, $min, $max)) {
            return $default;
        }

        return (int)$value;
    }

    /**
     * Sanitize float dengan default value
     *
     * @param mixed $value
     * @param float $default
     * @param float|null $min
     * @param float|null $max
     * @return float
     */
    public static function sanitizeFloat($value, float $default = 0.0, ?float $min = null, ?float $max = null): float
    {
        if (!self::isValidFloat($value, $min, $max)) {
            return $default;
        }

        return (float)$value;
    }

    // =========================================================================
    // PATTERN VALIDATION
    // =========================================================================

    /**
     * Check if string is alphanumeric only
     *
     * @param string $value
     * @return bool
     */
    public static function isAlphanumeric(string $value): bool
    {
        return (bool)preg_match('/^[a-zA-Z0-9]+$/', $value);
    }

    /**
     * Check if string is alphanumeric with underscores dan hyphens
     *
     * @param string $value
     * @return bool
     */
    public static function isSlug(string $value): bool
    {
        return (bool)preg_match('/^[a-z0-9_-]+$/', $value);
    }

    /**
     * Check if string contains only letters
     *
     * @param string $value
     * @return bool
     */
    public static function isAlpha(string $value): bool
    {
        return (bool)preg_match('/^[a-zA-Z]+$/', $value);
    }

    /**
     * Validate custom pattern dengan regex
     *
     * @param string $value
     * @param string $pattern
     * @return bool
     */
    public static function matchesPattern(string $value, string $pattern): bool
    {
        return (bool)preg_match($pattern, $value);
    }

    // =========================================================================
    // ARRAY VALIDATION
    // =========================================================================

    /**
     * Check if all array keys exist
     *
     * @param array<int|string, mixed> $array
     * @param array<int, string> $requiredKeys
     * @return bool
     */
    public static function hasRequiredKeys(array $array, array $requiredKeys): bool
    {
        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $array)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate array structure dengan type checking
     *
     * @param array<int|string, mixed> $array
     * @param array<string, string> $schema Format: ['key' => 'type']
     * @return bool
     */
    public static function validateArraySchema(array $array, array $schema): bool
    {
        foreach ($schema as $key => $type) {
            if (!isset($array[$key])) {
                return false;
            }

            $value = $array[$key];

            switch ($type) {
                case 'string':
                    if (!is_string($value)) {
                        return false;
                    }
                    break;
                case 'int':
                    if (!is_int($value)) {
                        return false;
                    }
                    break;
                case 'float':
                    if (!is_float($value)) {
                        return false;
                    }
                    break;
                case 'bool':
                    if (!is_bool($value)) {
                        return false;
                    }
                    break;
                case 'array':
                    if (!is_array($value)) {
                        return false;
                    }
                    break;
                default:
                    return false;
            }
        }

        return true;
    }

    // =========================================================================
    // BOOLEAN VALIDATION
    // =========================================================================

    /**
     * Convert berbagai format ke boolean
     *
     * @param mixed $value
     * @return bool
     */
    public static function toBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int)$value !== 0;
        }

        if (is_string($value)) {
            $lower = strtolower(trim($value));
            return in_array($lower, ['true', 'yes', '1', 'on'], true);
        }

        return false;
    }

    // =========================================================================
    // SECURITY HELPERS
    // =========================================================================

    /**
     * Generate random token untuk CSRF atau API keys
     *
     * @param int $length
     * @return string
     */
    public static function generateToken(int $length = 32): string
    {
        if (function_exists('random_bytes')) {
            return bin2hex(random_bytes($length));
        }

        // Fallback (less secure)
        return bin2hex(openssl_random_pseudo_bytes($length));
    }

    /**
     * Constant-time string comparison untuk prevent timing attacks
     *
     * @param string $known
     * @param string $user
     * @return bool
     */
    public static function hashEquals(string $known, string $user): bool
    {
        return hash_equals($known, $user);
    }

    /**
     * Check if string contains potential XSS patterns
     *
     * @param string $value
     * @return bool Returns true if dangerous patterns found
     */
    public static function containsXss(string $value): bool
    {
        $dangerousPatterns = [
            '/<script\b/i',
            '/javascript:/i',
            '/on\w+\s*=/i', // Event handlers like onclick=
            '/<iframe\b/i',
            '/<object\b/i',
            '/<embed\b/i',
            '/vbscript:/i',
            '/data:text\/html/i',
        ];

        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return true;
            }
        }

        return false;
    }

    // =========================================================================
    // DATE/TIME VALIDATION
    // =========================================================================

    /**
     * Validate timestamp (Unix timestamp)
     *
     * @param mixed $value
     * @return bool
     */
    public static function isValidTimestamp($value): bool
    {
        return self::isValidInt($value, 0, 2147483647); // Max 32-bit int
    }

    /**
     * Validate date string format
     *
     * @param string $date
     * @param string $format Default: Y-m-d
     * @return bool
     */
    public static function isValidDate(string $date, string $format = 'Y-m-d'): bool
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
}
