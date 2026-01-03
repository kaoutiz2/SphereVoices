<?php
/**
 * Nettoyage ULTRA PROFOND - Supprime TOUT
 * URL: https://www.spherevoices.com/nuclear-clean.php?token=spherevoices2026
 */

$security_token = 'spherevoices2026';
$provided_token = $_GET['token'] ?? '';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>☢️ Nuclear Clean</title>
    <style>
        body { font-family: monospace; max-width: 1000px; margin: 30px auto; padding: 20px; background: #1a1a1a; color: #0f0; }
        .container { background: #000; padding: 30px; border: 2px solid #0f0; border-radius: 4px; }
        .success { color: #0f0; }
        .error { color: #f00; }
        .warning { color: #ff0; }
        .info { color: #0ff; }
        .btn { display: inline-block; padding: 12px 24px; margin: 5px; background: #0f0; color: #000; text-decoration: none; border-radius: 4px; font-weight: bold; }
        pre { background: #111; padding: 10px; border: 1px solid #0f0; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="error">☢️ NETTOYAGE NUCLÉAIRE ☢️</h1>
        
        <?php
        if ($provided_token === $security_token) {
            
            echo '<p class="warning">⚠️ SUPPRESSION TOTALE EN COURS...</p>';
            
            $report = [];
            
            // 1. SUPPRIMER PHYSIQUEMENT LES TEMPLATES FORM
            echo '<p class="info">🗑️ Suppression templates form...</p>';
            
            $form_dir = __DIR__ . '/themes/custom/spherevoices_theme/templates/form';
            $backup_dir = __DIR__ . '/themes/custom/spherevoices_theme/templates/_backup_form';
            
            if (is_dir($form_dir)) {
                $files = glob($form_dir . '/*.twig');
                $deleted = 0;
                foreach ($files as $file) {
                    if (@unlink($file)) {
                        $deleted++;
                        $report[] = "✅ Supprimé: " . basename($file);
                    }
                }
                echo '<p class="success">✅ Templates form: ' . $deleted . ' fichiers supprimés</p>';
            } else {
                echo '<p class="success">✅ Répertoire form/ déjà vide</p>';
            }
            
            // 2. SUPPRIMER TOUT LE CACHE TWIG
            echo '<p class="info">🗑️ Suppression TOTALE du cache Twig...</p>';
            
            function nukedir($dir, &$count) {
                if (!is_dir($dir)) return;
                $items = @scandir($dir);
                if (!$items) return;
                foreach ($items as $item) {
                    if ($item === '.' || $item === '..') continue;
                    $path = $dir . '/' . $item;
                    if (is_dir($path)) {
                        nukedir($path, $count);
                        @rmdir($path);
                    } else {
                        @unlink($path);
                        $count++;
                    }
                }
            }
            
            $twig_deleted = 0;
            $twig_dir = __DIR__ . '/sites/default/files/php';
            nukedir($twig_dir, $twig_deleted);
            echo '<p class="success">✅ Cache Twig: ' . $twig_deleted . ' fichiers supprimés</p>';
            
            // 3. SUPPRIMER TOUT LE CACHE CSS/JS
            echo '<p class="info">🗑️ Suppression CSS/JS compilés...</p>';
            
            $css_deleted = 0;
            $css_dir = __DIR__ . '/sites/default/files/css';
            nukedir($css_dir, $css_deleted);
            
            $js_deleted = 0;
            $js_dir = __DIR__ . '/sites/default/files/js';
            nukedir($js_dir, $js_deleted);
            
            echo '<p class="success">✅ CSS: ' . $css_deleted . ' fichiers supprimés</p>';
            echo '<p class="success">✅ JS: ' . $js_deleted . ' fichiers supprimés</p>';
            
            // 4. VIDER LES TABLES DE CACHE
            echo '<p class="info">🗑️ Vidage des tables MySQL...</p>';
            
            $settings_file = __DIR__ . '/sites/default/settings.php';
            if (file_exists($settings_file)) {
                $databases = [];
                include $settings_file;
                
                if (!empty($databases['default']['default'])) {
                    $db = $databases['default']['default'];
                    
                    try {
                        $pdo = new PDO(
                            "mysql:host={$db['host']};dbname={$db['database']};charset=utf8mb4",
                            $db['username'],
                            $db['password'],
                            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                        );
                        
                        $cache_tables = [
                            'cache_bootstrap', 'cache_config', 'cache_container',
                            'cache_data', 'cache_default', 'cache_discovery',
                            'cache_dynamic_page_cache', 'cache_entity', 'cache_menu',
                            'cache_page', 'cache_render', 'cache_toolbar',
                        ];
                        
                        $cleared = 0;
                        foreach ($cache_tables as $table) {
                            try {
                                $stmt = $pdo->prepare("TRUNCATE TABLE `$table`");
                                $stmt->execute();
                                $cleared++;
                            } catch (PDOException $e) {}
                        }
                        
                        echo '<p class="success">✅ MySQL: ' . $cleared . ' tables vidées</p>';
                        
                    } catch (PDOException $e) {
                        echo '<p class="error">❌ MySQL: ' . htmlspecialchars($e->getMessage()) . '</p>';
                    }
                }
            }
            
            // 5. VÉRIFICATION
            echo '<h2 class="success">✅ NETTOYAGE TERMINÉ</h2>';
            
            echo '<pre class="success">';
            echo "TOTAL SUPPRIMÉ:\n";
            echo "  - Templates form\n";
            echo "  - Twig: " . $twig_deleted . " fichiers\n";
            echo "  - CSS: " . $css_deleted . " fichiers\n";
            echo "  - JS: " . $js_deleted . " fichiers\n";
            echo "  - MySQL: tables vidées\n";
            echo "\nTOTAL: " . ($twig_deleted + $css_deleted + $js_deleted) . " fichiers\n";
            echo '</pre>';
            
            // Vérifier que tout est bien vide
            echo '<h2 class="info">🔍 VÉRIFICATION</h2>';
            
            $checks = [
                'Templates form' => $form_dir,
                'Cache Twig' => $twig_dir,
                'CSS compilés' => $css_dir,
                'JS compilés' => $js_dir,
            ];
            
            echo '<pre>';
            foreach ($checks as $label => $dir) {
                if (!is_dir($dir)) {
                    echo "$label: ✅ VIDE (répertoire inexistant)\n";
                } else {
                    $files = glob($dir . '/*');
                    if (empty($files)) {
                        echo "$label: ✅ VIDE\n";
                    } else {
                        echo "$label: ⚠️ " . count($files) . " fichiers restants\n";
                    }
                }
            }
            echo '</pre>';
            
            echo '<div style="text-align: center; margin: 30px 0;">';
            echo '<a href="/user/login" class="btn" style="font-size: 20px;">🔐 TESTER LE LOGIN MAINTENANT</a>';
            echo '</div>';
            
            echo '<p class="warning" style="text-align: center;">⚠️ Les fichiers vont se régénérer automatiquement quand vous accédez au site</p>';
            
        } else {
            ?>
            <p class="error">⚠️ TOKEN DE SÉCURITÉ REQUIS</p>
            <p>URL: <code>?token=spherevoices2026</code></p>
            <?php
        }
        ?>
    </div>
</body>
</html>

