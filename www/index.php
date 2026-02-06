<?php

/**
 * @file
 * The PHP page that serves all page requests on a Drupal installation.
 */

use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;

$autoloader = require_once 'autoload.php';

// Production: force clean URLs so generated links do not contain index.php.
// When SCRIPT_NAME is /index.php, Drupal uses it as base path; setting it to /
// makes the base path empty so all URLs are like /agenda, /node/1, etc.
if (!empty($_SERVER['HTTP_HOST']) && str_contains((string) $_SERVER['HTTP_HOST'], 'spherevoices.com')) {
  if (isset($_SERVER['SCRIPT_NAME']) && $_SERVER['SCRIPT_NAME'] === '/index.php') {
    $_SERVER['SCRIPT_NAME'] = '/';
  }
}

$kernel = new DrupalKernel('prod', $autoloader);

$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();

$kernel->terminate($request, $response);
