<?php

/**
 * @file
 * Script pour générer des brèves de démonstration réalistes.
 *
 * Usage: drush php:script generate_breves.php
 */

use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;

// Vérifier que le type de contenu "breve" existe
$node_type = \Drupal::entityTypeManager()
  ->getStorage('node_type')
  ->load('breve');

if (!$node_type) {
  echo "❌ Le type de contenu 'breve' n'existe pas. Veuillez d'abord installer le module spherevoices_core.\n";
  exit(1);
}

// Récupérer l'utilisateur admin
$admin_user = User::load(1);
if (!$admin_user) {
  echo "❌ Utilisateur admin introuvable.\n";
  exit(1);
}

// Brèves réalistes avec titres et contenus
$breves = [
  [
    'title' => 'Nouvelle hausse des prix de l\'énergie prévue pour janvier',
    'body' => 'Les tarifs réglementés du gaz et de l\'électricité devraient augmenter de 3,5% en moyenne à partir du 1er janvier, selon les dernières estimations de la Commission de régulation de l\'énergie.',
  ],
  [
    'title' => 'Manifestation prévue samedi contre la réforme des retraites',
    'body' => 'Plusieurs syndicats appellent à une nouvelle journée de mobilisation ce samedi dans les grandes villes françaises pour protester contre le projet de réforme des retraites.',
  ],
  [
    'title' => 'Record de fréquentation pour les musées parisiens en 2024',
    'body' => 'Les musées de la capitale ont enregistré plus de 15 millions de visiteurs cette année, soit une hausse de 12% par rapport à 2023, selon les chiffres publiés par la Ville de Paris.',
  ],
  [
    'title' => 'Nouvelle ligne de métro automatique inaugurée à Lyon',
    'body' => 'La ligne D du métro lyonnais, entièrement automatisée, a été inaugurée ce matin par le maire de la ville. Elle relie désormais le centre-ville à la banlieue nord en 25 minutes.',
  ],
  [
    'title' => 'Accord commercial signé entre la France et le Canada',
    'body' => 'Un nouvel accord de libre-échange a été conclu entre les deux pays, facilitant notamment les échanges dans les secteurs de l\'aéronautique et de l\'agroalimentaire.',
  ],
  [
    'title' => 'Lancement réussi du nouveau satellite d\'observation européen',
    'body' => 'Le satellite Sentinel-6, destiné à surveiller le niveau des océans, a été mis en orbite avec succès depuis la base de Kourou en Guyane française.',
  ],
  [
    'title' => 'Festival de jazz de Montreux annonce sa programmation 2025',
    'body' => 'Herbie Hancock, Diana Krall et Kamasi Washington seront les têtes d\'affiche de la prochaine édition du célèbre festival suisse, qui se déroulera du 3 au 18 juillet.',
  ],
  [
    'title' => 'Nouvelle réglementation sur les trottinettes électriques',
    'body' => 'À partir du 1er mars, les trottinettes électriques devront être immatriculées et leurs conducteurs devront posséder un permis de conduire, selon un décret publié au Journal officiel.',
  ],
  [
    'title' => 'Record de température battu en Antarctique',
    'body' => 'Les scientifiques ont enregistré une température de 18,3°C à la base de recherche Esperanza, soit 0,8°C de plus que le précédent record datant de 2015.',
  ],
  [
    'title' => 'Lancement d\'une nouvelle application de covoiturage urbain',
    'body' => 'La start-up française MobiliCity propose une alternative aux VTC avec un système de covoiturage optimisé pour les trajets quotidiens en ville. L\'application est disponible dès aujourd\'hui.',
  ],
  [
    'title' => 'Exposition Van Gogh prolongée jusqu\'en mars',
    'body' => 'En raison du succès exceptionnel, l\'exposition "Van Gogh et les étoiles" au musée d\'Orsay est prolongée de deux mois. Plus de 800 000 visiteurs l\'ont déjà découverte.',
  ],
  [
    'title' => 'Nouveau plan de rénovation énergétique des bâtiments publics',
    'body' => 'Le gouvernement annonce un investissement de 2 milliards d\'euros sur trois ans pour rénover les écoles, hôpitaux et administrations afin de réduire leur consommation énergétique de 40%.',
  ],
  [
    'title' => 'Championnat du monde de cyclisme : la France remporte l\'or',
    'body' => 'L\'équipe de France masculine a remporté la médaille d\'or au contre-la-montre par équipes lors des championnats du monde qui se déroulent actuellement en Suisse.',
  ],
  [
    'title' => 'Nouvelle espèce de papillon découverte en Amazonie',
    'body' => 'Des chercheurs brésiliens ont identifié une nouvelle espèce de papillon aux ailes bleu irisé dans la forêt amazonienne. Elle a été nommée Morpho amazonicus en référence à son habitat.',
  ],
  [
    'title' => 'Grève des contrôleurs aériens prévue la semaine prochaine',
    'body' => 'Le syndicat des contrôleurs aériens a annoncé une grève de 48 heures à partir de mardi prochain pour protester contre leurs conditions de travail et réclamer des recrutements supplémentaires.',
  ],
  [
    'title' => 'Inauguration du plus grand parc éolien offshore d\'Europe',
    'body' => 'Le parc éolien de Saint-Brieuc, situé au large des Côtes-d\'Armor, a été officiellement inauguré. Avec ses 62 éoliennes, il peut alimenter 835 000 foyers en électricité.',
  ],
  [
    'title' => 'Nouveau traitement contre le cancer du poumon approuvé',
    'body' => 'L\'Agence européenne du médicament a donné son feu vert à un nouveau traitement par immunothérapie qui améliore significativement la survie des patients atteints de cancer du poumon.',
  ],
  [
    'title' => 'Festival de Cannes dévoile sa sélection officielle',
    'body' => 'Vingt films sont en compétition pour la Palme d\'or, dont trois productions françaises. Le festival se déroulera du 14 au 25 mai prochain.',
  ],
  [
    'title' => 'Nouvelle ligne TGV entre Paris et Barcelone annoncée',
    'body' => 'La SNCF et Renfe ont signé un accord pour créer une ligne à grande vitesse directe entre les deux capitales. Le trajet durera 5h30 contre 6h30 actuellement.',
  ],
  [
    'title' => 'Record de participation aux élections européennes',
    'body' => 'Le taux de participation aux élections européennes a atteint 52,3%, soit le plus haut niveau depuis 1994, selon les premières estimations du ministère de l\'Intérieur.',
  ],
];

$created = 0;
$errors = 0;

echo "🚀 Génération de " . count($breves) . " brèves de démonstration...\n\n";

foreach ($breves as $index => $breve_data) {
  try {
    // Vérifier si une brève avec ce titre existe déjà
    $existing = \Drupal::entityQuery('node')
      ->condition('type', 'breve')
      ->condition('title', $breve_data['title'])
      ->accessCheck(FALSE)
      ->execute();
    
    if (!empty($existing)) {
      echo "⏭️  Brève déjà existante : " . $breve_data['title'] . "\n";
      continue;
    }
    
    // Créer la brève
    $node = Node::create([
      'type' => 'breve',
      'title' => $breve_data['title'],
      'body' => [
        'value' => $breve_data['body'],
        'format' => 'basic_html',
      ],
      'uid' => $admin_user->id(),
      'status' => 1, // Publié
      'created' => time() - (count($breves) - $index) * 3600, // Espacer les dates de création
    ]);
    
    $node->save();
    $created++;
    echo "✅ Brève créée : " . $breve_data['title'] . "\n";
  }
  catch (\Exception $e) {
    $errors++;
    echo "❌ Erreur lors de la création de la brève : " . $breve_data['title'] . "\n";
    echo "   Message : " . $e->getMessage() . "\n";
  }
}

echo "\n";
echo "✨ Génération terminée !\n";
echo "   - Brèves créées : $created\n";
echo "   - Erreurs : $errors\n";
echo "\n";
echo "Les brèves sont maintenant visibles dans le bloc 'Brèves' sur la page d'accueil.\n";

