<?php
/**
 * SRP Decision API - Simple Integration
 *
 * Drop-in PHP script for quick integration with SRP Decision API
 * Just update the configuration and upload to your server
 *
 * @version 1.0.0
 */

// ============================================
// CONFIGURATION - UPDATE THESE VALUES
// ============================================

$config = [
    'api_key' => 'your_api_key_here',              // Get from SRP Dashboard
    'fallback_url' => 'https://fallback-url.com',  // Your fallback URL
    'api_url' => 'https://api.qvtrk.com/decision.php',
    'timeout' => 5,                                 // Seconds
    'trust_cloudflare' => true                      // Use CF headers for IP/Country
];

// ============================================
// IMPLEMENTATION - NO NEED TO EDIT BELOW
// ============================================

// Get client data
$clickId = $_GET['cid'] ?? $_GET['click_id'] ?? uniqid('click_', true);
$countryCode = 'XX';
$ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

// Check Cloudflare headers
if ($config['trust_cloudflare']) {
    if (!empty($_SERVER['HTTP_CF_IPCOUNTRY'])) {
        $countryCode = $_SERVER['HTTP_CF_IPCOUNTRY'];
    }
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        $ipAddress = $_SERVER['HTTP_CF_CONNECTING_IP'];
    }
}

// Prepare API request
$requestData = [
    'click_id' => $clickId,
    'country_code' => $countryCode,
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
    'ip_address' => $ipAddress
];

// Add optional landing page
if (!empty($_GET['lp'])) {
    $requestData['user_lp'] = $_GET['lp'];
}

// Make API call
$ch = curl_init($config['api_url']);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($requestData),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => $config['timeout'],
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'X-API-Key: ' . $config['api_key']
    ],
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Process response
$targetUrl = $config['fallback_url'];

if ($httpCode === 200 && $response !== false) {
    $data = json_decode($response, true);
    if (!empty($data['ok']) && !empty($data['target'])) {
        $targetUrl = $data['target'];
    }
}

// Redirect
header('Location: ' . $targetUrl, true, 302);
exit;