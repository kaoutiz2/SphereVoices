<?php

/**
 * @file
 * Script pour générer des événements de démonstration réalistes.
 *
 * Usage: drush php:script generate_events.php
 */

use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;

// Vérifier que le type de contenu "event" existe
$node_type = \Drupal::entityTypeManager()
  ->getStorage('node_type')
  ->load('event');

if (!$node_type) {
  echo "❌ Le type de contenu 'event' n'existe pas. Veuillez d'abord installer le module spherevoices_core.\n";
  exit(1);
}

// Récupérer l'utilisateur admin
$admin_user = User::load(1);
if (!$admin_user) {
  echo "❌ Utilisateur admin introuvable.\n";
  exit(1);
}

// Événements réalistes avec titres, contenus et dates
$events = [
  [
    'title' => 'Conférence sur le changement climatique',
    'body' => 'Rejoignez-nous pour une conférence exceptionnelle sur les enjeux du changement climatique et les solutions pour l\'avenir. Des experts internationaux partageront leurs analyses et recommandations.',
    'date' => date('Y-m-d', strtotime('+5 days')),
  ],
  [
    'title' => 'Concert de musique classique à l\'Opéra',
    'body' => 'L\'Orchestre Philharmonique présente une soirée dédiée aux œuvres de Beethoven et Mozart. Une expérience musicale inoubliable dans un cadre prestigieux.',
    'date' => date('Y-m-d', strtotime('+10 days')),
  ],
  [
    'title' => 'Salon du livre et de la littérature',
    'body' => 'Rencontrez vos auteurs préférés, découvrez de nouveaux talents et participez à des débats littéraires passionnants. Plus de 200 exposants présents.',
    'date' => date('Y-m-d', strtotime('+15 days')),
  ],
  [
    'title' => 'Marathon de la ville - Inscription ouverte',
    'body' => 'Préparez-vous pour le marathon annuel ! Parcours de 42 km à travers les plus beaux quartiers de la ville. Inscriptions ouvertes jusqu\'au 31 janvier.',
    'date' => date('Y-m-d', strtotime('+20 days')),
  ],
  [
    'title' => 'Festival de cinéma international',
    'body' => 'Une semaine dédiée au 7ème art avec des projections de films du monde entier, des rencontres avec des réalisateurs et des ateliers pour tous les passionnés.',
    'date' => date('Y-m-d', strtotime('+25 days')),
  ],
  [
    'title' => 'Exposition d\'art contemporain',
    'body' => 'Le Musée d\'Art Moderne présente une exposition exceptionnelle mettant en lumière les artistes émergents de la scène contemporaine internationale.',
    'date' => date('Y-m-d', strtotime('+30 days')),
  ],
  [
    'title' => 'Journée portes ouvertes des universités',
    'body' => 'Découvrez les formations, rencontrez les enseignants et visitez les campus lors de cette journée spécialement organisée pour les futurs étudiants.',
    'date' => date('Y-m-d', strtotime('+35 days')),
  ],
  [
    'title' => 'Forum de l\'emploi et des carrières',
    'body' => 'Plus de 100 entreprises à la rencontre des candidats. Ateliers CV, simulations d\'entretien et conférences sur les métiers de demain.',
    'date' => date('Y-m-d', strtotime('+40 days')),
  ],
  [
    'title' => 'Spectacle de danse contemporaine',
    'body' => 'La compagnie nationale de danse présente une création originale mêlant tradition et modernité. Une performance visuelle et émotionnelle unique.',
    'date' => date('Y-m-d', strtotime('+45 days')),
  ],
  [
    'title' => 'Salon des technologies et de l\'innovation',
    'body' => 'Découvrez les dernières innovations technologiques, l\'intelligence artificielle, la robotique et les solutions pour un avenir durable.',
    'date' => date('Y-m-d', strtotime('+50 days')),
  ],
  [
    'title' => 'Marché artisanal de printemps',
    'body' => 'Artisans locaux, producteurs régionaux et créateurs d\'art se réunissent pour proposer leurs créations uniques. Animations musicales et restauration sur place.',
    'date' => date('Y-m-d', strtotime('+55 days')),
  ],
  [
    'title' => 'Conférence sur l\'entrepreneuriat',
    'body' => 'Entrepreneurs à succès partagent leurs expériences, conseils et stratégies pour réussir dans le monde des affaires. Sessions de networking incluses.',
    'date' => date('Y-m-d', strtotime('+60 days')),
  ],
  [
    'title' => 'Festival de gastronomie',
    'body' => 'Une célébration de la cuisine locale et internationale. Chefs étoilés, démonstrations culinaires, dégustations et ateliers pour petits et grands.',
    'date' => date('Y-m-d', strtotime('+65 days')),
  ],
  [
    'title' => 'Journée mondiale de l\'environnement',
    'body' => 'Activités de sensibilisation, nettoyage collectif, conférences sur la biodiversité et ateliers pratiques pour adopter un mode de vie plus durable.',
    'date' => date('Y-m-d', strtotime('+70 days')),
  ],
  [
    'title' => 'Concert de jazz en plein air',
    'body' => 'Une soirée sous les étoiles avec les meilleurs musiciens de jazz de la région. Ambiance décontractée et conviviale dans le parc municipal.',
    'date' => date('Y-m-d', strtotime('+75 days')),
  ],
];

$created = 0;
$errors = 0;

echo "🚀 Génération de " . count($events) . " événements de démonstration...\n\n";

foreach ($events as $event_data) {
  try {
    // Vérifier si un événement avec ce titre existe déjà
    $existing = \Drupal::entityQuery('node')
      ->condition('type', 'event')
      ->condition('title', $event_data['title'])
      ->accessCheck(FALSE)
      ->execute();
    
    if (!empty($existing)) {
      echo "⏭️  Événement déjà existant : " . $event_data['title'] . "\n";
      continue;
    }
    
    // Créer l'événement
    $node = Node::create([
      'type' => 'event',
      'title' => $event_data['title'],
      'body' => [
        'value' => $event_data['body'],
        'format' => 'basic_html',
      ],
      'field_event_date' => [
        'value' => $event_data['date'],
      ],
      'uid' => $admin_user->id(),
      'status' => 1, // Publié
      'created' => time(),
    ]);
    
    $node->save();
    $created++;
    echo "✅ Événement créé : " . $event_data['title'] . " (Date: " . $event_data['date'] . ")\n";
  }
  catch (\Exception $e) {
    $errors++;
    echo "❌ Erreur lors de la création de l'événement : " . $event_data['title'] . "\n";
    echo "   Message : " . $e->getMessage() . "\n";
  }
}

echo "\n";
echo "✨ Génération terminée !\n";
echo "   - Événements créés : $created\n";
echo "   - Erreurs : $errors\n";
echo "\n";
echo "Les événements sont maintenant visibles dans le bloc 'Agenda' sur la page d'accueil.\n";
echo "Consultez la page complète sur /agenda pour voir tous les événements.\n";

