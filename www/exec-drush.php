<?php
/**
 * Appel DRUSH via ligne de commande
 * URL: https://www.spherevoices.com/exec-drush.php?token=spherevoices2026
 */

$security_token = 'spherevoices2026';
$provided_token = $_GET['token'] ?? '';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>🔄 Exec Drush CR</title>
    <style>
        body { font-family: monospace; max-width: 900px; margin: 50px auto; padding: 20px; background: #1e1e1e; color: #0f0; }
        .container { background: #000; padding: 30px; border-radius: 8px; border: 2px solid #0f0; }
        .success { color: #0f0; }
        .error { color: #f00; }
        .warning { color: #ff0; }
        .info { color: #0ff; }
        pre { background: #111; padding: 15px; border-radius: 4px; overflow-x: auto; color: #0f0; border: 1px solid #333; }
        .btn { display: inline-block; padding: 10px 20px; background: #0f0; color: #000; text-decoration: none; border-radius: 4px; font-weight: bold; }
        hr { border-color: #333; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="success">🔄 EXEC DRUSH CR</h1>
        
        <?php
        if ($provided_token === $security_token) {
            require_once __DIR__ . '/spherevoices-ops-bootstrap.inc.php';
            $paths = spherevoices_ops_paths(__DIR__);
            
            echo '<div class="info">🚀 Exécution de drush cr via ligne de commande...</div>';
            echo '<hr>';
            
            if ($paths['drush']) {
                echo "<div class='success'>✅ Drush trouvé : " . htmlspecialchars($paths['drush']) . "</div>";
            }
            echo "<div class='info'>📁 Drupal root : " . htmlspecialchars($paths['drupal_root']) . "</div>";
            echo "<div class='info'>📁 Project root : " . htmlspecialchars($paths['project_root']) . "</div>";
            echo '<hr>';
            
            $drush_result = spherevoices_ops_run_drush_cr(__DIR__);
            if ($drush_result['command']) {
                echo '<div class="info">⚡ Commande exécutée :</div><pre>' . htmlspecialchars($drush_result['command']) . '</pre>';
            }
            if ($drush_result['output'] !== '') {
                echo '<div class="info">🔄 Sortie Drush :</div><pre>' . htmlspecialchars($drush_result['output']) . '</pre>';
            }
            echo '<hr>';
            
            $return_var = $drush_result['ok'] ? 0 : $drush_result['code'];
            
            if (!$drush_result['ok']) {
                echo '<div class="warning">⚠️ Drush indisponible — vidage cache via PHP (équivalent drush cr)...</div>';
                try {
                    $kernel = spherevoices_ops_bootstrap_drupal(__DIR__);
                    $log = spherevoices_ops_rebuild_cache($kernel);
                    echo spherevoices_ops_render_rebuild_log($log);
                    $failed = array_filter($log, static fn($entry) => empty($entry['ok']));
                    $return_var = $failed ? 1 : 0;
                }
                catch (\Throwable $e) {
                    echo '<div class="error">❌ Erreur fallback : ' . htmlspecialchars($e->getMessage()) . '</div>';
                    echo '<p class="info">Utilisez plutôt : <a href="/drush-cr.php?token=spherevoices2026">drush-cr.php</a></p>';
                    $return_var = 1;
                }
            }
            
            if ($return_var === 0) {
                echo '<h2 class="success">🎉 DRUSH CR RÉUSSI !</h2>';
                echo '<div class="success">';
                echo '<p><strong>✅ Le cache a été vidé avec succès !</strong></p>';
                echo '<ul>';
                echo '<li>✅ Code retour : 0 (succès)</li>';
                echo '<li>✅ Tous les caches Drupal vidés</li>';
                echo '<li>✅ Templates recompilés</li>';
                echo '<li>✅ Routes reconstruites</li>';
                echo '</ul>';
                echo '</div>';
                
                echo '<div class="warning">';
                echo '<h3>⚠️ MAINTENANT :</h3>';
                echo '<ol>';
                echo '<li>Actualisez le site avec <strong>Ctrl+Shift+R</strong></li>';
                echo '<li>Testez : <a href="/www/" style="color: #0ff;">https://www.spherevoices.com/www/</a></li>';
                echo '<li>Tout devrait fonctionner !</li>';
                echo '</ol>';
                echo '</div>';
                
                echo '<p><a href="/www/" class="btn">← ALLER SUR LE SITE</a></p>';
                
            } else {
                echo '<div class="error">❌ ERREUR - Code retour : ' . $return_var . '</div>';
                echo '<div class="warning">';
                echo '<p>Drush a échoué. Vérifiez les messages ci-dessus.</p>';
                echo '</div>';
            }
            
        } else {
            ?>
            <div class="warning">⚠️ Ce script exécute : <code>drush cr</code></div>
            
            <form method="get">
                <label for="token" style="color: #0f0;">Token de sécurité:</label><br>
                <input type="text" id="token" name="token" value="" style="width: 300px; padding: 5px; margin: 10px 0; background: #000; color: #0f0; border: 1px solid #0f0;">
                <br>
                <button type="submit" class="btn">EXEC DRUSH CR</button>
            </form>
            
            <hr>
            
            <h3 class="info">📝 Ce script :</h3>
            <ul>
                <li>Cherche l'exécutable drush</li>
                <li>Exécute : <code>drush cr</code></li>
                <li>Affiche la sortie en temps réel</li>
            </ul>
            
            <h3 class="info">🔗 URL directe :</h3>
            <pre>https://www.spherevoices.com/exec-drush.php?token=spherevoices2026</pre>
            <?php
        }
        ?>
    </div>
</body>
</html>

