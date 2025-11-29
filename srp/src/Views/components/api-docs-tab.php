<!-- API Documentation Tab -->
<div x-show="activeTab === 'api-docs'" x-cloak>
    <div class="space-y-4">
        <!-- Header -->
        <div>
            <h2 class="text-sm font-semibold">Decision API Documentation</h2>
            <p class="text-[10px] text-muted-foreground mt-0.5">
                Panduan lengkap implementasi Decision API untuk smart routing traffic dari external hosting
            </p>
        </div>

        <!-- Quick Start -->
        <div class="card p-3 border-primary/30 bg-primary/5">
            <div class="flex items-start gap-2 mb-2">
                <svg class="h-4 w-4 text-primary mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                <div class="flex-1">
                    <h3 class="text-xs font-semibold">Quick Start</h3>
                    <div class="text-[10px] mt-1 space-y-1">
                        <p>1. Dapatkan API Key dari tab Environment Configuration</p>
                        <p>2. Implementasi HTTP POST ke <code class="text-primary">https://api.qvtrk.com/decision.php</code></p>
                        <p>3. Handle response decision untuk redirect user</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overview Section -->
        <div class="card p-3">
            <div class="flex items-start gap-2 mb-2.5">
                <svg class="h-4 w-4 text-primary mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="flex-1">
                    <h3 class="text-xs font-semibold">Overview</h3>
                    <p class="text-[10px] text-muted-foreground mt-1">
                        Decision API adalah endpoint untuk mendapatkan keputusan routing traffic secara real-time.
                        API ini menerima data user (IP, country, device) dan mengembalikan target URL berdasarkan aturan routing yang dikonfigurasi.
                    </p>
                </div>
            </div>

            <div class="mt-3 p-2 rounded bg-muted/50 border">
                <div class="grid grid-cols-2 gap-2 text-[10px]">
                    <div>
                        <span class="text-muted-foreground">Method:</span>
                        <code class="ml-1 font-semibold">POST</code>
                    </div>
                    <div>
                        <span class="text-muted-foreground">Content-Type:</span>
                        <code class="ml-1">application/json</code>
                    </div>
                </div>
            </div>
        </div>

        <!-- Endpoint Section -->
        <div class="card p-3">
            <div class="flex items-start gap-2 mb-2.5">
                <svg class="h-4 w-4 text-primary mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                </svg>
                <div class="flex-1">
                    <h3 class="text-xs font-semibold">Endpoint & Authentication</h3>
                </div>
            </div>

            <div class="space-y-2">
                <div>
                    <label class="block text-[10px] font-medium mb-1">API URL</label>
                    <div class="relative">
                        <input
                            type="text"
                            class="input input-sm w-full font-mono text-[10px]"
                            value="https://api.qvtrk.com/decision.php"
                            readonly>
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-medium mb-1">Required Header</label>
                    <div class="p-2 rounded bg-slate-900 text-emerald-400 font-mono text-[10px] overflow-x-auto">
                        <div>X-API-Key: <span class="text-amber-300">your_api_key_here</span></div>
                    </div>
                    <p class="text-[10px] text-muted-foreground mt-1">
                        Dapatkan API key dari tab Environment Configuration
                    </p>
                </div>
            </div>
        </div>

        <!-- Request Format Section -->
        <div class="card p-3">
            <div class="flex items-start gap-2 mb-2.5">
                <svg class="h-4 w-4 text-primary mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <div class="flex-1">
                    <h3 class="text-xs font-semibold">Request Format</h3>
                    <p class="text-[10px] text-muted-foreground mt-1">JSON payload dengan parameter berikut:</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-[10px] border-collapse">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-1.5 px-2 font-semibold">Parameter</th>
                            <th class="text-left py-1.5 px-2 font-semibold">Type</th>
                            <th class="text-left py-1.5 px-2 font-semibold">Required</th>
                            <th class="text-left py-1.5 px-2 font-semibold">Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b">
                            <td class="py-1.5 px-2"><code class="text-primary">click_id</code></td>
                            <td class="py-1.5 px-2">string</td>
                            <td class="py-1.5 px-2">Yes</td>
                            <td class="py-1.5 px-2">Unique click/tracking ID (max 100 char)</td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-1.5 px-2"><code class="text-primary">country_code</code></td>
                            <td class="py-1.5 px-2">string</td>
                            <td class="py-1.5 px-2">Yes</td>
                            <td class="py-1.5 px-2">ISO 3166-1 Alpha-2 code (e.g., US, GB, ID)</td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-1.5 px-2"><code class="text-primary">user_agent</code></td>
                            <td class="py-1.5 px-2">string</td>
                            <td class="py-1.5 px-2">Yes</td>
                            <td class="py-1.5 px-2">Browser user agent string</td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-1.5 px-2"><code class="text-primary">ip_address</code></td>
                            <td class="py-1.5 px-2">string</td>
                            <td class="py-1.5 px-2">Yes</td>
                            <td class="py-1.5 px-2">Client IP address (IPv4 or IPv6)</td>
                        </tr>
                        <tr>
                            <td class="py-1.5 px-2"><code class="text-primary">user_lp</code></td>
                            <td class="py-1.5 px-2">string</td>
                            <td class="py-1.5 px-2">No</td>
                            <td class="py-1.5 px-2">Landing page identifier (max 100 char)</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                <label class="block text-[10px] font-medium mb-1">Example Request Body</label>
                <div class="p-2 rounded bg-slate-900 text-emerald-400 font-mono text-[10px] overflow-x-auto">
<pre>{
  "click_id": "abc123xyz",
  "country_code": "US",
  "user_agent": "Mozilla/5.0 (iPhone; CPU iPhone OS 14_0...)",
  "ip_address": "203.0.113.45",
  "user_lp": "landing01"
}</pre>
                </div>
            </div>
        </div>

        <!-- Response Format Section -->
        <div class="card p-3">
            <div class="flex items-start gap-2 mb-2.5">
                <svg class="h-4 w-4 text-primary mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                </svg>
                <div class="flex-1">
                    <h3 class="text-xs font-semibold">Response Format</h3>
                </div>
            </div>

            <div class="space-y-3">
                <div>
                    <p class="text-[10px] font-medium mb-1">Success Response (HTTP 200)</p>
                    <div class="p-2 rounded bg-slate-900 text-emerald-400 font-mono text-[10px] overflow-x-auto">
<pre>{
  "ok": true,
  "decision": "A",
  "target": "https://example.com/offer"
}</pre>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-[10px] border-collapse">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-1.5 px-2 font-semibold">Field</th>
                                <th class="text-left py-1.5 px-2 font-semibold">Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b">
                                <td class="py-1.5 px-2"><code class="text-primary">ok</code></td>
                                <td class="py-1.5 px-2">Request berhasil diproses</td>
                            </tr>
                            <tr class="border-b">
                                <td class="py-1.5 px-2"><code class="text-primary">decision</code></td>
                                <td class="py-1.5 px-2">"A" = Redirect ke offer, "B" = Fallback</td>
                            </tr>
                            <tr>
                                <td class="py-1.5 px-2"><code class="text-primary">target</code></td>
                                <td class="py-1.5 px-2">URL tujuan redirect</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div>
                    <p class="text-[10px] font-medium mb-1">Error Response (HTTP 4xx/5xx)</p>
                    <div class="p-2 rounded bg-slate-900 text-red-400 font-mono text-[10px] overflow-x-auto">
<pre>{
  "ok": false,
  "error": "Missing required field: ip_address"
}</pre>
                    </div>
                </div>
            </div>
        </div>

        <!-- Implementation Examples -->
        <div class="card p-3">
            <div class="flex items-start gap-2 mb-2.5">
                <svg class="h-4 w-4 text-primary mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <div class="flex-1">
                    <h3 class="text-xs font-semibold">Implementation Examples</h3>
                    <p class="text-[10px] text-muted-foreground mt-1">
                        Contoh implementasi dalam berbagai bahasa pemrograman
                    </p>
                </div>
            </div>

            <!-- Language Tabs -->
            <div x-data="{ lang: 'php' }" class="mt-3">
                <div class="flex gap-1 mb-2">
                    <button @click="lang = 'php'" :class="lang === 'php' ? 'btn btn-primary' : 'btn btn-outline'" class="btn btn-xs">PHP cURL</button>
                    <button @click="lang = 'simple'" :class="lang === 'simple' ? 'btn btn-primary' : 'btn btn-outline'" class="btn btn-xs">PHP Simple</button>
                    <button @click="lang = 'js'" :class="lang === 'js' ? 'btn btn-primary' : 'btn btn-outline'" class="btn btn-xs">JavaScript</button>
                    <button @click="lang = 'python'" :class="lang === 'python' ? 'btn btn-primary' : 'btn btn-outline'" class="btn btn-xs">Python</button>
                </div>

                <!-- PHP cURL Full Implementation -->
                <div x-show="lang === 'php'" x-cloak>

            <div class="p-2 rounded bg-slate-900 text-slate-200 font-mono text-[10px] overflow-x-auto">
<pre><?= htmlspecialchars('<?php
declare(strict_types=1);

/**
 * SRP Decision API Client
 *
 * @param string $apiUrl API endpoint URL
 * @param string $apiKey API authentication key
 * @param array $params Request parameters
 * @return array|null Response array or null on failure
 */
function getSrpDecision(string $apiUrl, string $apiKey, array $params): ?array
{
    // Validate required parameters
    $required = [\'click_id\', \'country_code\', \'user_agent\', \'ip_address\'];
    foreach ($required as $field) {
        if (empty($params[$field])) {
            error_log("Missing required field: {$field}");
            return null;
        }
    }

    // Prepare JSON payload
    $payload = json_encode($params);
    if ($payload === false) {
        error_log("Failed to encode JSON payload");
        return null;
    }

    // Initialize cURL
    $ch = curl_init($apiUrl);
    if ($ch === false) {
        error_log("Failed to initialize cURL");
        return null;
    }

    // Set cURL options
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_HTTPHEADER => [
            \'Content-Type: application/json\',
            \'X-API-Key: \' . $apiKey,
            \'User-Agent: SRP-Client/1.0\'
        ],
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);

    // Execute request
    $response = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    // Handle cURL errors
    if ($response === false || $error !== \'\') {
        error_log("cURL error: {$error}");
        return null;
    }

    // Handle HTTP errors
    if ($httpCode !== 200) {
        error_log("HTTP error: {$httpCode} - {$response}");
        return null;
    }

    // Decode JSON response
    $data = json_decode($response, true);
    if (!is_array($data) || empty($data[\'ok\'])) {
        error_log("Invalid API response");
        return null;
    }

    return $data;
}

// ==========================================
// Usage Example
// ==========================================

$apiUrl = \'https://api.qvtrk.com/decision.php\';
$apiKey = \'your_api_key_here\'; // Ambil dari Environment Config

// Prepare request parameters
$params = [
    \'click_id\' => $_GET[\'cid\'] ?? uniqid(\'click_\', true),
    \'country_code\' => $_SERVER[\'HTTP_CF_IPCOUNTRY\'] ?? \'XX\',
    \'user_agent\' => $_SERVER[\'HTTP_USER_AGENT\'] ?? \'\',
    \'ip_address\' => $_SERVER[\'REMOTE_ADDR\'] ?? \'0.0.0.0\',
    \'user_lp\' => $_GET[\'lp\'] ?? \'default\'
];

// Get decision from API
$decision = getSrpDecision($apiUrl, $apiKey, $params);

if ($decision === null) {
    // Fallback handling on API failure
    header(\'Location: https://your-fallback-url.com\');
    exit;
}

// Handle routing based on decision
if ($decision[\'decision\'] === \'A\' && !empty($decision[\'target\'])) {
    // Redirect to offer
    header(\'Location: \' . $decision[\'target\']);
    exit;
} else {
    // Fallback
    header(\'Location: \' . $decision[\'target\']);
    exit;
}', ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8'); ?></pre>
                    </div>
                </div>

                <!-- PHP Simple Implementation -->
                <div x-show="lang === 'simple'" x-cloak>
                    <div class="p-2 rounded bg-slate-900 text-slate-200 font-mono text-[10px] overflow-x-auto">
<pre><?= htmlspecialchars('<?php
// Simple implementation untuk quick integration
$apiUrl = "https://api.qvtrk.com/decision.php";
$apiKey = "your_api_key_here";

$data = [
    "click_id" => $_GET["cid"] ?? uniqid(),
    "country_code" => $_SERVER["HTTP_CF_IPCOUNTRY"] ?? "XX",
    "user_agent" => $_SERVER["HTTP_USER_AGENT"],
    "ip_address" => $_SERVER["HTTP_CF_CONNECTING_IP"] ?? $_SERVER["REMOTE_ADDR"]
];

$options = [
    "http" => [
        "header" => [
            "Content-Type: application/json",
            "X-API-Key: " . $apiKey
        ],
        "method" => "POST",
        "content" => json_encode($data),
        "timeout" => 5
    ]
];

$context = stream_context_create($options);
$result = @file_get_contents($apiUrl, false, $context);

if ($result) {
    $response = json_decode($result, true);
    header("Location: " . $response["target"]);
} else {
    header("Location: https://fallback-url.com");
}
exit;', ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8'); ?></pre>
                    </div>
                </div>

                <!-- JavaScript Implementation -->
                <div x-show="lang === 'js'" x-cloak>
                    <div class="p-2 rounded bg-slate-900 text-slate-200 font-mono text-[10px] overflow-x-auto">
<pre><?= htmlspecialchars('// JavaScript/Node.js implementation
const https = require("https");

async function getSrpDecision(clickId, countryCode, userAgent, ipAddress) {
    const data = JSON.stringify({
        click_id: clickId,
        country_code: countryCode,
        user_agent: userAgent,
        ip_address: ipAddress
    });

    const options = {
        hostname: "api.qvtrk.com",
        port: 443,
        path: "/decision.php",
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Content-Length": data.length,
            "X-API-Key": "your_api_key_here"
        },
        timeout: 5000
    };

    return new Promise((resolve, reject) => {
        const req = https.request(options, (res) => {
            let responseData = "";

            res.on("data", (chunk) => {
                responseData += chunk;
            });

            res.on("end", () => {
                try {
                    const response = JSON.parse(responseData);
                    resolve(response);
                } catch (e) {
                    reject(e);
                }
            });
        });

        req.on("error", reject);
        req.on("timeout", () => {
            req.abort();
            reject(new Error("Request timeout"));
        });

        req.write(data);
        req.end();
    });
}

// Usage in Express.js
app.get("/redirect", async (req, res) => {
    try {
        const decision = await getSrpDecision(
            req.query.cid || Date.now().toString(),
            req.headers["cf-ipcountry"] || "XX",
            req.headers["user-agent"],
            req.headers["cf-connecting-ip"] || req.ip
        );

        res.redirect(decision.target);
    } catch (error) {
        res.redirect("https://fallback-url.com");
    }
});', ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8'); ?></pre>
                    </div>
                </div>

                <!-- Python Implementation -->
                <div x-show="lang === 'python'" x-cloak>
                    <div class="p-2 rounded bg-slate-900 text-slate-200 font-mono text-[10px] overflow-x-auto">
<pre><?= htmlspecialchars('# Python implementation using requests
import requests
import json
from flask import Flask, request, redirect

app = Flask(__name__)

def get_srp_decision(click_id, country_code, user_agent, ip_address):
    """Get routing decision from SRP API"""

    api_url = "https://api.qvtrk.com/decision.php"
    api_key = "your_api_key_here"

    headers = {
        "Content-Type": "application/json",
        "X-API-Key": api_key
    }

    payload = {
        "click_id": click_id,
        "country_code": country_code,
        "user_agent": user_agent,
        "ip_address": ip_address
    }

    try:
        response = requests.post(
            api_url,
            headers=headers,
            data=json.dumps(payload),
            timeout=5
        )

        if response.status_code == 200:
            return response.json()
        else:
            return None

    except requests.exceptions.RequestException:
        return None

@app.route("/redirect")
def handle_redirect():
    """Handle redirect with SRP decision"""

    # Get parameters
    click_id = request.args.get("cid", "")
    if not click_id:
        import uuid
        click_id = str(uuid.uuid4())

    country_code = request.headers.get("CF-IPCountry", "XX")
    user_agent = request.headers.get("User-Agent", "")
    ip_address = request.headers.get("CF-Connecting-IP", request.remote_addr)

    # Get decision
    decision = get_srp_decision(click_id, country_code, user_agent, ip_address)

    if decision and "target" in decision:
        return redirect(decision["target"], code=302)
    else:
        return redirect("https://fallback-url.com", code=302)

if __name__ == "__main__":
    app.run()
', ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8'); ?></pre>
                    </div>
                </div>
            </div>
        </div>

        <!-- Best Practices Section -->
        <div class="card p-3">
            <div class="flex items-start gap-2 mb-2.5">
                <svg class="h-4 w-4 text-primary mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
                <div class="flex-1">
                    <h3 class="text-xs font-semibold">Best Practices & Security</h3>
                </div>
            </div>

            <div class="space-y-2 text-[10px]">
                <div class="flex items-start gap-2">
                    <div class="mt-0.5 h-1.5 w-1.5 rounded-full bg-primary flex-shrink-0"></div>
                    <p><strong>Timeout Configuration:</strong> Gunakan timeout 5-10 detik untuk menghindari blocking request terlalu lama</p>
                </div>
                <div class="flex items-start gap-2">
                    <div class="mt-0.5 h-1.5 w-1.5 rounded-full bg-primary flex-shrink-0"></div>
                    <p><strong>Error Handling:</strong> Selalu sediakan fallback URL jika API gagal atau tidak merespons</p>
                </div>
                <div class="flex items-start gap-2">
                    <div class="mt-0.5 h-1.5 w-1.5 rounded-full bg-primary flex-shrink-0"></div>
                    <p><strong>SSL Verification:</strong> Pastikan SSL verification aktif (CURLOPT_SSL_VERIFYPEER = true)</p>
                </div>
                <div class="flex items-start gap-2">
                    <div class="mt-0.5 h-1.5 w-1.5 rounded-full bg-primary flex-shrink-0"></div>
                    <p><strong>IP Address:</strong> Gunakan real client IP, bukan server IP. Jika di balik proxy/CDN, gunakan header seperti HTTP_CF_CONNECTING_IP</p>
                </div>
                <div class="flex items-start gap-2">
                    <div class="mt-0.5 h-1.5 w-1.5 rounded-full bg-primary flex-shrink-0"></div>
                    <p><strong>API Key Security:</strong> Simpan API key di environment variable atau config terpisah, jangan hardcode di kode</p>
                </div>
                <div class="flex items-start gap-2">
                    <div class="mt-0.5 h-1.5 w-1.5 rounded-full bg-primary flex-shrink-0"></div>
                    <p><strong>Logging:</strong> Log semua error untuk debugging, tapi jangan log API key atau data sensitif</p>
                </div>
                <div class="flex items-start gap-2">
                    <div class="mt-0.5 h-1.5 w-1.5 rounded-full bg-primary flex-shrink-0"></div>
                    <p><strong>Rate Limiting:</strong> Implementasi cache sederhana jika perlu untuk mengurangi beban API</p>
                </div>
            </div>
        </div>

        <!-- Common Errors & Troubleshooting -->
        <div class="card p-3">
            <div class="flex items-start gap-2 mb-2.5">
                <svg class="h-4 w-4 text-primary mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="flex-1">
                    <h3 class="text-xs font-semibold">Common Errors & Solutions</h3>
                </div>
            </div>

            <div class="space-y-2">
                <div class="p-2 rounded border border-red-200 bg-red-50">
                    <p class="text-[10px] font-medium text-red-900 mb-1">Error: 401 Unauthorized</p>
                    <p class="text-[10px] text-red-800">
                        <strong>Penyebab:</strong> API key tidak valid atau tidak ada<br>
                        <strong>Solusi:</strong> Periksa header X-API-Key dan pastikan menggunakan API key yang benar dari Environment Config
                    </p>
                </div>

                <div class="p-2 rounded border border-red-200 bg-red-50">
                    <p class="text-[10px] font-medium text-red-900 mb-1">Error: 400 Bad Request</p>
                    <p class="text-[10px] text-red-800">
                        <strong>Penyebab:</strong> Parameter required tidak lengkap atau format salah<br>
                        <strong>Solusi:</strong> Pastikan semua field required terisi dengan format yang benar (country_code 2 huruf, IP valid)
                    </p>
                </div>

                <div class="p-2 rounded border border-red-200 bg-red-50">
                    <p class="text-[10px] font-medium text-red-900 mb-1">Error: 429 Too Many Requests</p>
                    <p class="text-[10px] text-red-800">
                        <strong>Penyebab:</strong> Rate limit exceeded<br>
                        <strong>Solusi:</strong> Implementasi caching atau backoff strategy, maksimal 100 req/menit per IP
                    </p>
                </div>

                <div class="p-2 rounded border border-amber-200 bg-amber-50">
                    <p class="text-[10px] font-medium text-amber-900 mb-1">Warning: Fallback Triggered</p>
                    <p class="text-[10px] text-amber-800">
                        <strong>Kemungkinan:</strong> System OFF, routing rules tidak match, atau traffic auto-muted<br>
                        <strong>Cek:</strong> System status, routing configuration, dan auto-mute settings
                    </p>
                </div>
            </div>
        </div>

        <!-- Integration Checklist -->
        <div class="card p-3">
            <div class="flex items-start gap-2 mb-2.5">
                <svg class="h-4 w-4 text-primary mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                </svg>
                <div class="flex-1">
                    <h3 class="text-xs font-semibold">Integration Checklist</h3>
                </div>
            </div>

            <div class="space-y-2 text-[10px]">
                <div class="space-y-1">
                    <p class="font-medium mb-1">Pre-Launch:</p>
                    <div class="pl-3 space-y-1">
                        <label class="flex items-start gap-2">
                            <input type="checkbox" class="checkbox checkbox-xs mt-0.5">
                            <span>API Key sudah digenerate dan disimpan secure</span>
                        </label>
                        <label class="flex items-start gap-2">
                            <input type="checkbox" class="checkbox checkbox-xs mt-0.5">
                            <span>Routing rules sudah dikonfigurasi dengan benar</span>
                        </label>
                        <label class="flex items-start gap-2">
                            <input type="checkbox" class="checkbox checkbox-xs mt-0.5">
                            <span>Fallback URL sudah disiapkan</span>
                        </label>
                        <label class="flex items-start gap-2">
                            <input type="checkbox" class="checkbox checkbox-xs mt-0.5">
                            <span>Test dengan berbagai country code dan device</span>
                        </label>
                        <label class="flex items-start gap-2">
                            <input type="checkbox" class="checkbox checkbox-xs mt-0.5">
                            <span>Error handling dan logging implemented</span>
                        </label>
                    </div>
                </div>

                <div class="space-y-1">
                    <p class="font-medium mb-1">Post-Launch:</p>
                    <div class="pl-3 space-y-1">
                        <label class="flex items-start gap-2">
                            <input type="checkbox" class="checkbox checkbox-xs mt-0.5">
                            <span>Monitor traffic logs untuk anomali</span>
                        </label>
                        <label class="flex items-start gap-2">
                            <input type="checkbox" class="checkbox checkbox-xs mt-0.5">
                            <span>Cek conversion tracking dan postback</span>
                        </label>
                        <label class="flex items-start gap-2">
                            <input type="checkbox" class="checkbox checkbox-xs mt-0.5">
                            <span>Review performance metrics</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Testing Section -->
        <div class="card p-3">
            <div class="flex items-start gap-2 mb-2.5">
                <svg class="h-4 w-4 text-primary mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="flex-1">
                    <h3 class="text-xs font-semibold">Testing & Debugging</h3>
                </div>
            </div>

            <div class="space-y-2 text-[10px]">
                <div>
                    <p class="font-medium mb-1">Quick Test dengan cURL Command:</p>
                    <div class="p-2 rounded bg-slate-900 text-emerald-400 font-mono text-[10px] overflow-x-auto">
<pre>curl -X POST https://api.qvtrk.com/decision.php \
  -H "Content-Type: application/json" \
  -H "X-API-Key: your_api_key" \
  -d '{
    "click_id": "test123",
    "country_code": "US",
    "user_agent": "Mozilla/5.0",
    "ip_address": "203.0.113.1"
  }'</pre>
                    </div>
                </div>

                <div>
                    <p class="font-medium mb-1">Test dengan Postman:</p>
                    <div class="text-[10px] space-y-1">
                        <p>1. Method: POST</p>
                        <p>2. URL: https://api.qvtrk.com/decision.php</p>
                        <p>3. Headers: X-API-Key: your_api_key</p>
                        <p>4. Body: JSON dengan required fields</p>
                    </div>
                </div>

                <div class="p-2 rounded bg-amber-50 border border-amber-200">
                    <p class="text-amber-900">
                        <strong>💡 Tips:</strong> Gunakan tab "Overview" untuk test decision logic secara interaktif sebelum implementasi
                    </p>
                </div>
            </div>
        </div>

        <!-- Advanced Features -->
        <div class="card p-3">
            <div class="flex items-start gap-2 mb-2.5">
                <svg class="h-4 w-4 text-primary mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                <div class="flex-1">
                    <h3 class="text-xs font-semibold">Advanced Features</h3>
                </div>
            </div>

            <div class="space-y-2 text-[10px]">
                <div class="space-y-1">
                    <p class="font-medium">Postback Integration:</p>
                    <p class="text-muted-foreground">Setelah mendapat decision, sistem akan otomatis track conversion melalui postback URL yang dikonfigurasi di affiliate network.</p>
                </div>

                <div class="space-y-1">
                    <p class="font-medium">Auto-Mute Protection:</p>
                    <p class="text-muted-foreground">Jika traffic dari country tertentu menghasilkan conversion rendah, sistem otomatis route ke fallback untuk melindungi quality score.</p>
                </div>

                <div class="space-y-1">
                    <p class="font-medium">VPN Detection:</p>
                    <p class="text-muted-foreground">API otomatis detect VPN/proxy traffic dan dapat dikonfigurasi untuk block atau route ke fallback.</p>
                </div>

                <div class="space-y-1">
                    <p class="font-medium">Multiple Routing Rules:</p>
                    <p class="text-muted-foreground">Support complex routing berdasarkan kombinasi country, device, browser, OS, dan custom parameters.</p>
                </div>
            </div>
        </div>

        <!-- Support Section -->
        <div class="rounded-[0.3rem] border border-primary/30 bg-primary/5 p-2.5">
            <div class="flex items-start gap-2">
                <svg class="h-4 w-4 text-primary mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <p class="text-[10px] font-medium text-foreground">Butuh Bantuan?</p>
                    <p class="text-[10px] text-muted-foreground mt-0.5">
                        Pastikan system status dalam kondisi ON dan konfigurasi routing sudah benar.
                        Gunakan tab Statistics dan Traffic Logs untuk monitoring real-time.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
