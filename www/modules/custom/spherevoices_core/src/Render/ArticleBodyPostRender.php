<?php

declare(strict_types=1);

namespace Drupal\spherevoices_core\Render;

use Drupal\Core\Security\TrustedCallbackInterface;

/**
 * Trusted #post_render callback for article body (empty media embed cleanup).
 */
final class ArticleBodyPostRender implements TrustedCallbackInterface {

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks(): array {
    return ['postRender'];
  }

  /**
   * Replaces empty .media-video-embed wrappers in rendered body HTML.
   *
   * @param mixed $html
   *   Rendered children HTML.
   * @param array $element
   *   The parent render element.
   *
   * @return string
   *   The altered HTML.
   */
  public static function postRender($html, array $element): string {
    $html = is_string($html) ? $html : (string) $html;
    if ($html === '') {
      return '';
    }
    return _spherevoices_core_cleanup_empty_media_video_embeds($html);
  }

}
