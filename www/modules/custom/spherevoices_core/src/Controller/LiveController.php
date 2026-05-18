<?php

namespace Drupal\spherevoices_core\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\spherevoices_core\LiveStreamEmbed;

/**
 * Public Live page with Facebook, Instagram and YouTube embeds.
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
    $streams = [];

    $facebook_url = LiveStreamEmbed::normalizeUrl((string) $config->get('facebook_video_url'));
    if ($config->get('live_is_active') && $facebook_url !== '') {
      $embed_src = LiveStreamEmbed::getFacebookEmbedSrc($facebook_url);
      if ($embed_src) {
        $streams[] = [
          'platform' => 'facebook',
          'label' => $this->t('Facebook'),
          'embed_src' => $embed_src,
        ];
      }
    }

    $instagram_url = LiveStreamEmbed::normalizeUrl((string) $config->get('instagram_live_url'));
    if ($config->get('instagram_live_is_active') && $instagram_url !== ''
      && LiveStreamEmbed::isAllowedInstagramUrl($instagram_url)) {
      $streams[] = [
        'platform' => 'instagram',
        'label' => $this->t('Instagram'),
        'permalink' => $instagram_url,
      ];
    }

    $youtube_url = LiveStreamEmbed::normalizeUrl((string) $config->get('youtube_video_url'));
    if ($config->get('youtube_live_is_active') && $youtube_url !== '') {
      $embed_src = LiveStreamEmbed::getYoutubeEmbedSrc($youtube_url);
      if ($embed_src) {
        $streams[] = [
          'platform' => 'youtube',
          'label' => $this->t('YouTube'),
          'embed_src' => $embed_src,
        ];
      }
    }

    $has_streams = count($streams) > 0;
    $build = [
      '#theme' => 'live_streams_page',
      '#streams' => $streams,
      '#has_streams' => $has_streams,
      '#on_air_heading' => $this->t('Direct en cours'),
      '#empty_message' => $this->t('Pas de live en cours pour le moment.'),
      '#attached' => [
        'library' => ['spherevoices_core/live_streams'],
      ],
      '#cache' => [
        'tags' => $config->getCacheTags(),
        'contexts' => ['languages:language_interface'],
      ],
    ];

    if ($has_streams && $this->hasInstagramStream($streams)) {
      $build['#attached']['library'][] = 'spherevoices_core/instagram_embed';
    }

    return $build;
  }

  /**
   * @param array $streams
   *   Stream render data.
   */
  protected function hasInstagramStream(array $streams): bool {
    foreach ($streams as $stream) {
      if (($stream['platform'] ?? '') === 'instagram') {
        return TRUE;
      }
    }
    return FALSE;
  }

}
