<?php
/**
 * Script de diagnostic pour erreur 500
 * À SUPPRIMER après diagnostic !
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Diagnostic SphereVoices</h1>";
echo "<pre>";

// 1. Vérifier PHP
echo "=== PHP Version ===\n";
echo "Version: " . phpversion() . "\n";
echo "Extensions: " . implode(', ', get_loaded_extensions()) . "\n\n";

// 2. Vérifier la structure des fichiers
echo "=== Structure des fichiers ===\n";
$root = dirname(__DIR__);
echo "Racine: $root\n";
echo "www existe: " . (is_dir($root . '/www') ? 'OUI' : 'NON') . "\n";
echo "vendor existe: " . (is_dir($root . '/vendor') ? 'OUI' : 'NON') . "\n";
echo "config existe: " . (is_dir($root . '/config') ? 'OUI' : 'NON') . "\n";
echo ".env.production existe: " . (file_exists($root . '/.env.production') ? 'OUI' : 'NON') . "\n\n";

// 3. Vérifier autoload.php
echo "=== Autoloader ===\n";
$autoload = $root . '/vendor/autoload.php';
if (file_exists($autoload)) {
    echo "autoload.php trouvé: OUI\n";
    echo "Chemin: $autoload\n";
    try {
        require_once $autoload;
        echo "autoload.php chargé: OUI\n";
    } catch (Exception $e) {
        echo "ERREUR lors du chargement: " . $e->getMessage() . "\n";
    }
} else {
    echo "autoload.php trouvé: NON\n";
}
echo "\n";

// 4. Vérifier settings.php
echo "=== Settings.php ===\n";
$settings = $root . '/www/sites/default/settings.php';
if (file_exists($settings)) {
    echo "settings.php existe: OUI\n";
    echo "Lisible: " . (is_readable($settings) ? 'OUI' : 'NON') . "\n";
    
    // Lire le contenu pour vérifier la config DB
    $content = file_get_contents($settings);
    if (strpos($content, '$databases') !== false) {
        echo "Variable \$databases trouvée: OUI\n";
    } else {
        echo "Variable \$databases trouvée: NON\n";
    }
    
    // Vérifier si .env.production est chargé
    if (strpos($content, '.env.production') !== false) {
        echo "Chargement .env.production: OUI\n";
    } else {
        echo "Chargement .env.production: NON\n";
    }
} else {
    echo "settings.php existe: NON\n";
}
echo "\n";

// 5. Vérifier .env.production
echo "=== .env.production ===\n";
$env = $root . '/.env.production';
if (file_exists($env)) {
    echo "Fichier existe: OUI\n";
    echo "Lisible: " . (is_readable($env) ? 'OUI' : 'NON') . "\n";
    if (is_readable($env)) {
        $envContent = file_get_contents($env);
        if (strpos($envContent, 'DB_HOST') !== false) {
            echo "DB_HOST trouvé: OUI\n";
        }
        if (strpos($envContent, 'DB_NAME') !== false) {
            echo "DB_NAME trouvé: OUI\n";
        }
        if (strpos($envContent, 'DB_USER') !== false) {
            echo "DB_USER trouvé: OUI\n";
        }
        if (strpos($envContent, 'DB_PASSWORD') !== false) {
            echo "DB_PASSWORD trouvé: OUI\n";
        }
    }
} else {
    echo "Fichier existe: NON\n";
}
echo "\n";

// 6. Vérifier les permissions
echo "=== Permissions ===\n";
$filesDir = $root . '/www/sites/default/files';
if (is_dir($filesDir)) {
    echo "files/ existe: OUI\n";
    echo "Permissions: " . substr(sprintf('%o', fileperms($filesDir)), -4) . "\n";
    echo "Écriture: " . (is_writable($filesDir) ? 'OUI' : 'NON') . "\n";
} else {
    echo "files/ existe: NON\n";
}
echo "\n";

// 7. Tester la connexion à la base de données
echo "=== Test Base de données ===\n";
if (file_exists($env) && is_readable($env)) {
    $envVars = [];
    $lines = file($env, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $envVars[trim($key)] = trim($value);
        }
    }
    
    if (isset($envVars['DB_HOST']) && isset($envVars['DB_NAME'])) {
        try {
            $dsn = "mysql:host={$envVars['DB_HOST']};dbname={$envVars['DB_NAME']};charset=utf8mb4";
            $pdo = new PDO($dsn, $envVars['DB_USER'], $envVars['DB_PASSWORD']);
            echo "Connexion DB: RÉUSSIE\n";
        } catch (PDOException $e) {
            echo "Connexion DB: ÉCHOUÉE\n";
            echo "Erreur: " . $e->getMessage() . "\n";
        }
    } else {
        echo "Variables DB manquantes dans .env.production\n";
    }
} else {
    echo ".env.production non accessible\n";
}
echo "\n";

// 8. Vérifier index.php
echo "=== index.php ===\n";
$index = $root . '/www/index.php';
if (file_exists($index)) {
    echo "index.php existe: OUI\n";
    $indexContent = file_get_contents($index);
    if (strpos($indexContent, 'autoload.php') !== false) {
        echo "Référence autoload.php: OUI\n";
        // Extraire le chemin
        if (preg_match("/require.*['\"](.*autoload\.php)['\"]/", $indexContent, $matches)) {
            echo "Chemin autoload: " . $matches[1] . "\n";
        }
    }
} else {
    echo "index.php existe: NON\n";
}

echo "\n=== FIN DU DIAGNOSTIC ===\n";
echo "</pre>";
echo "<p style='color: red; font-weight: bold;'>⚠️ SUPPRIMEZ CE FICHIER APRÈS DIAGNOSTIC !</p>";
