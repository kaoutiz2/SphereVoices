<?php

/**
 * @file
 * Script simple pour installer le type de contenu Sondage.
 * 
 * Usage: php install-poll-simple.php
 */

// Charger Drupal
$autoloader = require_once __DIR__ . '/www/autoload.php';
$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
$kernel = \Drupal\Core\DrupalKernel::createFromRequest($request, $autoloader, 'prod');
$kernel->boot();
$container = $kernel->getContainer();
$container->get('request_stack')->push($request);

chdir(__DIR__ . '/www');

$module_path = $container->get('extension.list.module')->getPath('spherevoices_core');
$config_path = $module_path . '/config/install';

echo "📦 Installation du type de contenu Sondage...\n\n";

// Vider le cache d'abord
$container->get('cache_factory')->get('config')->deleteAll();
echo "🔄 Cache vidé\n\n";

$storage = new \Drupal\Core\Config\FileStorage($config_path);
$config_files = $storage->listAll();

$installed = 0;
$skipped = 0;

foreach ($config_files as $config_name) {
  if (strpos($config_name, 'poll') !== FALSE) {
    $data = $storage->read($config_name);
    if ($data) {
      try {
        $config = $container->get('config.factory')->getEditable($config_name);
        if ($config->isNew()) {
          $config->setData($data)->save();
          echo "✅ Installé: $config_name\n";
          $installed++;
        } else {
          echo "⏭️  Déjà installé: $config_name\n";
          $skipped++;
        }
      } catch (\Exception $e) {
        echo "❌ Erreur: $config_name - " . $e->getMessage() . "\n";
      }
    }
  }
}

// Vider le cache des entités
$container->get('entity_type.manager')->clearCachedDefinitions();
$container->get('entity_field.manager')->clearCachedFieldDefinitions();
$container->get('cache_tags.invalidator')->invalidateTags(['config:core.extension']);

echo "\n✅ Installation terminée!\n";
echo "   - Installé: $installed configuration(s)\n";
echo "   - Déjà présent: $skipped configuration(s)\n";
echo "\n💡 Vous pouvez maintenant créer un sondage dans le backend!\n";
echo "   Allez dans: Contenu > Ajouter du contenu > Sondage\n";
