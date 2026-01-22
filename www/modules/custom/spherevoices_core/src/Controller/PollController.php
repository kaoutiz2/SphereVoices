<?php

namespace Drupal\spherevoices_core\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for poll voting.
 */
class PollController extends ControllerBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a PollController object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * AJAX callback to vote on a poll choice.
   */
  public function vote(Request $request) {
    // Récupérer les données depuis POST ou depuis le contenu JSON
    $content = $request->getContent();
    $data = [];
    if (!empty($content)) {
      $data = json_decode($content, TRUE);
    }
    
    $poll_id = $request->request->get('poll_id') ?? $data['poll_id'] ?? NULL;
    $choice_index = $request->request->get('choice_index') ?? $data['choice_index'] ?? NULL;
    $choice_index = (int) $choice_index;
    
    if (!$poll_id || $choice_index === NULL) {
      return new JsonResponse([
        'success' => FALSE,
        'message' => 'Paramètres manquants',
      ], 400);
    }
    
    // Charger le nœud
    $node_storage = $this->entityTypeManager->getStorage('node');
    $node = $node_storage->load($poll_id);
    
    if (!$node || $node->bundle() !== 'poll') {
      return new JsonResponse([
        'success' => FALSE,
        'message' => 'Sondage introuvable',
      ], 404);
    }
    
    // Décoder les choix
    $choices_json = $node->get('field_poll_choices')->value;
    $choices = json_decode($choices_json, TRUE);
    
    if (!is_array($choices) || !isset($choices[$choice_index])) {
      return new JsonResponse([
        'success' => FALSE,
        'message' => 'Choix introuvable',
      ], 404);
    }
    
    // Incrémenter les votes
    if (!isset($choices[$choice_index]['votes'])) {
      $choices[$choice_index]['votes'] = 0;
    }
    $choices[$choice_index]['votes']++;
    
    // Sauvegarder
    $node->set('field_poll_choices', json_encode($choices));
    $node->save();
    
    // Calculer les totaux et pourcentages
    $total_votes = 0;
    foreach ($choices as $choice) {
      $total_votes += isset($choice['votes']) ? (int) $choice['votes'] : 0;
    }
    
    $results = [];
    foreach ($choices as $index => $choice) {
      $votes = isset($choice['votes']) ? (int) $choice['votes'] : 0;
      $percentage = $total_votes > 0 ? round(($votes / $total_votes) * 100, 1) : 0;
      
      $results[] = [
        'index' => $index,
        'text' => $choice['text'],
        'votes' => $votes,
        'percentage' => $percentage,
      ];
    }
    
    return new JsonResponse([
      'success' => TRUE,
      'total_votes' => $total_votes,
      'results' => $results,
    ]);
  }

}
