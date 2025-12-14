<?php

/**
 * @file
 * Script pour générer les aperçus (thumbnails) pour toutes les images.
 *
 * Usage: drush php:script web/modules/custom/spherevoices_core/scripts/generate_image_thumbnails.php
 */

use Drupal\file\Entity\File;

// Charger le style d'image thumbnail
$style = \Drupal::entityTypeManager()->getStorage('image_style')->load('thumbnail');

if (!$style) {
  echo "Erreur: Le style d'image 'thumbnail' n'existe pas.\n";
  exit(1);
}

// Récupérer toutes les images
$file_storage = \Drupal::entityTypeManager()->getStorage('file');
$query = $file_storage->getQuery()
  ->condition('filemime', 'image/%', 'LIKE')
  ->accessCheck(FALSE);

$fids = $query->execute();

if (empty($fids)) {
  echo "Aucune image trouvée.\n";
  exit(0);
}

echo "Génération des aperçus pour " . count($fids) . " images...\n\n";

$count = 0;
$errors = 0;

foreach ($fids as $fid) {
  $file = $file_storage->load($fid);
  if (!$file) {
    continue;
  }

  $uri = $file->getFileUri();
  $derivative_uri = $style->buildUri($uri);

  // Vérifier si le fichier dérivé existe déjà
  if (file_exists($derivative_uri)) {
    continue;
  }

  // Vérifier si le fichier original existe
  if (!file_exists($uri)) {
    echo "⚠ Fichier original introuvable: $uri\n";
    $errors++;
    continue;
  }

  try {
    // Générer le fichier dérivé
    $style->createDerivative($uri, $derivative_uri);
    
    if (file_exists($derivative_uri)) {
      $count++;
      echo "✓ Généré: " . basename($uri) . "\n";
    } else {
      echo "✗ Échec: " . basename($uri) . "\n";
      $errors++;
    }
  } catch (\Exception $e) {
    echo "✗ Erreur pour " . basename($uri) . ": " . $e->getMessage() . "\n";
    $errors++;
  }
}

echo "\n";
echo "Terminé !\n";
echo "  - Aperçus générés: $count\n";
if ($errors > 0) {
  echo "  - Erreurs: $errors\n";
}



