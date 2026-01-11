<?php
/**
 * Script de vidage de cache accessible via le web
 * URL: https://www.spherevoices.com/www/clear-cache-web.php
 * 
 * Ce script vide le cache Drupal sans nécessiter d'accès SSH
 * Il peut être appelé manuellement ou automatiquement après un déploiement
 * 
 * SÉCURITÉ: Supprimez ce fichier après utilisation ou ajoutez une protection par mot de passe
 */

// Définir le type de contenu
header('Content-Type: text/html; charset=utf-8');

// Vérifier si on a un token de sécurité (optionnel)
$security_token = 'spherevoices2026'; // Changez cette valeur !
$provided_token = $_GET['token'] ?? '';

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Vidage du cache Drupal</title>
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
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        .info { color: #17a2b8; }
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
        <h1>🔄 Vidage du cache Drupal</h1>
        
        <?php
        // Si le token est fourni et correct, vider le cache
        if ($provided_token === $security_token) {
            echo '<p class="info">🚀 Début du vidage du cache...</p>';
            
            $drupal_root = __DIR__;
            
            // Vérifier que Drupal est accessible
            if (!file_exists($drupal_root . '/autoload.php')) {
                echo '<p class="error">❌ Erreur: Drupal non trouvé dans ' . htmlspecialchars($drupal_root) . '</p>';
                exit;
            }
            
            try {
                // Charger Drupal
                require_once $drupal_root . '/autoload.php';
                $autoloader = require $drupal_root . '/autoload.php';
                $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
                $kernel = \Drupal\Core\DrupalKernel::createFromRequest($request, $autoloader, 'prod');
                
                $kernel->boot();
                $kernel->prepareLegacyRequest($request);
                
                echo '<p class="success">✅ Drupal chargé avec succès</p>';
                
                // Vider tous les caches
                echo '<p class="info">🔄 Vidage des caches en cours...</p>';
                drupal_flush_all_caches();
                echo '<p class="success">✅ Tous les caches ont été vidés</p>';
                
                // Invalider les caches CSS/JS
                \Drupal::service('asset.css.collection_optimizer')->deleteAll();
                \Drupal::service('asset.js.collection_optimizer')->deleteAll();
                echo '<p class="success">✅ Caches CSS/JS invalidés</p>';
                
                // Reconstruire les routes
                \Drupal::service('router.builder')->rebuild();
                echo '<p class="success">✅ Routes reconstruites</p>';
                
                // Invalider les caches de rendu
                \Drupal\Core\Cache\Cache::invalidateTags(['rendered']);
                echo '<p class="success">✅ Caches de rendu invalidés</p>';
                
                echo '<h2 class="success">🎉 Vidage du cache terminé avec succès!</h2>';
                echo '<p>Le site affiche maintenant la dernière version.</p>';
                echo '<p><a href="/" class="btn">← Retour au site</a></p>';
                
            } catch (\Exception $e) {
                echo '<p class="error">❌ Erreur: ' . htmlspecialchars($e->getMessage()) . '</p>';
                echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
            }
            
        } else {
            // Afficher le formulaire
            ?>
            <p class="warning">⚠️ Ce script vide le cache de Drupal.</p>
            <p>Pour des raisons de sécurité, un token est requis.</p>
            
            <form method="get">
                <label for="token">Token de sécurité:</label><br>
                <input type="text" id="token" name="token" style="width: 300px; padding: 5px; margin: 10px 0;">
                <br>
                <button type="submit" class="btn">Vider le cache</button>
            </form>
            
            <hr>
            
            <h3>📝 Utilisation</h3>
            <p><strong>URL avec token:</strong></p>
            <pre>https://www.spherevoices.com/www/clear-cache-web.php?token=<?php echo htmlspecialchars($security_token); ?></pre>
            
            <h3>🔒 Sécurité</h3>
            <ul>
                <li>Changez le token dans le code source de ce fichier</li>
                <li>Supprimez ce fichier après utilisation</li>
                <li>Ou ajoutez une protection par .htaccess</li>
            </ul>
            
            <h3>🚀 Alternatives</h3>
            <ul>
                <li><strong>Via Drush:</strong> <code>vendor/bin/drush cr</code></li>
                <li><strong>Via script:</strong> <code>php ../post-deploy.php</code></li>
                <li><strong>Via interface:</strong> Configuration > Development > Performance</li>
            </ul>
            <?php
        }
        ?>
    </div>
</body>
</html>


