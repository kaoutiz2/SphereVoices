<?php

namespace Drupal\spherevoices_core\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\file\FileInterface;

/**
 * Builds render arrays for configured ad placements.
 */
class AdSlotManager {

  public const PLACEMENT_HEADER = 'header';

  public const PLACEMENT_SIDEBAR = 'sidebar';

  public const PLACEMENT_GRID = 'grid';

  public const TYPE_IMAGE = 'image';

  public const TYPE_ADSENSE = 'adsense';

  /**
   * File entity storage.
   *
   * @var \Drupal\file\FileStorageInterface
   */
  protected $fileStorage;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * File URL generator.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected FileUrlGeneratorInterface $fileUrlGenerator;

  /**
   * Constructs the service.
   */
  public function __construct(
    ConfigFactoryInterface $configFactory,
    EntityTypeManagerInterface $entityTypeManager,
    FileUrlGeneratorInterface $fileUrlGenerator,
  ) {
    $this->configFactory = $configFactory;
    $this->fileStorage = $entityTypeManager->getStorage('file');
    $this->fileUrlGenerator = $fileUrlGenerator;
  }

  /**
   * Builds a themed ad slot if enabled and configured.
   *
   * @return array
   *   Render array or empty array.
   */
  public function build(string $placement): array {
    if (!in_array($placement, [
      self::PLACEMENT_HEADER,
      self::PLACEMENT_SIDEBAR,
      self::PLACEMENT_GRID,
    ], TRUE)) {
      return [];
    }

    $config = $this->configFactory->get('spherevoices_core.ads');
    if (!$config->get($placement . '_enabled')) {
      return [];
    }

    $type = $config->get($placement . '_type') ?: self::TYPE_IMAGE;
    if ($type === self::TYPE_ADSENSE) {
      return $this->buildAdsense($placement, $config->getCacheTags());
    }

    return $this->buildImage($placement, $config);
  }

  /**
   * Builds an image-based ad slot.
   */
  protected function buildImage(string $placement, $config): array {
    $fid = $config->get($placement . '_image');
    if (empty($fid)) {
      return [
        '#theme' => 'spherevoices_ad_slot',
        '#mode' => 'placeholder',
        '#placement' => $placement,
        '#url' => '',
        '#alt' => '',
        '#image_url' => '',
        '#ad_client' => '',
        '#ad_slot' => '',
        '#ad_format' => '',
        '#placeholder_message' => (string) t('Emplacement publicitaire (test) — ajoutez une image dans la configuration.'),
        '#attached' => [
          'library' => ['spherevoices_core/ads'],
        ],
        '#cache' => [
          'tags' => $config->getCacheTags(),
          'contexts' => ['languages:language_interface'],
        ],
      ];
    }

    $file = $this->fileStorage->load($fid);
    if (!$file instanceof FileInterface || !$file->isPermanent()) {
      return [
        '#theme' => 'spherevoices_ad_slot',
        '#mode' => 'placeholder',
        '#placement' => $placement,
        '#url' => '',
        '#alt' => '',
        '#image_url' => '',
        '#ad_client' => '',
        '#ad_slot' => '',
        '#ad_format' => '',
        '#placeholder_message' => (string) t('Emplacement publicitaire (test) — image introuvable ou non publiée.'),
        '#attached' => [
          'library' => ['spherevoices_core/ads'],
        ],
        '#cache' => [
          'tags' => $config->getCacheTags(),
          'contexts' => ['languages:language_interface'],
        ],
      ];
    }

    $image_url = $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
    $url = trim((string) $config->get($placement . '_url'));
    $alt = trim((string) $config->get($placement . '_alt'));
    if ($alt === '') {
      $alt = (string) t('Publicité');
    }

    return [
      '#theme' => 'spherevoices_ad_slot',
      '#mode' => self::TYPE_IMAGE,
      '#placement' => $placement,
      '#url' => $url,
      '#alt' => $alt,
      '#image_url' => $image_url,
      '#ad_client' => '',
      '#ad_slot' => '',
      '#ad_format' => '',
      '#placeholder_message' => '',
      '#attached' => [
        'library' => ['spherevoices_core/ads'],
      ],
      '#cache' => [
        'tags' => $config->getCacheTags(),
        'contexts' => ['languages:language_interface'],
      ],
    ];
  }

  /**
   * Builds a Google AdSense ad unit or a visible placeholder for layout tests.
   */
  protected function buildAdsense(string $placement, array $cache_tags): array {
    $config = $this->configFactory->get('spherevoices_core.ads');
    $client = AdSenseHelper::sanitizeClientId((string) $config->get('adsense_client'));
    $slot = AdSenseHelper::sanitizeSlotId((string) $config->get($placement . '_ad_slot'));
    $format = AdSenseHelper::sanitizeFormat((string) $config->get($placement . '_ad_format'));

    $base = [
      '#placement' => $placement,
      '#url' => '',
      '#alt' => '',
      '#image_url' => '',
      '#attached' => [
        'library' => ['spherevoices_core/ads'],
      ],
      '#cache' => [
        'tags' => $cache_tags,
        'contexts' => ['languages:language_interface'],
      ],
    ];

    // Full AdSense unit when client + slot are configured.
    if ($client !== '' && $slot !== '') {
      $build = $base + [
        '#theme' => 'spherevoices_ad_slot',
        '#mode' => self::TYPE_ADSENSE,
        '#ad_client' => $client,
        '#ad_slot' => $slot,
        '#ad_format' => $format,
        '#placeholder_message' => (string) t('Google AdSense — en attente de diffusion (compte ou slot à valider).'),
      ];
      $build['#attached']['library'][] = 'spherevoices_core/adsense';
      return $build;
    }

    // Visible test placeholder (no Google script) — layout preview without an account.
    return $base + [
      '#theme' => 'spherevoices_ad_slot',
      '#mode' => 'placeholder',
      '#ad_client' => $client,
      '#ad_slot' => $slot,
      '#ad_format' => $format,
      '#placeholder_message' => (string) t('Emplacement publicitaire (test) — configurez le slot AdSense ou utilisez une image.'),
    ];
  }

  /**
   * Inserts in-grid ads after every N articles.
   *
   * @param array $articles
   *   Article render arrays.
   * @param int $every
   *   Insert an ad after this many articles.
   *
   * @return array
   *   List of items with keys type (article|ad) and render.
   */
  public function interleaveGridAds(array $articles, int $every = 5): array {
    $items = [];
    $count = 0;
    foreach ($articles as $article) {
      $items[] = [
        'type' => 'article',
        'render' => $article,
      ];
      $count++;
      if ($every > 0 && $count % $every === 0) {
        $ad = $this->build(self::PLACEMENT_GRID);
        if (!empty($ad)) {
          $items[] = [
            'type' => 'ad',
            'render' => $ad,
          ];
        }
      }
    }
    return $items;
  }

}
