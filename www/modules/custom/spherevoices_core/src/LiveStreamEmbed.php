<?php

namespace Drupal\spherevoices_core;

use Drupal\Component\Utility\UrlHelper;

/**
 * URL normalization and embed helpers for social live streams.
 */
final class LiveStreamEmbed {

  /**
   * Trims and adds https:// when the scheme is missing.
   */
  public static function normalizeUrl(string $url): string {
    $url = trim($url);
    if ($url === '') {
      return '';
    }
    if (!preg_match('#^https?://#i', $url)) {
      $url = 'https://' . $url;
    }
    return $url;
  }

  public static function isAllowedFacebookUrl(string $url): bool {
    if (!UrlHelper::isValid($url, TRUE)) {
      return FALSE;
    }
    $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');
    return (bool) preg_match('/(^|\.)facebook\.com$/', $host)
      || (bool) preg_match('/(^|\.)fb\.watch$/', $host);
  }

  public static function isAllowedInstagramUrl(string $url): bool {
    if (!UrlHelper::isValid($url, TRUE)) {
      return FALSE;
    }
    $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');
    return (bool) preg_match('/(^|\.)instagram\.com$/', $host);
  }

  public static function isAllowedYoutubeUrl(string $url): bool {
    if (!UrlHelper::isValid($url, TRUE)) {
      return FALSE;
    }
    $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');
    return (bool) preg_match('/(^|\.)youtube\.com$/', $host)
      || (bool) preg_match('/(^|\.)youtu\.be$/', $host)
      || (bool) preg_match('/(^|\.)youtube-nocookie\.com$/', $host);
  }

  public static function getFacebookEmbedSrc(string $video_url): ?string {
    if (!self::isAllowedFacebookUrl($video_url)) {
      return NULL;
    }
    return 'https://www.facebook.com/plugins/video.php?href='
      . rawurlencode($video_url)
      . '&show_text=0&width=1280&height=720';
  }

  /**
   * Extracts a YouTube video ID from common URL shapes.
   */
  public static function getYoutubeVideoId(string $url): string {
    if (!self::isAllowedYoutubeUrl($url)) {
      return '';
    }
    $parts = parse_url($url);
    $host = strtolower($parts['host'] ?? '');
    if (isset($parts['query'])) {
      parse_str($parts['query'], $query);
      if (!empty($query['v'])) {
        return (string) $query['v'];
      }
    }
    $path = trim($parts['path'] ?? '', '/');
    if ($path === '') {
      return '';
    }
    if (preg_match('/(^|\.)youtu\.be$/', $host)) {
      return explode('/', $path)[0];
    }
    foreach (['live', 'embed', 'shorts', 'v'] as $prefix) {
      if (preg_match('#^' . preg_quote($prefix, '#') . '/([^/?]+)#', $path, $m)) {
        return $m[1];
      }
    }
    return '';
  }

  public static function getYoutubeEmbedSrc(string $url): ?string {
    $id = self::getYoutubeVideoId($url);
    if ($id === '') {
      return NULL;
    }
    return 'https://www.youtube.com/embed/' . rawurlencode($id) . '?rel=0';
  }

}
