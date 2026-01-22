<?php

namespace Drupal\spherevoices_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Provides a 'Poll' block.
 *
 * @Block(
 *   id = "poll_block",
 *   admin_label = @Translation("Poll Block"),
 *   category = @Translation("SphereVoices")
 * )
 */
class PollBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new PollBlock instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Récupérer le sondage actif le plus récent
    $node_storage = $this->entityTypeManager->getStorage('node');
    
    $query = $node_storage->getQuery()
      ->condition('type', 'poll')
      ->condition('status', 1)
      ->sort('created', 'DESC')
      ->range(0, 1)
      ->accessCheck(TRUE);
    
    $nids = $query->execute();
    
    if (empty($nids)) {
      return [
        '#markup' => '',
        '#cache' => [
          'max-age' => 0,
        ],
      ];
    }
    
    $nid = reset($nids);
    $node = $node_storage->load($nid);
    
    if (!$node) {
      return [];
    }
    
    // Décoder les choix
    $choices = [];
    $total_votes = 0;
    
    if ($node->hasField('field_poll_choices') && !$node->get('field_poll_choices')->isEmpty()) {
      $choices_json = $node->get('field_poll_choices')->value;
      $decoded = json_decode($choices_json, TRUE);
      if (is_array($decoded)) {
        $choices = $decoded;
        // Calculer le total des votes
        foreach ($choices as $choice) {
          $total_votes += isset($choice['votes']) ? (int) $choice['votes'] : 0;
        }
      }
    }
    
    // Construire le render array
    $build = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['poll-block'],
        'data-poll-id' => $node->id(),
      ],
      'title' => [
        '#type' => 'html_tag',
        '#tag' => 'h2',
        '#value' => $node->getTitle(),
        '#attributes' => ['class' => ['poll-title']],
      ],
      'description' => [],
      'choices' => [
        '#type' => 'container',
        '#attributes' => ['class' => ['poll-choices']],
      ],
      '#attached' => [
        'library' => ['spherevoices_core/poll_block'],
      ],
      '#cache' => [
        'tags' => ['node:' . $node->id()],
        'max-age' => 0, // Pas de cache pour les votes en temps réel
      ],
    ];
    
    // Ajouter la description si présente
    if ($node->hasField('field_poll_description') && !$node->get('field_poll_description')->isEmpty()) {
      $build['description'] = [
        '#type' => 'processed_text',
        '#text' => $node->get('field_poll_description')->value,
        '#format' => $node->get('field_poll_description')->format,
        '#attributes' => ['class' => ['poll-description']],
      ];
    }
    
    // Construire les choix
      foreach ($choices as $index => $choice) {
        $votes = isset($choice['votes']) ? (int) $choice['votes'] : 0;
        $percentage = $total_votes > 0 ? round(($votes / $total_votes) * 100, 1) : 0;
        
        $build['choices'][$index] = [
          '#type' => 'container',
          '#attributes' => [
            'class' => ['poll-choice'],
            'data-choice-index' => $index,
          ],
          'text' => [
            '#markup' => '<div class="poll-choice-text-wrapper"><span class="poll-choice-text">' . htmlspecialchars($choice['text'], ENT_QUOTES, 'UTF-8') . '</span><span class="poll-choice-check-icon"></span></div>',
          ],
          'vote_button' => [
            '#type' => 'html_tag',
            '#tag' => 'button',
            '#value' => 'Voter',
            '#attributes' => [
              'type' => 'button',
              'class' => ['poll-vote-button'],
              'data-poll-id' => $node->id(),
              'data-choice-index' => $index,
            ],
          ],
        'results' => [
          '#type' => 'container',
          '#attributes' => ['class' => ['poll-choice-results']],
          'bar_container' => [
            '#type' => 'container',
            '#attributes' => ['class' => ['poll-bar-container']],
            'bar' => [
              '#type' => 'html_tag',
              '#tag' => 'div',
              '#attributes' => [
                'class' => ['poll-bar'],
                'style' => 'width: ' . $percentage . '%;',
              ],
            ],
          ],
          'info' => [
            '#type' => 'container',
            '#attributes' => ['class' => ['poll-choice-info']],
            'votes' => [
              '#markup' => '<span class="poll-votes-count">' . $votes . '</span> vote' . ($votes > 1 ? 's' : ''),
              '#attributes' => ['class' => ['poll-votes']],
            ],
            'percentage' => [
              '#markup' => '<span class="poll-percentage">' . $percentage . '%</span>',
              '#attributes' => ['class' => ['poll-percentage']],
            ],
          ],
        ],
      ];
    }
    
    // Wrapper pour l'AJAX
    $build['#attributes']['id'] = 'poll-block-' . $node->id();
    $build['#attributes']['data-total-votes'] = $total_votes;
    
    return $build;
  }

}
