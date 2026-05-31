<?php

namespace Drupal\spherevoices_core\Form;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\file\Entity\File;
use Drupal\spherevoices_core\Service\AdSenseHelper;
use Drupal\spherevoices_core\Service\AdSlotManager;

/**
 * Configuration form for homepage and header advertising slots.
 */
class AdsSettingsForm extends ConfigFormBase {

  /**
   * Slot keys used in config and form.
   */
  private const SLOTS = [
    'header' => 'Bandeau au-dessus de la navigation (pleine largeur)',
    'sidebar' => 'Encart carré sous le sondage (colonne latérale)',
    'grid' => 'Encart dans la grille (taille article, tous les 5 articles)',
  ];

  /**
   * {@inheritdoc}
   */
  public static function access(AccountInterface $account) {
    return AccessResult::allowedIf(
      $account->hasPermission('administer site configuration') ||
      $account->hasPermission('administer spherevoices ads')
    )->cachePerPermissions();
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['spherevoices_core.ads'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'spherevoices_core_ads_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('spherevoices_core.ads');

    $form['help'] = [
      '#type' => 'markup',
      '#markup' => '<p>' . $this->t('Configurez jusqu’à trois emplacements publicitaires. Chaque emplacement peut utiliser une <strong>image personnalisée</strong> ou un <strong>bloc Google AdSense</strong>. Le bandeau s’affiche sur toutes les pages ; les encarts latéral et grille uniquement sur la page d’accueil.') . '</p>',
    ];

    $form['adsense_global'] = [
      '#type' => 'details',
      '#title' => $this->t('Google AdSense (global)'),
      '#open' => TRUE,
      '#description' => $this->t('Créez un compte sur <a href=":url" target="_blank" rel="noopener">Google AdSense</a>, puis un bloc d’annonces par emplacement. L’identifiant éditeur (ca-pub-…) est commun à tout le site ; chaque emplacement a son propre numéro de slot.', [
        ':url' => 'https://www.google.com/adsense/',
      ]),
    ];
    $form['adsense_global']['adsense_client'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Identifiant éditeur AdSense'),
      '#default_value' => $config->get('adsense_client'),
      '#maxlength' => 64,
      '#placeholder' => 'ca-pub-1234567890123456',
      '#description' => $this->t('Exemple : <code>ca-pub-1234567890123456</code>. Enregistrable sans compte AdSense actif : les encarts s’affichent en mode test tant que le numéro de slot n’est pas renseigné.'),
    ];

    foreach (self::SLOTS as $key => $label) {
      $form[$key] = [
        '#type' => 'details',
        '#title' => $label,
        '#open' => $key === 'header',
      ];
      $form[$key][$key . '_enabled'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Activer cette publicité'),
        '#default_value' => $config->get($key . '_enabled'),
      ];
      $form[$key][$key . '_type'] = [
        '#type' => 'radios',
        '#title' => $this->t('Type de publicité'),
        '#options' => [
          AdSlotManager::TYPE_IMAGE => $this->t('Image personnalisée'),
          AdSlotManager::TYPE_ADSENSE => $this->t('Google AdSense'),
        ],
        '#default_value' => $config->get($key . '_type') ?: AdSlotManager::TYPE_IMAGE,
      ];

      $image_states = [
        'visible' => [
          ':input[name="' . $key . '_type"]' => ['value' => AdSlotManager::TYPE_IMAGE],
        ],
      ];
      $adsense_states = [
        'visible' => [
          ':input[name="' . $key . '_type"]' => ['value' => AdSlotManager::TYPE_ADSENSE],
        ],
      ];

      $form[$key][$key . '_image'] = [
        '#type' => 'managed_file',
        '#title' => $this->t('Image'),
        '#upload_location' => 'public://ads',
        '#default_value' => $config->get($key . '_image') ? [$config->get($key . '_image')] : [],
        '#upload_validators' => [
          'file_validate_extensions' => ['png jpg jpeg gif webp'],
          'file_validate_size' => [5 * 1024 * 1024],
        ],
        '#description' => $this->t('Formats acceptés : PNG, JPG, GIF, WebP. Taille max. 5 Mo.'),
        '#states' => $image_states,
      ];
      $form[$key][$key . '_url'] = [
        '#type' => 'url',
        '#title' => $this->t('Lien de destination'),
        '#default_value' => $config->get($key . '_url'),
        '#maxlength' => 2048,
        '#states' => $image_states,
      ];
      $form[$key][$key . '_alt'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Texte alternatif'),
        '#default_value' => $config->get($key . '_alt'),
        '#description' => $this->t('Laisser vide pour utiliser « Publicité ».'),
        '#maxlength' => 255,
        '#states' => $image_states,
      ];

      $format_hint = match ($key) {
        'header' => $this->t('Bandeau : format « horizontal » ou « auto » recommandé dans AdSense.'),
        'sidebar' => $this->t('Encart carré : format « rectangle » recommandé (ex. 300×250).'),
        default => $this->t('Grille : format « rectangle » ou « auto » recommandé.'),
      };

      $form[$key][$key . '_ad_slot'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Numéro de slot AdSense'),
        '#default_value' => $config->get($key . '_ad_slot'),
        '#description' => $this->t('Copiez le <code>data-ad-slot</code> du code fourni par AdSense pour ce bloc (chiffres uniquement). @hint', [
          '@hint' => $format_hint,
        ]),
        '#maxlength' => 20,
        '#placeholder' => '1234567890',
        '#states' => $adsense_states,
      ];
      $form[$key][$key . '_ad_format'] = [
        '#type' => 'select',
        '#title' => $this->t('Format AdSense'),
        '#options' => [
          'auto' => $this->t('Auto'),
          'horizontal' => $this->t('Horizontal'),
          'rectangle' => $this->t('Rectangle'),
          'vertical' => $this->t('Vertical'),
          'fluid' => $this->t('Fluid'),
        ],
        '#default_value' => $config->get($key . '_ad_format') ?: 'auto',
        '#states' => $adsense_states,
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $raw_client = (string) $form_state->getValue('adsense_client');
    if (trim($raw_client) !== '' && AdSenseHelper::sanitizeClientId($raw_client) === '') {
      $form_state->setErrorByName('adsense_client', $this->t('L’identifiant éditeur doit être au format <code>ca-pub-</code> suivi de chiffres (ex. <code>ca-pub-1234567890123456</code>).'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('spherevoices_core.ads');

    $config->set('adsense_client', AdSenseHelper::sanitizeClientId((string) $form_state->getValue('adsense_client')));

    foreach (array_keys(self::SLOTS) as $key) {
      $config->set($key . '_enabled', (bool) $form_state->getValue($key . '_enabled'));
      $type = $form_state->getValue($key . '_type') ?: AdSlotManager::TYPE_IMAGE;
      $config->set($key . '_type', $type);
      $config->set($key . '_url', trim((string) $form_state->getValue($key . '_url')));
      $config->set($key . '_alt', trim((string) $form_state->getValue($key . '_alt')));
      $config->set($key . '_ad_slot', AdSenseHelper::sanitizeSlotId((string) $form_state->getValue($key . '_ad_slot')));
      $config->set($key . '_ad_format', AdSenseHelper::sanitizeFormat((string) $form_state->getValue($key . '_ad_format')));

      $fid = NULL;
      $image = $form_state->getValue($key . '_image');
      if (!empty($image[0])) {
        $file = File::load($image[0]);
        if ($file) {
          $file->setPermanent();
          $file->save();
          $fid = (int) $file->id();
        }
      }
      $config->set($key . '_image', $fid);
    }

    $config->save();
    parent::submitForm($form, $form_state);
  }

}
