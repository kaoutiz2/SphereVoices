<?php

namespace Drupal\spherevoices_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\views\Views;

/**
 * Provides an 'Agenda Filters' block.
 *
 * @Block(
 *   id = "agenda_filters_block",
 *   admin_label = @Translation("Agenda Filters"),
 * )
 */
class AgendaFiltersBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $view = Views::getView('agenda');
    
    if (!$view) {
      return [];
    }
    
    $view->setDisplay('page_agenda');
    $view->initHandlers();
    
    $exposed_form = $view->display_handler->getPlugin('exposed_form');
    
    if ($exposed_form) {
      $form_state = new \Drupal\Core\Form\FormState();
      $form_state->set('view', $view);
      $form_state->set('display', $view->display_handler->display);
      $form_state->set('rerender', TRUE);
      $form_state->setMethod('get');
      $form_state->disableRedirect();
      
      $form = \Drupal::formBuilder()->buildForm($exposed_form, $form_state);
      
      return [
        '#type' => 'container',
        '#attributes' => ['class' => ['agenda-filters-manual']],
        'form' => $form,
        '#cache' => [
          'max-age' => 0,
        ],
      ];
    }
    
    return [];
  }

}

