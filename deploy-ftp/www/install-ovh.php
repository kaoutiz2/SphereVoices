<?php
/**
 * Script d'installation automatique pour OVH
 * Exécutez ce script UNE FOIS puis SUPPRIMEZ-LE
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Installation SphereVoices sur OVH</h1>";
echo "<pre>";

$root = dirname(dirname(__FILE__));
$wwwRoot = $root . '/www';
$defaultSettings = $wwwRoot . '/sites/default/default.settings.php';
$settingsFile = $wwwRoot . '/sites/default/settings.php';
$envFile = $root . '/.env.production';
$filesDir = $wwwRoot . '/sites/default/files';

// Configuration de la base de données OVH
$dbConfig = array(
    'database' => 'spheree921',
    'username' => 'spheree921',
    'password' => 'Cameroun2026',
    'host' => 'spheree921.mysql.db',
    'port' => '3306',
    'driver' => 'mysql',
    'prefix' => '',
    'collation' => 'utf8mb4_general_ci',
);

echo "=== Étape 1: Vérification PHP ===\n";
$phpVersion = phpversion();
echo "Version PHP: $phpVersion\n";
if (version_compare($phpVersion, '8.1.0', '<')) {
    echo "ERREUR CRITIQUE: PHP 8.1+ requis!\n";
    echo "Changez PHP dans OVH: Hébergement → Configuration → Version PHP → PHP 8.1\n";
    echo "Puis rechargez cette page.\n\n";
    exit;
}
echo "OK\n\n";

echo "=== Étape 2: Création settings.php ===\n";
if (file_exists($settingsFile)) {
    echo "settings.php existe déjà\n";
} else {
    if (!file_exists($defaultSettings)) {
        die("ERREUR: default.settings.php non trouvé\n");
    }
    
    $content = file_get_contents($defaultSettings);
    
    // Code pour charger la base de données
    $dbCode = "\n\n// Configuration base de données OVH\n";
    $dbCode .= "\$databases['default']['default'] = array(\n";
    $dbCode .= "  'database' => '{$dbConfig['database']}',\n";
    $dbCode .= "  'username' => '{$dbConfig['username']}',\n";
    $dbCode .= "  'password' => '{$dbConfig['password']}',\n";
    $dbCode .= "  'host' => '{$dbConfig['host']}',\n";
    $dbCode .= "  'port' => '{$dbConfig['port']}',\n";
    $dbCode .= "  'driver' => '{$dbConfig['driver']}',\n";
    $dbCode .= "  'prefix' => '{$dbConfig['prefix']}',\n";
    $dbCode .= "  'collation' => '{$dbConfig['collation']}',\n";
    $dbCode .= ");\n";
    
    // Remplacer $databases = array(); par la configuration
    $content = preg_replace('/\$databases\s*=\s*array\(\);/', '$databases = array();' . $dbCode, $content);
    
    if (file_put_contents($settingsFile, $content)) {
        chmod($settingsFile, 0644);
        echo "OK: settings.php créé\n";
    } else {
        die("ERREUR: Impossible de créer settings.php. Vérifiez les permissions.\n");
    }
}
echo "\n";

echo "=== Étape 3: Création dossier files/ ===\n";
if (!is_dir($filesDir)) {
    if (mkdir($filesDir, 0777, true)) {
        chmod($filesDir, 0777);
        echo "OK: Dossier files/ créé avec permissions 777\n";
    } else {
        echo "ERREUR: Impossible de créer files/. Créez-le manuellement via FTP.\n";
    }
} else {
    chmod($filesDir, 0777);
    echo "OK: files/ existe\n";
}
echo "\n";

echo "=== Étape 4: Test connexion base de données ===\n";
try {
    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
    echo "OK: Connexion base de données réussie\n";
} catch (PDOException $e) {
    echo "ERREUR: " . $e->getMessage() . "\n";
    echo "Vérifiez les identifiants dans ce script.\n";
}
echo "\n";

echo "=== Étape 5: Vérification vendor/ ===\n";
$vendorDir = $root . '/vendor';
if (is_dir($vendorDir) && file_exists($vendorDir . '/autoload.php')) {
    echo "OK: vendor/ existe\n";
} else {
    echo "ATTENTION: vendor/ manquant\n";
    echo "Installez-le via SSH:\n";
    echo "  composer install --no-dev --optimize-autoloader\n";
    echo "Ou uploadez le dossier vendor/ depuis votre machine locale.\n";
}
echo "\n";

echo "=== INSTALLATION TERMINÉE ===\n";
echo "\n";
echo "Prochaines étapes:\n";
echo "1. Si vendor/ manque, installez-le (voir ci-dessus)\n";
echo "2. Testez le site: https://www.spherevoices.com\n";
echo "3. SUPPRIMEZ CE FICHIER (install-ovh.php) pour la sécurité!\n";
echo "\n";

echo "</pre>";
?>
