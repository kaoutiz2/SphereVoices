<?php
/**
 * Script de configuration automatique pour OVH
 * Compatible PHP 5.4+
 * À SUPPRIMER après utilisation !
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Configuration automatique SphereVoices</h1>";
echo "<pre>";

$root = dirname(dirname(__FILE__));
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
    echo "ERREUR CRITIQUE: PHP 8.1+ requis, vous avez PHP $phpVersion\n";
    echo "ACTION REQUISE:\n";
    echo "1. Allez dans OVH: Hébergement → Configuration → Version PHP\n";
    echo "2. Changez vers PHP 8.1 ou 8.2\n";
    echo "3. Rechargez cette page\n";
    echo "\n";
    echo "SANS PHP 8.1+, Drupal 10 ne fonctionnera JAMAIS!\n\n";
} else {
    echo "Version PHP OK\n\n";
}

// 2. Créer settings.php
echo "=== Création settings.php ===\n";
if (file_exists($settingsFile)) {
    echo "settings.php existe deja\n";
} else {
    if (!file_exists($defaultSettings)) {
        echo "ERREUR: default.settings.php non trouve\n";
    } else {
        $content = file_get_contents($defaultSettings);
        
        $envLoader = "\n\n// Load environment variables from .env.production\n";
        $envLoader .= "if (file_exists(__DIR__ . '/../../../.env.production')) {\n";
        $envLoader .= "    \$envFile = __DIR__ . '/../../../.env.production';\n";
        $envLoader .= "    \$envVars = array();\n";
        $envLoader .= "    if (is_readable(\$envFile)) {\n";
        $envLoader .= "        \$lines = file(\$envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);\n";
        $envLoader .= "        foreach (\$lines as \$line) {\n";
        $envLoader .= "            \$line = trim(\$line);\n";
        $envLoader .= "            if (empty(\$line) || strpos(\$line, '#') === 0) continue;\n";
        $envLoader .= "            if (strpos(\$line, '=') !== false) {\n";
        $envLoader .= "                \$parts = explode('=', \$line, 2);\n";
        $envLoader .= "                \$envVars[trim(\$parts[0])] = trim(\$parts[1]);\n";
        $envLoader .= "            }\n";
        $envLoader .= "        }\n";
        $envLoader .= "        \n";
        $envLoader .= "        if (!empty(\$envVars['DB_NAME']) && !empty(\$envVars['DB_USER'])) {\n";
        $envLoader .= "            \$databases['default']['default'] = array(\n";
        $envLoader .= "                'database' => \$envVars['DB_NAME'],\n";
        $envLoader .= "                'username' => \$envVars['DB_USER'],\n";
        $envLoader .= "                'password' => isset(\$envVars['DB_PASSWORD']) ? \$envVars['DB_PASSWORD'] : '',\n";
        $envLoader .= "                'host' => isset(\$envVars['DB_HOST']) ? \$envVars['DB_HOST'] : 'localhost',\n";
        $envLoader .= "                'port' => isset(\$envVars['DB_PORT']) ? \$envVURs['DB_PORT'] : '3306',\n";
        $envLoader .= "                'driver' => isset(\$envVars['DB_DRIVER']) ? \$envVars['DB_DRIVER'] : 'mysql',\n";
        $envLoader .= "                'prefix' => isset(\$envVars['DB_PREFIX']) ? \$envVars['DB_PREFIX'] : '',\n";
        $envLoader .= "                'collation' => isset(\$envVars['DB_COLLATION']) ? \$envVars['DB_COLLATION'] : 'utf8mb4_general_ci',\n";
        $envLoader .= "            );\n";
        $envLoader .= "        }\n";
        $envLoader .= "    }\n";
        $envLoader .= "}\n";
        
        $content = str_replace('$databases = [];', '$databases = [];' . $envLoader, $content);
        
        if (file_put_contents($settingsFile, $content)) {
            chmod($settingsFile, 0644);
            echo "settings.php cree avec succes\n";
        } else {
            echo "ERREUR: Impossible de creer settings.php\n";
            echo "Verifiez les permissions sur sites/default/\n";
        }
    }
}
echo "\n";

// 3. Créer le dossier files/
echo "=== Creation dossier files/ ===\n";
if (is_dir($filesDir)) {
    echo "files/ existe deja\n";
} else {
    if (mkdir($filesDir, 0777, true)) {
        chmod($filesDir, 0777);
        echo "Dossier files/ cree avec permissions 777\n";
    } else {
        echo "ERREUR: Impossible de creer files/\n";
        echo "Creez-le manuellement via FTP avec permissions 777\n";
    }
}
echo "\n";

// 4. Vérifier vendor/
echo "=== Verification vendor/ ===\n";
$vendorDir = $root . '/vendor';
if (is_dir($vendorDir)) {
    echo "vendor/ existe\n";
    if (file_exists($vendorDir . '/autoload.php')) {
        echo "autoload.php existe\n";
    } else {
        echo "autoload.php manquant dans vendor/\n";
        echo "Executez: composer install --no-dev --optimize-autoloader\n";
    }
} else {
    echo "vendor/ n'existe pas\n";
    echo "Le dossier vendor/ doit etre installe sur le serveur\n";
    echo "Options:\n";
    echo "  1. Via SSH: composer install --no-dev --optimize-autoloader\n";
    echo "  2. Ou uploadez le dossier vendor/ depuis votre machine locale\n";
}
echo "\n";

// 5. Vérifier .env.production
echo "=== Verification .env.production ===\n";
if (file_exists($envFile)) {
    echo ".env.production existe\n";
    if (is_readable($envFile)) {
        $envContent = file_get_contents($envFile);
        if (strpos($envContent, 'DB_NAME') !== false && strpos($envContent, 'DB_USER') !== false) {
            echo "Contient DB_NAME et DB_USER\n";
        } else {
            echo "Verifiez que .env.production contient DB_NAME et DB_USER\n";
        }
    } else {
        echo ".env.production n'est pas lisible\n";
    }
} else {
    echo ".env.production n'existe pas\n";
    echo "Creez-le a la racine avec vos parametres de base de donnees\n";
}
echo "\n";

echo "=== RESUME ===\n";
echo "Actions a faire:\n";
echo "1. CHANGEZ PHP dans OVH vers 8.1+ (CRITIQUE!)\n";
echo "2. Installez vendor/ sur le serveur (composer install)\n";
echo "3. Verifiez les identifiants dans .env.production\n";
echo "4. Testez le site apres ces corrections\n";
echo "\n";

echo "</pre>";
echo "<p style='color: red; font-weight: bold;'>SUPPRIMEZ CE FICHIER APRES UTILISATION !</p>";
