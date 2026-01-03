<?php
/**
 * Liste TOUS les templates form sur le serveur
 * URL: https://www.spherevoices.com/find-all-form-templates.php?token=spherevoices2026
 */

$security_token = 'spherevoices2026';
$provided_token = $_GET['token'] ?? '';

if ($provided_token !== $security_token) {
    die('Token requis');
}

header('Content-Type: text/plain; charset=utf-8');

echo "=== RECHERCHE DE TOUS LES TEMPLATES FORM ===\n\n";

$search_dirs = [
    'Thème custom' => __DIR__ . '/themes/custom',
    'Core Drupal' => __DIR__ . '/core',
    'Modules contrib' => __DIR__ . '/modules',
];

$found_templates = [];

function findFormTemplates($dir, &$results, $label) {
    if (!is_dir($dir)) return;
    
    $items = @scandir($dir);
    if (!$items) return;
    
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        
        $path = $dir . '/' . $item;
        
        if (is_dir($path)) {
            findFormTemplates($path, $results, $label);
        } elseif (is_file($path) && preg_match('/\b(form|input|form-element)\b.*\.twig$/i', $item)) {
            $results[] = [
                'location' => $label,
                'file' => $item,
                'path' => $path,
                'size' => filesize($path),
                'mtime' => filemtime($path),
            ];
        }
    }
}

foreach ($search_dirs as $label => $dir) {
    echo "Recherche dans $label ($dir)...\n";
    findFormTemplates($dir, $found_templates, $label);
}

echo "\n=== TEMPLATES TROUVÉS (" . count($found_templates) . ") ===\n\n";

if (empty($found_templates)) {
    echo "❌ AUCUN TEMPLATE FORM TROUVÉ !\n";
    echo "C'est normal si les templates personnalisés ont été supprimés.\n";
    echo "Drupal devrait utiliser les templates par défaut du core.\n";
} else {
    foreach ($found_templates as $tpl) {
        echo "📄 {$tpl['file']}\n";
        echo "   Location: {$tpl['location']}\n";
        echo "   Path: {$tpl['path']}\n";
        echo "   Size: " . number_format($tpl['size']) . " bytes\n";
        echo "   Modified: " . date('Y-m-d H:i:s', $tpl['mtime']) . "\n";
        
        // Afficher les premières lignes
        $content = file_get_contents($tpl['path']);
        $lines = explode("\n", $content);
        $first_lines = array_slice($lines, 0, 10);
        echo "   Premières lignes:\n";
        foreach ($first_lines as $line) {
            echo "     " . $line . "\n";
        }
        echo "\n";
    }
}

echo "\n=== TEMPLATES DANS LE CACHE TWIG ===\n\n";

$twig_dir = __DIR__ . '/sites/default/files/php/twig';
if (is_dir($twig_dir)) {
    $twig_files = glob($twig_dir . '/*');
    echo "Fichiers dans le cache: " . count($twig_files) . "\n\n";
    
    $form_twigs = [];
    foreach ($twig_files as $f) {
        if (preg_match('/form|input/i', basename($f))) {
            $form_twigs[] = $f;
        }
    }
    
    echo "Templates form/input compilés: " . count($form_twigs) . "\n\n";
    
    if (!empty($form_twigs)) {
        echo "⚠️ IL Y A ENCORE DES TEMPLATES FORM DANS LE CACHE !\n\n";
        foreach (array_slice($form_twigs, 0, 20) as $f) {
            echo "  - " . basename($f) . " (" . date('Y-m-d H:i:s', filemtime($f)) . ")\n";
        }
    } else {
        echo "✅ Aucun template form/input dans le cache (bon signe)\n";
    }
} else {
    echo "✅ Répertoire cache Twig inexistant (vide)\n";
}

echo "\n=== TEMPLATES CORE DRUPAL (référence) ===\n\n";

$core_templates = [
    'input.html.twig' => __DIR__ . '/core/themes/stable9/templates/form/input.html.twig',
    'form-element.html.twig' => __DIR__ . '/core/themes/stable9/templates/form/form-element.html.twig',
    'form.html.twig' => __DIR__ . '/core/themes/stable9/templates/form/form.html.twig',
];

foreach ($core_templates as $name => $path) {
    if (file_exists($path)) {
        echo "✅ $name (core)\n";
        echo "   Path: $path\n";
        echo "   Size: " . number_format(filesize($path)) . " bytes\n\n";
    } else {
        // Essayer stable
        $alt_path = str_replace('stable9', 'stable', $path);
        if (file_exists($alt_path)) {
            echo "✅ $name (core stable)\n";
            echo "   Path: $alt_path\n";
            echo "   Size: " . number_format(filesize($alt_path)) . " bytes\n\n";
        } else {
            echo "❌ $name INTROUVABLE\n\n";
        }
    }
}

echo "\n=== DIAGNOSTIC FINAL ===\n\n";

if (empty($found_templates) && count($form_twigs ?? []) === 0) {
    echo "✅ Aucun template form personnalisé trouvé\n";
    echo "✅ Aucun template form dans le cache Twig\n";
    echo "✅ Drupal devrait utiliser les templates du core\n\n";
    echo "⚠️ SI LES INPUTS NE S'AFFICHENT TOUJOURS PAS:\n";
    echo "   Le problème vient probablement d'autre chose:\n";
    echo "   - Un module qui interfère\n";
    echo "   - Un hook qui supprime les inputs\n";
    echo "   - Un problème de permissions\n";
    echo "   - Une différence de configuration entre local et prod\n";
} else {
    echo "⚠️ Des templates form personnalisés existent encore\n";
    echo "⚠️ Ils pourraient être la cause du problème\n";
}

