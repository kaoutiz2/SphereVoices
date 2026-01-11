#!/usr/bin/env php
<?php

/**
 * Script de post-déploiement pour vider le cache Drupal en production
 * 
 * Ce script est exécuté automatiquement après chaque déploiement pour :
 * - Vider le cache Drupal
 * - Reconstruire le registre des routes
 * - Invalider les caches CSS/JS
 * 
 * Usage: php post-deploy.php
 */

// Définir le chemin vers le dossier www
$drupal_root = __DIR__ . '/www';

// Vérifier que Drupal est accessible
if (!file_exists($drupal_root . '/autoload.php')) {
    echo "❌ Erreur: Drupal non trouvé dans {$drupal_root}\n";
    exit(1);
}

echo "🚀 Début du post-déploiement...\n";

// Charger l'autoloader de Drupal
require_once $drupal_root . '/autoload.php';

// Bootstrap Drupal
$autoloader = require $drupal_root . '/autoload.php';
$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
$kernel = \Drupal\Core\DrupalKernel::createFromRequest($request, $autoloader, 'prod');

try {
    $kernel->boot();
    $kernel->prepareLegacyRequest($request);
    
    echo "✅ Drupal chargé avec succès\n";
    
    // Vider tous les caches
    echo "🔄 Vidage des caches...\n";
    drupal_flush_all_caches();
    echo "✅ Caches vidés\n";
    
    // Invalider les tags de cache CSS/JS
    echo "🎨 Invalidation des caches CSS/JS...\n";
    \Drupal::service('asset.css.collection_optimizer')->deleteAll();
    \Drupal::service('asset.js.collection_optimizer')->deleteAll();
    echo "✅ Caches CSS/JS invalidés\n";
    
    // Reconstruire le registre des routes
    echo "🛣️  Reconstruction des routes...\n";
    \Drupal::service('router.builder')->rebuild();
    echo "✅ Routes reconstruites\n";
    
    // Invalider les caches de rendu
    echo "📄 Invalidation des caches de rendu...\n";
    \Drupal\Core\Cache\Cache::invalidateTags(['rendered']);
    echo "✅ Caches de rendu invalidés\n";
    
    echo "\n🎉 Post-déploiement terminé avec succès!\n";
    exit(0);
    
} catch (\Exception $e) {
    echo "❌ Erreur lors du post-déploiement: " . $e->getMessage() . "\n";
    echo "📝 Tentative avec drush en fallback...\n";
    
    // Fallback: essayer avec drush
    $drush_path = __DIR__ . '/vendor/bin/drush';
    if (file_exists($drush_path)) {
        echo "🔄 Exécution de drush cr...\n";
        passthru("cd " . escapeshellarg(__DIR__) . " && " . escapeshellarg($drush_path) . " cr", $return_code);
        
        if ($return_code === 0) {
            echo "✅ Cache vidé avec drush\n";
            exit(0);
        } else {
            echo "❌ Échec de drush cr\n";
            exit(1);
        }
    }
    
    exit(1);
}


