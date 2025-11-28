<?php

namespace Drupal\spherevoices_core\Commands;

use Drush\Commands\DrushCommands;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;

/**
 * Commande Drush pour générer des articles d'exemple.
 */
class GenerateSampleArticlesCommand extends DrushCommands {

  /**
   * Génère des articles d'exemple pour tester la page d'accueil.
   *
   * @command spherevoices:generate-sample-articles
   * @aliases sv-gen-articles
   * @usage spherevoices:generate-sample-articles
   *   Génère 10 articles d'exemple avec du contenu réaliste.
   */
  public function generateSampleArticles() {
    $this->output()->writeln('Création des articles d\'exemple...');

    // Récupérer l'utilisateur admin (UID 1)
    $user = User::load(1);
    if (!$user) {
      $this->output()->writeln('Erreur: Utilisateur admin introuvable.');
      return;
    }

    // Articles d'exemple
    $articles = [
      [
        'title' => 'Nouvelle découverte scientifique révolutionnaire dans le domaine de l\'intelligence artificielle',
        'summary' => 'Des chercheurs ont développé un nouveau modèle d\'IA capable de comprendre le contexte avec une précision jamais atteinte auparavant.',
        'body' => 'Une équipe internationale de scientifiques vient d\'annoncer une percée majeure dans le domaine de l\'intelligence artificielle. Le nouveau modèle, développé après des années de recherche, montre des capacités de compréhension contextuelle qui dépassent toutes les attentes. Cette découverte pourrait révolutionner de nombreux secteurs, de la médecine à l\'éducation en passant par les transports.',
        'breaking_news' => TRUE,
        'promote' => TRUE,
        'created' => time() - 3600, // Il y a 1 heure
      ],
      [
        'title' => 'Sommet international sur le climat : engagements historiques annoncés',
        'summary' => 'Les dirigeants mondiaux se sont engagés à réduire les émissions de gaz à effet de serre de 50% d\'ici 2030 lors d\'un sommet historique.',
        'body' => 'Lors d\'un sommet exceptionnel qui s\'est tenu cette semaine, les dirigeants des principales puissances mondiales ont annoncé des engagements sans précédent pour lutter contre le changement climatique. Ces mesures ambitieuses incluent des investissements massifs dans les énergies renouvelables et des réformes structurelles des industries polluantes. Les experts saluent cette initiative comme un tournant décisif dans la lutte contre le réchauffement climatique.',
        'breaking_news' => FALSE,
        'promote' => TRUE,
        'created' => time() - 7200, // Il y a 2 heures
      ],
      [
        'title' => 'Innovation technologique : lancement du premier smartphone pliable à prix abordable',
        'summary' => 'Une nouvelle génération de smartphones pliables arrive sur le marché avec un prix deux fois moins cher que les modèles précédents.',
        'body' => 'Le marché des smartphones pliables connaît une révolution avec l\'arrivée d\'un nouveau modèle qui casse les prix. Pendant que les géants de la tech proposaient des appareils à plus de 2000 euros, cette nouvelle entreprise propose un modèle fonctionnel à moins de 800 euros. Les premiers tests montrent une qualité de construction solide et des performances comparables aux modèles premium.',
        'breaking_news' => FALSE,
        'promote' => TRUE,
        'created' => time() - 10800, // Il y a 3 heures
      ],
      [
        'title' => 'Économie : croissance record dans le secteur des énergies vertes',
        'summary' => 'Le secteur des énergies renouvelables a enregistré une croissance de 35% cette année, créant des milliers d\'emplois.',
        'body' => 'Les investissements dans les énergies renouvelables ont atteint des sommets cette année, avec une croissance de 35% par rapport à l\'année précédente. Cette expansion rapide a généré plus de 50 000 nouveaux emplois dans le secteur, contribuant significativement à la relance économique. Les experts prévoient que cette tendance devrait se poursuivre dans les années à venir.',
        'breaking_news' => FALSE,
        'promote' => TRUE,
        'created' => time() - 14400, // Il y a 4 heures
      ],
      [
        'title' => 'Santé publique : nouvelle campagne de vaccination massive lancée',
        'summary' => 'Les autorités sanitaires lancent une campagne de vaccination ciblant les populations à risque dans toutes les régions.',
        'body' => 'Face à la recrudescence de certaines maladies, les autorités sanitaires ont décidé de lancer une campagne de vaccination d\'envergure. Cette initiative vise à protéger les populations les plus vulnérables et à prévenir la propagation de maladies évitables. Les centres de vaccination ont été renforcés pour accueillir un plus grand nombre de personnes.',
        'breaking_news' => FALSE,
        'promote' => TRUE,
        'created' => time() - 18000, // Il y a 5 heures
      ],
      [
        'title' => 'Sport : record du monde battu aux championnats internationaux',
        'summary' => 'Un athlète a établi un nouveau record du monde dans sa discipline, pulvérisant l\'ancien record de plus de 2 secondes.',
        'body' => 'Dans une performance époustouflante, un athlète a réussi à battre le record du monde qui tenait depuis plus de 10 ans. Cette performance historique a été saluée par les spectateurs et les experts du sport. L\'athlète a déclaré que ce record était le fruit de plusieurs années d\'entraînement intensif et de préparation mentale.',
        'breaking_news' => FALSE,
        'promote' => TRUE,
        'created' => time() - 21600, // Il y a 6 heures
      ],
      [
        'title' => 'Culture : exposition majeure ouvre ses portes au musée national',
        'summary' => 'Une exposition exceptionnelle rassemblant des œuvres d\'artistes renommés du monde entier débute cette semaine.',
        'body' => 'Le musée national accueille une exposition d\'envergure internationale qui promet d\'être l\'événement culturel de l\'année. Plus de 200 œuvres d\'artistes de renommée mondiale seront présentées, certaines pour la première fois dans le pays. Les organisateurs s\'attendent à accueillir plus de 100 000 visiteurs au cours des trois prochains mois.',
        'breaking_news' => FALSE,
        'promote' => TRUE,
        'created' => time() - 25200, // Il y a 7 heures
      ],
      [
        'title' => 'Éducation : réforme majeure du système éducatif annoncée',
        'summary' => 'Le gouvernement dévoile un plan ambitieux pour moderniser l\'éducation et mieux préparer les étudiants aux défis futurs.',
        'body' => 'Une réforme complète du système éducatif a été annoncée, visant à moderniser les méthodes d\'enseignement et à mieux adapter la formation aux besoins du marché du travail. Cette réforme inclut l\'intégration de nouvelles technologies, une refonte des programmes et une meilleure formation des enseignants. Les premières mesures devraient être mises en place dès la rentrée prochaine.',
        'breaking_news' => FALSE,
        'promote' => TRUE,
        'created' => time() - 28800, // Il y a 8 heures
      ],
      [
        'title' => 'Technologie : percée dans le développement de batteries durables',
        'summary' => 'Des chercheurs ont mis au point une nouvelle technologie de batterie qui pourrait durer 10 fois plus longtemps.',
        'body' => 'Une équipe de chercheurs a développé une nouvelle technologie de batterie qui pourrait révolutionner l\'industrie électronique. Cette innovation promet une durée de vie jusqu\'à 10 fois supérieure aux batteries actuelles, tout en étant plus respectueuse de l\'environnement. Les applications potentielles sont nombreuses, des smartphones aux véhicules électriques.',
        'breaking_news' => FALSE,
        'promote' => TRUE,
        'created' => time() - 32400, // Il y a 9 heures
      ],
      [
        'title' => 'International : accord commercial historique signé entre plusieurs nations',
        'summary' => 'Un nouvel accord commercial de grande envergure a été signé, promettant de stimuler les échanges économiques.',
        'body' => 'Plusieurs nations ont signé un accord commercial historique qui devrait faciliter les échanges et stimuler la croissance économique. Cet accord, négocié pendant plusieurs années, réduit les barrières tarifaires et harmonise les réglementations entre les pays signataires. Les économistes prévoient que cet accord pourrait générer des milliards d\'euros d\'échanges supplémentaires.',
        'breaking_news' => FALSE,
        'promote' => TRUE,
        'created' => time() - 36000, // Il y a 10 heures
      ],
    ];

    $created = 0;
    foreach ($articles as $article_data) {
      try {
        $node = Node::create([
          'type' => 'article',
          'title' => $article_data['title'],
          'uid' => $user->id(),
          'status' => 1,
          'promote' => $article_data['promote'] ? 1 : 0,
          'created' => $article_data['created'],
        ]);

        // Ajouter le résumé si le champ existe
        if ($node->hasField('field_summary')) {
          $node->set('field_summary', $article_data['summary']);
        }

        // Ajouter le corps de l'article
        if ($node->hasField('body')) {
          $node->set('body', [
            'value' => $article_data['body'],
            'format' => 'basic_html',
          ]);
        }

        // Marquer comme breaking news si nécessaire
        if ($article_data['breaking_news'] && $node->hasField('field_breaking_news')) {
          $node->set('field_breaking_news', TRUE);
        }

        $node->save();
        $created++;

        $this->output()->writeln("✓ Article créé : {$article_data['title']}");
      }
      catch (\Exception $e) {
        $this->output()->writeln("✗ Erreur lors de la création de l'article : {$article_data['title']}");
        $this->output()->writeln("  Message : " . $e->getMessage());
      }
    }

    $this->output()->writeln("\n✅ {$created} article(s) créé(s) avec succès !");
    $this->output()->writeln("N'oubliez pas de vider le cache : drush cr");
  }

}

