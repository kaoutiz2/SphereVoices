<?php

/**
 * @file
 * Script pour configurer la prévisualisation d'image dans le formulaire d'édition.
 *
 * Usage: drush php:script web/modules/custom/spherevoices_core/scripts/configure_image_preview.php
 */

use Drupal\Core\Entity\Entity\EntityFormDisplay;

// Charger le formulaire d'édition pour les articles
$form_display = EntityFormDisplay::load('node.article.default');

if (!$form_display) {
  echo "Erreur: Le formulaire d'édition pour les articles n'existe pas.\n";
  exit(1);
}

// Vérifier si le champ field_image existe
if (!$form_display->getComponent('field_image')) {
  echo "Erreur: Le champ field_image n'est pas configuré dans le formulaire d'édition.\n";
  exit(1);
}

// Récupérer la configuration actuelle
$component = $form_display->getComponent('field_image');

if (!$component) {
  echo "Erreur: Le composant field_image n'est pas configuré.\n";
  exit(1);
}

// S'assurer que les settings existent
if (!isset($component['settings'])) {
  $component['settings'] = [];
}

// Toujours forcer la mise à jour pour s'assurer que les settings sont bien enregistrés
$component['settings']['preview_image_style'] = 'thumbnail';
$component['settings']['progress_indicator'] = 'throbber';

// Mettre à jour le composant
$form_display->setComponent('field_image', $component);
$form_display->save();

echo "✓ Configuration de la prévisualisation d'image mise à jour.\n";
echo "  - preview_image_style: thumbnail\n";
echo "  - progress_indicator: throbber\n";

// Vérifier que le style d'image 'thumbnail' existe
$image_style = \Drupal::entityTypeManager()->getStorage('image_style')->load('thumbnail');
if (!$image_style) {
  echo "⚠ Attention: Le style d'image 'thumbnail' n'existe pas. La prévisualisation pourrait ne pas fonctionner.\n";
  echo "  Vous pouvez créer ce style via l'interface d'administration ou utiliser un autre style existant.\n";
}
else {
  echo "✓ Le style d'image 'thumbnail' existe.\n";
}

echo "\nConfiguration terminée. Videz le cache pour que les changements prennent effet.\n";

