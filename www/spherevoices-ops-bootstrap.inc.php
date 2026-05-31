<?php

/**
 * @file
 * Helpers for web ops scripts (Drupal 10 bootstrap, PHP CLI resolution).
 */

/**
 * Bootstraps Drupal from a standalone www/*.php script.
 */
function spherevoices_ops_bootstrap_drupal(string $drupal_root): \Drupal\Core\DrupalKernel {
  if (empty($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = 'www.spherevoices.com';
  }
  if (!getenv('DRUPAL_ENV')) {
    putenv('DRUPAL_ENV=production');
    $_ENV['DRUPAL_ENV'] = 'production';
    $_SERVER['DRUPAL_ENV'] = 'production';
  }

  require_once $drupal_root . '/autoload.php';
  $autoloader = require $drupal_root . '/autoload.php';
  $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
  $kernel = \Drupal\Core\DrupalKernel::createFromRequest($request, $autoloader, 'prod');
  $kernel->boot();
  \Drupal::setContainer($kernel->getContainer());
  $kernel->getContainer()->get('request_stack')->push($request);
  return $kernel;
}

/**
 * Full cache rebuild without Drush (equivalent drush cr).
 */
function spherevoices_ops_rebuild_cache(\Drupal\Core\DrupalKernel $kernel): void {
  if (function_exists('cache_rebuild')) {
    cache_rebuild();
  }
  else {
    drupal_flush_all_caches();
  }
  \Drupal::service('asset.css.collection_optimizer')->deleteAll();
  \Drupal::service('asset.js.collection_optimizer')->deleteAll();
  \Drupal::service('router.builder')->rebuild();
  \Drupal\Core\Cache\Cache::invalidateTags(['rendered', 'config:core.extension', 'library_info']);
  $kernel->invalidateContainer();
}

/**
 * Resolves PHP CLI binary on OVH (web PHP is often not in PATH for exec()).
 */
function spherevoices_ops_resolve_cli_php(): ?string {
  if (defined('PHP_BINARY') && PHP_BINARY !== '' && is_executable(PHP_BINARY)) {
    return PHP_BINARY;
  }

  $candidates = [
    '/usr/local/php8.1/bin/php',
    '/usr/local/php8.2/bin/php',
    '/usr/local/php8.3/bin/php',
    '/usr/local/php8.0/bin/php',
    '/opt/alt/php81/usr/bin/php',
    '/opt/alt/php82/usr/bin/php',
    '/opt/alt/php83/usr/bin/php',
    '/opt/alt/php80/usr/bin/php',
    '/usr/bin/php8.1',
    '/usr/bin/php8.2',
    '/usr/bin/php8.3',
    '/usr/local/bin/php',
    '/usr/bin/php',
  ];

  foreach ($candidates as $path) {
    if (is_executable($path)) {
      return $path;
    }
  }

  $which_output = [];
  exec('command -v php 2>/dev/null || which php 2>/dev/null', $which_output, $which_return);
  if ($which_return === 0 && !empty($which_output[0]) && is_executable(trim($which_output[0]))) {
    return trim($which_output[0]);
  }

  return NULL;
}

/**
 * Finds project drush and Drupal root from www/.
 *
 * @return array{drush: string|null, drupal_root: string, project_root: string}
 */
function spherevoices_ops_paths(string $www_root): array {
  $project_root = realpath($www_root . '/..') ?: dirname($www_root);
  $drush_candidates = [
    $project_root . '/vendor/bin/drush',
    $www_root . '/../vendor/bin/drush',
  ];
  $drush = NULL;
  foreach ($drush_candidates as $path) {
    if (is_readable($path)) {
      $drush = $path;
      break;
    }
  }
  return [
    'drush' => $drush,
    'drupal_root' => $www_root,
    'project_root' => $project_root,
  ];
}
