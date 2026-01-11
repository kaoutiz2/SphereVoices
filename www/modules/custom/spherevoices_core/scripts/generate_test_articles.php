<?php

/**
 * @file
 * Script Drush pour générer 4 articles de test.
 */

use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;

// Titre et résumés pour les articles
$articles_data = [
  [
    'title' => 'Innovation technologique : L\'IA révolutionne le secteur de la santé',
    'summary' => 'Les avancées en intelligence artificielle transforment la médecine moderne avec des diagnostics plus précis et des traitements personnalisés.',
    'body' => 'L\'intelligence artificielle (IA) est en train de révolutionner le secteur de la santé de manière spectaculaire. Des algorithmes sophistiqués permettent désormais d\'analyser des millions de données médicales en quelques secondes, offrant aux médecins des outils de diagnostic d\'une précision inégalée. Les applications de l\'IA vont de la détection précoce des cancers à la prédiction des épidémies, en passant par la personnalisation des traitements. Cette révolution technologique promet d\'améliorer considérablement la qualité des soins et de sauver des millions de vies dans les années à venir.',
  ],
  [
    'title' => 'Économie verte : Les entreprises s\'engagent pour le climat',
    'summary' => 'De plus en plus d\'entreprises adoptent des stratégies durables et investissent massivement dans les énergies renouvelables.',
    'body' => 'La transition écologique n\'est plus une option mais une nécessité pour les entreprises modernes. Face à l\'urgence climatique, de nombreuses sociétés internationales annoncent des plans ambitieux pour réduire leur empreinte carbone. Les investissements dans les énergies renouvelables battent des records, tandis que de nouvelles technologies vertes émergent chaque jour. Cette mutation économique crée également des millions d\'emplois dans le secteur de l\'environnement, prouvant qu\'écologie et économie peuvent aller de pair.',
  ],
  [
    'title' => 'Culture : Le cinéma français rayonne sur la scène internationale',
    'summary' => 'Les productions françaises multiplient les prix dans les festivals internationaux et séduisent un public mondial.',
    'body' => 'Le septième art français connaît un âge d\'or remarquable. Les films hexagonaux collectionnent les récompenses dans les plus prestigieux festivals internationaux, de Cannes à Venise, en passant par Berlin. Cette reconnaissance mondiale s\'accompagne d\'un succès commercial croissant, avec des productions françaises qui cartonnent dans les salles du monde entier. Les réalisateurs français apportent une vision artistique unique qui séduit tant les critiques que le grand public, confirmant la place de la France comme l\'une des grandes nations du cinéma.',
  ],
  [
    'title' => 'Sport : Records battus lors du championnat mondial d\'athlétisme',
    'summary' => 'Les athlètes repoussent les limites de la performance humaine avec des performances extraordinaires.',
    'body' => 'Le championnat mondial d\'athlétisme vient de s\'achever sur une série de records spectaculaires. Les meilleurs athlètes de la planète ont repoussé les limites de la performance humaine dans plusieurs disciplines. Du sprint au saut en hauteur, en passant par les épreuves de fond, les chronos et les distances ont été pulvérisés. Ces exploits témoignent de l\'évolution constante des méthodes d\'entraînement et de la détermination sans faille de ces champions. Le public a vibré devant ces performances qui resteront gravées dans l\'histoire du sport.',
  ],
];

echo "Génération de 4 articles de test...\n\n";

$created_count = 0;

foreach ($articles_data as $index => $article_data) {
  try {
    $node = Node::create([
      'type' => 'article',
      'title' => $article_data['title'],
      'body' => [
        'value' => $article_data['body'],
        'format' => 'basic_html',
        'summary' => $article_data['summary'],
      ],
      'status' => 1, // Publié
      'promote' => 1, // Promu en page d'accueil
      'sticky' => 0, // Pas épinglé
      'uid' => 1, // Admin
      'created' => time() - ($index * 60), // Échelonner les dates de création
    ]);
    
    $node->save();
    $created_count++;
    
    echo "✓ Article créé : {$article_data['title']} (ID: {$node->id()})\n";
  }
  catch (Exception $e) {
    echo "✗ Erreur lors de la création de l'article : {$article_data['title']}\n";
    echo "  Message : {$e->getMessage()}\n";
  }
}

echo "\n";
echo "═══════════════════════════════════════════\n";
echo "  {$created_count} articles créés avec succès !\n";
echo "═══════════════════════════════════════════\n";
echo "\n";
echo "Vous pouvez maintenant voir ces articles sur la page d'accueil.\n";
echo "Les articles après les 12 premiers devraient apparaître dans la sidebar sous l'agenda.\n";

