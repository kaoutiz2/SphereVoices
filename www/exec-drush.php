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
            echo '<div class="info">🚀 Exécution de drush cr via ligne de commande...</div>';
            echo '<hr>';
            
            // Chemins à tester
            $paths_to_test = [
                __DIR__ . "/.." . '/vendor/bin/drush',
                __DIR__ . "/.." . '/www/../vendor/bin/drush',
                '/usr/local/bin/drush',
                '/usr/bin/drush',
            ];
            
            $drush_path = null;
            foreach ($paths_to_test as $path) {
                if (file_exists($path)) {
                    $drush_path = $path;
                    echo "<div class='success'>✅ Drush trouvé : $path</div>";
                    break;
                }
            }
            
            if (!$drush_path) {
                echo '<div class="error">❌ Drush introuvable !</div>';
                echo '<div class="warning">Chemins testés :</div><pre>';
                print_r($paths_to_test);
                echo '</pre>';
                
                echo '<div class="info">Fichiers dans ' . __DIR__ . "/.." . ' :</div><pre>';
                print_r(scandir(__DIR__ . "/.."));
                echo '</pre>';
                exit;
            }
            
            // Déterminer le répertoire de travail
            $working_dir = __DIR__ . "/.." . '/www';
            if (!is_dir($working_dir)) {
                $working_dir = __DIR__ . "/..";
            }
            
            echo "<div class='info'>📁 Répertoire de travail : $working_dir</div>";
            echo '<hr>';
            
            // Commande drush cr - Appel via PHP
            $command = "cd " . escapeshellarg($working_dir) . " && php " . escapeshellarg($drush_path) . " cr 2>&1";
            
            echo '<div class="info">⚡ Commande exécutée :</div>';
            echo '<pre>' . htmlspecialchars($command) . '</pre>';
            echo '<hr>';
            
            echo '<div class="info">🔄 Sortie de drush cr :</div>';
            echo '<pre>';
            
            $output = [];
            $return_var = 0;
            exec($command, $output, $return_var);
            
            foreach ($output as $line) {
                echo htmlspecialchars($line) . "\n";
            }
            
            echo '</pre>';
            echo '<hr>';
            
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

