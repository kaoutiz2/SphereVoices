<?php

/**
 * @file
 * Script pour configurer les modes d'affichage pour inclure le champ field_image.
 *
 * Usage: drush php:script web/modules/custom/spherevoices_core/scripts/configure_image_display.php
 */

use Drupal\Core\Entity\Entity\EntityViewDisplay;

// Bootstrap Drupal
\Drupal::service('kernel')->boot();

print "Configuration des modes d'affichage pour les images...\n\n";

// Modes d'affichage à configurer
$view_modes = ['default', 'teaser', 'full'];

foreach ($view_modes as $view_mode) {
  // Charger ou créer le mode d'affichage
  $entity_display = EntityViewDisplay::load('node.article.' . $view_mode);
  
  if (!$entity_display) {
    // Pour les modes par défaut, on ne peut pas les créer, ils doivent exister
    if ($view_mode === 'default') {
      print "⚠ Mode d'affichage '{$view_mode}' n'existe pas (normal pour default)\n";
      continue;
    }
    // Créer le mode d'affichage s'il n'existe pas (seulement pour les modes personnalisés)
    $entity_display = EntityViewDisplay::create([
      'targetEntityType' => 'node',
      'bundle' => 'article',
      'mode' => $view_mode,
      'status' => TRUE,
    ]);
    print "✓ Mode d'affichage '{$view_mode}' créé\n";
  }
  else {
    print "✓ Mode d'affichage '{$view_mode}' trouvé\n";
  }
  
  // Vérifier si le champ field_image existe
  $field_definitions = \Drupal::service('entity_field.manager')->getFieldDefinitions('node', 'article');
  if (!isset($field_definitions['field_image'])) {
    print "  ⚠ Le champ field_image n'existe pas pour le type de contenu 'article'\n";
    continue;
  }
  
  // Configurer le champ field_image
  $component = $entity_display->getComponent('field_image');
  
  if (!$component) {
    // Ajouter le champ s'il n'est pas présent
    $entity_display->setComponent('field_image', [
      'type' => 'image',
      'label' => 'hidden',
      'settings' => [
        'image_style' => 'large',
        'image_link' => '',
      ],
      'weight' => 0,
    ]);
    print "  → Champ field_image ajouté au mode '{$view_mode}'\n";
  }
  else {
    print "  → Champ field_image déjà configuré pour le mode '{$view_mode}'\n";
  }
  
  // Sauvegarder
  $entity_display->save();
}

print "\n✅ Configuration terminée !\n";
print "N'oubliez pas de vider le cache : drush cr\n";

