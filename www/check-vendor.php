<?php
/**
 * Diagnostic chemin vendor
 * URL: https://www.spherevoices.com/check-vendor.php?token=spherevoices2026
 */

$security_token = 'spherevoices2026';
$provided_token = $_GET['token'] ?? '';

if ($provided_token !== $security_token) {
    die('Token requis');
}

header('Content-Type: text/plain; charset=utf-8');

echo "=== DIAGNOSTIC VENDOR ===\n\n";

echo "Script actuel: " . __FILE__ . "\n";
echo "__DIR__: " . __DIR__ . "\n";
echo "dirname(__DIR__): " . dirname(__DIR__) . "\n\n";

echo "=== RECHERCHE DE VENDOR ===\n\n";

$vendor_paths = [
    '__DIR__ . "/vendor"' => __DIR__ . '/vendor',
    '__DIR__ . "/../vendor"' => __DIR__ . '/../vendor',
    'dirname(__DIR__) . "/vendor"' => dirname(__DIR__) . '/vendor',
    '/home/spheree/vendor' => '/home/spheree/vendor',
    '/home/spheree/www/vendor' => '/home/spheree/www/vendor',
];

foreach ($vendor_paths as $label => $path) {
    echo "$label:\n";
    echo "  Chemin: $path\n";
    
    if (file_exists($path)) {
        if (is_dir($path)) {
            echo "  ✅ EXISTE (répertoire)\n";
            
            // Compter les sous-dossiers
            $items = @scandir($path);
            if ($items) {
                $count = count($items) - 2; // -2 pour . et ..
                echo "  Contient: $count éléments\n";
                
                // Vérifier autoload.php
                if (file_exists($path . '/autoload.php')) {
                    echo "  ✅ autoload.php présent\n";
                } else {
                    echo "  ❌ autoload.php ABSENT\n";
                }
                
                // Vérifier composer
                if (is_dir($path . '/composer')) {
                    echo "  ✅ composer/ présent\n";
                } else {
                    echo "  ❌ composer/ ABSENT\n";
                }
            }
        } else {
            echo "  ⚠️ EXISTE mais pas un répertoire\n";
        }
    } else {
        echo "  ❌ N'EXISTE PAS\n";
    }
    echo "\n";
}

echo "\n=== STRUCTURE FILESYSTEM ===\n\n";

// Lister le parent de __DIR__
$parent = dirname(__DIR__);
echo "Contenu de $parent :\n";
$items = @scandir($parent);
if ($items) {
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        
        $full_path = $parent . '/' . $item;
        if (is_dir($full_path)) {
            echo "  [DIR]  $item/\n";
        } else {
            $size = filesize($full_path);
            echo "  [FILE] $item (" . number_format($size) . " bytes)\n";
        }
    }
} else {
    echo "  ❌ Impossible de lister\n";
}

echo "\n=== TEST AUTOLOAD ===\n\n";

$autoload_path = __DIR__ . '/autoload.php';
echo "Fichier autoload.php local:\n";
echo "  Chemin: $autoload_path\n";

if (file_exists($autoload_path)) {
    echo "  ✅ Existe\n";
    $content = file_get_contents($autoload_path);
    echo "  Contenu:\n";
    echo "---\n$content\n---\n";
    
    $expected_vendor = __DIR__ . '/../vendor/autoload.php';
    echo "\n  Cherche vendor à: $expected_vendor\n";
    if (file_exists($expected_vendor)) {
        echo "  ✅ vendor/autoload.php TROUVÉ !\n";
    } else {
        echo "  ❌ vendor/autoload.php INTROUVABLE !\n";
    }
} else {
    echo "  ❌ autoload.php n'existe pas\n";
}

echo "\n=== SOLUTION ===\n\n";

if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    echo "❌ LE PROBLÈME EST CONFIRMÉ !\n\n";
    echo "vendor/ n'est pas au bon endroit.\n";
    echo "Il devrait être à: " . dirname(__DIR__) . "/vendor/\n\n";
    echo "Solutions possibles:\n";
    echo "1. Créer un symlink: ln -s /home/spheree/vendor /home/spheree/www/../vendor\n";
    echo "2. Copier vendor/ au bon endroit\n";
    echo "3. Modifier le workflow de déploiement\n";
} else {
    echo "✅ vendor/ est au bon endroit !\n";
}

