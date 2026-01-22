<?php

/**
 * @file
 * Script pour réinitialiser tous les votes des sondages.
 * 
 * Usage: php reset-poll-votes.php
 */

use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;

// Charger Drupal
$autoloader = require_once __DIR__ . '/autoload.php';
$request = Request::createFromGlobals();
$kernel = DrupalKernel::createFromRequest($request, $autoloader, 'prod');
$kernel->boot();
$container = $kernel->getContainer();

// Charger le storage des nœuds
$node_storage = $container->get('entity_type.manager')->getStorage('node');

// Récupérer tous les sondages
$query = $node_storage->getQuery()
  ->condition('type', 'poll')
  ->condition('status', 1)
  ->accessCheck(FALSE);
$nids = $query->execute();

if (empty($nids)) {
  echo "Aucun sondage trouvé.\n";
  exit(0);
}

echo "Trouvé " . count($nids) . " sondage(s).\n";

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
  
  // Réinitialiser tous les votes à 0 (forcer même si déjà à 0)
  foreach ($choices as $index => &$choice) {
    $choice['votes'] = 0;
  }
  
  $node->set('field_poll_choices', json_encode($choices));
  $node->save();
  $updated++;
  echo "  -> Votes réinitialisés à 0\n";
}

echo "\nTerminé ! {$updated} sondage(s) mis à jour.\n";
