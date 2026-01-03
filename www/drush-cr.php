<?php
/**
 * Vider le cache Drupal COMPLET (comme drush cr)
 * URL: https://www.spherevoices.com/www/drush-cr.php?token=spherevoices2026
 */

$security_token = 'spherevoices2026';
$provided_token = $_GET['token'] ?? '';

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
        .btn { display: inline-block; padding: 10px 20px; margin: 10px 5px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; border: none; cursor: pointer; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔄 Drush Cache Rebuild</h1>
        
        <?php
        if ($provided_token === $security_token) {
            echo '<div class="info">🚀 Vidage COMPLET du cache Drupal (équivalent drush cr)...</div>';
            
            $drupal_root = __DIR__;
            
            if (!file_exists($drupal_root . '/autoload.php')) {
                echo '<div class="error">❌ Drupal non trouvé</div>';
                exit;
            }
            
            try {
                // Charger Drupal
                require_once $drupal_root . '/autoload.php';
                $autoloader = require $drupal_root . '/autoload.php';
                
                // Bootstrap Drupal
                $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
                $kernel = \Drupal\Core\DrupalKernel::createFromRequest($request, $autoloader, 'prod');
                $kernel->boot();
                $kernel->prepareLegacyRequest($request);
                
                echo '<div class="success">✅ Drupal chargé</div>';
                
                // VIDAGE COMPLET DU CACHE (équivalent drush cr)
                echo '<div class="info">🔄 Exécution de drupal_flush_all_caches()...</div>';
                drupal_flush_all_caches();
                echo '<div class="success">✅ Cache complet vidé !</div>';
                
                // Invalidations supplémentaires
                echo '<div class="info">🎨 Invalidation des assets CSS/JS...</div>';
                \Drupal::service('asset.css.collection_optimizer')->deleteAll();
                \Drupal::service('asset.js.collection_optimizer')->deleteAll();
                echo '<div class="success">✅ Assets invalidés</div>';
                
                // Rebuild des routes
                echo '<div class="info">🛣️ Reconstruction des routes...</div>';
                \Drupal::service('router.builder')->rebuild();
                echo '<div class="success">✅ Routes reconstruites</div>';
                
                // Invalidation des tags de cache
                echo '<div class="info">🏷️ Invalidation des tags de cache...</div>';
                \Drupal\Core\Cache\Cache::invalidateTags(['rendered', 'config:core.extension']);
                echo '<div class="success">✅ Tags invalidés</div>';
                
                // Rebuild du container
                echo '<div class="info">📦 Rebuild du container...</div>';
                $kernel->invalidateContainer();
                echo '<div class="success">✅ Container invalidé</div>';
                
                echo '<h2 class="success">🎉 CACHE VIDÉ COMPLÈTEMENT !</h2>';
                echo '<div class="info">';
                echo '<p><strong>Équivalent de : <code>drush cr</code></strong></p>';
                echo '<ul>';
                echo '<li>✅ Tous les caches vidés</li>';
                echo '<li>✅ Routes reconstruites</li>';
                echo '<li>✅ Container Drupal invalidé</li>';
                echo '<li>✅ Assets CSS/JS invalidés</li>';
                echo '<li>✅ Templates rechargés</li>';
                echo '</ul>';
                echo '</div>';
                
                echo '<div class="warning">';
                echo '<p><strong>⚠️ IMPORTANT :</strong></p>';
                echo '<p>Actualisez maintenant le site avec <strong>Ctrl+Shift+R</strong></p>';
                echo '<p>Les changements devraient être visibles immédiatement !</p>';
                echo '</div>';
                
                echo '<p><a href="/" class="btn">← Retour au site</a></p>';
                
            } catch (\Exception $e) {
                echo '<div class="error">❌ Erreur : ' . htmlspecialchars($e->getMessage()) . '</div>';
                echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
                
                echo '<div class="warning">';
                echo '<h3>💡 Solution alternative : Drush via SSH</h3>';
                echo '<p>Si ce script ne fonctionne pas, connectez-vous en SSH et exécutez :</p>';
                echo '<pre>cd ~/www && ../vendor/bin/drush cr</pre>';
                echo '</div>';
            }
            
        } else {
            ?>
            <div class="warning">⚠️ Ce script vide COMPLÈTEMENT le cache Drupal (équivalent drush cr)</div>
            
            <form method="get">
                <label for="token">Token de sécurité:</label><br>
                <input type="text" id="token" name="token" value="" style="width: 300px; padding: 5px; margin: 10px 0;">
                <br>
                <button type="submit" class="btn">Vider le cache</button>
            </form>
            
            <h3>📝 Ce script fait :</h3>
            <ol>
                <li>✅ drupal_flush_all_caches()</li>
                <li>✅ Invalidation CSS/JS</li>
                <li>✅ Rebuild des routes</li>
                <li>✅ Invalidation du container</li>
                <li>✅ Équivalent de <code>drush cr</code></li>
            </ol>
            
            <h3>🔗 URL directe :</h3>
            <pre>https://www.spherevoices.com/www/drush-cr.php?token=spherevoices2026</pre>
            <?php
        }
        ?>
    </div>
</body>
</html>

