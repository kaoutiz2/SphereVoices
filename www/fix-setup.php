<?php
/**
 * Script de configuration automatique pour OVH
 * À SUPPRIMER après utilisation !
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Configuration automatique SphereVoices</h1>";
echo "<pre>";

$root = dirname(__DIR__);
$wwwRoot = $root . '/www';
$defaultSettings = $wwwRoot . '/sites/default/default.settings.php';
$settingsFile = $wwwRoot . '/sites/default/settings.php';
$envFile = $root . '/.env.production';
$filesDir = $wwwRoot . '/sites/default/files';

// 1. Vérifier PHP version
echo "=== Vérification PHP ===\n";
$phpVersion = phpversion();
echo "Version PHP: $phpVersion\n";
if (version_compare($phpVersion, '8.1.0', '<')) {
    echo "⚠️ ERREUR: PHP 8.1+ requis, vous avez PHP $phpVersion\n";
    echo "→ Changez la version PHP dans OVH: Hébergement → Configuration → Version PHP\n";
    echo "\n";
} else {
    echo "✅ Version PHP OK\n\n";
}

// 2. Créer settings.php
echo "=== Création settings.php ===\n";
if (file_exists($settingsFile)) {
    echo "settings.php existe déjà\n";
} else {
    if (!file_exists($defaultSettings)) {
        echo "❌ ERREUR: default.settings.php non trouvé\n";
    } else {
        // Copier default.settings.php vers settings.php
        $content = file_get_contents($defaultSettings);
        
        // Ajouter le chargement de .env.production après la ligne $databases = [];
        $envLoader = <<<'PHP'

// Load environment variables from .env.production
if (file_exists(__DIR__ . '/../../../.env.production')) {
    $envFile = __DIR__ . '/../../../.env.production';
    $envVars = [];
    if (is_readable($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0) continue;
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $envVars[trim($key)] = trim($value);
            }
        }
        
        // Configure database from .env.production
        if (!empty($envVars['DB_NAME']) && !empty($envVars['DB_USER'])) {
            $databases['default']['default'] = [
                'database' => $envVars['DB_NAME'],
                'username' => $envVars['DB_USER'],
                'password' => $envVars['DB_PASSWORD'] ?? '',
                'host' => $envVars['DB_HOST'] ?? 'localhost',
                'port' => $envVars['DB_PORT'] ?? '3306',
                'driver' => $envVars['DB_DRIVER'] ?? 'mysql',
                'prefix' => $envVars['DB_PREFIX'] ?? '',
                'collation' => $envVars['DB_COLLATION'] ?? 'utf8mb4_general_ci',
            ];
        }
    }
}

PHP;
        
        // Insérer après $databases = [];
        $content = str_replace('$databases = [];', '$databases = [];' . $envLoader, $content);
        
        if (file_put_contents($settingsFile, $content)) {
            chmod($settingsFile, 0644);
            echo "✅ settings.php créé avec succès\n";
        } else {
            echo "❌ ERREUR: Impossible de créer settings.php\n";
            echo "Vérifiez les permissions sur sites/default/\n";
        }
    }
}
echo "\n";

// 3. Créer le dossier files/
echo "=== Création dossier files/ ===\n";
if (is_dir($filesDir)) {
    echo "files/ existe déjà\n";
} else {
    if (mkdir($filesDir, 0777, true)) {
        chmod($filesDir, 0777);
        echo "✅ Dossier files/ créé avec permissions 777\n";
    } else {
        echo "❌ ERREUR: Impossible de créer files/\n";
        echo "Créez-le manuellement via FTP avec permissions 777\n";
    }
}
echo "\n";

// 4. Vérifier vendor/
echo "=== Vérification vendor/ ===\n";
$vendorDir = $root . '/vendor';
if (is_dir($vendorDir)) {
    echo "✅ vendor/ existe\n";
    if (file_exists($vendorDir . '/autoload.php')) {
        echo "✅ autoload.php existe\n";
    } else {
        echo "⚠️ autoload.php manquant dans vendor/\n";
        echo "→ Exécutez: composer install --no-dev --optimize-autoloader\n";
    }
} else {
    echo "❌ vendor/ n'existe pas\n";
    echo "→ Le dossier vendor/ doit être installé sur le serveur\n";
    echo "→ Options:\n";
    echo "  1. Via SSH: composer install --no-dev --optimize-autoloader\n";
    echo "  2. Ou uploadez le dossier vendor/ depuis votre machine locale\n";
}
echo "\n";

// 5. Vérifier .env.production
echo "=== Vérification .env.production ===\n";
if (file_exists($envFile)) {
    echo "✅ .env.production existe\n";
    if (is_readable($envFile)) {
        $envContent = file_get_contents($envFile);
        if (strpos($envContent, 'DB_NAME') !== false && strpos($envContent, 'DB_USER') !== false) {
            echo "✅ Contient DB_NAME et DB_USER\n";
        } else {
            echo "⚠️ Vérifiez que .env.production contient DB_NAME et DB_USER\n";
        }
    } else {
        echo "⚠️ .env.production n'est pas lisible\n";
    }
} else {
    echo "❌ .env.production n'existe pas\n";
    echo "→ Créez-le à la racine avec vos paramètres de base de données\n";
}
echo "\n";

// 6. Résumé
echo "=== RÉSUMÉ ===\n";
echo "Actions à faire:\n";
echo "1. ⚠️ CHANGEZ PHP dans OVH vers 8.1+ (CRITIQUE!)\n";
echo "2. Installez vendor/ sur le serveur (composer install)\n";
echo "3. Vérifiez les identifiants dans .env.production\n";
echo "4. Testez le site après ces corrections\n";
echo "\n";

echo "</pre>";
echo "<p style='color: red; font-weight: bold;'>⚠️ SUPPRIMEZ CE FICHIER APRÈS UTILISATION !</p>";
