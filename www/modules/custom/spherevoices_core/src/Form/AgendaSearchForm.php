<?php

namespace Drupal\spherevoices_core\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Formulaire de recherche pour l'agenda.
 */
class AgendaSearchForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'agenda_search_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#method'] = 'get';
    $form['#action'] = '/agenda';
    
    $form['wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['form-row']],
    ];
    
    $form['wrapper']['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Rechercher un événement'),
      '#default_value' => \Drupal::request()->query->get('title', ''),
      '#attributes' => [
        'placeholder' => $this->t('Rechercher par titre...'),
      ],
      '#wrapper_attributes' => ['class' => ['form-item']],
    ];
    
    $form['wrapper']['month'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mois'),
      '#default_value' => \Drupal::request()->query->get('month', ''),
      '#attributes' => [
        'type' => 'month',
        'placeholder' => 'YYYY-MM',
      ],
      '#wrapper_attributes' => ['class' => ['form-item']],
    ];
    
    $form['wrapper']['actions'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['form-actions']],
    ];
    
    $form['wrapper']['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Rechercher'),
      '#attributes' => ['class' => ['btn-search']],
    ];
    
    $form['wrapper']['actions']['reset'] = [
      '#type' => 'link',
      '#title' => $this->t('Réinitialiser'),
      '#url' => \Drupal\Core\Url::fromRoute('spherevoices_core.agenda'),
      '#attributes' => ['class' => ['reset-btn']],
    ];
    
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // La soumission est gérée par le GET, pas besoin de traitement ici
  }

}
