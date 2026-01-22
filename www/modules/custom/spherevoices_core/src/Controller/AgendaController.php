<?php

namespace Drupal\spherevoices_core\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Render\Markup;

/**
 * Controller for the Agenda pages.
 */
class AgendaController extends ControllerBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructs an AgendaController object.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  /**
   * Display the main agenda page with search and month filters.
   */
  public function agendaPage(Request $request) {
    // Le contenu est géré par le preprocessing du thème et le template page--agenda.html.twig
    return [
      '#markup' => '',
      '#title' => $this->t('Agenda des événements'),
      '#attached' => [
        'library' => [
          'spherevoices_theme/global-styling',
        ],
      ],
      '#cache' => [
        'max-age' => 0,  // Désactiver le cache pour que les paramètres de recherche soient toujours pris en compte
        'contexts' => ['url.query_args'],
      ],
    ];
  }

}
