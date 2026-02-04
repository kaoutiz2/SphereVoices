<?php
/**
 * DRUSH CR à la RACINE - Vidage cache complet
 * URL: https://www.spherevoices.com/drush-cr.php?token=spherevoices2026
 */

$security_token = 'spherevoices2026';
$provided_token = $_GET['token'] ?? '';

// Afficher les erreurs pour debug (à retirer après diagnostic)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>🔄 Drush Cache Rebuild</title>
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
        <h1>🔄 Drush Cache Rebuild (Racine)</h1>
        
        <?php
        if ($provided_token === $security_token) {
            echo '<div class="info">🚀 Vidage COMPLET du cache (équivalent drush cr)...</div>';
            
            $drupal_root = __DIR__;  // On est déjà dans www/
            
            echo '<div class="info">Chemin Drupal : ' . htmlspecialchars($drupal_root) . '</div>';
            
            if (!file_exists($drupal_root . '/autoload.php')) {
                echo '<div class="error">❌ Drupal non trouvé dans : ' . htmlspecialchars($drupal_root) . '</div>';
                echo '<div class="info">Fichiers présents :</div><pre>';
                print_r(scandir($drupal_root));
                echo '</pre>';
                exit;
            }
            
            try {
                // Forcer l'environnement de prod pour charger .env.production
                if (!getenv('DRUPAL_ENV')) {
                    putenv('DRUPAL_ENV=production');
                    $_ENV['DRUPAL_ENV'] = 'production';
                    $_SERVER['DRUPAL_ENV'] = 'production';
                }

                // Définir un HTTP_HOST valide si absent (nécessaire pour settings.php)
                if (empty($_SERVER['HTTP_HOST'])) {
                    $_SERVER['HTTP_HOST'] = 'www.spherevoices.com';
                }

                // Charger Drupal
                require_once $drupal_root . '/autoload.php';
                $autoloader = require $drupal_root . '/autoload.php';
                
                // Bootstrap Drupal
                $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
                $kernel = \Drupal\Core\DrupalKernel::createFromRequest($request, $autoloader, 'prod');
                $kernel->boot();

                // Enregistrer la requête dans la stack (Drupal 10+)
                \Drupal::setContainer($kernel->getContainer());
                $kernel->getContainer()->get('request_stack')->push($request);
                
                echo '<div class="success">✅ Drupal chargé avec succès</div>';
                
                // VIDAGE COMPLET (équivalent drush cr)
                echo '<div class="info">🔄 Exécution de drupal_flush_all_caches()...</div>';
                drupal_flush_all_caches();
                echo '<div class="success">✅ drupal_flush_all_caches() terminé !</div>';
                
                // Assets CSS/JS
                echo '<div class="info">🎨 Invalidation des assets...</div>';
                \Drupal::service('asset.css.collection_optimizer')->deleteAll();
                \Drupal::service('asset.js.collection_optimizer')->deleteAll();
                echo '<div class="success">✅ Assets CSS/JS invalidés</div>';
                
                // Routes
                echo '<div class="info">🛣️ Reconstruction des routes...</div>';
                \Drupal::service('router.builder')->rebuild();
                echo '<div class="success">✅ Routes reconstruites</div>';
                
                // Cache tags
                echo '<div class="info">🏷️ Invalidation des tags...</div>';
                \Drupal\Core\Cache\Cache::invalidateTags(['rendered', 'config:core.extension', 'library_info']);
                echo '<div class="success">✅ Tags invalidés</div>';
                
                // Container
                echo '<div class="info">📦 Invalidation du container...</div>';
                $kernel->invalidateContainer();
                echo '<div class="success">✅ Container invalidé</div>';
                
                echo '<h2 class="success">🎉 CACHE VIDÉ COMPLÈTEMENT !</h2>';
                echo '<div class="info">';
                echo '<p><strong>✅ Équivalent de : <code>drush cr</code></strong></p>';
                echo '<ul>';
                echo '<li>✅ Tous les caches Drupal vidés</li>';
                echo '<li>✅ Templates Twig recompilés</li>';
                echo '<li>✅ Routes reconstruites</li>';
                echo '<li>✅ Container Drupal invalidé</li>';
                echo '<li>✅ Assets CSS/JS rechargés</li>';
                echo '</ul>';
                echo '</div>';
                
                echo '<div class="warning">';
                echo '<h3>⚠️ IMPORTANT - Prochaines étapes :</h3>';
                echo '<ol>';
                echo '<li><strong>Actualisez le site</strong> avec <code>Ctrl+Shift+R</code></li>';
                echo '<li><strong>Testez</strong> : <a href="/www/">https://www.spherevoices.com/www/</a></li>';
                echo '<li>Les inputs, la galerie, tout devrait fonctionner !</li>';
                echo '</ol>';
                echo '</div>';
                
                echo '<p><a href="/www/" class="btn">← Aller sur le site</a></p>';
                
            } catch (\Exception $e) {
                echo '<div class="error">❌ Erreur : ' . htmlspecialchars($e->getMessage()) . '</div>';
                echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
                
                echo '<div class="warning">';
                echo '<h3>💡 Solution alternative : Via SSH</h3>';
                echo '<pre>ssh votre_user@ssh.clusterXXX.hosting.ovh.net
cd ~/www
../vendor/bin/drush cr</pre>';
                echo '</div>';
            }
            
        } else {
            ?>
            <div class="warning">⚠️ Ce script fait un vidage COMPLET du cache Drupal.</div>
            
            <form method="get">
                <label for="token">Token de sécurité:</label><br>
                <input type="text" id="token" name="token" value="" style="width: 300px; padding: 5px; margin: 10px 0;">
                <br>
                <button type="submit" class="btn">Vider le cache (drush cr)</button>
            </form>
            
            <h3>📝 Équivalent de :</h3>
            <pre>drush cr</pre>
            
            <h3>🔗 URL directe :</h3>
            <pre>https://www.spherevoices.com/drush-cr.php?token=spherevoices2026</pre>
            
            <p><small>Script à la racine du projet pour éviter les redirections.</small></p>
            <?php
        }
        ?>
    </div>
</body>
</html>

