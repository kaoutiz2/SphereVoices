<?php
/**
 * Script de diagnostic pour erreur 500
 * Accédez à: https://www.spherevoices.com/check-500.php
 * SUPPRIMEZ ce fichier après diagnostic !
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Diagnostic Erreur 500 - SphereVoices</h1>";
echo "<pre>";

$root = dirname(dirname(__FILE__));
$wwwRoot = $root . '/www';
$vendorDir = $root . '/vendor';
$autoloader = $vendorDir . '/autoload.php';
$settingsFile = $wwwRoot . '/sites/default/settings.php';
$defaultSettings = $wwwRoot . '/sites/default/default.settings.php';
$filesDir = $wwwRoot . '/sites/default/files';
$indexFile = $wwwRoot . '/index.php';

echo "=== 1. Vérification PHP ===\n";
$phpVersion = phpversion();
echo "Version PHP: $phpVersion\n";
if (version_compare($phpVersion, '8.1.0', '<')) {
    echo "❌ ERREUR: PHP 8.1+ requis! Version actuelle: $phpVersion\n";
    echo "→ Changez PHP dans OVH: Hébergement → Configuration → Version PHP → PHP 8.1 ou 8.2\n\n";
} else {
    echo "✅ OK\n\n";
}

echo "=== 2. Vérification vendor/ ===\n";
if (is_dir($vendorDir)) {
    echo "✅ Dossier vendor/ existe: $vendorDir\n";
    if (file_exists($autoloader)) {
        echo "✅ autoload.php existe\n";
    } else {
        echo "❌ ERREUR: autoload.php manquant dans vendor/\n";
        echo "→ Le dossier vendor/ est incomplet. Réinstallez avec: composer install --no-dev --optimize-autoloader\n\n";
    }
} else {
    echo "❌ ERREUR: Dossier vendor/ manquant à: $vendorDir\n";
    echo "→ Installez-le via SSH: composer install --no-dev --optimize-autoloader\n";
    echo "→ OU uploadez le dossier vendor/ depuis votre machine locale\n\n";
}

echo "=== 3. Vérification settings.php ===\n";
if (file_exists($settingsFile)) {
    echo "✅ settings.php existe\n";
    if (is_readable($settingsFile)) {
        echo "✅ settings.php est lisible\n";
        $content = file_get_contents($settingsFile);
        if (strpos($content, '$databases') !== false) {
            echo "✅ Configuration base de données trouvée dans settings.php\n";
        } else {
            echo "⚠️  ATTENTION: Configuration base de données non trouvée dans settings.php\n";
        }
    } else {
        echo "❌ ERREUR: settings.php n'est pas lisible (permissions?)\n";
    }
} else {
    echo "❌ ERREUR: settings.php manquant\n";
    if (file_exists($defaultSettings)) {
        echo "→ default.settings.php existe, vous pouvez créer settings.php\n";
        echo "→ Exécutez: https://www.spherevoices.com/install-ovh.php\n";
    } else {
        echo "❌ ERREUR CRITIQUE: default.settings.php aussi manquant!\n";
    }
}
echo "\n";

echo "=== 4. Vérification dossier files/ ===\n";
if (is_dir($filesDir)) {
    echo "✅ Dossier files/ existe\n";
    if (is_writable($filesDir)) {
        echo "✅ Dossier files/ est accessible en écriture\n";
    } else {
        echo "⚠️  ATTENTION: Dossier files/ n'est pas accessible en écriture\n";
        echo "→ Changez les permissions: chmod 777 $filesDir\n";
    }
} else {
    echo "❌ ERREUR: Dossier files/ manquant\n";
    echo "→ Créez-le: mkdir -p $filesDir && chmod 777 $filesDir\n";
    echo "→ OU exécutez: https://www.spherevoices.com/install-ovh.php\n";
}
echo "\n";

echo "=== 5. Vérification index.php ===\n";
if (file_exists($indexFile)) {
    echo "✅ index.php existe\n";
    if (is_readable($indexFile)) {
        echo "✅ index.php est lisible\n";
    } else {
        echo "❌ ERREUR: index.php n'est pas lisible\n";
    }
} else {
    echo "❌ ERREUR CRITIQUE: index.php manquant!\n";
}
echo "\n";

echo "=== 6. Test autoloader ===\n";
if (file_exists($autoloader)) {
    try {
        require_once $autoloader;
        echo "✅ Autoloader chargé avec succès\n";
    } catch (Exception $e) {
        echo "❌ ERREUR lors du chargement de l'autoloader: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ ERREUR: autoload.php manquant\n";
}
echo "\n";

echo "=== 7. Test connexion base de données ===\n";
if (file_exists($settingsFile)) {
    try {
        require_once $settingsFile;
        if (isset($databases['default']['default'])) {
            $db = $databases['default']['default'];
            $dsn = "mysql:host={$db['host']};dbname={$db['database']};charset=utf8mb4";
            $pdo = new PDO($dsn, $db['username'], $db['password']);
            echo "✅ Connexion base de données réussie\n";
        } else {
            echo "❌ ERREUR: Configuration base de données non trouvée dans settings.php\n";
        }
    } catch (Exception $e) {
        echo "❌ ERREUR connexion base de données: " . $e->getMessage() . "\n";
    }
} else {
    echo "⚠️  Impossible de tester: settings.php manquant\n";
}
echo "\n";

echo "=== 8. Vérification structure ===\n";
$requiredDirs = [
    'www/core' => $wwwRoot . '/core',
    'www/modules' => $wwwRoot . '/modules',
    'www/themes' => $wwwRoot . '/themes',
];
foreach ($requiredDirs as $name => $path) {
    if (is_dir($path)) {
        echo "✅ $name existe\n";
    } else {
        echo "❌ ERREUR: $name manquant\n";
    }
}
echo "\n";

echo "=== RÉSUMÉ ===\n";
echo "Si vous voyez des ❌ ci-dessus, corrigez ces problèmes.\n";
echo "Les problèmes les plus courants:\n";
echo "1. PHP version < 8.1 → Changez dans OVH\n";
echo "2. vendor/ manquant → Installez avec composer install\n";
echo "3. settings.php manquant → Exécutez install-ovh.php\n";
echo "4. files/ manquant ou permissions → Créez et chmod 777\n";
echo "\n";
echo "⚠️  SUPPRIMEZ CE FICHIER APRÈS DIAGNOSTIC !\n";

echo "</pre>";
?>
