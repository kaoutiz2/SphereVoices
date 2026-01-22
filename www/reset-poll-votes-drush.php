<?php

/**
 * @file
 * Script drush pour réinitialiser tous les votes des sondages.
 * 
 * Usage: drush php:script reset-poll-votes-drush.php
 */

// Charger le storage des nœuds
$node_storage = \Drupal::entityTypeManager()->getStorage('node');

// Récupérer tous les sondages
$query = $node_storage->getQuery()
  ->condition('type', 'poll')
  ->condition('status', 1)
  ->accessCheck(FALSE);
$nids = $query->execute();

if (empty($nids)) {
  echo "Aucun sondage trouvé.\n";
  return;
}

echo "Trouvé " . count($nids) . " sondage(s).\n\n";

$updated = 0;
foreach ($nids as $nid) {
  $node = $node_storage->load($nid);
  
  if (!$node || !$node->hasField('field_poll_choices')) {
    continue;
  }
  
  $choices_json = $node->get('field_poll_choices')->value;
  if (empty($choices_json)) {
    continue;
  }
  
  $choices = json_decode($choices_json, TRUE);
  if (!is_array($choices)) {
    continue;
  }
  
  // Afficher l'état actuel
  $total_votes = 0;
  foreach ($choices as $choice) {
    $votes = isset($choice['votes']) ? (int) $choice['votes'] : 0;
    $total_votes += $votes;
  }
  echo "Sondage #{$nid} ({$node->getTitle()}) : {$total_votes} vote(s) actuel(s)\n";
  
  // Réinitialiser tous les votes à 0
  foreach ($choices as $index => &$choice) {
    $choice['votes'] = 0;
  }
  
  $node->set('field_poll_choices', json_encode($choices));
  $node->save();
  $updated++;
  echo "  -> Votes réinitialisés à 0\n\n";
}

echo "Terminé ! {$updated} sondage(s) mis à jour.\n";
echo "\nNote: Pour pouvoir revoter dans le navigateur, vous devez aussi vider le localStorage.\n";
echo "Ouvrez la console du navigateur (F12) et exécutez:\n";
echo "  localStorage.removeItem('poll_voted_83');\n";
echo "(Remplacez 83 par l'ID du sondage si différent)\n";
