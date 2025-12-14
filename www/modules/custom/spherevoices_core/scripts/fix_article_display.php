<?php

/**
 * @file
 * Script pour vérifier et corriger la configuration du mode d'affichage "full" pour les articles.
 * S'assure que le body est configuré comme pour les brèves.
 * 
 * Utilisation: drush php:eval "require 'web/modules/custom/spherevoices_core/scripts/fix_article_display.php';"
 */

// Récupérer le storage pour les entity_view_display
$storage = \Drupal::entityTypeManager()->getStorage('entity_view_display');

// Vérifier la configuration pour les brèves (mode default)
$breve_display = $storage->load('node.breve.default');
if ($breve_display) {
  echo "Configuration des brèves (default):\n";
  $breve_body = $breve_display->getComponent('body');
  if ($breve_body) {
    echo "  - Body configuré: " . print_r($breve_body, TRUE) . "\n";
  } else {
    echo "  - Body NON configuré\n";
  }
}

// Vérifier la configuration pour les articles (mode full)
$article_display = $storage->load('node.article.full');
if ($article_display) {
  echo "\nConfiguration actuelle des articles (full):\n";
  $article_body = $article_display->getComponent('body');
  if ($article_body) {
    echo "  - Body configuré: " . print_r($article_body, TRUE) . "\n";
  } else {
    echo "  - Body NON configuré - CORRECTION NÉCESSAIRE\n";
    
    // Copier la configuration du body depuis les brèves
    if ($breve_display && $breve_body) {
      $article_display->setComponent('body', $breve_body);
      $article_display->save();
      echo "  ✓ Body ajouté à la configuration des articles\n";
    }
  }
} else {
  echo "\nConfiguration des articles (full) n'existe pas - CRÉATION NÉCESSAIRE\n";
  
  // Créer la configuration en copiant depuis les brèves
  if ($breve_display) {
    $article_display = $storage->create([
      'targetEntityType' => 'node',
      'bundle' => 'article',
      'mode' => 'full',
      'status' => TRUE,
    ]);
    
    // Copier tous les composants depuis les brèves
    $components = $breve_display->getComponents();
    foreach ($components as $field_name => $component) {
      $article_display->setComponent($field_name, $component);
    }
    
    $article_display->save();
    echo "  ✓ Configuration créée avec le body configuré\n";
  }
}

// Vérifier aussi le mode "default" pour les articles
$article_default = $storage->load('node.article.default');
if ($article_default) {
  echo "\nConfiguration des articles (default):\n";
  $article_default_body = $article_default->getComponent('body');
  if ($article_default_body) {
    echo "  - Body configuré\n";
  } else {
    echo "  - Body NON configuré - CORRECTION NÉCESSAIRE\n";
    if ($breve_display && $breve_body) {
      $article_default->setComponent('body', $breve_body);
      $article_default->save();
      echo "  ✓ Body ajouté à la configuration des articles (default)\n";
    }
  }
}

echo "\n✓ Vérification terminée. Videz le cache avec: drush cr\n";



