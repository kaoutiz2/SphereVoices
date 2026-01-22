<?php

namespace Drupal\spherevoices_core\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'poll_choices_widget' widget.
 *
 * @FieldWidget(
 *   id = "poll_choices_widget",
 *   label = @Translation("Poll Choices Widget"),
 *   field_types = {
 *     "text_long"
 *   }
 * )
 */
class PollChoicesWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $value = isset($items[$delta]->value) ? $items[$delta]->value : '';
    
    // Récupérer ou initialiser les choix depuis form_state
    $field_name = $this->fieldDefinition->getName();
    $parents = $element['#field_parents'];
    $field_state_key = implode('_', array_merge($parents, [$field_name, $delta]));
    
    // Décoder le JSON si présent
    $choices = [];
    if (!empty($value)) {
      $decoded = json_decode($value, TRUE);
      if (is_array($decoded)) {
        $choices = $decoded;
      }
    }
    
    // Si pas de choix, initialiser avec deux choix vides
    if (empty($choices)) {
      $choices = [
        ['text' => '', 'votes' => 0],
        ['text' => '', 'votes' => 0],
      ];
    }
    
    // Récupérer les choix depuis form_state si disponibles
    $stored_choices = $form_state->get([$field_state_key, 'choices']);
    if ($stored_choices !== NULL) {
      $choices = $stored_choices;
    }
    
    // Gérer les actions AJAX
    $triggering_element = $form_state->getTriggeringElement();
    if ($triggering_element && isset($triggering_element['#name'])) {
      if (strpos($triggering_element['#name'], 'add_choice') !== FALSE) {
        $choices[] = ['text' => '', 'votes' => 0];
        $form_state->set([$field_state_key, 'choices'], $choices);
        $form_state->setRebuild();
      }
      elseif (strpos($triggering_element['#name'], 'remove_choice') !== FALSE) {
        $index = (int) str_replace('remove_choice_', '', $triggering_element['#name']);
        if (count($choices) > 1) {
          unset($choices[$index]);
          $choices = array_values($choices); // Réindexer
          $form_state->set([$field_state_key, 'choices'], $choices);
          $form_state->setRebuild();
        }
      }
    } else {
      // Stocker les choix initiaux
      $form_state->set([$field_state_key, 'choices'], $choices);
    }
    
    $element['choices'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Choix du sondage'),
      '#prefix' => '<div id="poll-choices-wrapper-' . $delta . '">',
      '#suffix' => '</div>',
    ];
    
    foreach ($choices as $index => $choice) {
      $element['choices'][$index] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['poll-choice-item']],
        'text' => [
          '#type' => 'textfield',
          '#title' => $this->t('Choix @num', ['@num' => $index + 1]),
          '#default_value' => isset($choice['text']) ? $choice['text'] : '',
          '#required' => TRUE,
          '#size' => 60,
        ],
        'votes' => [
          '#type' => 'hidden',
          '#value' => isset($choice['votes']) ? (int) $choice['votes'] : 0,
        ],
        'remove' => [
          '#type' => 'button',
          '#value' => $this->t('Supprimer'),
          '#name' => 'remove_choice_' . $index,
          '#limit_validation_errors' => [],
          '#ajax' => [
            'callback' => [$this, 'ajaxUpdateChoices'],
            'wrapper' => 'poll-choices-wrapper-' . $delta,
            'effect' => 'fade',
          ],
        ],
      ];
    }
    
    $element['choices']['add'] = [
      '#type' => 'button',
      '#value' => $this->t('Ajouter un choix'),
      '#name' => 'add_choice',
      '#limit_validation_errors' => [],
      '#ajax' => [
        'callback' => [$this, 'ajaxUpdateChoices'],
        'wrapper' => 'poll-choices-wrapper-' . $delta,
        'effect' => 'fade',
      ],
    ];
    
    // Champ caché pour stocker la valeur JSON
    $element['value'] = [
      '#type' => 'hidden',
      '#value' => $value,
    ];
    
    // Attacher le JavaScript pour mettre à jour le champ caché
    $element['#attached']['library'][] = 'spherevoices_core/poll_choices_widget';
    
    return $element;
  }
  
  /**
   * AJAX callback pour mettre à jour les choix.
   */
  public function ajaxUpdateChoices(array &$form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    $array_parents = $triggering_element['#array_parents'];
    
    // Retirer les deux derniers éléments (add/remove et choices)
    array_pop($array_parents);
    array_pop($array_parents);
    
    // Reconstruire le chemin vers l'élément
    $element = &$form;
    foreach ($array_parents as $parent) {
      $element = &$element[$parent];
    }
    
    return $element['choices'];
  }
  
  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    // Convertir les choix en JSON
    foreach ($values as &$value) {
      if (isset($value['choices'])) {
        $choices = [];
        foreach ($value['choices'] as $index => $choice) {
          if (is_numeric($index) && isset($choice['text']) && !empty($choice['text'])) {
            $choices[] = [
              'text' => $choice['text'],
              'votes' => isset($choice['votes']) ? (int) $choice['votes'] : 0,
            ];
          }
        }
        $value['value'] = json_encode($choices);
        unset($value['choices']);
      }
    }
    return $values;
  }

}
