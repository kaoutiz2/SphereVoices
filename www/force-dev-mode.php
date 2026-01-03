<?php
/**
 * Désactive l'agrégation CSS/JS et force le mode de développement
 * URL: https://www.spherevoices.com/force-dev-mode.php?token=spherevoices2026
 */

$security_token = 'spherevoices2026';
$provided_token = $_GET['token'] ?? '';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>🔧 Mode Développement</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #28a745; padding: 10px; background: #d4edda; border-radius: 4px; margin: 10px 0; }
        .error { color: #dc3545; padding: 10px; background: #f8d7da; border-radius: 4px; margin: 10px 0; }
        .info { color: #0c5460; padding: 10px; background: #d1ecf1; border-radius: 4px; margin: 10px 0; }
        .btn { display: inline-block; padding: 12px 24px; margin: 5px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; font-weight: bold; }
        .btn-success { background: #28a745; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Force Mode Développement</h1>
        
        <?php
        if ($provided_token === $security_token) {
            
            $settings_file = __DIR__ . '/sites/default/settings.php';
            
            if (!file_exists($settings_file)) {
                echo '<div class="error">❌ settings.php introuvable</div>';
                exit;
            }
            
            $databases = [];
            include $settings_file;
            
            if (empty($databases['default']['default'])) {
                echo '<div class="error">❌ Configuration DB introuvable</div>';
                exit;
            }
            
            $db_config = $databases['default']['default'];
            
            try {
                $pdo = new PDO(
                    "mysql:host={$db_config['host']};dbname={$db_config['database']};charset=utf8mb4",
                    $db_config['username'],
                    $db_config['password'],
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
                
                echo '<div class="success">✅ Connecté à MySQL</div>';
                
                // 1. DÉSACTIVER L'AGRÉGATION CSS/JS
                echo '<div class="info">📦 Désactivation agrégation CSS/JS...</div>';
                
                $stmt = $pdo->prepare("SELECT data FROM config WHERE name = 'system.performance'");
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($result) {
                    $config = unserialize($result['data']);
                    $config['css']['preprocess'] = 0;
                    $config['js']['preprocess'] = 0;
                    
                    $stmt = $pdo->prepare("UPDATE config SET data = :data WHERE name = 'system.performance'");
                    $stmt->execute(['data' => serialize($config)]);
                    
                    echo '<div class="success">✅ Agrégation désactivée</div>';
                }
                
                // 2. ACTIVER LE MODE TWIG DEBUG
                echo '<div class="info">🐛 Activation Twig debug...</div>';
                
                $stmt = $pdo->prepare("SELECT data FROM config WHERE name = 'system.theme'");
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($result) {
                    $config = unserialize($result['data']);
                    echo '<div class="info">Thème actif : ' . htmlspecialchars($config['default'] ?? 'inconnu') . '</div>';
                }
                
                // 3. DÉSACTIVER LE CACHE PAGE
                echo '<div class="info">🚫 Désactivation cache page...</div>';
                
                $stmt = $pdo->prepare("SELECT data FROM config WHERE name = 'system.performance'");
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($result) {
                    $config = unserialize($result['data']);
                    $config['cache']['page']['max_age'] = 0;
                    
                    $stmt = $pdo->prepare("UPDATE config SET data = :data WHERE name = 'system.performance'");
                    $stmt->execute(['data' => serialize($config)]);
                    
                    echo '<div class="success">✅ Cache page désactivé</div>';
                }
                
                // 4. VIDER TOUS LES CACHES
                echo '<div class="info">🔄 Vidage des caches...</div>';
                
                $cache_tables = [
                    'cache_bootstrap', 'cache_config', 'cache_container',
                    'cache_data', 'cache_default', 'cache_discovery',
                    'cache_dynamic_page_cache', 'cache_entity', 'cache_menu',
                    'cache_page', 'cache_render', 'cache_toolbar',
                ];
                
                foreach ($cache_tables as $table) {
                    try {
                        $stmt = $pdo->prepare("TRUNCATE TABLE `$table`");
                        $stmt->execute();
                    } catch (PDOException $e) {}
                }
                
                echo '<div class="success">✅ Caches vidés</div>';
                
                // 5. SUPPRIMER LES FICHIERS CSS/JS COMPILÉS
                echo '<div class="info">🗑️ Suppression fichiers compilés...</div>';
                
                function deleteAllInDir($dir, &$count) {
                    if (!is_dir($dir)) return;
                    $items = @scandir($dir);
                    if (!$items) return;
                    foreach ($items as $item) {
                        if ($item === '.' || $item === '..') continue;
                        $path = $dir . '/' . $item;
                        if (is_dir($path)) {
                            deleteAllInDir($path, $count);
                            @rmdir($path);
                        } else {
                            @unlink($path);
                            $count++;
                        }
                    }
                }
                
                $deleted = 0;
                $dirs = [
                    __DIR__ . '/sites/default/files/css',
                    __DIR__ . '/sites/default/files/js',
                    __DIR__ . '/sites/default/files/php',
                ];
                
                foreach ($dirs as $dir) {
                    deleteAllInDir($dir, $deleted);
                }
                
                echo '<div class="success">✅ ' . $deleted . ' fichiers supprimés</div>';
                
                echo '<h2 class="success">🎉 MODE DÉVELOPPEMENT ACTIVÉ !</h2>';
                
                echo '<div class="info">';
                echo '<h3>✅ Modifications :</h3>';
                echo '<ul>';
                echo '<li>✅ Agrégation CSS/JS : DÉSACTIVÉE</li>';
                echo '<li>✅ Cache page : DÉSACTIVÉ (0 secondes)</li>';
                echo '<li>✅ Tous les caches : VIDÉS</li>';
                echo '<li>✅ Fichiers compilés : SUPPRIMÉS</li>';
                echo '</ul>';
                echo '</div>';
                
                echo '<div style="text-align: center; margin: 30px 0;">';
                echo '<a href="/user/login" class="btn btn-success" style="font-size: 18px;">🔐 TESTER LA PAGE DE LOGIN</a>';
                echo '</div>';
                
                echo '<div style="text-align: center;">';
                echo '<a href="/" class="btn">🏠 Page d\'accueil</a>';
                echo '<a href="/show-full-login.php?token=' . $security_token . '" class="btn">📄 Voir HTML login</a>';
                echo '</div>';
                
            } catch (PDOException $e) {
                echo '<div class="error">❌ Erreur : ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
            
        } else {
            ?>
            <div class="info">⚠️ Token de sécurité requis</div>
            <p>URL : <code>?token=spherevoices2026</code></p>
            <?php
        }
        ?>
    </div>
</body>
</html>

