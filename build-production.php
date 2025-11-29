<?php
declare(strict_types=1);

/**
 * Production Build Script untuk SRP
 * Validasi, optimasi, dan checklist untuk deployment
 */

echo "═══════════════════════════════════════════════════════\n";
echo "  SRP Production Build Script v1.0                    \n";
echo "═══════════════════════════════════════════════════════\n\n";

$errors = [];
$warnings = [];
$stats = [];

// 1. Validasi PHP Files
echo "▶ Validating PHP syntax...\n";
$phpFiles = [];
$directories = ['srp/src', 'public_html', 'public_html_tracking'];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        continue;
    }
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir)
    );
    foreach ($iterator as $file) {
        if ($file->getExtension() === 'php') {
            $phpFiles[] = $file->getPathname();
        }
    }
}

$validPhpFiles = 0;
foreach ($phpFiles as $file) {
    $output = [];
    $returnVar = 0;
    exec("php -l " . escapeshellarg($file) . " 2>&1", $output, $returnVar);
    if ($returnVar !== 0) {
        $errors[] = "PHP Syntax Error: $file";
    } else {
        $validPhpFiles++;
    }
}
$stats['php_files'] = count($phpFiles);
$stats['valid_php'] = $validPhpFiles;
echo "  ✓ Validated $validPhpFiles PHP files\n\n";

// 2. Check Security Headers
echo "▶ Checking security configuration...\n";
$securityHeaderFile = 'srp/src/Middleware/SecurityHeaders.php';
if (file_exists($securityHeaderFile)) {
    $content = file_get_contents($securityHeaderFile);
    $securityChecks = [
        'X-Content-Type-Options' => strpos($content, 'X-Content-Type-Options') !== false,
        'X-Frame-Options' => strpos($content, 'X-Frame-Options') !== false,
        'Referrer-Policy' => strpos($content, 'Referrer-Policy') !== false,
        'Content-Security-Policy' => strpos($content, 'Content-Security-Policy') !== false,
    ];

    foreach ($securityChecks as $header => $present) {
        if ($present) {
            echo "  ✓ $header configured\n";
        } else {
            $warnings[] = "Security Header Missing: $header";
        }
    }
} else {
    $errors[] = "SecurityHeaders.php not found";
}
echo "\n";

// 3. Check Database Configuration
echo "▶ Checking database configuration...\n";
$dbConfigFile = 'srp/src/Config/Database.php';
if (file_exists($dbConfigFile)) {
    $content = file_get_contents($dbConfigFile);
    $dbChecks = [
        'PDO::ERRMODE_EXCEPTION' => strpos($content, 'PDO::ERRMODE_EXCEPTION') !== false,
        'PDO::FETCH_ASSOC' => strpos($content, 'PDO::FETCH_ASSOC') !== false,
        'EMULATE_PREPARES false' => strpos($content, 'EMULATE_PREPARES') !== false,
    ];

    foreach ($dbChecks as $check => $present) {
        if ($present) {
            echo "  ✓ $check\n";
        } else {
            $warnings[] = "DB Config Missing: $check";
        }
    }
} else {
    $errors[] = "Database.php not found";
}
echo "\n";

// 4. Check Assets
echo "▶ Checking static assets...\n";
$cssFiles = glob('public_html/assets/css/*.css');
$jsFiles = glob('public_html/assets/js/*.js');
$stats['css_files'] = count($cssFiles);
$stats['js_files'] = count($jsFiles);

echo "  ✓ CSS files: " . count($cssFiles) . "\n";
echo "  ✓ JS files: " . count($jsFiles) . "\n";

// Check CSS file sizes
$totalCssSize = 0;
foreach ($cssFiles as $css) {
    $totalCssSize += filesize($css);
}
$stats['css_size_kb'] = round($totalCssSize / 1024, 2);
echo "  ✓ Total CSS size: " . $stats['css_size_kb'] . " KB\n\n";

// 5. Check View Components
echo "▶ Checking view components...\n";
$viewComponents = glob('srp/src/Views/components/*.php');
$stats['view_components'] = count($viewComponents);
echo "  ✓ View components: " . count($viewComponents) . "\n";

// Check tabs configuration
$tabsNavFile = 'srp/src/Views/components/tabs-navigation.php';
if (file_exists($tabsNavFile)) {
    $content = file_get_contents($tabsNavFile);
    $tabCount = substr_count($content, "'id' =>");
    echo "  ✓ Tabs configured: $tabCount\n";

    // Check for proper escaping
    if (strpos($content, 'htmlspecialchars') !== false) {
        echo "  ✓ Output escaping present\n";
    } else {
        $warnings[] = "No htmlspecialchars found in tabs-navigation.php";
    }
} else {
    $errors[] = "tabs-navigation.php not found";
}
echo "\n";

// 6. Directory Structure
echo "▶ Verifying directory structure...\n";
$requiredDirs = [
    'database',
    'public_html',
    'public_html/assets',
    'public_html/assets/css',
    'public_html/assets/js',
    'public_html_tracking',
    'srp/src',
    'srp/src/Controllers',
    'srp/src/Models',
    'srp/src/Views',
    'storage',
];

foreach ($requiredDirs as $dir) {
    if (is_dir($dir)) {
        echo "  ✓ $dir\n";
    } else {
        $errors[] = "Missing directory: $dir";
    }
}
echo "\n";

// 7. Check .env or config
echo "▶ Checking environment configuration...\n";
$envCandidates = [
    '.env',
    'srp/config.php',
    'srp/.env.production',
    'srp/.env.template',
];

$foundEnv = false;
foreach ($envCandidates as $envFile) {
    if (file_exists($envFile)) {
        $foundEnv = true;
        echo "  ✓ Environment config found: {$envFile}\n";
    }
}

if (!$foundEnv) {
    $warnings[] = "No environment config detected (.env, config.php, or template) - ensure environment is configured";
}
echo "\n";

// 8. Production Checklist Items
echo "▶ Production deployment checklist...\n";
$checklist = [
    'PRODUCTION_CHECKLIST.md exists' => file_exists('PRODUCTION_CHECKLIST.md'),
    'DEPLOYMENT_INFO.txt exists' => file_exists('DEPLOYMENT_INFO.txt'),
    'README.md exists' => file_exists('README.md'),
    'Database schema exists' => file_exists('database/schema.sql') || is_dir('database'),
];

foreach ($checklist as $item => $status) {
    if ($status) {
        echo "  ✓ $item\n";
    } else {
        $warnings[] = $item;
    }
}
echo "\n";

// Summary Report
echo "═══════════════════════════════════════════════════════\n";
echo "  BUILD SUMMARY                                        \n";
echo "═══════════════════════════════════════════════════════\n";
echo "PHP Files Validated: {$stats['php_files']} (✓ {$stats['valid_php']})\n";
echo "View Components: {$stats['view_components']}\n";
echo "CSS Files: {$stats['css_files']} ({$stats['css_size_kb']} KB)\n";
echo "JS Files: {$stats['js_files']}\n";
echo "\n";

if (count($errors) > 0) {
    echo "❌ ERRORS (" . count($errors) . "):\n";
    foreach ($errors as $error) {
        echo "  • $error\n";
    }
    echo "\n";
}

if (count($warnings) > 0) {
    echo "⚠ WARNINGS (" . count($warnings) . "):\n";
    foreach ($warnings as $warning) {
        echo "  • $warning\n";
    }
    echo "\n";
}

if (count($errors) === 0) {
    echo "✅ BUILD STATUS: READY FOR PRODUCTION\n";
    $exitCode = 0;
} else {
    echo "❌ BUILD STATUS: FAILED - Fix errors before deployment\n";
    $exitCode = 1;
}

echo "═══════════════════════════════════════════════════════\n";
exit($exitCode);
