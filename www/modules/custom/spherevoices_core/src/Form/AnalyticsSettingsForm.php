<?php

declare(strict_types=1);

namespace Drupal\spherevoices_core\Form;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Formulaire Google Analytics 4 + SEO.
 */
class AnalyticsSettingsForm extends ConfigFormBase {

  protected function getEditableConfigNames(): array {
    return ['spherevoices_core.analytics'];
  }

  public function getFormId(): string {
    return 'spherevoices_core_analytics_settings';
  }

  public static function access(AccountInterface $account): AccessResult {
    return AccessResult::allowedIfHasPermission($account, 'administer site configuration');
  }

  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('spherevoices_core.analytics');

    $form['ga4'] = [
      '#type'  => 'details',
      '#title' => $this->t('Google Analytics 4'),
      '#open'  => TRUE,
    ];

    $form['ga4']['enabled'] = [
      '#type'          => 'checkbox',
      '#title'         => $this->t('Activer Google Analytics 4'),
      '#default_value' => (bool) $config->get('enabled'),
    ];

    $form['ga4']['ga4_measurement_id'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Measurement ID'),
      '#description'   => $this->t(
        'Trouvez cet identifiant dans <strong>Google Analytics → Admin → Flux de données → votre flux</strong>. '
        . 'Format : <code>G-XXXXXXXXXX</code>.'
      ),
      '#default_value' => $config->get('ga4_measurement_id') ?: '',
      '#placeholder'   => 'G-XXXXXXXXXX',
      '#maxlength'     => 30,
      '#states'        => [
        'visible' => [':input[name="enabled"]' => ['checked' => TRUE]],
      ],
    ];

    $form['ga4']['anonymize_ip'] = [
      '#type'          => 'checkbox',
      '#title'         => $this->t('Anonymiser les adresses IP (recommandé RGPD)'),
      '#default_value' => $config->get('anonymize_ip') ?? TRUE,
      '#states'        => [
        'visible' => [':input[name="enabled"]' => ['checked' => TRUE]],
      ],
    ];

    $form['ga4']['track_logged_in'] = [
      '#type'          => 'checkbox',
      '#title'         => $this->t('Suivre les utilisateurs connectés (désactivé par défaut pour éviter de comptabiliser les admins)'),
      '#default_value' => (bool) $config->get('track_logged_in'),
      '#states'        => [
        'visible' => [':input[name="enabled"]' => ['checked' => TRUE]],
      ],
    ];

    $form['seo'] = [
      '#type'  => 'details',
      '#title' => $this->t('Google Search Console'),
      '#open'  => TRUE,
    ];

    $form['seo']['google_search_console'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Code de vérification Search Console'),
      '#description'   => $this->t(
        'Dans <strong>Search Console → Paramètres → Vérification de la propriété → Balise HTML</strong>, '
        . 'copiez uniquement la valeur de l\'attribut <code>content</code> (ex. <code>abc123xyz</code>).'
      ),
      '#default_value' => $config->get('google_search_console') ?: '',
      '#placeholder'   => 'abc123xyz...',
      '#maxlength'     => 200,
    ];

    $form['help'] = [
      '#type'   => 'markup',
      '#markup' => '<div class="messages messages--info" style="margin-top:1rem">'
        . '<strong>Accès rapide :</strong> '
        . '<a href="https://analytics.google.com/" target="_blank" rel="noopener">Google Analytics</a> · '
        . '<a href="https://search.google.com/search-console" target="_blank" rel="noopener">Search Console</a>'
        . '</div>',
    ];

    return parent::buildForm($form, $form_state);
  }

  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $id = trim((string) $form_state->getValue('ga4_measurement_id'));
    if ($form_state->getValue('enabled') && $id !== '' && !preg_match('/^G-[A-Z0-9]{4,}$/i', $id)) {
      $form_state->setErrorByName(
        'ga4_measurement_id',
        $this->t('Le Measurement ID doit être au format <code>G-XXXXXXXXXX</code>.')
      );
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $id = strtoupper(trim((string) $form_state->getValue('ga4_measurement_id')));

    $this->config('spherevoices_core.analytics')
      ->set('enabled', (bool) $form_state->getValue('enabled'))
      ->set('ga4_measurement_id', $id)
      ->set('anonymize_ip', (bool) $form_state->getValue('anonymize_ip'))
      ->set('track_logged_in', (bool) $form_state->getValue('track_logged_in'))
      ->set('google_search_console', trim((string) $form_state->getValue('google_search_console')))
      ->save();

    \Drupal::service('cache.page')->invalidateAll();
    parent::submitForm($form, $form_state);
  }

}
