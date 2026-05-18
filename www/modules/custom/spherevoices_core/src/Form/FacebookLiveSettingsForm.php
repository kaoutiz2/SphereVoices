<?php

namespace Drupal\spherevoices_core\Form;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\spherevoices_core\LiveStreamEmbed;

/**
 * Admin settings for social live embeds (Facebook, Instagram, YouTube).
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

    $form['facebook'] = [
      '#type' => 'details',
      '#title' => $this->t('Facebook Live'),
      '#open' => TRUE,
    ];
    $form['facebook']['facebook_video_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL de la vidéo Facebook'),
      '#default_value' => $config->get('facebook_video_url'),
      '#description' => $this->t('Collez l’URL du direct ou de la vidéo (facebook.com ou fb.watch).'),
      '#size' => 80,
      '#maxlength' => 2048,
    ];
    $form['facebook']['live_is_active'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Direct Facebook en cours'),
      '#description' => $this->t('Affiche le lecteur Facebook sur la page Live.'),
      '#default_value' => $config->get('live_is_active'),
    ];

    $form['instagram'] = [
      '#type' => 'details',
      '#title' => $this->t('Instagram Live'),
      '#open' => TRUE,
    ];
    $form['instagram']['instagram_live_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL du live Instagram'),
      '#default_value' => $config->get('instagram_live_url'),
      '#description' => $this->t('URL de la page ou du live Instagram (instagram.com).'),
      '#size' => 80,
      '#maxlength' => 2048,
    ];
    $form['instagram']['instagram_live_is_active'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Direct Instagram en cours'),
      '#description' => $this->t('Affiche le lecteur Instagram sur la page Live.'),
      '#default_value' => $config->get('instagram_live_is_active'),
    ];

    $form['youtube'] = [
      '#type' => 'details',
      '#title' => $this->t('YouTube Live'),
      '#open' => TRUE,
    ];
    $form['youtube']['youtube_video_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL de la vidéo YouTube'),
      '#default_value' => $config->get('youtube_video_url'),
      '#description' => $this->t('URL du direct ou de la vidéo (youtube.com ou youtu.be).'),
      '#size' => 80,
      '#maxlength' => 2048,
    ];
    $form['youtube']['youtube_live_is_active'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Direct YouTube en cours'),
      '#description' => $this->t('Affiche le lecteur YouTube sur la page Live.'),
      '#default_value' => $config->get('youtube_live_is_active'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $this->validatePlatform($form_state, 'facebook_video_url', 'live_is_active', [
      LiveStreamEmbed::class,
      'isAllowedFacebookUrl',
    ], $this->t('L’URL doit pointer vers une page Facebook ou fb.watch.'));

    $this->validatePlatform($form_state, 'instagram_live_url', 'instagram_live_is_active', [
      LiveStreamEmbed::class,
      'isAllowedInstagramUrl',
    ], $this->t('L’URL doit pointer vers instagram.com.'));

    $this->validatePlatform($form_state, 'youtube_video_url', 'youtube_live_is_active', [
      LiveStreamEmbed::class,
      'isAllowedYoutubeUrl',
    ], $this->t('L’URL doit pointer vers YouTube (youtube.com ou youtu.be).'));

    parent::validateForm($form, $form_state);
  }

  /**
   * Normalizes and validates one platform field group.
   */
  protected function validatePlatform(
    FormStateInterface $form_state,
    string $url_key,
    string $active_key,
    callable $validator,
    string $invalid_message,
  ): void {
    $url = LiveStreamEmbed::normalizeUrl((string) $form_state->getValue($url_key));
    $form_state->setValue($url_key, $url);
    $active = (bool) $form_state->getValue($active_key);

    if ($active && $url === '') {
      $form_state->setErrorByName($url_key, $this->t('Une URL est requise lorsque le direct est marqué comme en cours.'));
    }
    if ($url !== '' && !$validator($url)) {
      $form_state->setErrorByName($url_key, $invalid_message);
    }
    if ($active_key === 'youtube_live_is_active' && $active && $url !== '' && LiveStreamEmbed::getYoutubeVideoId($url) === '') {
      $form_state->setErrorByName($url_key, $this->t('Impossible d’extraire l’identifiant de la vidéo YouTube depuis cette URL.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('spherevoices_core.facebook_live')
      ->set('facebook_video_url', LiveStreamEmbed::normalizeUrl((string) $form_state->getValue('facebook_video_url')))
      ->set('live_is_active', (bool) $form_state->getValue('live_is_active'))
      ->set('instagram_live_url', LiveStreamEmbed::normalizeUrl((string) $form_state->getValue('instagram_live_url')))
      ->set('instagram_live_is_active', (bool) $form_state->getValue('instagram_live_is_active'))
      ->set('youtube_video_url', LiveStreamEmbed::normalizeUrl((string) $form_state->getValue('youtube_video_url')))
      ->set('youtube_live_is_active', (bool) $form_state->getValue('youtube_live_is_active'))
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * @deprecated Use LiveStreamEmbed::normalizeUrl().
   */
  public static function normalizeFacebookUrl($url) {
    return LiveStreamEmbed::normalizeUrl((string) $url);
  }

  /**
   * @deprecated Use LiveStreamEmbed::isAllowedFacebookUrl().
   */
  public static function isAllowedFacebookUrl($url) {
    return LiveStreamEmbed::isAllowedFacebookUrl((string) $url);
  }

}
