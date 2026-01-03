<?php
/**
 * Import de la base de données locale vers prod
 * URL: https://www.spherevoices.com/import-db.php?token=spherevoices2026
 * 
 * ATTENTION : Ce script va ÉCRASER la base de données de production !
 */

$security_token = 'spherevoices2026';
$provided_token = $_GET['token'] ?? '';

if ($provided_token !== $security_token) {
    die('Token requis');
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>⚠️ Import DB</title>
    <style>
        body { font-family: monospace; max-width: 800px; margin: 50px auto; padding: 20px; background: #1a1a1a; color: #ff0; }
        .container { background: #000; padding: 30px; border: 3px solid #f00; }
        .warning { color: #f00; font-size: 20px; font-weight: bold; }
        .success { color: #0f0; }
        .error { color: #f00; }
        .info { color: #0ff; }
        .btn { display: inline-block; padding: 15px 30px; margin: 10px; background: #f00; color: #fff; text-decoration: none; border-radius: 4px; font-weight: bold; cursor: pointer; border: none; font-size: 16px; }
        .btn:hover { background: #a00; }
        pre { background: #222; padding: 15px; border: 1px solid #444; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="warning">⚠️ IMPORT BASE DE DONNÉES ⚠️</h1>
        
        <div class="warning">
            <p>⛔ ATTENTION : Ce script va ÉCRASER la base de données de production !</p>
            <p>⛔ Toutes les données actuelles seront PERDUES !</p>
            <p>⛔ Sauvegardez d'abord si nécessaire !</p>
        </div>
        
        <h2 class="info">📋 Instructions</h2>
        
        <p class="info">1. Sur votre machine locale, exportez la base :</p>
        <pre>cd /Users/bryangast/Documents/Kaoutiz.dev/SphereVoices/site/www
../vendor/bin/drush sql:dump --result-file=/tmp/drupal-local.sql
gzip /tmp/drupal-local.sql</pre>
        
        <p class="info">2. Uploadez le fichier drupal-local.sql.gz sur le serveur dans :</p>
        <pre>/home/spheree/www/drupal-local.sql.gz</pre>
        
        <p class="info">3. Vérifiez que le fichier est présent :</p>
        <?php
        $sql_file = __DIR__ . '/drupal-local.sql.gz';
        if (file_exists($sql_file)) {
            $size = filesize($sql_file);
            $date = date('Y-m-d H:i:s', filemtime($sql_file));
            echo '<p class="success">✅ Fichier trouvé : ' . number_format($size) . ' bytes</p>';
            echo '<p class="success">✅ Date : ' . $date . '</p>';
            
            echo '<form method="POST">';
            echo '<p class="warning">⚠️ Êtes-vous SÛR de vouloir importer ?</p>';
            echo '<button type="submit" name="import" value="yes" class="btn">🔥 OUI, IMPORTER MAINTENANT</button>';
            echo '</form>';
        } else {
            echo '<p class="error">❌ Fichier NON trouvé : ' . $sql_file . '</p>';
            echo '<p class="error">Uploadez d\'abord le fichier drupal-local.sql.gz</p>';
        }
        ?>
        
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import']) && $_POST['import'] === 'yes') {
            echo '<hr style="border-color: #f00; margin: 30px 0;">';
            echo '<h2 class="warning">🔥 IMPORT EN COURS...</h2>';
            
            // Définir $app_root
            $app_root = dirname(__DIR__);
            
            // Charger la config DB
            $databases = [];
            @include __DIR__ . '/sites/default/settings.php';
            
            if (empty($databases['default']['default'])) {
                echo '<p class="error">❌ Config DB introuvable</p>';
                exit;
            }
            
            $db = $databases['default']['default'];
            
            echo '<p class="info">1. Décompression du fichier...</p>';
            exec("gunzip -c " . escapeshellarg($sql_file) . " > " . escapeshellarg(__DIR__ . '/drupal-local.sql'), $output, $return);
            
            if ($return !== 0) {
                echo '<p class="error">❌ Erreur de décompression</p>';
                exit;
            }
            
            echo '<p class="success">✅ Fichier décompressé</p>';
            
            echo '<p class="info">2. Import dans MySQL (via PHP PDO)...</p>';
            
            try {
                $pdo = new PDO(
                    "mysql:host={$db['host']};dbname={$db['database']};charset=utf8mb4",
                    $db['username'],
                    $db['password'],
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
                
                // Lire le fichier SQL et l'exécuter
                $sql = file_get_contents(__DIR__ . '/drupal-local.sql');
                
                // Supprimer les commentaires et diviser en requêtes
                $statements = array_filter(
                    array_map('trim', explode(';', $sql)),
                    function($stmt) {
                        return !empty($stmt) && 
                               strpos($stmt, '--') !== 0 && 
                               strpos($stmt, '/*') !== 0;
                    }
                );
                
                echo '<p class="info">Nombre de requêtes: ' . count($statements) . '</p>';
                echo '<p class="info">Exécution en cours...</p>';
                flush();
                
                $count = 0;
                foreach ($statements as $statement) {
                    try {
                        $pdo->exec($statement);
                        $count++;
                        if ($count % 100 === 0) {
                            echo '<p class="info">  ' . $count . ' requêtes exécutées...</p>';
                            flush();
                        }
                    } catch (PDOException $e) {
                        // Ignorer les erreurs non critiques (tables déjà existantes, etc.)
                        if ($e->getCode() !== '42S01' && $e->getCode() !== '23000') {
                            echo '<p class="error">Erreur à la requête ' . $count . ': ' . $e->getMessage() . '</p>';
                        }
                    }
                }
                
                echo '<p class="success">✅ Base de données importée ! (' . $count . ' requêtes)</p>';
                
            } catch (PDOException $e) {
                echo '<p class="error">❌ Erreur de connexion : ' . $e->getMessage() . '</p>';
                exit;
            }
            
            // Nettoyer
            @unlink(__DIR__ . '/drupal-local.sql');
            
            echo '<p class="info">3. Mise à jour des URLs...</p>';
            
            try {
                $pdo = new PDO(
                    "mysql:host={$db['host']};dbname={$db['database']};charset=utf8mb4",
                    $db['username'],
                    $db['password'],
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
                
                // Mettre à jour les URLs
                $pdo->exec("UPDATE config SET data = REPLACE(data, 'localhost:8888', 'www.spherevoices.com')");
                $pdo->exec("UPDATE config SET data = REPLACE(data, 'http://', 'https://')");
                
                echo '<p class="success">✅ URLs mises à jour</p>';
                
                // Vider les caches
                $cache_tables = [
                    'cache_bootstrap', 'cache_config', 'cache_container',
                    'cache_data', 'cache_default', 'cache_discovery',
                    'cache_dynamic_page_cache', 'cache_entity', 'cache_menu',
                    'cache_page', 'cache_render', 'cache_toolbar',
                ];
                
                foreach ($cache_tables as $table) {
                    try {
                        $pdo->exec("TRUNCATE TABLE `$table`");
                    } catch (PDOException $e) {}
                }
                
                echo '<p class="success">✅ Caches vidés</p>';
                
            } catch (PDOException $e) {
                echo '<p class="error">❌ Erreur : ' . $e->getMessage() . '</p>';
            }
            
            echo '<h2 class="success">🎉 IMPORT TERMINÉ !</h2>';
            echo '<p class="success">La base de données de production est maintenant identique au local.</p>';
            echo '<p class="info">Prochaines étapes :</p>';
            echo '<ol class="info">';
            echo '<li>Videz le cache : <a href="/full-reset.php?token=spherevoices2026" style="color: #0ff;">full-reset.php</a></li>';
            echo '<li>Testez le site : <a href="/" style="color: #0ff;">Page d\'accueil</a></li>';
            echo '<li>Connectez-vous : <a href="/user/login" style="color: #0ff;">Login</a></li>';
            echo '</ol>';
        }
        ?>
    </div>
</body>
</html>

