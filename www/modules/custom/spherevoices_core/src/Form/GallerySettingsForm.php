<?php

declare(strict_types=1);

namespace Drupal\spherevoices_core\Form;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\file\Entity\File;

/**
 * Formulaire de configuration de la galerie photo page d'accueil.
 * Utilise managed_file (upload multiple) — identique au pattern AdsSettingsForm.
 */
class GallerySettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['spherevoices_core.gallery'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'spherevoices_core_gallery_settings';
  }

  /**
   * Accès : administrateurs et éditeurs.
   */
  public static function access(AccountInterface $account): AccessResult {
    return AccessResult::allowedIfHasPermission($account, 'administer site configuration')
      ->orIf(AccessResult::allowedIfHasPermission($account, 'administer nodes'));
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('spherevoices_core.gallery');
    $file_ids = $config->get('file_ids') ?? [];

    $form['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Afficher la galerie sur la page d\'accueil'),
      '#default_value' => (bool) $config->get('enabled'),
    ];

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Titre de la section'),
      '#default_value' => $config->get('title') ?: 'Galerie photo',
      '#maxlength' => 120,
      '#states' => [
        'visible' => [':input[name="enabled"]' => ['checked' => TRUE]],
      ],
    ];

    $form['columns'] = [
      '#type' => 'select',
      '#title' => $this->t('Colonnes'),
      '#options' => [2 => '2', 3 => '3', 4 => '4', 5 => '5'],
      '#default_value' => $config->get('columns') ?: 4,
      '#states' => [
        'visible' => [':input[name="enabled"]' => ['checked' => TRUE]],
      ],
    ];

    $form['photos'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Photos de la galerie'),
      '#description' => $this->t(
        'Cliquez sur « Choisir un fichier » pour uploader une ou plusieurs images. '
        . 'Formats acceptés : JPG, PNG, WebP, GIF. Taille max. 10 Mo par image. '
        . 'Vous pouvez supprimer les photos individuellement avec le bouton « Supprimer ».'
      ),
      '#upload_location' => 'public://gallery',
      '#multiple' => TRUE,
      '#default_value' => !empty($file_ids) ? $file_ids : [],
      '#upload_validators' => [
        'file_validate_extensions' => ['jpg jpeg png gif webp'],
        'file_validate_size'       => [10 * 1024 * 1024],
      ],
      '#states' => [
        'visible' => [':input[name="enabled"]' => ['checked' => TRUE]],
      ],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    // Récupérer les FIDs sélectionnés/uploadés.
    $raw = $form_state->getValue('photos') ?? [];
    $file_ids = [];
    foreach ((array) $raw as $fid) {
      $fid = (int) $fid;
      if ($fid <= 0) {
        continue;
      }
      $file = File::load($fid);
      if ($file) {
        // Marquer comme permanent pour qu'il ne soit pas nettoyé par cron.
        $file->setPermanent();
        $file->save();
        $file_ids[] = $fid;
      }
    }

    $this->config('spherevoices_core.gallery')
      ->set('enabled', (bool) $form_state->getValue('enabled'))
      ->set('title', (string) $form_state->getValue('title'))
      ->set('columns', (int) $form_state->getValue('columns'))
      ->set('file_ids', $file_ids)
      ->save();

    // Invalider le cache de la page d'accueil.
    \Drupal::service('cache.page')->invalidateAll();

    parent::submitForm($form, $form_state);
  }

}
