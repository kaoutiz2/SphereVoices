<?php

namespace Drupal\spherevoices_core\Form;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;

/**
 * Admin settings for Facebook Live embed (public route /en-direct).
 */
class FacebookLiveSettingsForm extends ConfigFormBase {

  /**
   * Access: site configuration admins, or users with the dedicated permission.
   */
  public static function access(AccountInterface $account) {
    return AccessResult::allowedIf(
      $account->hasPermission('administer site configuration') ||
      $account->hasPermission('administer spherevoices facebook live')
    )->cachePerPermissions();
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['spherevoices_core.facebook_live'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'spherevoices_core_facebook_live_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('spherevoices_core.facebook_live');

    $alias_link = Link::fromTextAndUrl(
      $this->t('alias d’URL'),
      Url::fromUri('internal:/admin/config/search/path')
    )->toString();
    $form['public_page_help'] = [
      '#type' => 'markup',
      '#markup' => '<p>' . $this->t('La page publique du lecteur est <code>/en-direct</code>. Si une ancienne page utilise « /live », supprimez ou modifiez son !alias_link, puis ajoutez un alias : chemin interne <code>/en-direct</code>, alias <code>/live</code> si vous souhaitez garder cette adresse.', [
        '!alias_link' => $alias_link,
      ]) . '</p>',
    ];

    $form['facebook_video_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL de la vidéo Facebook'),
      '#default_value' => $config->get('facebook_video_url'),
      '#description' => $this->t('Collez l’URL du direct ou de la vidéo (facebook.com ou fb.watch).'),
      '#size' => 80,
      '#maxlength' => 2048,
    ];

    $form['live_is_active'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Direct en cours'),
      '#description' => $this->t('Cochez pendant l’émission pour afficher le lecteur sur la page Live. Décochez quand le direct est terminé.'),
      '#default_value' => $config->get('live_is_active'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $url = self::normalizeFacebookUrl((string) $form_state->getValue('facebook_video_url'));
    $form_state->setValue('facebook_video_url', $url);

    $active = (bool) $form_state->getValue('live_is_active');

    if ($active && $url === '') {
      $form_state->setErrorByName('facebook_video_url', $this->t('Une URL est requise lorsque le direct est marqué comme en cours.'));
    }

    if ($url !== '' && !self::isAllowedFacebookUrl($url)) {
      $form_state->setErrorByName('facebook_video_url', $this->t('L’URL doit pointer vers une page Facebook ou fb.watch.'));
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('spherevoices_core.facebook_live')
      ->set('facebook_video_url', self::normalizeFacebookUrl((string) $form_state->getValue('facebook_video_url')))
      ->set('live_is_active', (bool) $form_state->getValue('live_is_active'))
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * Trims and adds https:// when the scheme is missing.
   *
   * @param string $url
   *   Raw input.
   *
   * @return string
   *   Normalized URL or empty string.
   */
  public static function normalizeFacebookUrl($url) {
    $url = trim($url);
    if ($url === '') {
      return '';
    }
    if (!preg_match('#^https?://#i', $url)) {
      $url = 'https://' . $url;
    }
    return $url;
  }

  /**
   * Validates URL host for Facebook embed safety.
   *
   * @param string $url
   *   Raw URL string.
   *
   * @return bool
   *   TRUE if the URL is a plausible Facebook video URL.
   */
  public static function isAllowedFacebookUrl($url) {
    if (!UrlHelper::isValid($url, TRUE)) {
      return FALSE;
    }
    $parts = parse_url($url);
    $host = strtolower($parts['host'] ?? '');
    if ($host === '') {
      return FALSE;
    }
    return (bool) preg_match('/(^|\.)facebook\.com$/', $host)
      || (bool) preg_match('/(^|\.)fb\.watch$/', $host);
  }

}
