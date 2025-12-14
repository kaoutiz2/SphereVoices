<?php

/**
 * @file
 * Script pour déboguer la prévisualisation d'image.
 */

// Récupérer un article avec une image
$query = \Drupal::entityQuery('node')
  ->condition('type', 'article')
  ->condition('status', 1)
  ->condition('field_image', NULL, 'IS NOT NULL')
  ->range(0, 1)
  ->accessCheck(FALSE);

$nids = $query->execute();

if (empty($nids)) {
  echo "Aucun article avec image trouvé.\n";
  exit(0);
}

$node = \Drupal::entityTypeManager()->getStorage('node')->load(reset($nids));
echo "Article trouvé: " . $node->getTitle() . " (ID: " . $node->id() . ")\n\n";

if ($node->hasField('field_image') && !$node->get('field_image')->isEmpty()) {
  $image_field = $node->get('field_image');
  $file = $image_field->entity;
  
  if ($file) {
    echo "Fichier image:\n";
    echo "  - URI: " . $file->getFileUri() . "\n";
    $url_generator = \Drupal::service('file_url_generator');
    echo "  - URL: " . $url_generator->generateAbsoluteString($file->getFileUri()) . "\n";
    echo "  - Existe: " . (file_exists($file->getFileUri()) ? 'OUI' : 'NON') . "\n";
    
    // Vérifier si l'image est valide
    $image = \Drupal::service('image.factory')->get($file->getFileUri());
    if ($image->isValid()) {
      echo "  - Largeur: " . $image->getWidth() . "px\n";
      echo "  - Hauteur: " . $image->getHeight() . "px\n";
    }
    else {
      echo "  - ⚠ Image invalide\n";
    }
    
    // Vérifier le style thumbnail
    $style = \Drupal::entityTypeManager()->getStorage('image_style')->load('thumbnail');
    if ($style) {
      echo "\nStyle 'thumbnail':\n";
      echo "  - Existe: OUI\n";
      echo "  - Label: " . $style->label() . "\n";
      
      // Générer l'URL du style
      $style_url = $style->buildUrl($file->getFileUri());
      echo "  - URL du style: " . $style_url . "\n";
    }
    else {
      echo "\n⚠ Style 'thumbnail' n'existe pas\n";
    }
  }
  else {
    echo "⚠ Aucun fichier associé au champ field_image\n";
  }
}
else {
  echo "⚠ Le champ field_image est vide\n";
}

// Vérifier la configuration du formulaire
$form_display = \Drupal::entityTypeManager()
  ->getStorage('entity_form_display')
  ->load('node.article.default');

if ($form_display) {
  $component = $form_display->getComponent('field_image');
  echo "\nConfiguration du formulaire:\n";
  echo "  - Type: " . ($component['type'] ?? 'N/A') . "\n";
  if (isset($component['settings']['preview_image_style'])) {
    echo "  - preview_image_style: " . $component['settings']['preview_image_style'] . "\n";
  }
  else {
    echo "  - ⚠ preview_image_style: NON DÉFINI\n";
  }
}

