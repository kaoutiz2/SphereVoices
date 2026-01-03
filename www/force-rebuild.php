<?php
/**
 * Force la régénération complète de tous les assets
 * URL: https://www.spherevoices.com/force-rebuild.php?token=spherevoices2026
 */

$security_token = 'spherevoices2026';
$provided_token = $_GET['token'] ?? '';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>🔄 Force Rebuild</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #28a745; padding: 10px; background: #d4edda; border-radius: 4px; margin: 10px 0; }
        .error { color: #dc3545; padding: 10px; background: #f8d7da; border-radius: 4px; margin: 10px 0; }
        .warning { color: #856404; padding: 10px; background: #fff3cd; border-radius: 4px; margin: 10px 0; }
        .info { color: #0c5460; padding: 10px; background: #d1ecf1; border-radius: 4px; margin: 10px 0; }
        .btn { display: inline-block; padding: 10px 20px; margin: 5px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; }
        .btn:hover { background: #0056b3; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔄 Force Rebuild Complet</h1>
        
        <?php
        if ($provided_token === $security_token) {
            
            $drupal_root = __DIR__;
            
            if (!file_exists($drupal_root . '/autoload.php')) {
                echo '<div class="error">❌ Drupal non trouvé</div>';
                exit;
            }
            
            try {
                echo '<div class="info">🚀 Chargement de Drupal...</div>';
                
                require_once $drupal_root . '/autoload.php';
                $autoloader = require $drupal_root . '/autoload.php';
                
                $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
                $kernel = \Drupal\Core\DrupalKernel::createFromRequest($request, $autoloader, 'prod');
                $kernel->boot();
                $kernel->prepareLegacyRequest($request);
                
                echo '<div class="success">✅ Drupal chargé</div>';
                
                // 1. DÉSACTIVER L'AGRÉGATION CSS/JS
                echo '<div class="info">📦 Désactivation de l\'agrégation CSS/JS...</div>';
                
                $config = \Drupal::configFactory()->getEditable('system.performance');
                $css_aggregation_was = $config->get('css.preprocess');
                $js_aggregation_was = $config->get('js.preprocess');
                
                $config->set('css.preprocess', FALSE);
                $config->set('js.preprocess', FALSE);
                $config->save();
                
                echo '<div class="success">✅ Agrégation désactivée (CSS: ' . ($css_aggregation_was ? 'était ON' : 'était OFF') . ', JS: ' . ($js_aggregation_was ? 'était ON' : 'était OFF') . ')</div>';
                
                // 2. SUPPRIMER TOUS LES FICHIERS CSS/JS/PHP/TWIG
                echo '<div class="info">🗑️ Suppression TOTALE des caches...</div>';
                
                function deleteAllFiles($dir, &$count) {
                    if (!is_dir($dir)) {
                        return;
                    }
                    
                    $items = @scandir($dir);
                    if (!$items) {
                        return;
                    }
                    
                    foreach ($items as $item) {
                        if ($item === '.' || $item === '..') {
                            continue;
                        }
                        
                        $path = $dir . '/' . $item;
                        
                        if (is_dir($path)) {
                            deleteAllFiles($path, $count);
                            @rmdir($path);
                        } else {
                            @unlink($path);
                            $count++;
                        }
                    }
                }
                
                $deleted_total = 0;
                $dirs_to_clean = [
                    'CSS' => __DIR__ . '/sites/default/files/css',
                    'JS' => __DIR__ . '/sites/default/files/js',
                    'PHP' => __DIR__ . '/sites/default/files/php',
                ];
                
                foreach ($dirs_to_clean as $label => $dir) {
                    $deleted = 0;
                    deleteAllFiles($dir, $deleted);
                    $deleted_total += $deleted;
                    echo '<div class="success">✅ ' . $label . ' : ' . $deleted . ' fichiers supprimés</div>';
                }
                
                echo '<div class="success">🎉 TOTAL : ' . $deleted_total . ' fichiers supprimés</div>';
                
                // 3. VIDER LE CACHE DRUPAL COMPLET
                echo '<div class="info">🔄 Vidage du cache Drupal complet...</div>';
                
                drupal_flush_all_caches();
                
                echo '<div class="success">✅ Cache Drupal vidé (équivalent drush cr)</div>';
                
                // 4. RÉACTIVER L'AGRÉGATION
                echo '<div class="info">📦 Réactivation de l\'agrégation CSS/JS...</div>';
                
                $config = \Drupal::configFactory()->getEditable('system.performance');
                $config->set('css.preprocess', TRUE);
                $config->set('js.preprocess', TRUE);
                $config->save();
                
                echo '<div class="success">✅ Agrégation réactivée</div>';
                
                // 5. VIDER À NOUVEAU LE CACHE
                echo '<div class="info">🔄 Vidage final du cache...</div>';
                
                drupal_flush_all_caches();
                
                echo '<div class="success">✅ Cache final vidé</div>';
                
                // 6. INFORMATIONS SUR LE THÈME
                echo '<div class="info">🎨 Vérification du thème...</div>';
                
                $theme_handler = \Drupal::service('theme_handler');
                $default_theme = \Drupal::config('system.theme')->get('default');
                
                echo '<div class="success">✅ Thème actif : ' . htmlspecialchars($default_theme) . '</div>';
                
                $theme_path = $theme_handler->getTheme($default_theme)->getPath();
                echo '<div class="info">📁 Chemin : ' . htmlspecialchars($theme_path) . '</div>';
                
                // Vérifier les fichiers CSS du thème
                $css_files = glob(__DIR__ . '/' . $theme_path . '/css/*.css');
                if ($css_files) {
                    echo '<div class="success">✅ ' . count($css_files) . ' fichiers CSS trouvés dans le thème</div>';
                    echo '<ul>';
                    foreach ($css_files as $css) {
                        $size = filesize($css);
                        echo '<li>' . htmlspecialchars(basename($css)) . ' (' . number_format($size) . ' bytes)</li>';
                    }
                    echo '</ul>';
                } else {
                    echo '<div class="error">❌ Aucun fichier CSS trouvé dans le thème !</div>';
                }
                
                echo '<h2 class="success">🎉 REBUILD TERMINÉ !</h2>';
                
                echo '<div class="info">';
                echo '<h3>✅ Actions effectuées :</h3>';
                echo '<ol>';
                echo '<li>✅ Agrégation CSS/JS désactivée temporairement</li>';
                echo '<li>✅ ' . $deleted_total . ' fichiers de cache supprimés</li>';
                echo '<li>✅ Cache Drupal vidé (2x)</li>';
                echo '<li>✅ Agrégation réactivée</li>';
                echo '<li>✅ Thème vérifié</li>';
                echo '</ol>';
                echo '</div>';
                
                echo '<div class="warning">';
                echo '<h3>⚡ Prochaines étapes :</h3>';
                echo '<ol>';
                echo '<li><strong>Ctrl+Shift+R</strong> sur toutes les pages</li>';
                echo '<li>Testez la page d\'accueil</li>';
                echo '<li>Testez la page de login</li>';
                echo '<li>Les CSS/JS vont se régénérer automatiquement</li>';
                echo '</ol>';
                echo '</div>';
                
                echo '<p>';
                echo '<a href="/" class="btn">🏠 Page d\'accueil</a>';
                echo '<a href="/user/login" class="btn">🔐 Login</a>';
                echo '<a href="/check-login-form.php?token=' . $security_token . '" class="btn">🔍 Diagnostic</a>';
                echo '</p>';
                
            } catch (\Exception $e) {
                echo '<div class="error">❌ Erreur : ' . htmlspecialchars($e->getMessage()) . '</div>';
                echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
            }
            
        } else {
            ?>
            <div class="warning">⚠️ Ce script force la régénération complète des assets.</div>
            
            <form method="get">
                <label for="token">Token de sécurité:</label><br>
                <input type="text" id="token" name="token" value="" style="width: 300px; padding: 5px; margin: 10px 0;">
                <br>
                <button type="submit" class="btn">Lancer le rebuild</button>
            </form>
            
            <h3>📋 Ce que fait ce script :</h3>
            <ol>
                <li>Désactive temporairement l'agrégation CSS/JS</li>
                <li>Supprime TOUS les fichiers CSS/JS/PHP/Twig compilés</li>
                <li>Vide le cache Drupal complet</li>
                <li>Réactive l'agrégation</li>
                <li>Force Drupal à tout régénérer</li>
            </ol>
            
            <h3>🔗 URL :</h3>
            <pre>https://www.spherevoices.com/force-rebuild.php?token=spherevoices2026</pre>
            <?php
        }
        ?>
    </div>
</body>
</html>

