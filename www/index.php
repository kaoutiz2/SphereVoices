<?php

/**
 * @file
 * The PHP page that serves all page requests on a Drupal installation.
 */

use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;

$autoloader = require_once 'autoload.php';

// Force clean URLs: avoid index.php in generated links (logo, menu, etc.).
// When SCRIPT_NAME ends with index.php, Drupal uses it as base path; we set it
// to the script directory so the base path is empty and URLs are like /agenda.
if (!empty($_SERVER['SCRIPT_NAME']) && str_ends_with($_SERVER['SCRIPT_NAME'], 'index.php')) {
  $_SERVER['SCRIPT_NAME'] = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') ?: '/';
}

$kernel = new DrupalKernel('prod', $autoloader);

$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();

$kernel->terminate($request, $response);
