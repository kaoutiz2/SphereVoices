<?php

namespace Drupal\spherevoices_core\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Controller pour la gestion du Breaking News.
 */
class BreakingNewsController extends ControllerBase {

  /**
   * Récupère le dernier article marqué comme Breaking News.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JSON response avec les données du breaking news.
   */
  public function getBreakingNews() {
    $query = \Drupal::entityQuery('node')
      ->condition('type', 'article')
      ->condition('field_breaking_news', TRUE)
      ->condition('status', 1)
      ->sort('created', 'DESC')
      ->range(0, 1)
      ->accessCheck(TRUE);

    $nids = $query->execute();

    if (!empty($nids)) {
      $node = Node::load(reset($nids));
      $data = [
        'title' => $node->getTitle(),
        'url' => $node->toUrl('canonical', ['absolute' => TRUE])->toString(),
        'summary' => $node->hasField('field_summary') ? $node->get('field_summary')->value : '',
      ];
      return new JsonResponse($data);
    }

    return new JsonResponse(['message' => 'No breaking news'], 404);
  }

}

