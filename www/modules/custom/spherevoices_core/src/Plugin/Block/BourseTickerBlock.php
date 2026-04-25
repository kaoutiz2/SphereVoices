<?php

declare(strict_types=1);

namespace Drupal\spherevoices_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Bandeau cours (indices) via widget TradingView.
 *
 * @Block(
 *   id = "spherevoices_bourse_ticker",
 *   admin_label = @Translation("Bandeau bourse (indices)"),
 *   category = @Translation("SphereVoices"),
 * )
 */
final class BourseTickerBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    return [
      '#theme' => 'bourse_ticker',
      '#attached' => [
        'library' => ['spherevoices_core/bourse_ticker'],
      ],
      '#cache' => [
        'contexts' => ['languages:language_interface'],
        'max-age' => 3600,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge(): int {
    return 3600;
  }

}
