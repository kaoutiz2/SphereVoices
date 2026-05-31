<?php

/**
 * @file
 * Helpers for web ops scripts (Drupal 10 bootstrap, PHP CLI resolution).
 */

use Drupal\Core\Cache\Cache;

/**
 * Bootstraps Drupal from a standalone www/*.php script.
 */
function spherevoices_ops_bootstrap_drupal(string $drupal_root): \Drupal\Core\DrupalKernel {
  @ini_set('memory_limit', '512M');
  @set_time_limit(300);

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

  $common = $drupal_root . '/core/includes/common.inc';
  if (is_readable($common)) {
    require_once $common;
  }

  return $kernel;
}

/**
 * Runs one cache rebuild step and records the result.
 */
function spherevoices_ops_step(array &$log, string $label, callable $callback): void {
  try {
    $callback();
    $log[] = ['ok' => TRUE, 'label' => $label];
  }
  catch (\Throwable $e) {
    $log[] = [
      'ok' => FALSE,
      'label' => $label,
      'error' => $e->getMessage(),
    ];
  }
}

/**
 * Full cache rebuild without Drush (equivalent drush cr).
 *
 * @return array<int, array{ok: bool, label: string, error?: string}>
 */
function spherevoices_ops_rebuild_cache(\Drupal\Core\DrupalKernel $kernel): array {
  $log = [];

  if (function_exists('drupal_flush_all_caches')) {
    spherevoices_ops_step($log, 'drupal_flush_all_caches()', function () use ($kernel) {
      drupal_flush_all_caches($kernel);
    });
    if (end($log)['ok'] ?? FALSE) {
      return $log;
    }
  }

  spherevoices_ops_step($log, 'Cache bins deleteAll()', function () {
    foreach (Cache::getBins() as $cache_backend) {
      $cache_backend->deleteAll();
    }
  });

  spherevoices_ops_step($log, 'Assets CSS/JS + query string', function () {
    \Drupal::service('asset.css.collection_optimizer')->deleteAll();
    \Drupal::service('asset.js.collection_optimizer')->deleteAll();
    \Drupal::service('asset.query_string')->reset();
  });

  spherevoices_ops_step($log, 'Twig invalidate()', function () {
    \Drupal::service('twig')->invalidate();
  });

  spherevoices_ops_step($log, 'Theme refreshInfo()', function () {
    \Drupal::service('extension.list.theme_engine')->reset();
    \Drupal::service('theme_handler')->refreshInfo();
    \Drupal::theme()->resetActiveTheme();
  });

  spherevoices_ops_step($log, 'Plugin cache clearer', function () {
    \Drupal::service('plugin.cache_clearer')->clearCachedDefinitions();
  });

  spherevoices_ops_step($log, 'Router rebuild()', function () {
    \Drupal::service('router.builder')->rebuild();
  });

  spherevoices_ops_step($log, 'Cache tags rendered/library_info', function () {
    Cache::invalidateTags(['rendered', 'config:core.extension', 'library_info']);
  });

  spherevoices_ops_step($log, 'Kernel invalidateContainer()', function () use ($kernel) {
    $kernel->invalidateContainer();
  });

  return $log;
}

/**
 * Whether Drupal maintenance mode is active (uses state, not config UI only).
 */
function spherevoices_ops_maintenance_enabled(): bool {
  return (bool) \Drupal::state()->get('system.maintenance_mode');
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

/**
 * Renders rebuild log lines as HTML list items.
 */
function spherevoices_ops_render_rebuild_log(array $log): string {
  $html = '<ul>';
  foreach ($log as $entry) {
    $class = !empty($entry['ok']) ? 'success' : 'error';
    $line = htmlspecialchars($entry['label'], ENT_QUOTES, 'UTF-8');
    if (empty($entry['ok']) && !empty($entry['error'])) {
      $line .= ' — ' . htmlspecialchars($entry['error'], ENT_QUOTES, 'UTF-8');
    }
    $icon = !empty($entry['ok']) ? '✅' : '❌';
    $html .= '<li class="' . $class . '">' . $icon . ' ' . $line . '</li>';
  }
  $html .= '</ul>';
  return $html;
}

/**
 * Runs drush cr from project root with explicit Drupal root.
 *
 * @return array{ok: bool, command: string, output: string, code: int}
 */
function spherevoices_ops_run_drush_cr(string $www_root): array {
  $paths = spherevoices_ops_paths($www_root);
  $php = spherevoices_ops_resolve_cli_php();
  if (!$paths['drush'] || !$php) {
    return [
      'ok' => FALSE,
      'command' => '',
      'output' => 'Drush or PHP CLI not found.',
      'code' => 127,
    ];
  }

  $command = 'cd ' . escapeshellarg($paths['project_root'])
    . ' && ' . escapeshellarg($php)
    . ' ' . escapeshellarg($paths['drush'])
    . ' -r ' . escapeshellarg($paths['drupal_root'])
    . ' cr 2>&1';

  $output = [];
  $code = 0;
  exec($command, $output, $code);

  return [
    'ok' => $code === 0,
    'command' => $command,
    'output' => implode("\n", $output),
    'code' => $code,
  ];
}
