<?php
/**
 * Vider le cache Drupal COMPLET (comme drush cr)
 * URL: https://www.spherevoices.com/drush-cr.php?token=spherevoices2026
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
        ul { margin: 0; padding-left: 1.2rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔄 Drush Cache Rebuild</h1>
        
        <?php
        if ($provided_token === $security_token) {
            echo '<div class="info">🚀 Vidage COMPLET du cache Drupal (équivalent drush cr)...</div>';
            
            if (!file_exists(__DIR__ . '/autoload.php')) {
                echo '<div class="error">❌ Drupal non trouvé</div>';
                exit;
            }
            
            try {
                require_once __DIR__ . '/spherevoices-ops-bootstrap.inc.php';
                $kernel = spherevoices_ops_bootstrap_drupal(__DIR__);
                echo '<div class="success">✅ Drupal chargé</div>';
                
                echo '<div class="info">🔄 Rebuild du cache...</div>';
                $log = spherevoices_ops_rebuild_cache($kernel);
                echo spherevoices_ops_render_rebuild_log($log);
                
                $failed = array_filter($log, static fn($entry) => empty($entry['ok']));
                if ($failed) {
                    echo '<div class="warning">⚠️ Certaines étapes ont échoué, mais le cache principal a probablement été vidé.</div>';
                }
                else {
                    echo '<h2 class="success">🎉 CACHE VIDÉ COMPLÈTEMENT !</h2>';
                }
                
                echo '<div class="warning">';
                echo '<p><strong>⚠️ IMPORTANT :</strong> Actualisez le site avec <strong>Ctrl+Shift+R</strong></p>';
                echo '</div>';
                
                echo '<p><a href="/" class="btn">← Retour au site</a></p>';
                
            } catch (\Throwable $e) {
                echo '<div class="error">❌ Erreur : ' . htmlspecialchars($e->getMessage()) . '</div>';
                echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
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
            
            <h3>🔗 URL directe :</h3>
            <pre>https://www.spherevoices.com/drush-cr.php?token=spherevoices2026</pre>
            <?php
        }
        ?>
    </div>
</body>
</html>
