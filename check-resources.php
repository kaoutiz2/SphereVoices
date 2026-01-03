<?php
/**
 * Diagnostic des ressources CSS/JS
 * URL: https://www.spherevoices.com/check-resources.php
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Diagnostic Ressources</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #fff; }
        .ok { color: #0f0; }
        .error { color: #f00; }
        .warning { color: #ff0; }
        pre { background: #000; padding: 10px; border-radius: 4px; }
    </style>
</head>
<body>
    <h1>🔍 Diagnostic des ressources</h1>
    
    <?php
    $drupal_root = __DIR__ . '/www';
    
    echo "<h2>📁 Structure des fichiers</h2>";
    
    // Vérifier les fichiers CSS du thème
    $theme_css_dir = $drupal_root . '/themes/custom/spherevoices_theme/css';
    
    if (is_dir($theme_css_dir)) {
        echo "<div class='ok'>✅ Dossier CSS du thème existe</div>";
        echo "<pre>";
        $css_files = scandir($theme_css_dir);
        foreach ($css_files as $file) {
            if ($file !== '.' && $file !== '..') {
                $filepath = $theme_css_dir . '/' . $file;
                $size = filesize($filepath);
                echo "$file - " . number_format($size) . " bytes\n";
            }
        }
        echo "</pre>";
    } else {
        echo "<div class='error'>❌ Dossier CSS introuvable : $theme_css_dir</div>";
    }
    
    // Vérifier les fichiers templates
    $templates_dir = $drupal_root . '/themes/custom/spherevoices_theme/templates';
    
    if (is_dir($templates_dir)) {
        echo "<div class='ok'>✅ Dossier templates existe</div>";
        echo "<pre>";
        // Lister récursivement
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($templates_dir),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $path = str_replace($templates_dir . '/', '', $file->getPathname());
                echo "$path\n";
            }
        }
        echo "</pre>";
    }
    
    // Vérifier le fichier libraries.yml
    $libraries_file = $drupal_root . '/themes/custom/spherevoices_theme/spherevoices_theme.libraries.yml';
    
    echo "<h2>📚 Fichier libraries.yml</h2>";
    if (file_exists($libraries_file)) {
        echo "<div class='ok'>✅ Fichier libraries.yml existe</div>";
        echo "<pre>";
        echo htmlspecialchars(file_get_contents($libraries_file));
        echo "</pre>";
    } else {
        echo "<div class='error'>❌ Fichier libraries.yml introuvable</div>";
    }
    
    // Tester l'accès aux CSS via HTTP
    echo "<h2>🌐 Test d'accès HTTP aux CSS</h2>";
    
    $base_url = 'https://www.spherevoices.com';
    $css_urls = [
        '/www/themes/custom/spherevoices_theme/css/layout.css',
        '/www/themes/custom/spherevoices_theme/css/components.css',
        '/www/themes/custom/spherevoices_theme/css/gallery.css',
    ];
    
    foreach ($css_urls as $url) {
        $full_url = $base_url . $url;
        echo "<div>Test : <a href='$full_url' target='_blank'>$url</a> ... ";
        
        $headers = @get_headers($full_url);
        if ($headers && strpos($headers[0], '200') !== false) {
            echo "<span class='ok'>✅ OK</span></div>";
        } else {
            echo "<span class='error'>❌ ERREUR</span></div>";
        }
    }
    
    // Afficher le contenu de page--front.html.twig
    echo "<h2>📄 Template page--front.html.twig</h2>";
    $front_template = $drupal_root . '/themes/custom/spherevoices_theme/templates/layout/page--front.html.twig';
    
    if (file_exists($front_template)) {
        echo "<div class='ok'>✅ Template existe</div>";
        echo "<pre>";
        echo htmlspecialchars(file_get_contents($front_template));
        echo "</pre>";
    } else {
        echo "<div class='error'>❌ Template introuvable</div>";
    }
    ?>
    
    <hr>
    <p><a href="/">← Retour au site</a></p>
</body>
</html>

