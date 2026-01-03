<?php
/**
 * Suppression PHYSIQUE des templates form compilés + diagnostic config
 * URL: https://www.spherevoices.com/final-fix.php?token=spherevoices2026
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
    <title>🔧 Final Fix</title>
    <style>
        body { font-family: monospace; max-width: 1200px; margin: 20px auto; padding: 20px; background: #000; color: #0f0; }
        .container { background: #111; padding: 30px; border: 2px solid #0f0; }
        .success { color: #0f0; }
        .error { color: #f00; }
        .warning { color: #ff0; }
        .info { color: #0ff; }
        .btn { display: inline-block; padding: 15px 30px; margin: 10px; background: #0f0; color: #000; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 18px; }
        pre { background: #000; padding: 15px; border: 1px solid #0f0; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        table th, table td { padding: 8px; text-align: left; border: 1px solid #0f0; }
        table th { background: #003300; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="success">🔧 FIX FINAL - SUPPRESSION PHYSIQUE</h1>
        
        <?php
        // 1. SUPPRIMER PHYSIQUEMENT LES TEMPLATES FORM COMPILÉS
        echo '<h2 class="info">1️⃣ Suppression physique des templates form compilés</h2>';
        
        $form_templates_to_delete = [
            '6935564a9957e_form-element-label.html.t_YKr59Y2oUkNqRC5NmPBFMS5wR',
            '6935564a9957e_form-element.html.twig_21_lfjc2BYTlr7Oynjs_Mnd0K',
            '6935564a9957e_form.html.twig_x6pOL8OMmMv72JU9gJ6T-mI97',
            '6935564a9957e_input.html.twig_PWEDL37cIOysUfb814MIQhW5D',
        ];
        
        $twig_cache = __DIR__ . '/sites/default/files/php/twig';
        $deleted_count = 0;
        
        foreach ($form_templates_to_delete as $tpl) {
            $path = $twig_cache . '/' . $tpl;
            if (file_exists($path)) {
                if (is_dir($path)) {
                    // Supprimer le répertoire et son contenu
                    $files = glob($path . '/*');
                    foreach ($files as $file) {
                        @unlink($file);
                    }
                    if (@rmdir($path)) {
                        echo '<p class="success">✅ Supprimé: ' . htmlspecialchars($tpl) . '</p>';
                        $deleted_count++;
                    }
                } else {
                    if (@unlink($path)) {
                        echo '<p class="success">✅ Supprimé: ' . htmlspecialchars($tpl) . '</p>';
                        $deleted_count++;
                    }
                }
            } else {
                echo '<p class="warning">⚠️ Déjà supprimé: ' . htmlspecialchars($tpl) . '</p>';
            }
        }
        
        echo '<p class="success"><strong>Total: ' . $deleted_count . ' templates supprimés</strong></p>';
        
        // 2. SUPPRIMER TOUT LE RESTE DU CACHE TWIG
        echo '<h2 class="info">2️⃣ Suppression de TOUT le cache Twig</h2>';
        
        function nukeTwig($dir, &$count) {
            if (!is_dir($dir)) return;
            $items = @scandir($dir);
            if (!$items) return;
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') continue;
                $path = $dir . '/' . $item;
                if (is_dir($path)) {
                    nukeTwig($path, $count);
                    @rmdir($path);
                } else {
                    @unlink($path);
                    $count++;
                }
            }
        }
        
        $twig_deleted = 0;
        nukeTwig($twig_cache, $twig_deleted);
        echo '<p class="success">✅ ' . $twig_deleted . ' fichiers Twig supprimés</p>';
        
        // 3. OPCACHE
        echo '<h2 class="info">3️⃣ Vidage Opcache</h2>';
        if (function_exists('opcache_reset')) {
            opcache_reset();
            echo '<p class="success">✅ Opcache vidé</p>';
        } else {
            echo '<p class="warning">⚠️ Opcache non disponible</p>';
        }
        
        // 4. VIDER LES TABLES CACHE
        echo '<h2 class="info">4️⃣ Vidage MySQL</h2>';
        
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
                
                // 5. VÉRIFIER LA CONFIGURATION DU THÈME
                echo '<h2 class="info">5️⃣ Configuration du thème</h2>';
                
                $stmt = $pdo->prepare("SELECT data FROM config WHERE name = 'system.theme'");
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($result) {
                    $theme_config = unserialize($result['data']);
                    
                    echo '<table>';
                    echo '<tr><th>Paramètre</th><th>Valeur</th></tr>';
                    echo '<tr><td>Thème par défaut</td><td>' . htmlspecialchars($theme_config['default'] ?? 'inconnu') . '</td></tr>';
                    echo '<tr><td>Thème admin</td><td>' . htmlspecialchars($theme_config['admin'] ?? 'inconnu') . '</td></tr>';
                    echo '</table>';
                }
                
                // 6. VÉRIFIER LES MODULES QUI POURRAIENT INTERFÉRER
                echo '<h2 class="info">6️⃣ Modules actifs (form-related)</h2>';
                
                $stmt = $pdo->prepare("SELECT name, status FROM key_value WHERE collection = 'system.schema' AND name LIKE '%form%'");
                $stmt->execute();
                $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (!empty($modules)) {
                    echo '<table>';
                    echo '<tr><th>Module</th><th>Version</th></tr>';
                    foreach ($modules as $mod) {
                        echo '<tr><td>' . htmlspecialchars($mod['name']) . '</td><td>' . htmlspecialchars($mod['status']) . '</td></tr>';
                    }
                    echo '</table>';
                } else {
                    echo '<p class="success">✅ Aucun module form spécifique</p>';
                }
                
            } catch (PDOException $e) {
                echo '<p class="error">❌ MySQL: ' . htmlspecialchars($e->getMessage()) . '</p>';
            }
        }
        
        echo '<h2 class="success">🎉 NETTOYAGE TERMINÉ</h2>';
        
        echo '<div style="text-align: center; margin: 40px 0;">';
        echo '<a href="/user/login" class="btn">🔐 TESTER LE LOGIN MAINTENANT</a>';
        echo '</div>';
        
        echo '<p class="warning" style="text-align: center; font-size: 20px;">⚡ CTRL+SHIFT+R SUR LA PAGE DE LOGIN ⚡</p>';
        
        echo '<hr style="border-color: #0f0; margin: 40px 0;">';
        
        echo '<h2 class="warning">🔍 SI ÇA NE FONCTIONNE TOUJOURS PAS</h2>';
        echo '<p class="info">Le problème vient probablement de :</p>';
        echo '<ol class="info">';
        echo '<li>Un module tiers qui modifie le rendu des formulaires</li>';
        echo '<li>Une configuration différente dans la base de données entre local et prod</li>';
        echo '<li>Un problème de permissions PHP sur le serveur</li>';
        echo '<li>Une différence de version PHP entre local et prod</li>';
        echo '</ol>';
        
        echo '<p class="info">Prochaine étape : Exporter la config local et l\'importer en prod</p>';
        ?>
    </div>
</body>
</html>

