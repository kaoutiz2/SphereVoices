<?php

declare(strict_types=1);

namespace Drupal\spherevoices_core\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Page de catégorie : liste les articles filtrés par étiquette.
 *
 * Route : /categorie/{category}
 * Le paramètre {category} est le slug (ex. "politique", "economie").
 */
class CategoryController extends ControllerBase {

  /**
   * Correspondance slug → nom exact du terme taxonomique.
   */
  private const SLUG_MAP = [
    'politique'     => 'Politique',
    'economie'      => 'Économie',
    'sante'         => 'Santé',
    'sport'         => 'Sport',
    'culture'       => 'Culture',
    'climat'        => 'Climat',
    'science'       => 'Science',
    'entertainment' => 'Entertainment',
  ];

  /**
   * Page de liste des articles d'une catégorie.
   */
  public function page(string $category): array {
    $term_name = self::SLUG_MAP[$category] ?? NULL;

    // Recherche aussi par nom direct (tolérance aux slugs inconnus)
    if ($term_name === NULL) {
      throw new NotFoundHttpException();
    }

    // Charger le terme taxonomique
    $terms = $this->entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadByProperties(['vid' => 'tags', 'name' => $term_name]);

    $term = $terms ? reset($terms) : NULL;

    // Requête des articles publiés avec ce terme
    $articles_rendered = [];
    $total = 0;

    if ($term) {
      $storage      = $this->entityTypeManager()->getStorage('node');
      $view_builder  = $this->entityTypeManager()->getViewBuilder('node');

      $query = $storage->getQuery()
        ->condition('type', 'article')
        ->condition('status', 1)
        ->condition('field_tags', $term->id())
        ->sort('created', 'DESC')
        ->range(0, 60)
        ->accessCheck(TRUE);

      $nids  = $query->execute();
      $total = count($nids);

      if (!empty($nids)) {
        $nodes = $storage->loadMultiple($nids);
        foreach ($nids as $nid) {
          if (isset($nodes[$nid])) {
            $articles_rendered[] = $view_builder->view($nodes[$nid], 'teaser');
          }
        }
      }
    }

    return [
      '#theme'    => 'category_page',
      '#category' => $term_name,
      '#slug'     => $category,
      '#term'     => $term,
      '#articles' => $articles_rendered,
      '#total'    => $total,
      '#cache'    => [
        'tags'     => $term ? array_merge(['node_list'], $term->getCacheTags()) : ['node_list'],
        'contexts' => ['url'],
        'max-age'  => 0,
      ],
    ];
  }

  /**
   * Titre de page dynamique.
   */
  public function title(string $category): string {
    return self::SLUG_MAP[$category] ?? ucfirst($category);
  }

}
