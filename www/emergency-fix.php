<?php
/**
 * Script d'urgence : Désactiver maintenance + Vider cache
 * URL: https://www.spherevoices.com/www/emergency-fix.php?token=spherevoices2026
 * 
 * Ce script bypass le mode maintenance et vide le cache
 */

// Token de sécurité
$security_token = 'spherevoices2026';
$provided_token = $_GET['token'] ?? '';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>🚨 Réparation d'urgence</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { color: #333; }
        .success { color: #28a745; padding: 10px; background: #d4edda; border-radius: 4px; margin: 10px 0; }
        .error { color: #dc3545; padding: 10px; background: #f8d7da; border-radius: 4px; margin: 10px 0; }
        .warning { color: #856404; padding: 10px; background: #fff3cd; border-radius: 4px; margin: 10px 0; }
        .info { color: #0c5460; padding: 10px; background: #d1ecf1; border-radius: 4px; margin: 10px 0; }
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px 5px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚨 Réparation d'urgence Drupal</h1>
        
        <?php
        if ($provided_token === $security_token) {
            echo '<div class="info">🚀 Début de la réparation d\'urgence...</div>';
            
            $drupal_root = __DIR__;
            
            // Vérifier Drupal
            if (!file_exists($drupal_root . '/autoload.php')) {
                echo '<div class="error">❌ Erreur: Drupal non trouvé</div>';
                exit;
            }
            
            try {
                // Charger Drupal
                require_once $drupal_root . '/autoload.php';
                $autoloader = require $drupal_root . '/autoload.php';
                
                // Créer une requête sans passer par le système de maintenance
                $_SERVER['SCRIPT_NAME'] = '/index.php';
                $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
                
                $kernel = \Drupal\Core\DrupalKernel::createFromRequest($request, $autoloader, 'prod');
                $kernel->boot();
                $kernel->prepareLegacyRequest($request);
                
                echo '<div class="success">✅ Drupal chargé avec succès</div>';
                
                // 1. DÉSACTIVER LE MODE MAINTENANCE
                echo '<div class="info">🔧 Désactivation du mode maintenance...</div>';
                \Drupal::state()->set('system.maintenance_mode', FALSE);
                echo '<div class="success">✅ Mode maintenance DÉSACTIVÉ</div>';
                
                // 2. VIDER TOUS LES CACHES
                echo '<div class="info">🔄 Vidage des caches en cours...</div>';
                drupal_flush_all_caches();
                echo '<div class="success">✅ Tous les caches vidés</div>';
                
                // 3. INVALIDER CACHES CSS/JS
                echo '<div class="info">🎨 Invalidation des caches CSS/JS...</div>';
                \Drupal::service('asset.css.collection_optimizer')->deleteAll();
                \Drupal::service('asset.js.collection_optimizer')->deleteAll();
                echo '<div class="success">✅ Caches CSS/JS invalidés</div>';
                
                // 4. RECONSTRUIRE LES ROUTES
                echo '<div class="info">🛣️ Reconstruction des routes...</div>';
                \Drupal::service('router.builder')->rebuild();
                echo '<div class="success">✅ Routes reconstruites</div>';
                
                echo '<h2 class="success">🎉 RÉPARATION TERMINÉE !</h2>';
                echo '<div class="info">';
                echo '<p><strong>Le site est maintenant :</strong></p>';
                echo '<ul>';
                echo '<li>✅ Mode maintenance DÉSACTIVÉ</li>';
                echo '<li>✅ Cache vidé complètement</li>';
                echo '<li>✅ Prêt à fonctionner</li>';
                echo '</ul>';
                echo '</div>';
                
                echo '<p><a href="/" class="btn">← Retour au site</a></p>';
                
            } catch (\Exception $e) {
                echo '<div class="error">❌ Erreur: ' . htmlspecialchars($e->getMessage()) . '</div>';
                echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
            }
            
        } else {
            // Formulaire de token
            ?>
            <div class="warning">⚠️ Ce script désactive le mode maintenance et vide le cache.</div>
            <p>Token requis pour sécurité.</p>
            
            <form method="get">
                <label for="token">Token de sécurité:</label><br>
                <input type="text" id="token" name="token" value="" style="width: 300px; padding: 5px; margin: 10px 0;">
                <br>
                <button type="submit" class="btn">Réparer maintenant</button>
            </form>
            
            <hr>
            
            <h3>📝 Ce script va :</h3>
            <ol>
                <li>✅ Désactiver le mode maintenance</li>
                <li>✅ Vider tous les caches Drupal</li>
                <li>✅ Invalider les caches CSS/JS</li>
                <li>✅ Reconstruire les routes</li>
            </ol>
            
            <h3>🔗 URL avec token :</h3>
            <pre>https://www.spherevoices.com/www/emergency-fix.php?token=<?php echo htmlspecialchars($security_token); ?></pre>
            <?php
        }
        ?>
    </div>
</body>
</html>

