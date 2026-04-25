<?php

namespace Drupal\spherevoices_core\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Liste simple des brèves (puces + lien vers chaque nœud).
 */
class BrevesController extends ControllerBase implements ContainerInjectionInterface {

  public function __construct(
    protected EntityRepositoryInterface $entityRepository,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.repository'),
    );
  }

  /**
   * Titre de page.
   */
  public function title() {
    return $this->t('Brèves');
  }

  /**
   * Page /breves : liste à puces, un lien par brève.
   *
   * @return array
   *   Render array.
   */
  public function page() {
    $storage = $this->entityTypeManager()->getStorage('node');
    $langcode = $this->languageManager()->getCurrentLanguage()->getId();

    $nids = $storage->getQuery()
      ->condition('type', 'breve')
      ->condition('status', NodeInterface::PUBLISHED)
      ->condition('langcode', $langcode)
      ->sort('created', 'DESC')
      ->accessCheck(TRUE)
      ->execute();

    $items = [];
    $cache_tags = ['node_list'];
    if (!empty($nids)) {
      $nodes = $storage->loadMultiple($nids);
      foreach ($nids as $nid) {
        if (!isset($nodes[$nid])) {
          continue;
        }
        $node = $nodes[$nid];
        if (!$node->access('view')) {
          continue;
        }
        $node = $this->entityRepository->getTranslationFromContext($node);
        $items[] = [
          '#type' => 'link',
          '#title' => $node->getTitle(),
          '#url' => $node->toUrl(),
        ];
        $cache_tags = array_merge($cache_tags, $node->getCacheTags());
      }
    }

    $cache_tags = array_unique($cache_tags);
    $cache = [
      'tags' => $cache_tags,
      'contexts' => ['languages:language_interface', 'user.permissions'],
    ];

    if (empty($items)) {
      return [
        '#markup' => '<p class="breves-page-empty">' . $this->t('Aucune brève pour le moment.') . '</p>',
        '#cache' => $cache + ['max-age' => -1],
        '#attached' => [
          'library' => ['spherevoices_core/breves_simple_page'],
        ],
      ];
    }

    return [
      '#theme' => 'item_list',
      '#list_type' => 'ul',
      '#items' => $items,
      '#attributes' => ['class' => ['breves-simple-list']],
      '#cache' => $cache + ['max-age' => -1],
      '#attached' => [
        'library' => ['spherevoices_core/breves_simple_page'],
      ],
    ];
  }

}
