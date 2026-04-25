<?php

namespace Drupal\spherevoices_core\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\spherevoices_core\Form\FacebookLiveSettingsForm;

/**
 * Public Live page with Facebook embed.
 */
class LiveController extends ControllerBase {

  /**
   * Renders the public live page (route /en-direct; alias /live recommended).
   *
   * @return array
   *   A render array.
   */
  public function page() {
    $config = $this->config('spherevoices_core.facebook_live');
    $video_url = FacebookLiveSettingsForm::normalizeFacebookUrl((string) $config->get('facebook_video_url'));
    $active = (bool) $config->get('live_is_active');

    $show_player = $active && $video_url !== ''
      && FacebookLiveSettingsForm::isAllowedFacebookUrl($video_url);

    $embed_src = NULL;
    if ($show_player) {
      $embed_src = 'https://www.facebook.com/plugins/video.php?href='
        . rawurlencode($video_url)
        . '&show_text=0&width=1280&height=720';
    }

    return [
      '#theme' => 'facebook_live_page',
      '#embed_src' => $embed_src,
      '#show_player' => $show_player,
      '#on_air_heading' => $this->t('Direct en cours'),
      '#empty_message' => $this->t('Pas de live en cours pour le moment.'),
      '#attached' => [
        'library' => ['spherevoices_core/facebook_live'],
      ],
      '#cache' => [
        'tags' => $config->getCacheTags(),
        'contexts' => ['languages:language_interface'],
      ],
    ];
  }

}
