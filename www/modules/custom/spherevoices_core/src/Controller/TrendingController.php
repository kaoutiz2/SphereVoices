<?php

namespace Drupal\spherevoices_core\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Url;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Page « Tendances » : articles les plus lus (node_counter).
 */
class TrendingController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Constructs a TrendingController.
   */
  public function __construct(
    protected Connection $database,
    protected EntityRepositoryInterface $entityRepository,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('entity.repository')
    );
  }

  /**
   * Page title.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   Translated title.
   */
  public function title() {
    return $this->t('Tendances');
  }

  /**
   * Redirects /trending to the canonical /tendances URL.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A 301 redirect response.
   */
  public function legacyRedirect() {
    $url = Url::fromRoute('spherevoices_core.trending')->setAbsolute();
    return new RedirectResponse($url->toString(), 301);
  }

  /**
   * Builds the trending page.
   *
   * @return array
   *   A render array.
   */
  public function page() {
    // Marqueur de requête pour le thème : teasers « breaking » comme cartes normales (uniquement cette page).
    \Drupal::request()->attributes->set('spherevoices_trending_page', TRUE);

    $storage = $this->entityTypeManager()->getStorage('node');
    $view_builder = $this->entityTypeManager()->getViewBuilder('node');
    $langcode = $this->languageManager()->getCurrentLanguage()->getId();

    $nids = [];
    if ($this->database->schema()->tableExists('node_counter')) {
      $query = $this->database->select('node_field_data', 'n');
      $query->leftJoin('node_counter', 'nc', 'n.nid = nc.nid');
      $query->fields('n', ['nid']);
      $query->addExpression('COALESCE(nc.totalcount, 0)', 'sortcount');
      $query->condition('n.type', 'article');
      $query->condition('n.status', NodeInterface::PUBLISHED);
      $query->condition('n.langcode', $langcode);
      $query->orderBy('sortcount', 'DESC');
      $query->orderBy('n.created', 'DESC');
      $query->range(0, 60);
      $nids = $query->execute()->fetchCol();
    }
    else {
      $nids = $storage->getQuery()
        ->condition('type', 'article')
        ->condition('status', NodeInterface::PUBLISHED)
        ->condition('langcode', $langcode)
        ->sort('created', 'DESC')
        ->range(0, 60)
        ->accessCheck(TRUE)
        ->execute();
    }

    $rows = [];
    $cache_tags = ['node_list'];
    if (!empty($nids)) {
      /** @var \Drupal\node\NodeInterface[] $nodes */
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
        $build = $view_builder->view($node, 'teaser');
        // Carte identique aux autres articles (Twig + suggestion de template).
        $build['#force_standard_teaser'] = TRUE;
        if (!isset($build['#cache'])) {
          $build['#cache'] = [];
        }
        $build['#cache']['tags'] = $node->getCacheTags();
        // Ne pas réutiliser le même rendu teaser que l’accueil (sans ce flag).
        $build['#cache']['contexts'][] = 'route';
        $build['#cache']['contexts'][] = 'url.path';
        $rows[] = $build;
        $cache_tags = array_merge($cache_tags, $node->getCacheTags());
      }
    }

    $cache_tags = array_unique($cache_tags);

    return [
      '#theme' => 'trending_page',
      '#rows' => $rows,
      '#is_empty' => empty($rows),
      '#attached' => [
        'library' => ['spherevoices_core/trending_page'],
      ],
      '#cache' => [
        'tags' => $cache_tags,
        'contexts' => ['languages:language_interface', 'user.permissions'],
        'max-age' => 300,
      ],
    ];
  }

}
