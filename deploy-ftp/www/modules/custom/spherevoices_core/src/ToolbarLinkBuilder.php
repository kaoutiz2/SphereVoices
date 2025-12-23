<?php

namespace Drupal\spherevoices_core;

use Drupal\Core\Security\TrustedCallbackInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\user\Entity\User;

/**
 * ToolbarLinkBuilder personnalisé pour forcer le rendu des liens utilisateur.
 */
class ToolbarLinkBuilder implements TrustedCallbackInterface {

  use StringTranslationTrait;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $account;

  /**
   * ToolbarLinkBuilder constructor.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $account
   *   The current user.
   */
  public function __construct(AccountProxyInterface $account) {
    $this->account = $account;
  }

  /**
   * Lazy builder callback for rendering toolbar links.
   *
   * @return array
   *   A renderable array as expected by the renderer service.
   */
  public function renderToolbarLinks() {
    $links = [
      'account' => [
        'title' => $this->t('Voir le profil'),
        'url' => Url::fromRoute('user.page'),
        'attributes' => [
          'title' => $this->t('Compte utilisateur'),
        ],
      ],
      'account_edit' => [
        'title' => $this->t('Modifier le profil'),
        'url' => Url::fromRoute('entity.user.edit_form', ['user' => $this->account->id()]),
        'attributes' => [
          'title' => $this->t('Modifier le compte utilisateur'),
        ],
      ],
      'logout' => [
        'title' => $this->t('Se déconnecter'),
        'url' => Url::fromRoute('user.logout'),
      ],
    ];
    $build = [
      '#theme' => 'links__toolbar_user',
      '#links' => $links,
      '#attributes' => [
        'class' => ['toolbar-menu'],
      ],
      '#cache' => [
        'contexts' => ['user'],
        'max-age' => 0, // Désactiver le cache pour forcer le rendu
      ],
    ];

    return $build;
  }

  /**
   * Lazy builder callback for rendering the username.
   *
   * @return array
   *   A renderable array as expected by the renderer service.
   */
  public function renderDisplayName() {
    $account = User::load($this->account->id());
    $username = $account ? $account->getDisplayName() : $this->account->getAccountName();
    
    return [
      '#markup' => $username ?: $this->t('Mon compte'),
      '#cache' => [
        'contexts' => ['user'],
        'max-age' => 0, // Désactiver le cache pour forcer le rendu
      ],
    ];
  }

}

