<?php

/**
 * @file
 * The PHP page that serves all page requests on a Drupal installation.
 */

use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;

$autoloader = require_once 'autoload.php';

// Force clean URLs site-wide (front + back office): no index.php in any link.
$sn = $_SERVER['SCRIPT_NAME'] ?? '';
if ($sn !== '' && str_ends_with($sn, 'index.php')) {
  $script_dir = dirname($sn);
  $_SERVER['SCRIPT_NAME'] = ($script_dir === '/' || $script_dir === '.' || $script_dir === '') ? '/' : $script_dir;

  if (!empty($_SERVER['REQUEST_URI'])) {
    $uri = $_SERVER['REQUEST_URI'];
    $q = (($p = strpos($uri, '?')) !== false) ? substr($uri, $p) : '';
    $path = $q !== '' ? substr($uri, 0, $p) : $uri;
    if (str_starts_with($path, $sn)) {
      $path = substr($path, strlen($sn)) ?: '/';
    }
    $_SERVER['REQUEST_URI'] = $path . $q;
  }
  if (!empty($_SERVER['PHP_SELF']) && str_contains($_SERVER['PHP_SELF'], 'index.php')) {
    $_SERVER['PHP_SELF'] = preg_replace('#/index\.php(/|$)#', '$1', $_SERVER['PHP_SELF']) ?: '/';
  }
}

$kernel = new DrupalKernel('prod', $autoloader);

$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();

$kernel->terminate($request, $response);
