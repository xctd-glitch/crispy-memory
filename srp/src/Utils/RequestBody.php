<?php

declare(strict_types=1);

namespace SRP\Utils;

/**
 * Request Body Utility
 * Centralized request body parsing and validation
 */
class RequestBody
{
    /**
     * Read and parse JSON request body
     *
     * @param bool $assoc Return associative array instead of object
     * @param int $maxLength Maximum body length in bytes (default: 1MB)
     * @throws \RuntimeException If body cannot be read or parsed
     * @return mixed
     */
    public static function parseJson(bool $assoc = true, int $maxLength = 1048576): mixed
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        // Validate content type
        if (!str_contains($contentType, 'application/json')) {
            throw new \RuntimeException('Content-Type must be application/json');
        }

        // Read request body
        $body = self::read($maxLength);

        if ($body === '') {
            throw new \RuntimeException('Request body is empty');
        }

        // Parse JSON
        try {
            $data = json_decode($body, $assoc, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \RuntimeException('Invalid JSON in request body: ' . $e->getMessage());
        }

        return $data;
    }

    /**
     * Read raw request body
     *
     * @param int $maxLength Maximum body length in bytes
     * @throws \RuntimeException If body exceeds maximum length
     * @return string
     */
    public static function read(int $maxLength = 1048576): string
    {
        // Check content length header
        $contentLength = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);

        if ($contentLength > $maxLength) {
            throw new \RuntimeException(sprintf(
                'Request body too large: %d bytes (max: %d bytes)',
                $contentLength,
                $maxLength
            ));
        }

        // Read from php://input
        $body = file_get_contents('php://input');

        if ($body === false) {
            throw new \RuntimeException('Failed to read request body');
        }

        // Double-check actual length
        $actualLength = strlen($body);
        if ($actualLength > $maxLength) {
            throw new \RuntimeException(sprintf(
                'Request body too large: %d bytes (max: %d bytes)',
                $actualLength,
                $maxLength
            ));
        }

        return $body;
    }

    /**
     * Safely get a value from parsed JSON data
     *
     * @param array $data Parsed JSON data
     * @param string $key Key to retrieve
     * @param mixed $default Default value if key doesn't exist
     * @return mixed
     */
    public static function get(array $data, string $key, mixed $default = null): mixed
    {
        return $data[$key] ?? $default;
    }

    /**
     * Validate required fields exist in request data
     *
     * @param array $data Request data
     * @param array $requiredFields List of required field names
     * @throws \RuntimeException If any required field is missing
     * @return void
     */
    public static function validateRequired(array $data, array $requiredFields): void
    {
        $missingFields = [];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === '' || $data[$field] === null) {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            throw new \RuntimeException(
                'Missing required fields: ' . implode(', ', $missingFields)
            );
        }
    }

    /**
     * Parse form-urlencoded request body
     *
     * @param int $maxLength Maximum body length in bytes
     * @return array
     */
    public static function parseFormData(int $maxLength = 1048576): array
    {
        $body = self::read($maxLength);

        if ($body === '') {
            return [];
        }

        parse_str($body, $data);
        return $data;
    }
}
