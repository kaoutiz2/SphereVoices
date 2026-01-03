<?php
/**
 * Nettoyage complet des caches SANS charger Drupal
 * URL: https://www.spherevoices.com/deep-clean.php?token=spherevoices2026
 */

$security_token = 'spherevoices2026';
$provided_token = $_GET['token'] ?? '';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>🧹 Deep Clean</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 30px auto; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #28a745; padding: 10px; background: #d4edda; border-radius: 4px; margin: 10px 0; }
        .error { color: #dc3545; padding: 10px; background: #f8d7da; border-radius: 4px; margin: 10px 0; }
        .warning { color: #856404; padding: 10px; background: #fff3cd; border-radius: 4px; margin: 10px 0; }
        .info { color: #0c5460; padding: 10px; background: #d1ecf1; border-radius: 4px; margin: 10px 0; }
        .btn { display: inline-block; padding: 12px 24px; margin: 5px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; font-weight: bold; }
        .btn:hover { background: #0056b3; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #218838; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; font-size: 11px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧹 Nettoyage Profond (Deep Clean)</h1>
        
        <?php
        if ($provided_token === $security_token) {
            
            echo '<div class="info">🚀 Nettoyage SANS charger Drupal (plus fiable)...</div>';
            
            // Fonction pour supprimer récursivement
            function deleteRecursive($path, &$count, &$details) {
                if (!file_exists($path)) {
                    return;
                }
                
                if (is_dir($path)) {
                    $items = @scandir($path);
                    if ($items === false) {
                        return;
                    }
                    
                    foreach ($items as $item) {
                        if ($item === '.' || $item === '..') {
                            continue;
                        }
                        deleteRecursive($path . '/' . $item, $count, $details);
                    }
                    
                    if (@rmdir($path)) {
                        $details[] = 'DIR: ' . basename($path);
                    }
                } else {
                    if (@unlink($path)) {
                        $count++;
                    }
                }
            }
            
            // 1. CONNEXION À MYSQL
            echo '<div class="info">📊 Connexion à MySQL...</div>';
            
            // Charger les infos depuis settings.php
            $settings_file = __DIR__ . '/sites/default/settings.php';
            
            if (!file_exists($settings_file)) {
                echo '<div class="error">❌ settings.php introuvable</div>';
                exit;
            }
            
            // Inclure settings.php pour charger la config
            $databases = [];
            include $settings_file;
            
            // Vérifier que la config existe
            if (empty($databases['default']['default'])) {
                echo '<div class="error">❌ Configuration base de données introuvable</div>';
                exit;
            }
            
            $db_config = $databases['default']['default'];
            $db_host = $db_config['host'];
            $db_name = $db_config['database'];
            $db_user = $db_config['username'];
            $db_pass = $db_config['password'];
            
            echo '<div class="success">✅ Configuration trouvée : ' . htmlspecialchars($db_name) . '@' . htmlspecialchars($db_host) . '</div>';
            
            try {
                $pdo = new PDO(
                    "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4",
                    $db_user,
                    $db_pass,
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
                
                echo '<div class="success">✅ Connecté à MySQL : ' . htmlspecialchars($db_name) . '</div>';
                
                // 2. DÉSACTIVER LE MODE MAINTENANCE
                echo '<div class="info">🔧 Désactivation mode maintenance...</div>';
                
                $stmt = $pdo->prepare("DELETE FROM key_value WHERE collection = 'state' AND name = 'system.maintenance_mode'");
                $stmt->execute();
                
                echo '<div class="success">✅ Mode maintenance désactivé</div>';
                
                // 3. DÉSACTIVER L'AGRÉGATION CSS/JS
                echo '<div class="info">📦 Désactivation agrégation CSS/JS dans la DB...</div>';
                
                $stmt = $pdo->prepare("SELECT data FROM config WHERE name = 'system.performance'");
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($result) {
                    $config = unserialize($result['data']);
                    $css_was = $config['css']['preprocess'] ?? null;
                    $js_was = $config['js']['preprocess'] ?? null;
                    
                    $config['css']['preprocess'] = 0;
                    $config['js']['preprocess'] = 0;
                    
                    $stmt = $pdo->prepare("UPDATE config SET data = :data WHERE name = 'system.performance'");
                    $stmt->execute(['data' => serialize($config)]);
                    
                    echo '<div class="success">✅ Agrégation désactivée (CSS: ' . ($css_was ? 'ON→OFF' : 'déjà OFF') . ', JS: ' . ($js_was ? 'ON→OFF' : 'déjà OFF') . ')</div>';
                } else {
                    echo '<div class="warning">⚠️ Config system.performance introuvable</div>';
                }
                
                // 4. VIDER LES TABLES DE CACHE
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
                    } catch (PDOException $e) {
                        // Table n'existe peut-être pas
                    }
                }
                
                echo '<div class="success">✅ ' . $cleared . ' tables de cache vidées</div>';
                
            } catch (PDOException $e) {
                echo '<div class="error">❌ Erreur MySQL : ' . htmlspecialchars($e->getMessage()) . '</div>';
                exit;
            }
            
            // 5. SUPPRIMER TOUS LES FICHIERS COMPILÉS
            echo '<div class="info">🗑️ Suppression TOTALE des fichiers compilés...</div>';
            
            $dirs_to_clean = [
                'Twig compilés' => __DIR__ . '/sites/default/files/php/twig',
                'PHP compilés' => __DIR__ . '/sites/default/files/php',
                'CSS compilés' => __DIR__ . '/sites/default/files/css',
                'JS compilés' => __DIR__ . '/sites/default/files/js',
            ];
            
            $total_deleted = 0;
            
            foreach ($dirs_to_clean as $label => $dir) {
                if (!is_dir($dir)) {
                    echo '<div class="warning">⚠️ ' . $label . ' : répertoire inexistant</div>';
                    continue;
                }
                
                $count = 0;
                $details = [];
                
                $items = @scandir($dir);
                if ($items !== false) {
                    foreach ($items as $item) {
                        if ($item === '.' || $item === '..') {
                            continue;
                        }
                        deleteRecursive($dir . '/' . $item, $count, $details);
                    }
                }
                
                $total_deleted += $count;
                echo '<div class="success">✅ ' . $label . ' : ' . $count . ' fichiers supprimés</div>';
            }
            
            echo '<div class="success">🎉 TOTAL : ' . $total_deleted . ' fichiers supprimés</div>';
            
            // 6. RÉACTIVER L'AGRÉGATION
            echo '<div class="info">📦 Réactivation agrégation CSS/JS...</div>';
            
            try {
                $stmt = $pdo->prepare("SELECT data FROM config WHERE name = 'system.performance'");
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($result) {
                    $config = unserialize($result['data']);
                    $config['css']['preprocess'] = 1;
                    $config['js']['preprocess'] = 1;
                    
                    $stmt = $pdo->prepare("UPDATE config SET data = :data WHERE name = 'system.performance'");
                    $stmt->execute(['data' => serialize($config)]);
                    
                    echo '<div class="success">✅ Agrégation réactivée</div>';
                }
                
                // Vider à nouveau les tables de cache
                foreach ($cache_tables as $table) {
                    try {
                        $stmt = $pdo->prepare("TRUNCATE TABLE `$table`");
                        $stmt->execute();
                    } catch (PDOException $e) {}
                }
                
                echo '<div class="success">✅ Cache vidé à nouveau</div>';
                
            } catch (PDOException $e) {
                echo '<div class="warning">⚠️ Erreur lors de la réactivation : ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
            
            echo '<h2 class="success">🎉 NETTOYAGE TERMINÉ !</h2>';
            
            echo '<div class="info">';
            echo '<h3>✅ Actions effectuées :</h3>';
            echo '<ol>';
            echo '<li>✅ Mode maintenance désactivé</li>';
            echo '<li>✅ Agrégation CSS/JS désactivée temporairement</li>';
            echo '<li>✅ ' . $cleared . ' tables de cache vidées (2x)</li>';
            echo '<li>✅ ' . $total_deleted . ' fichiers compilés supprimés</li>';
            echo '<li>✅ Agrégation réactivée</li>';
            echo '</ol>';
            echo '</div>';
            
            echo '<div class="warning">';
            echo '<h3>⚡ MAINTENANT :</h3>';
            echo '<ol>';
            echo '<li><strong>Cliquez sur "Page d\'accueil"</strong> ci-dessous</li>';
            echo '<li>Cela va <strong>forcer Drupal à régénérer</strong> tous les CSS/JS/Twig</li>';
            echo '<li>Faites <strong>Ctrl+Shift+R</strong> pour forcer le rechargement</li>';
            echo '<li>Les inputs et la galerie devraient apparaître !</li>';
            echo '</ol>';
            echo '</div>';
            
            echo '<div style="text-align: center; margin: 30px 0;">';
            echo '<a href="/" class="btn btn-success" style="font-size: 18px;">🏠 ALLER SUR LA PAGE D\'ACCUEIL</a>';
            echo '</div>';
            
            echo '<div style="text-align: center;">';
            echo '<a href="/user/login" class="btn">🔐 Page de login</a>';
            echo '<a href="/check-login-form.php?token=' . $security_token . '" class="btn">🔍 Diagnostic</a>';
            echo '</div>';
            
        } else {
            ?>
            <div class="warning">⚠️ Ce script nettoie TOUT sans charger Drupal.</div>
            
            <form method="get">
                <label for="token">Token de sécurité:</label><br>
                <input type="text" id="token" name="token" value="" style="width: 300px; padding: 5px; margin: 10px 0;">
                <br>
                <button type="submit" class="btn">Lancer le nettoyage</button>
            </form>
            
            <h3>🔗 URL :</h3>
            <pre>https://www.spherevoices.com/deep-clean.php?token=spherevoices2026</pre>
            <?php
        }
        ?>
    </div>
</body>
</html>

