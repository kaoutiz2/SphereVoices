<?php

/**
 * @file
 * Script pour vérifier la configuration de la prévisualisation d'image.
 */

use Drupal\Core\Entity\Entity\EntityFormDisplay;

// Charger le formulaire d'édition pour les articles
$form_display = EntityFormDisplay::load('node.article.default');

if (!$form_display) {
  echo "Erreur: Le formulaire d'édition pour les articles n'existe pas.\n";
  exit(1);
}

// Récupérer la configuration actuelle
$component = $form_display->getComponent('field_image');

if (!$component) {
  echo "Erreur: Le composant field_image n'est pas configuré.\n";
  exit(1);
}

echo "Configuration actuelle du champ field_image:\n";
echo "  type: " . ($component['type'] ?? 'N/A') . "\n";
echo "  weight: " . ($component['weight'] ?? 'N/A') . "\n";

if (isset($component['settings'])) {
  echo "  settings:\n";
  foreach ($component['settings'] as $key => $value) {
    echo "    - $key: " . (is_string($value) ? $value : json_encode($value)) . "\n";
  }
}
else {
  echo "  ⚠ settings: NON DÉFINI\n";
}

// Vérifier via l'API du widget
$widget = \Drupal::service('plugin.manager.field.widget')->getInstance([
  'field_definition' => \Drupal::service('entity_field.manager')->getFieldDefinitions('node', 'article')['field_image'],
  'form_mode' => 'default',
  'settings' => $component['settings'] ?? [],
]);

if ($widget) {
  $default_settings = $widget::defaultSettings();
  echo "\nParamètres par défaut du widget:\n";
  foreach ($default_settings as $key => $value) {
    echo "  - $key: " . (is_string($value) ? $value : json_encode($value)) . "\n";
  }
  
  $current_settings = $widget->getSettings();
  echo "\nParamètres actuels du widget:\n";
  foreach ($current_settings as $key => $value) {
    echo "  - $key: " . (is_string($value) ? $value : json_encode($value)) . "\n";
  }
}


