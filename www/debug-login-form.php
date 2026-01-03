<?php
/**
 * Debug complet du formulaire de login
 * URL: https://www.spherevoices.com/debug-login-form.php?token=spherevoices2026
 */

$security_token = 'spherevoices2026';
$provided_token = $_GET['token'] ?? '';

if ($provided_token !== $security_token) {
    die('Token requis');
}

$drupal_root = __DIR__;

if (!file_exists($drupal_root . '/autoload.php')) {
    die('Drupal non trouvé');
}

header('Content-Type: text/plain; charset=utf-8');

echo "=== DEBUG FORMULAIRE DE LOGIN ===\n\n";

try {
    require_once $drupal_root . '/autoload.php';
    $autoloader = require $drupal_root . '/autoload.php';
    
    $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
    $kernel = \Drupal\Core\DrupalKernel::createFromRequest($request, $autoloader, 'prod');
    $kernel->boot();
    $kernel->prepareLegacyRequest($request);
    
    echo "✅ Drupal chargé\n\n";
    
    // Récupérer le formulaire de login
    $form_builder = \Drupal::formBuilder();
    $form = $form_builder->getForm('Drupal\user\Form\UserLoginForm');
    
    echo "=== STRUCTURE DU FORMULAIRE ===\n\n";
    
    // Afficher la structure complète
    function printFormElement($element, $indent = 0) {
        $prefix = str_repeat("  ", $indent);
        
        foreach ($element as $key => $value) {
            // Ignorer les clés qui commencent par #
            if (is_string($key) && strpos($key, '#') === 0) {
                continue;
            }
            
            if (is_array($value)) {
                echo $prefix . "[$key]\n";
                
                // Afficher les propriétés importantes
                if (isset($value['#type'])) {
                    echo $prefix . "  #type: " . $value['#type'] . "\n";
                }
                if (isset($value['#title'])) {
                    $title = is_object($value['#title']) ? $value['#title']->render() : $value['#title'];
                    echo $prefix . "  #title: " . $title . "\n";
                }
                if (isset($value['#name'])) {
                    echo $prefix . "  #name: " . $value['#name'] . "\n";
                }
                if (isset($value['#attributes'])) {
                    echo $prefix . "  #attributes: " . json_encode($value['#attributes']) . "\n";
                }
                if (isset($value['#access']) && $value['#access'] === FALSE) {
                    echo $prefix . "  ⚠️ #access: FALSE (élément caché!)\n";
                }
                
                // Récursion
                printFormElement($value, $indent + 1);
            }
        }
    }
    
    printFormElement($form);
    
    echo "\n\n=== RENDU DU FORMULAIRE ===\n\n";
    
    $renderer = \Drupal::service('renderer');
    $rendered = $renderer->renderPlain($form);
    
    echo "Taille HTML: " . strlen($rendered) . " bytes\n\n";
    
    // Chercher les inputs
    $has_name_input = preg_match('/<input[^>]*name=["\']name["\']/i', $rendered);
    $has_pass_input = preg_match('/<input[^>]*name=["\']pass["\']/i', $rendered);
    
    echo "Input name='name' présent: " . ($has_name_input ? "✅ OUI" : "❌ NON") . "\n";
    echo "Input name='pass' présent: " . ($has_pass_input ? "✅ OUI" : "❌ NON") . "\n\n";
    
    if (!$has_name_input || !$has_pass_input) {
        echo "⚠️ LES INPUTS SONT MANQUANTS DANS LE HTML RENDU !\n\n";
    }
    
    echo "=== HTML COMPLET ===\n\n";
    echo $rendered;
    
    echo "\n\n=== TEMPLATES FORM ACTIFS ===\n\n";
    
    $theme_handler = \Drupal::service('theme_handler');
    $default_theme = \Drupal::config('system.theme')->get('default');
    $theme_path = $theme_handler->getTheme($default_theme)->getPath();
    
    echo "Thème actif: $default_theme\n";
    echo "Chemin: $theme_path\n\n";
    
    $form_templates = glob(__DIR__ . '/' . $theme_path . '/templates/form/*.twig');
    $backup_templates = glob(__DIR__ . '/' . $theme_path . '/templates/_backup_form/*.twig');
    
    echo "Templates form personnalisés: " . count($form_templates) . "\n";
    foreach ($form_templates as $tpl) {
        echo "  - " . basename($tpl) . "\n";
    }
    
    echo "\nTemplates sauvegardés: " . count($backup_templates) . "\n";
    foreach ($backup_templates as $tpl) {
        echo "  - " . basename($tpl) . "\n";
    }
    
    echo "\n=== CACHE TWIG ===\n\n";
    
    $twig_cache_dir = __DIR__ . '/sites/default/files/php/twig';
    if (is_dir($twig_cache_dir)) {
        $twig_files = glob($twig_cache_dir . '/*');
        echo "Fichiers Twig compilés: " . count($twig_files) . "\n";
        
        // Chercher les templates form
        $form_twigs = array_filter($twig_files, function($f) {
            return stripos(basename($f), 'form') !== false || stripos(basename($f), 'input') !== false;
        });
        
        echo "Templates form compilés: " . count($form_twigs) . "\n";
        foreach (array_slice($form_twigs, 0, 10) as $f) {
            echo "  - " . basename($f) . "\n";
        }
    } else {
        echo "Répertoire Twig cache inexistant\n";
    }
    
} catch (\Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n\n";
    echo $e->getTraceAsString();
}

