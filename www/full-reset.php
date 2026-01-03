<?php
/**
 * Réinitialisation TOTALE - Vide TOUS les caches (Opcache, APCu, Drupal, Twig)
 * URL: https://www.spherevoices.com/full-reset.php?token=spherevoices2026
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
    <title>🔄 Full Reset</title>
    <style>
        body { font-family: monospace; max-width: 1000px; margin: 30px auto; padding: 20px; background: #000; color: #0f0; }
        .container { background: #111; padding: 30px; border: 2px solid #0f0; }
        .success { color: #0f0; }
        .error { color: #f00; }
        .warning { color: #ff0; }
        .info { color: #0ff; }
        .btn { display: inline-block; padding: 15px 30px; margin: 10px; background: #0f0; color: #000; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 18px; }
        pre { background: #000; padding: 15px; border: 1px solid #0f0; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="success">🔄 RÉINITIALISATION TOTALE</h1>
        
        <?php
        $report = [];
        
        // 1. OPCACHE
        echo '<h2 class="info">1️⃣ Cache PHP Opcache</h2>';
        if (function_exists('opcache_reset')) {
            if (opcache_reset()) {
                echo '<p class="success">✅ Opcache vidé</p>';
                $report[] = '✅ Opcache';
            } else {
                echo '<p class="warning">⚠️ Opcache reset failed</p>';
                $report[] = '⚠️ Opcache (échec)';
            }
        } else {
            echo '<p class="warning">⚠️ Opcache non disponible</p>';
            $report[] = '⚠️ Opcache (non dispo)';
        }
        
        // 2. APCU
        echo '<h2 class="info">2️⃣ Cache APCu</h2>';
        if (function_exists('apcu_clear_cache')) {
            if (apcu_clear_cache()) {
                echo '<p class="success">✅ APCu vidé</p>';
                $report[] = '✅ APCu';
            } else {
                echo '<p class="warning">⚠️ APCu failed</p>';
                $report[] = '⚠️ APCu (échec)';
            }
        } else {
            echo '<p class="warning">⚠️ APCu non disponible</p>';
            $report[] = '⚠️ APCu (non dispo)';
        }
        
        // 3. REALPATH CACHE
        echo '<h2 class="info">3️⃣ Cache Realpath</h2>';
        if (function_exists('clearstatcache')) {
            clearstatcache(true);
            echo '<p class="success">✅ Realpath cache vidé</p>';
            $report[] = '✅ Realpath';
        }
        
        // 4. SUPPRIMER LES TEMPLATES FORM
        echo '<h2 class="info">4️⃣ Templates Form</h2>';
        $form_dir = __DIR__ . '/themes/custom/spherevoices_theme/templates/form';
        if (is_dir($form_dir)) {
            $deleted = 0;
            $files = glob($form_dir . '/*.twig');
            foreach ($files as $file) {
                if (@unlink($file)) $deleted++;
            }
            if ($deleted > 0) {
                echo '<p class="success">✅ ' . $deleted . ' templates form supprimés</p>';
                $report[] = '✅ Templates form: ' . $deleted;
            } else {
                echo '<p class="success">✅ Aucun template form à supprimer</p>';
                $report[] = '✅ Templates form: vide';
            }
        } else {
            echo '<p class="success">✅ Répertoire form/ n\'existe pas</p>';
            $report[] = '✅ Templates form: pas de dir';
        }
        
        // 5. SUPPRIMER TOUT LE CACHE TWIG
        echo '<h2 class="info">5️⃣ Cache Twig</h2>';
        function nukeAll($dir, &$count) {
            if (!is_dir($dir)) return;
            $items = @scandir($dir);
            if (!$items) return;
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') continue;
                $path = $dir . '/' . $item;
                if (is_dir($path)) {
                    nukeAll($path, $count);
                    @rmdir($path);
                } else {
                    @unlink($path);
                    $count++;
                }
            }
        }
        
        $twig_deleted = 0;
        nukeAll(__DIR__ . '/sites/default/files/php', $twig_deleted);
        echo '<p class="success">✅ ' . $twig_deleted . ' fichiers Twig/PHP supprimés</p>';
        $report[] = '✅ Twig: ' . $twig_deleted;
        
        // 6. CSS/JS
        echo '<h2 class="info">6️⃣ CSS/JS compilés</h2>';
        $css_deleted = 0;
        $js_deleted = 0;
        nukeAll(__DIR__ . '/sites/default/files/css', $css_deleted);
        nukeAll(__DIR__ . '/sites/default/files/js', $js_deleted);
        echo '<p class="success">✅ CSS: ' . $css_deleted . ' / JS: ' . $js_deleted . '</p>';
        $report[] = '✅ CSS: ' . $css_deleted;
        $report[] = '✅ JS: ' . $js_deleted;
        
        // 7. MYSQL
        echo '<h2 class="info">7️⃣ Tables MySQL</h2>';
        $databases = [];
        include __DIR__ . '/sites/default/settings.php';
        
        if (!empty($databases['default']['default'])) {
            $db = $databases['default']['default'];
            try {
                $pdo = new PDO(
                    "mysql:host={$db['host']};dbname={$db['database']};charset=utf8mb4",
                    $db['username'],
                    $db['password'],
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
                
                $tables = [
                    'cache_bootstrap', 'cache_config', 'cache_container',
                    'cache_data', 'cache_default', 'cache_discovery',
                    'cache_dynamic_page_cache', 'cache_entity', 'cache_menu',
                    'cache_page', 'cache_render', 'cache_toolbar',
                ];
                
                $cleared = 0;
                foreach ($tables as $table) {
                    try {
                        $stmt = $pdo->prepare("TRUNCATE TABLE `$table`");
                        $stmt->execute();
                        $cleared++;
                    } catch (PDOException $e) {}
                }
                
                echo '<p class="success">✅ ' . $cleared . ' tables vidées</p>';
                $report[] = '✅ MySQL: ' . $cleared . ' tables';
                
            } catch (PDOException $e) {
                echo '<p class="error">❌ MySQL: ' . htmlspecialchars($e->getMessage()) . '</p>';
                $report[] = '❌ MySQL: erreur';
            }
        }
        
        // 8. FORCER LA RÉINITIALISATION
        echo '<h2 class="info">8️⃣ Touches finales</h2>';
        
        // Toucher les fichiers critiques pour invalider le cache
        @touch(__DIR__ . '/themes/custom/spherevoices_theme/spherevoices_theme.info.yml');
        @touch(__DIR__ . '/sites/default/settings.php');
        
        echo '<p class="success">✅ Fichiers critiques touchés</p>';
        $report[] = '✅ Files touched';
        
        echo '<h2 class="success">🎉 RÉINITIALISATION TERMINÉE</h2>';
        
        echo '<pre class="success">';
        echo "RÉSUMÉ:\n\n";
        foreach ($report as $line) {
            echo "$line\n";
        }
        echo "\nTOTAL fichiers supprimés: " . ($twig_deleted + $css_deleted + $js_deleted) . "\n";
        echo '</pre>';
        
        echo '<div style="text-align: center; margin: 40px 0;">';
        echo '<a href="/user/login" class="btn">🔐 TESTER LE LOGIN</a>';
        echo '<a href="/" class="btn">🏠 PAGE D\'ACCUEIL</a>';
        echo '</div>';
        
        echo '<p class="warning" style="text-align: center; font-size: 18px;">⚡ FAITES CTRL+SHIFT+R POUR FORCER LE RECHARGEMENT ⚡</p>';
        ?>
    </div>
</body>
</html>

