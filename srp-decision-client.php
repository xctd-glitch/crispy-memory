<?php
/**
 * Smart Redirect Platform (SRP) Decision API Client
 *
 * Simple PHP client for integrating with SRP Decision API
 * Compatible with PHP 7.3+
 *
 * @version 1.0.0
 * @author SRP Team
 * @license MIT
 */

declare(strict_types=1);

/**
 * SRP Decision API Client Class
 */
class SrpDecisionClient
{
    /** @var string API endpoint URL */
    private string $apiUrl;

    /** @var string API authentication key */
    private string $apiKey;

    /** @var int Request timeout in seconds */
    private int $timeout;

    /** @var string Fallback URL if API fails */
    private string $fallbackUrl;

    /**
     * Constructor
     *
     * @param string $apiKey Your SRP API key
     * @param string $fallbackUrl URL to redirect on API failure
     * @param string $apiUrl API endpoint (default: https://api.qvtrk.com/decision.php)
     * @param int $timeout Request timeout in seconds (default: 5)
     */
    public function __construct(
        string $apiKey,
        string $fallbackUrl,
        string $apiUrl = 'https://api.qvtrk.com/decision.php',
        int $timeout = 5
    ) {
        $this->apiKey = $apiKey;
        $this->fallbackUrl = $fallbackUrl;
        $this->apiUrl = $apiUrl;
        $this->timeout = $timeout;
    }

    /**
     * Get routing decision from SRP API
     *
     * @param string $clickId Unique click/tracking ID
     * @param string $countryCode ISO 3166-1 Alpha-2 country code
     * @param string $userAgent Browser user agent string
     * @param string $ipAddress Client IP address
     * @param string|null $userLp Optional landing page identifier
     * @return array|null API response array or null on failure
     */
    public function getDecision(
        string $clickId,
        string $countryCode,
        string $userAgent,
        string $ipAddress,
        ?string $userLp = null
    ): ?array {
        // Prepare request data
        $data = [
            'click_id' => $clickId,
            'country_code' => $countryCode,
            'user_agent' => $userAgent,
            'ip_address' => $ipAddress
        ];

        if ($userLp !== null) {
            $data['user_lp'] = $userLp;
        }

        // Encode to JSON
        $payload = json_encode($data);
        if ($payload === false) {
            $this->logError('Failed to encode JSON payload');
            return null;
        }

        // Initialize cURL
        $ch = curl_init($this->apiUrl);
        if ($ch === false) {
            $this->logError('Failed to initialize cURL');
            return null;
        }

        // Set cURL options
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-API-Key: ' . $this->apiKey,
                'User-Agent: SRP-Client/1.0'
            ],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_FOLLOWLOCATION => false
        ]);

        // Execute request
        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        // Handle cURL errors
        if ($response === false || $error !== '') {
            $this->logError("cURL error: {$error}");
            return null;
        }

        // Handle HTTP errors
        if ($httpCode !== 200) {
            $this->logError("HTTP error: {$httpCode} - {$response}");
            return null;
        }

        // Decode JSON response
        $data = json_decode($response, true);
        if (!is_array($data) || empty($data['ok'])) {
            $this->logError('Invalid API response');
            return null;
        }

        return $data;
    }

    /**
     * Process redirect based on API decision
     *
     * @param array|null $decision API response
     * @return void
     */
    public function redirect(?array $decision): void
    {
        if ($decision === null || empty($decision['target'])) {
            // Fallback on API failure
            $this->doRedirect($this->fallbackUrl);
            return;
        }

        // Redirect based on decision
        $this->doRedirect($decision['target']);
    }

    /**
     * Helper method to get client data from request
     *
     * @param bool $trustCloudflare Trust Cloudflare headers for IP/country
     * @return array Client data array
     */
    public function getClientData(bool $trustCloudflare = true): array
    {
        // Generate or get click ID
        $clickId = $_GET['cid'] ?? $_GET['click_id'] ?? $this->generateClickId();

        // Get country code
        $countryCode = 'XX'; // Default unknown
        if ($trustCloudflare && !empty($_SERVER['HTTP_CF_IPCOUNTRY'])) {
            $countryCode = $_SERVER['HTTP_CF_IPCOUNTRY'];
        }

        // Get user agent
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        // Get IP address
        $ipAddress = '0.0.0.0';
        if ($trustCloudflare && !empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $ipAddress = $_SERVER['HTTP_CF_CONNECTING_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ipAddress = trim($ips[0]);
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ipAddress = $_SERVER['REMOTE_ADDR'];
        }

        // Get landing page
        $userLp = $_GET['lp'] ?? null;

        return [
            'click_id' => $clickId,
            'country_code' => $countryCode,
            'user_agent' => $userAgent,
            'ip_address' => $ipAddress,
            'user_lp' => $userLp
        ];
    }

    /**
     * Quick method to process request and redirect
     *
     * @param bool $trustCloudflare Trust Cloudflare headers
     * @return void
     */
    public function processRequest(bool $trustCloudflare = true): void
    {
        // Get client data
        $clientData = $this->getClientData($trustCloudflare);

        // Get decision
        $decision = $this->getDecision(
            $clientData['click_id'],
            $clientData['country_code'],
            $clientData['user_agent'],
            $clientData['ip_address'],
            $clientData['user_lp']
        );

        // Process redirect
        $this->redirect($decision);
    }

    /**
     * Generate unique click ID
     *
     * @return string
     */
    private function generateClickId(): string
    {
        return uniqid('click_', true);
    }

    /**
     * Perform actual redirect
     *
     * @param string $url Target URL
     * @return void
     */
    private function doRedirect(string $url): void
    {
        // Ensure no output before redirect
        if (ob_get_level()) {
            ob_end_clean();
        }

        // Validate URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $url = $this->fallbackUrl;
        }

        // Perform redirect
        header('Location: ' . $url, true, 302);
        exit;
    }

    /**
     * Log error message
     *
     * @param string $message Error message
     * @return void
     */
    private function logError(string $message): void
    {
        error_log("[SRP Client] {$message}");
    }
}

// ============================================
// USAGE EXAMPLES
// ============================================

/*
// Example 1: Basic Usage
require_once 'srp-decision-client.php';

$client = new SrpDecisionClient(
    'your_api_key_here',
    'https://your-fallback-url.com'
);

$client->processRequest();
*/

/*
// Example 2: Manual Control
require_once 'srp-decision-client.php';

$client = new SrpDecisionClient(
    'your_api_key_here',
    'https://your-fallback-url.com'
);

// Get decision
$decision = $client->getDecision(
    'click_123',
    'US',
    $_SERVER['HTTP_USER_AGENT'],
    $_SERVER['REMOTE_ADDR']
);

// Custom handling
if ($decision && $decision['decision'] === 'A') {
    header('Location: ' . $decision['target']);
} else {
    header('Location: https://your-fallback-url.com');
}
exit;
*/

/*
// Example 3: With Custom Settings
require_once 'srp-decision-client.php';

$client = new SrpDecisionClient(
    apiKey: 'your_api_key_here',
    fallbackUrl: 'https://your-fallback-url.com',
    apiUrl: 'https://custom-api.qvtrk.com/decision.php',
    timeout: 10
);

// Process with Cloudflare support
$client->processRequest(trustCloudflare: true);
*/

/*
// Example 4: Integration in Existing Code
require_once 'srp-decision-client.php';

// Your existing code...
$campaignId = $_GET['campaign'] ?? 'default';
$source = $_GET['source'] ?? 'organic';

// Initialize SRP client
$srpClient = new SrpDecisionClient(
    'your_api_key_here',
    'https://your-fallback-url.com'
);

// Get client data with custom click ID
$clientData = $srpClient->getClientData();
$clientData['click_id'] = "{$campaignId}_{$source}_" . time();

// Get decision
$decision = $srpClient->getDecision(
    $clientData['click_id'],
    $clientData['country_code'],
    $clientData['user_agent'],
    $clientData['ip_address']
);

// Log the decision (optional)
if ($decision) {
    file_put_contents(
        'srp_decisions.log',
        date('Y-m-d H:i:s') . " - {$clientData['click_id']} - {$decision['decision']}\n",
        FILE_APPEND
    );
}

// Perform redirect
$srpClient->redirect($decision);
*/