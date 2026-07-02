<?php

declare(strict_types=1);

namespace Drupal\spherevoices_core\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Pages statiques légales : mentions légales et politique de cookies.
 */
class LegalController extends ControllerBase {

  public function mentionsLegales(): array {
    return [
      '#theme' => 'legal_mentions_legales',
      '#cache' => ['max-age' => 86400],
    ];
  }

  public function politiqueCookies(): array {
    return [
      '#theme' => 'legal_politique_cookies',
      '#cache' => ['max-age' => 86400],
    ];
  }

  public function quiSommesNous(): array {
    return [
      '#theme' => 'legal_qui_sommes_nous',
      '#cache' => ['max-age' => 86400],
    ];
  }

}
