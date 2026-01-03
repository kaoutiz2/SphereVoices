<?php
/**
 * Diagnostic : Pourquoi les inputs du formulaire de login ne s'affichent pas ?
 * URL: https://www.spherevoices.com/check-login-form.php?token=spherevoices2026
 */

$security_token = 'spherevoices2026';
$provided_token = $_GET['token'] ?? '';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>🔍 Diagnostic Login Form</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 20px auto; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #28a745; padding: 10px; background: #d4edda; border-radius: 4px; margin: 10px 0; }
        .error { color: #dc3545; padding: 10px; background: #f8d7da; border-radius: 4px; margin: 10px 0; }
        .warning { color: #856404; padding: 10px; background: #fff3cd; border-radius: 4px; margin: 10px 0; }
        .info { color: #0c5460; padding: 10px; background: #d1ecf1; border-radius: 4px; margin: 10px 0; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        table th, table td { padding: 8px; text-align: left; border: 1px solid #ddd; }
        table th { background: #f8f9fa; font-weight: bold; }
        .file-list { max-height: 300px; overflow-y: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Diagnostic Formulaire de Connexion</h1>
        
        <?php
        if ($provided_token === $security_token) {
            
            echo '<div class="info">🔍 Vérification des fichiers CSS/JS compilés...</div>';
            
            // Chemins à vérifier
            $paths = [
                'CSS' => __DIR__ . '/sites/default/files/css',
                'JS' => __DIR__ . '/sites/default/files/js',
                'PHP' => __DIR__ . '/sites/default/files/php',
                'Twig' => __DIR__ . '/sites/default/files/php/twig',
            ];
            
            echo '<h2>📂 Fichiers de Cache Compilés</h2>';
            
            foreach ($paths as $type => $path) {
                echo '<h3>' . $type . '</h3>';
                
                if (!file_exists($path)) {
                    echo '<div class="warning">⚠️ Répertoire inexistant : ' . htmlspecialchars($path) . '</div>';
                    continue;
                }
                
                if (!is_dir($path)) {
                    echo '<div class="error">❌ Pas un répertoire : ' . htmlspecialchars($path) . '</div>';
                    continue;
                }
                
                if (!is_readable($path)) {
                    echo '<div class="error">❌ Répertoire non lisible : ' . htmlspecialchars($path) . '</div>';
                    continue;
                }
                
                $files = glob($path . '/*');
                
                if (empty($files)) {
                    echo '<div class="warning">⚠️ Répertoire vide</div>';
                    continue;
                }
                
                echo '<div class="success">✅ ' . count($files) . ' fichiers trouvés</div>';
                
                // Afficher les 10 premiers fichiers
                echo '<div class="file-list"><pre>';
                $count = 0;
                foreach ($files as $file) {
                    if ($count++ >= 10) {
                        echo "\n... et " . (count($files) - 10) . " autres fichiers\n";
                        break;
                    }
                    $filename = basename($file);
                    $size = is_file($file) ? filesize($file) : 0;
                    $mtime = filemtime($file);
                    echo sprintf("%-60s %10s %s\n", 
                        $filename,
                        $size > 0 ? number_format($size) . ' bytes' : '(dir)',
                        date('Y-m-d H:i:s', $mtime)
                    );
                }
                echo '</pre></div>';
            }
            
            // Vérifier le HTML de la page de login
            echo '<h2>🌐 Contenu de la Page de Login</h2>';
            
            $login_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') 
                       . '://' . $_SERVER['HTTP_HOST'] . '/user/login';
            
            echo '<div class="info">📡 Récupération de : ' . htmlspecialchars($login_url) . '</div>';
            
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => 'User-Agent: Mozilla/5.0',
                    'timeout' => 10,
                ]
            ]);
            
            $html = @file_get_contents($login_url, false, $context);
            
            if ($html === false) {
                echo '<div class="error">❌ Impossible de récupérer la page de login</div>';
            } else {
                echo '<div class="success">✅ Page récupérée (' . number_format(strlen($html)) . ' bytes)</div>';
                
                // Chercher les inputs
                $has_username_input = preg_match('/<input[^>]*name=["\']name["\']/i', $html);
                $has_password_input = preg_match('/<input[^>]*name=["\']pass["\']/i', $html);
                $has_form = preg_match('/<form[^>]*id=["\']user-login-form["\']/i', $html);
                
                echo '<h3>🔍 Éléments du Formulaire</h3>';
                echo '<table>';
                echo '<tr><th>Élément</th><th>Présent</th></tr>';
                echo '<tr><td>Formulaire #user-login-form</td><td>' . ($has_form ? '✅ OUI' : '❌ NON') . '</td></tr>';
                echo '<tr><td>Input name="name"</td><td>' . ($has_username_input ? '✅ OUI' : '❌ NON') . '</td></tr>';
                echo '<tr><td>Input name="pass"</td><td>' . ($has_password_input ? '✅ OUI' : '❌ NON') . '</td></tr>';
                echo '</table>';
                
                // Chercher les CSS
                preg_match_all('/<link[^>]*href=["\']([^"\']*\.css[^"\']*)["\']/i', $html, $css_matches);
                $css_files = $css_matches[1] ?? [];
                
                echo '<h3>📄 Fichiers CSS Chargés (' . count($css_files) . ')</h3>';
                if (!empty($css_files)) {
                    echo '<div class="file-list"><pre>';
                    foreach ($css_files as $css) {
                        echo htmlspecialchars($css) . "\n";
                    }
                    echo '</pre></div>';
                } else {
                    echo '<div class="warning">⚠️ Aucun fichier CSS trouvé dans le HTML</div>';
                }
                
                // Chercher les JS
                preg_match_all('/<script[^>]*src=["\']([^"\']*\.js[^"\']*)["\']/i', $html, $js_matches);
                $js_files = $js_matches[1] ?? [];
                
                echo '<h3>📄 Fichiers JS Chargés (' . count($js_files) . ')</h3>';
                if (!empty($js_files)) {
                    echo '<div class="file-list"><pre>';
                    foreach ($js_files as $js) {
                        echo htmlspecialchars($js) . "\n";
                    }
                    echo '</pre></div>';
                } else {
                    echo '<div class="warning">⚠️ Aucun fichier JS trouvé dans le HTML</div>';
                }
                
                // Afficher un extrait du HTML du formulaire
                if (preg_match('/<form[^>]*id=["\']user-login-form["\'][^>]*>(.*?)<\/form>/is', $html, $form_match)) {
                    echo '<h3>📝 HTML du Formulaire</h3>';
                    echo '<pre>' . htmlspecialchars(substr($form_match[0], 0, 2000)) . '</pre>';
                } else {
                    echo '<div class="warning">⚠️ Formulaire non trouvé dans le HTML</div>';
                }
            }
            
            // Vérifier les permissions
            echo '<h2>🔐 Permissions des Répertoires</h2>';
            echo '<table>';
            echo '<tr><th>Répertoire</th><th>Existe</th><th>Lisible</th><th>Modifiable</th></tr>';
            
            $check_dirs = [
                __DIR__ . '/sites/default/files',
                __DIR__ . '/sites/default/files/css',
                __DIR__ . '/sites/default/files/js',
                __DIR__ . '/sites/default/files/php',
            ];
            
            foreach ($check_dirs as $dir) {
                $exists = file_exists($dir);
                $readable = is_readable($dir);
                $writable = is_writable($dir);
                
                echo '<tr>';
                echo '<td>' . htmlspecialchars(str_replace(__DIR__, '', $dir)) . '</td>';
                echo '<td>' . ($exists ? '✅' : '❌') . '</td>';
                echo '<td>' . ($readable ? '✅' : '❌') . '</td>';
                echo '<td>' . ($writable ? '✅' : '❌') . '</td>';
                echo '</tr>';
            }
            echo '</table>';
            
            // Diagnostic final
            echo '<h2>🎯 Diagnostic</h2>';
            
            if ($has_username_input && $has_password_input) {
                echo '<div class="success">✅ Les inputs SONT présents dans le HTML</div>';
                echo '<div class="warning">⚠️ Le problème est probablement lié au CSS qui les cache</div>';
                echo '<div class="info">💡 Solution : Supprimer les fichiers CSS compilés et reconstruire le cache</div>';
            } else {
                echo '<div class="error">❌ Les inputs NE SONT PAS présents dans le HTML</div>';
                echo '<div class="warning">⚠️ Le problème est au niveau du rendu Drupal/Twig</div>';
                echo '<div class="info">💡 Solution : Vider le cache Drupal complet et reconstruire les templates Twig</div>';
            }
            
        } else {
            ?>
            <div class="warning">⚠️ Token de sécurité requis</div>
            <p>URL : <code>?token=spherevoices2026</code></p>
            <?php
        }
        ?>
    </div>
</body>
</html>

