<?php

declare(strict_types=1);

namespace Drupal\spherevoices_core\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\file\FileInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Sert un fichier vidéo public référencé par un média « video » (contourne 404 Apache).
 */
final class InlinePublicVideoController extends ControllerBase {

  /**
   * Access: fichier public:// référencé par au moins un média vidéo consultable.
   */
  public static function access(RouteMatchInterface $route_match, AccountInterface $account): AccessResult {
    $fid = (int) ($route_match->getParameter('file') ?? 0);
    if ($fid <= 0) {
      return AccessResult::forbidden()->setCacheMaxAge(0);
    }
    $file = \Drupal::entityTypeManager()->getStorage('file')->load($fid);
    if (!$file instanceof FileInterface) {
      return AccessResult::forbidden()->setCacheMaxAge(0);
    }
    if (!str_starts_with($file->getFileUri(), 'public://')) {
      return AccessResult::forbidden()->setCacheMaxAge(0);
    }
    if (!_spherevoices_core_file_linked_to_viewable_video_media($file, $account)) {
      return AccessResult::forbidden()->setCacheMaxAge(0);
    }
    return AccessResult::allowed()
      ->addCacheableDependency($file)
      ->addCacheContexts(['user']);
  }

  /**
   * Stream le fichier MP4 (lecteur navigateur, Range requests gérés par Symfony).
   */
  public function deliver($file): BinaryFileResponse {
    $fid = (int) $file;
    $file_entity = $this->entityTypeManager()->getStorage('file')->load($fid);
    if (!$file_entity instanceof FileInterface) {
      throw new NotFoundHttpException();
    }
    if (!str_starts_with($file_entity->getFileUri(), 'public://')) {
      throw new AccessDeniedHttpException();
    }
    if (!_spherevoices_core_file_linked_to_viewable_video_media($file_entity, $this->currentUser())) {
      throw new AccessDeniedHttpException();
    }
    $realpath = \Drupal::service('file_system')->realpath($file_entity->getFileUri());
    if ($realpath === FALSE || !is_readable($realpath)) {
      throw new NotFoundHttpException();
    }
    $mime = $file_entity->getMimeType() ?: 'video/mp4';
    $filename = basename($realpath);
    $response = new BinaryFileResponse($realpath, 200, [
      'Content-Type' => $mime,
    ], TRUE);
    $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $filename);
    $response->setAutoLastModified();
    $response->setPublic();
    $response->setMaxAge(3600);
    $response->headers->set('Accept-Ranges', 'bytes');
    return $response;
  }

}
