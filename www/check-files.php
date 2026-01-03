<?php
/**
 * Vérifie que tous les fichiers critiques sont présents
 * URL: https://www.spherevoices.com/check-files.php?token=spherevoices2026
 */

$security_token = 'spherevoices2026';
$provided_token = $_GET['token'] ?? '';

if ($provided_token !== $security_token) {
    die('Token requis');
}

header('Content-Type: text/plain; charset=utf-8');

echo "=== VÉRIFICATION DES FICHIERS CRITIQUES ===\n\n";

$files_to_check = [
    // Core Drupal
    'autoload.php',
    'index.php',
    'core/lib/Drupal.php',
    
    // Modules
    'modules/README.txt',
    
    // Sites
    'sites/default/settings.php',
    'sites/default/files',
    
    // Thème
    'themes/custom/spherevoices_theme/spherevoices_theme.info.yml',
    'themes/custom/spherevoices_theme/spherevoices_theme.theme',
    'themes/custom/spherevoices_theme/spherevoices_theme.libraries.yml',
    
    // Templates critiques
    'themes/custom/spherevoices_theme/templates/layout/page.html.twig',
    'themes/custom/spherevoices_theme/templates/layout/page--front.html.twig',
    
    // CSS
    'themes/custom/spherevoices_theme/css/layout.css',
    'themes/custom/spherevoices_theme/css/components.css',
    'themes/custom/spherevoices_theme/css/gallery.css',
    
    // Vendor
    'vendor/autoload.php',
    'vendor/composer/autoload_real.php',
];

echo "Fichiers à vérifier: " . count($files_to_check) . "\n\n";

$missing = [];
$found = [];

foreach ($files_to_check as $file) {
    $full_path = __DIR__ . '/' . $file;
    if (file_exists($full_path)) {
        $size = is_file($full_path) ? filesize($full_path) : 'dir';
        $mtime = filemtime($full_path);
        $found[] = [
            'file' => $file,
            'size' => $size,
            'date' => date('Y-m-d H:i:s', $mtime),
        ];
    } else {
        $missing[] = $file;
    }
}

echo "=== FICHIERS PRÉSENTS (" . count($found) . ") ===\n\n";
foreach ($found as $f) {
    $size = is_numeric($f['size']) ? number_format($f['size']) . ' bytes' : $f['size'];
    echo sprintf("✅ %-70s %20s %s\n", $f['file'], $size, $f['date']);
}

if (!empty($missing)) {
    echo "\n\n=== FICHIERS MANQUANTS (" . count($missing) . ") ===\n\n";
    foreach ($missing as $m) {
        echo "❌ $m\n";
    }
}

echo "\n\n=== STRUCTURE DU THÈME ===\n\n";

$theme_dir = __DIR__ . '/themes/custom/spherevoices_theme';

if (!is_dir($theme_dir)) {
    echo "❌ Répertoire thème inexistant !\n";
} else {
    echo "✅ Répertoire thème: $theme_dir\n\n";
    
    // Lister tous les fichiers du thème
    function listDir($dir, $prefix = '') {
        $items = @scandir($dir);
        if (!$items) return;
        
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            
            $path = $dir . '/' . $item;
            $rel = str_replace(__DIR__ . '/themes/custom/spherevoices_theme/', '', $path);
            
            if (is_dir($path)) {
                echo "$prefix[DIR]  $rel/\n";
                if (substr_count($rel, '/') < 3) { // Limiter la profondeur
                    listDir($path, $prefix . '  ');
                }
            } else {
                $size = filesize($path);
                echo sprintf("%s[FILE] %-60s %10s\n", $prefix, $rel, number_format($size) . ' b');
            }
        }
    }
    
    listDir($theme_dir);
}

echo "\n\n=== CONFIGURATION PHP ===\n\n";

echo "PHP Version: " . phpversion() . "\n";
echo "Drupal root: " . __DIR__ . "\n";
echo "Document root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "Script filename: " . $_SERVER['SCRIPT_FILENAME'] . "\n";

echo "\n\n=== TEST CHARGEMENT AUTOLOAD ===\n\n";

$autoload_path = __DIR__ . '/autoload.php';
if (file_exists($autoload_path)) {
    echo "✅ autoload.php existe\n";
    
    try {
        require_once $autoload_path;
        echo "✅ autoload.php chargé sans erreur\n";
        
        // Vérifier que les classes Drupal existent
        if (class_exists('Drupal')) {
            echo "✅ Classe Drupal disponible\n";
        } else {
            echo "❌ Classe Drupal INTROUVABLE\n";
        }
        
        if (class_exists('Symfony\Component\HttpFoundation\Request')) {
            echo "✅ Classe Symfony Request disponible\n";
        } else {
            echo "❌ Classe Symfony Request INTROUVABLE\n";
        }
        
    } catch (\Exception $e) {
        echo "❌ Erreur au chargement: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ autoload.php INEXISTANT\n";
}

echo "\n\n=== FIN DE LA VÉRIFICATION ===\n";

