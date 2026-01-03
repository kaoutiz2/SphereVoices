<?php
/**
 * Vérifie l'état de connexion du point de vue de Drupal
 * URL: https://www.spherevoices.com/am-i-logged-in.php?token=spherevoices2026
 */

$security_token = 'spherevoices2026';
$provided_token = $_GET['token'] ?? '';

if ($provided_token !== $security_token) {
    die('Token requis');
}

header('Content-Type: text/plain; charset=utf-8');

echo "=== VÉRIFICATION CONNEXION DRUPAL ===\n\n";

try {
    // Définir $app_root
    $app_root = dirname(__DIR__);
    
    require_once __DIR__ . '/autoload.php';
    $autoloader = require __DIR__ . '/autoload.php';
    
    $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
    $kernel = \Drupal\Core\DrupalKernel::createFromRequest($request, $autoloader, 'prod');
    $kernel->boot();
    $kernel->prepareLegacyRequest($request);
    
    echo "✅ Drupal chargé\n\n";
    
    // Vérifier l'utilisateur actuel
    $current_user = \Drupal::currentUser();
    
    echo "=== UTILISATEUR ACTUEL ===\n\n";
    echo "UID: " . $current_user->id() . "\n";
    echo "Nom: " . $current_user->getAccountName() . "\n";
    echo "Email: " . $current_user->getEmail() . "\n";
    echo "Authentifié: " . ($current_user->isAuthenticated() ? "OUI" : "NON") . "\n";
    echo "Anonyme: " . ($current_user->isAnonymous() ? "OUI" : "NON") . "\n";
    
    if ($current_user->isAuthenticated()) {
        echo "\n✅ VOUS ÊTES CONNECTÉ !\n";
        
        // Vérifier les rôles
        $roles = $current_user->getRoles();
        echo "\nRôles:\n";
        foreach ($roles as $role) {
            echo "  - $role\n";
        }
        
        // Vérifier les permissions importantes
        echo "\nPermissions:\n";
        $perms = [
            'access toolbar',
            'access administration pages',
            'administer nodes',
            'administer site configuration',
        ];
        
        foreach ($perms as $perm) {
            $has = $current_user->hasPermission($perm);
            echo "  " . ($has ? "✅" : "❌") . " $perm\n";
        }
        
    } else {
        echo "\n❌ VOUS N'ÊTES PAS CONNECTÉ\n";
        echo "Drupal vous voit comme anonyme (uid: 0)\n";
    }
    
    // Vérifier le mode maintenance
    echo "\n\n=== CONFIGURATION SITE ===\n\n";
    
    $maintenance_mode = \Drupal::state()->get('system.maintenance_mode');
    echo "Mode maintenance: " . ($maintenance_mode ? "ACTIVÉ ❌" : "Désactivé ✅") . "\n";
    
    $theme = \Drupal::config('system.theme')->get('default');
    echo "Thème actif: $theme\n";
    
    // Vérifier si la toolbar est activée
    $module_handler = \Drupal::service('module_handler');
    $toolbar_enabled = $module_handler->moduleExists('toolbar');
    echo "Module toolbar: " . ($toolbar_enabled ? "Activé ✅" : "Désactivé ❌") . "\n";
    
} catch (\Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n\n";
    echo $e->getTraceAsString();
}

