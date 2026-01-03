<?php
/**
 * BYPASS COMPLET du mode maintenance
 * Ce script n'utilise PAS Drupal, il accède directement à la base de données
 * URL: https://www.spherevoices.com/www/bypass-maintenance.php?token=spherevoices2026
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
        <h1>🚨 Bypass Mode Maintenance</h1>
        
        <?php
        if ($provided_token === $security_token) {
            echo '<div class="info">🚀 Connexion directe à la base de données...</div>';
            
            // Charger les identifiants depuis settings.php
            $settings_file = __DIR__ . '/sites/default/settings.php';
            
            if (!file_exists($settings_file)) {
                echo '<div class="error">❌ settings.php introuvable</div>';
                exit;
            }
            
            // Parser settings.php pour extraire les credentials DB
            include $settings_file;
            
            if (!isset($databases['default']['default'])) {
                echo '<div class="error">❌ Configuration base de données introuvable</div>';
                exit;
            }
            
            $db_config = $databases['default']['default'];
            
            echo '<div class="info">📊 Configuration DB trouvée</div>';
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
                
                echo '<div class="success">✅ Connexion base de données réussie</div>';
                
                // 1. DÉSACTIVER LE MODE MAINTENANCE
                echo '<div class="info">🔧 Désactivation du mode maintenance dans key_value...</div>';
                
                // Drupal stocke le mode maintenance dans la table key_value
                $stmt = $pdo->prepare("
                    DELETE FROM key_value 
                    WHERE collection = 'state' 
                    AND name = 'system.maintenance_mode'
                ");
                $stmt->execute();
                
                echo '<div class="success">✅ Mode maintenance DÉSACTIVÉ dans la base de données</div>';
                
                // 2. VIDER LA TABLE CACHE
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
                    'cache_toolbar',
                ];
                
                $cleared = 0;
                foreach ($cache_tables as $table) {
                    try {
                        $stmt = $pdo->prepare("TRUNCATE TABLE `$table`");
                        $stmt->execute();
                        $cleared++;
                        echo '<div class="success">✅ Table ' . htmlspecialchars($table) . ' vidée</div>';
                    } catch (PDOException $e) {
                        echo '<div class="warning">⚠️ Table ' . htmlspecialchars($table) . ' : ' . htmlspecialchars($e->getMessage()) . '</div>';
                    }
                }
                
                echo '<div class="success">✅ ' . $cleared . ' tables de cache vidées</div>';
                
                echo '<h2 class="success">🎉 RÉPARATION TERMINÉE !</h2>';
                echo '<div class="info">';
                echo '<p><strong>Actions effectuées :</strong></p>';
                echo '<ul>';
                echo '<li>✅ Mode maintenance DÉSACTIVÉ (accès direct DB)</li>';
                echo '<li>✅ ' . $cleared . ' tables de cache vidées</li>';
                echo '<li>✅ Le site devrait maintenant être accessible</li>';
                echo '</ul>';
                echo '</div>';
                
                echo '<div class="warning">';
                echo '<p><strong>⚠️ Prochaines étapes :</strong></p>';
                echo '<ol>';
                echo '<li>Visitez la page d\'accueil : <a href="/">https://www.spherevoices.com</a></li>';
                echo '<li>Connectez-vous en admin</li>';
                echo '<li>Allez sur Configuration > Development > Performance</li>';
                echo '<li>Cliquez sur "Clear all caches" pour un vidage complet</li>';
                echo '</ol>';
                echo '</div>';
                
                echo '<p><a href="/" class="btn">← Aller sur le site</a></p>';
                
            } catch (PDOException $e) {
                echo '<div class="error">❌ Erreur de connexion : ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
            
        } else {
            ?>
            <div class="warning">⚠️ Ce script bypass le mode maintenance en accédant directement à la base de données.</div>
            <p><strong>⚠️ N'utilisez ce script qu'en cas d'urgence !</strong></p>
            
            <form method="get">
                <label for="token">Token de sécurité:</label><br>
                <input type="text" id="token" name="token" value="" style="width: 300px; padding: 5px; margin: 10px 0;">
                <br>
                <button type="submit" class="btn">Désactiver maintenance</button>
            </form>
            
            <hr>
            
            <h3>📝 Ce script va :</h3>
            <ol>
                <li>✅ Se connecter DIRECTEMENT à la base de données (bypass Drupal)</li>
                <li>✅ Supprimer le flag de maintenance dans key_value</li>
                <li>✅ Vider toutes les tables de cache</li>
                <li>✅ Permettre l'accès au site</li>
            </ol>
            
            <h3>🔗 URL avec token :</h3>
            <pre>https://www.spherevoices.com/www/bypass-maintenance.php?token=spherevoices2026</pre>
            <?php
        }
        ?>
    </div>
</body>
</html>

