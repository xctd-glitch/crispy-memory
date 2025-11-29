<?php
declare(strict_types=1);

/**
 * Production Clean Script untuk SRP
 * Menghapus file development, test, dan temporary files
 */

echo "═══════════════════════════════════════════════════════\n";
echo "  SRP Production Clean Script v1.0                    \n";
echo "═══════════════════════════════════════════════════════\n\n";

$cleaned = [];
$skipped = [];

// File patterns yang harus dihapus di production
$cleanPatterns = [
    // Test files
    'test-*.php',
    'test-*.html',
    '*-test.php',

    // Development files
    '.phpunit.cache',
    'phpunit.xml',
    '.phpcs.xml',

    // Editor files
    '.vscode',
    '.idea',
    '*.swp',
    '*.swo',
    '*~',

    // OS files
    '.DS_Store',
    'Thumbs.db',
    'desktop.ini',

    // Git (optional - uncomment if deploying without git)
    // '.git',
    // '.gitignore',
    // '.gitattributes',
];

// Directories yang harus dibersihkan
$cleanDirectories = [
    'storage/cache/*.cache',
    'storage/logs/debug-*.log',
];

echo "▶ Cleaning test files...\n";
foreach ($cleanPatterns as $pattern) {
    $files = glob($pattern, GLOB_BRACE);
    foreach ($files as $file) {
        if (is_file($file)) {
            if (unlink($file)) {
                $cleaned[] = $file;
                echo "  ✓ Removed: $file\n";
            } else {
                $skipped[] = $file;
                echo "  ✗ Failed: $file\n";
            }
        } elseif (is_dir($file)) {
            // Untuk direktori, hanya laporkan tanpa hapus (aman)
            echo "  ⚠ Directory found (manual review): $file\n";
            $skipped[] = $file;
        }
    }
}

echo "\n▶ Cleaning temporary files in storage...\n";
if (is_dir('storage')) {
    // Clean cache files
    $cacheFiles = glob('storage/cache/*.cache');
    foreach ($cacheFiles as $cache) {
        if (unlink($cache)) {
            $cleaned[] = $cache;
            echo "  ✓ Removed cache: $cache\n";
        }
    }

    // Clean old debug logs (keep error logs)
    $debugLogs = glob('storage/logs/debug-*.log');
    foreach ($debugLogs as $log) {
        if (unlink($log)) {
            $cleaned[] = $log;
            echo "  ✓ Removed log: $log\n";
        }
    }
}

echo "\n▶ Checking for sensitive files...\n";
$sensitivePatterns = [
    '.env.backup',
    '.env.local',
    'config.local.php',
    '*.key',
    '*.pem',
    'credentials.json',
];

$foundSensitive = [];
foreach ($sensitivePatterns as $pattern) {
    $files = glob($pattern);
    foreach ($files as $file) {
        $foundSensitive[] = $file;
        echo "  ⚠ SENSITIVE FILE FOUND: $file (manual review required)\n";
    }
}

echo "\n▶ Optimizing for production...\n";

// Set proper permissions (only show recommendations)
echo "  ℹ Recommended file permissions:\n";
echo "    - PHP files: 644 (chmod 644 *.php)\n";
echo "    - Directories: 755 (chmod 755 directories)\n";
echo "    - Storage directory: 775 (writable by web server)\n";
echo "    - Config files: 600 (chmod 600 .env config.php)\n";

// Check if error_reporting is production-ready
if (file_exists('srp/src/bootstrap.php')) {
    $bootstrap = file_get_contents('srp/src/bootstrap.php');
    if (strpos($bootstrap, 'error_reporting(0)') !== false ||
        strpos($bootstrap, "display_errors', '0'") !== false ||
        strpos($bootstrap, "display_errors', 0") !== false) {
        echo "  ✓ Error reporting configured for production\n";
    } else {
        echo "  ⚠ Warning: Check error_reporting in bootstrap.php\n";
    }
}

echo "\n═══════════════════════════════════════════════════════\n";
echo "  CLEAN SUMMARY                                        \n";
echo "═══════════════════════════════════════════════════════\n";
echo "Files cleaned: " . count($cleaned) . "\n";
echo "Files skipped: " . count($skipped) . "\n";
echo "Sensitive files found: " . count($foundSensitive) . "\n";
echo "\n";

if (count($foundSensitive) > 0) {
    echo "⚠ WARNING: Review sensitive files before deployment!\n";
} else {
    echo "✅ No sensitive files found in root directory\n";
}

echo "\n▶ Production deployment checklist:\n";
echo "  [ ] Update .env with production credentials\n";
echo "  [ ] Set error_reporting(0) and display_errors=0\n";
echo "  [ ] Enable HTTPS and set Secure cookie flags\n";
echo "  [ ] Configure CSP nonce generation\n";
echo "  [ ] Set up database backup schedule\n";
echo "  [ ] Configure rate limiting for public endpoints\n";
echo "  [ ] Review PRODUCTION_CHECKLIST.md\n";
echo "  [ ] Test all functionality on staging first\n";

echo "\n═══════════════════════════════════════════════════════\n";
echo "✅ CLEAN COMPLETE - Review output before deployment\n";
echo "═══════════════════════════════════════════════════════\n";
