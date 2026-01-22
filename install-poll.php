<?php

/**
 * @file
 * Script pour installer le type de contenu Sondage.
 * 
 * Usage: php install-poll.php
 * Ou via drush: drush php:script install-poll.php
 */

use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;

// Charger Drupal
$autoloader = require_once __DIR__ . '/www/autoload.php';
$request = Request::createFromGlobals();
$kernel = DrupalKernel::createFromRequest($request, $autoloader, 'prod');
$kernel->boot();
$container = $kernel->getContainer();

// Définir le contexte
$container->get('request_stack')->push($request);

// Changer vers le répertoire www pour que les chemins soient corrects
chdir(__DIR__ . '/www');

$module_path = $container->get('extension.list.module')->getPath('spherevoices_core');
$config_path = $module_path . '/config/install';

if (!is_dir($config_path)) {
  echo "❌ Répertoire de configuration non trouvé: $config_path\n";
  exit(1);
}

echo "📦 Installation du type de contenu Sondage...\n\n";

// Utiliser le ConfigInstaller pour installer la configuration
$config_installer = $container->get('config.installer');
$storage = new \Drupal\Core\Config\FileStorage($config_path);

$config_files = [
  'field.storage.node.field_poll_description',
  'field.storage.node.field_poll_choices',
  'node.type.poll',
  'field.field.node.poll.field_poll_description',
  'field.field.node.poll.field_poll_choices',
  'core.entity_form_display.node.poll.default',
  'core.entity_view_display.node.poll.default',
];

$installed = [];
$skipped = [];
$config_factory = $container->get('config.factory');

foreach ($config_files as $config_name) {
  // Vérifier si la configuration existe
  $config = $config_factory->getEditable($config_name);
  $exists = !$config->isNew();
  
  if (!$exists) {
    $data = $storage->read($config_name);
    if ($data) {
      try {
        $config->setData($data)->save();
        $installed[] = $config_name;
        echo "✅ Installé: $config_name\n";
      } catch (\Exception $e) {
        echo "❌ Erreur: $config_name - " . $e->getMessage() . "\n";
      }
    } else {
      echo "⚠️  Fichier non trouvé: $config_name.yml\n";
    }
  } else {
    $skipped[] = $config_name;
    echo "⏭️  Déjà installé: $config_name\n";
  }
}

// Vider le cache
echo "\n🔄 Vidage du cache...\n";
$container->get('entity_type.manager')->clearCachedDefinitions();
$container->get('entity_field.manager')->clearCachedFieldDefinitions();
$container->get('cache_tags.invalidator')->invalidateTags(['config:core.extension']);

echo "\n✅ Installation terminée!\n";
echo "   - Installé: " . count($installed) . " configuration(s)\n";
echo "   - Déjà présent: " . count($skipped) . " configuration(s)\n";
echo "\n💡 Vous pouvez maintenant créer un sondage dans le backend!\n";
