<?php
/**
 * BYPASS COMPLET du mode maintenance - VERSION RACINE
 * Ce script est à la RACINE du projet (pas dans www/)
 * URL: https://www.spherevoices.com/bypass-maintenance.php?token=spherevoices2026
 */

$security_token = 'spherevoices2026';
$provided_token = $_GET['token'] ?? '';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>🚨 Bypass Mode Maintenance</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #28a745; padding: 10px; background: #d4edda; border-radius: 4px; margin: 10px 0; }
        .error { color: #dc3545; padding: 10px; background: #f8d7da; border-radius: 4px; margin: 10px 0; }
        .warning { color: #856404; padding: 10px; background: #fff3cd; border-radius: 4px; margin: 10px 0; }
        .info { color: #0c5460; padding: 10px; background: #d1ecf1; border-radius: 4px; margin: 10px 0; }
        .btn { display: inline-block; padding: 10px 20px; margin: 10px 5px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚨 Bypass Mode Maintenance (Racine)</h1>
        
        <?php
        if ($provided_token === $security_token) {
            echo '<div class="info">🚀 Connexion directe à la base de données...</div>';
            
            // Charger les identifiants depuis settings.php (dans sites/default/)
            $settings_file = __DIR__ . '/sites/default/settings.php';
            
            echo '<div class="info">Chemin settings.php : ' . htmlspecialchars($settings_file) . '</div>';
            
            if (!file_exists($settings_file)) {
                echo '<div class="error">❌ settings.php introuvable à : ' . htmlspecialchars($settings_file) . '</div>';
                echo '<div class="info">Fichiers dans la racine :</div><pre>';
                print_r(scandir(__DIR__));
                echo '</pre>';
                exit;
            }
            
            // Parser settings.php pour extraire les credentials DB
            include $settings_file;
            
            if (!isset($databases['default']['default'])) {
                echo '<div class="error">❌ Configuration base de données introuvable dans settings.php</div>';
                exit;
            }
            
            $db_config = $databases['default']['default'];
            
            echo '<div class="success">✅ Configuration DB trouvée</div>';
            echo '<pre>';
            echo 'Host: ' . htmlspecialchars($db_config['host']) . "\n";
            echo 'Database: ' . htmlspecialchars($db_config['database']) . "\n";
            echo 'User: ' . htmlspecialchars($db_config['username']) . "\n";
            echo '</pre>';
            
            try {
                // Connexion directe à la base de données
                $dsn = "mysql:host={$db_config['host']};dbname={$db_config['database']};charset=utf8mb4";
                $pdo = new PDO($dsn, $db_config['username'], $db_config['password'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
                
                echo '<div class="success">✅ Connexion base de données réussie !</div>';
                
                // 1. DÉSACTIVER LE MODE MAINTENANCE
                echo '<div class="info">🔧 Désactivation du mode maintenance...</div>';
                
                $stmt = $pdo->prepare("
                    DELETE FROM key_value 
                    WHERE collection = 'state' 
                    AND name = 'system.maintenance_mode'
                ");
                $result = $stmt->execute();
                $deleted = $stmt->rowCount();
                
                if ($deleted > 0) {
                    echo '<div class="success">✅ Mode maintenance DÉSACTIVÉ ! (' . $deleted . ' entrée supprimée)</div>';
                } else {
                    echo '<div class="warning">⚠️ Aucune entrée maintenance trouvée (déjà désactivé ?)</div>';
                }
                
                // 2. VIDER LES TABLES DE CACHE
                echo '<div class="info">🔄 Vidage des tables de cache...</div>';
                
                $cache_tables = [
                    'cache_bootstrap',
                    'cache_config',
                    'cache_container',
                    'cache_data',
                    'cache_default',
                    'cache_discovery',
                    'cache_dynamic_page_cache',
                    'cache_entity',
                    'cache_menu',
                    'cache_page',
                    'cache_render',
                ];
                
                $cleared = 0;
                foreach ($cache_tables as $table) {
                    try {
                        $stmt = $pdo->prepare("TRUNCATE TABLE `$table`");
                        $stmt->execute();
                        $cleared++;
                    } catch (PDOException $e) {
                        // Table n'existe peut-être pas
                    }
                }
                
                echo '<div class="success">✅ ' . $cleared . ' tables de cache vidées</div>';
                
                // 3. VIDER LES FICHIERS DE CACHE COMPILÉS
                echo '<div class="info">🗑️ Suppression des fichiers de cache compilés...</div>';
                
                $cache_dirs = [
                    __DIR__ . '/sites/default/files/php',
                    __DIR__ . '/sites/default/files/css',
                    __DIR__ . '/sites/default/files/js',
                ];
                
                $deleted_files = 0;
                foreach ($cache_dirs as $dir) {
                    if (is_dir($dir)) {
                        $files = glob($dir . '/*');
                        foreach ($files as $file) {
                            if (is_file($file)) {
                                @unlink($file);
                                $deleted_files++;
                            }
                        }
                    }
                }
                
                if ($deleted_files > 0) {
                    echo '<div class="success">✅ ' . $deleted_files . ' fichiers de cache supprimés</div>';
                } else {
                    echo '<div class="warning">⚠️ Aucun fichier de cache à supprimer</div>';
                }
                
                echo '<h2 class="success">🎉 RÉPARATION TERMINÉE !</h2>';
                echo '<div class="info">';
                echo '<p><strong>✅ Le site devrait maintenant être accessible !</strong></p>';
                echo '<ul>';
                echo '<li>✅ Mode maintenance désactivé</li>';
                echo '<li>✅ Cache vidé</li>';
                echo '</ul>';
                echo '</div>';
                
                echo '<p><a href="/www/" class="btn">← Aller sur le site</a></p>';
                echo '<p><a href="/" class="btn">← Tester racine</a></p>';
                
            } catch (PDOException $e) {
                echo '<div class="error">❌ Erreur de connexion MySQL : ' . htmlspecialchars($e->getMessage()) . '</div>';
                echo '<div class="warning">Vérifiez que MySQL est accessible depuis le serveur web.</div>';
            }
            
        } else {
            ?>
            <div class="warning">⚠️ Ce script bypass le mode maintenance en accédant directement à MySQL.</div>
            
            <form method="get">
                <label for="token">Token de sécurité:</label><br>
                <input type="text" id="token" name="token" value="" style="width: 300px; padding: 5px; margin: 10px 0;">
                <br>
                <button type="submit" class="btn">Désactiver maintenance</button>
            </form>
            
            <h3>🔗 URL directe :</h3>
            <pre>https://www.spherevoices.com/bypass-maintenance.php?token=spherevoices2026</pre>
            
            <p><small>Ce fichier est à la RACINE du projet pour éviter la redirection Drupal.</small></p>
            <?php
        }
        ?>
    </div>
</body>
</html>
