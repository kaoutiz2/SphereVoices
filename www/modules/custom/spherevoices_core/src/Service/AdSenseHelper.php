<?php

namespace Drupal\spherevoices_core\Service;

/**
 * Validation and script attachment for Google AdSense.
 */
final class AdSenseHelper {

  /**
   * Publisher ID pattern (ca-pub-XXXXXXXXXXXXXXXX).
   */
  public const CLIENT_PATTERN = '/^ca-pub-\d+$/';

  /**
   * Ad unit slot ID (numeric string from AdSense).
   */
  public const SLOT_PATTERN = '/^\d+$/';

  /**
   * Allowed ad format values for data-ad-format.
   */
  public const FORMATS = ['auto', 'rectangle', 'horizontal', 'vertical', 'fluid'];

  /**
   * Normalizes raw input before validation (spaces, typographic dashes).
   */
  public static function normalizeClientInput(string $value): string {
    $value = trim($value);
    $value = preg_replace('/\s+/', '', $value);
    $value = str_replace(['–', '—', '−'], '-', $value);
    return strtolower($value);
  }

  /**
   * Sanitizes a publisher client ID.
   */
  public static function sanitizeClientId(string $value): string {
    $value = self::normalizeClientInput($value);
    return preg_match(self::CLIENT_PATTERN, $value) ? $value : '';
  }

  /**
   * Sanitizes an ad unit slot ID.
   */
  public static function sanitizeSlotId(string $value): string {
    $value = trim($value);
    return preg_match(self::SLOT_PATTERN, $value) ? $value : '';
  }

  /**
   * Sanitizes format or returns default.
   */
  public static function sanitizeFormat(string $value): string {
    $value = trim($value);
    return in_array($value, self::FORMATS, TRUE) ? $value : 'auto';
  }

  /**
   * Attaches the AdSense loader script once per page (html_head).
   *
   * @param array $element
   *   Render array to attach to (#attached).
   * @param string $client_id
   *   Publisher ID (ca-pub-…).
   */
  public static function attachLoaderScript(array &$element, string $client_id): void {
    if ($client_id === '') {
      return;
    }
    $element['#attached']['html_head'][] = [
      [
        '#type' => 'html_tag',
        '#tag' => 'script',
        '#attributes' => [
          'async' => 'async',
          'src' => 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=' . rawurlencode($client_id),
          'crossorigin' => 'anonymous',
        ],
      ],
      'spherevoices_adsense_loader',
    ];
  }

}
