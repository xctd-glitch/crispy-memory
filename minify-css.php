<?php
declare(strict_types=1);

/**
 * Simple CSS Minifier untuk Production Build
 */

function minifyCSS(string $css): string
{
    // Remove comments
    $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);

    // Remove whitespace
    $css = (string) preg_replace('/\s+/', ' ', $css);

    // Remove spaces around selectors and declarations
    $css = (string) preg_replace('/\s*([{}:;,])\s*/', '$1', $css);

    // Remove trailing semicolons
    $css = (string) preg_replace('/;}/', '}', $css);

    return trim($css);
}

echo "═══════════════════════════════════════════════════════\n";
echo "  CSS Minification for Production                     \n";
echo "═══════════════════════════════════════════════════════\n\n";

$cssFiles = [
    'public_html/assets/css/design-tokens.css',
    'public_html/assets/css/components.css',
];

$totalOriginal = 0;
$totalMinified = 0;

foreach ($cssFiles as $cssFile) {
    if (!file_exists($cssFile)) {
        echo "✗ File not found: $cssFile\n";
        continue;
    }

    $original = file_get_contents($cssFile);
    $originalSize = strlen($original);
    $totalOriginal += $originalSize;

    $minified = minifyCSS($original);
    $minifiedSize = strlen($minified);
    $totalMinified += $minifiedSize;

    $savings = round((1 - $minifiedSize / $originalSize) * 100, 2);

    $outputFile = str_replace('.css', '.min.css', $cssFile);
    file_put_contents($outputFile, $minified);

    echo "✓ " . basename($cssFile) . "\n";
    echo "  Original:  " . number_format($originalSize) . " bytes\n";
    echo "  Minified:  " . number_format($minifiedSize) . " bytes\n";
    echo "  Savings:   $savings%\n";
    echo "  Output:    " . basename($outputFile) . "\n\n";
}

echo "═══════════════════════════════════════════════════════\n";
echo "Total original: " . number_format($totalOriginal) . " bytes\n";
echo "Total minified: " . number_format($totalMinified) . " bytes\n";
echo "Total savings:  " . round((1 - $totalMinified / $totalOriginal) * 100, 2) . "%\n";
echo "═══════════════════════════════════════════════════════\n\n";

echo "✅ CSS minification complete!\n";
echo "ℹ To use minified CSS in production, update HTML to load .min.css files\n";
