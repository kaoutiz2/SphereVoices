<?php

/**
 * @file
 * The PHP page that serves all page requests on a Drupal installation.
 */

use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;

// Force HTTPS in $_SERVER when the client used HTTPS (proxy/load balancer behind SSL).
// Evite le contenu mixte : JS/CSS en http:// sur une page https://.
if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
  $_SERVER['HTTPS'] = 'on';
  $_SERVER['SERVER_PORT'] = '443';
}
if (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) === 'on') {
  $_SERVER['HTTPS'] = 'on';
  $_SERVER['SERVER_PORT'] = '443';
}
// Fallback production : si le domaine est le site en prod, forcer HTTPS (au cas où le proxy n'envoie pas les en-têtes).
$host = $_SERVER['HTTP_HOST'] ?? '';
if (($host === 'www.spherevoices.com' || $host === 'spherevoices.com') && empty($_SERVER['HTTPS'])) {
  $_SERVER['HTTPS'] = 'on';
  $_SERVER['SERVER_PORT'] = '443';
}
// Pour Symfony / Drupal (éviter ERR_HTTP2_PROTOCOL_ERROR quand les assets passent en https).
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
  $_SERVER['REQUEST_SCHEME'] = 'https';
  $_SERVER['SERVER_PORT'] = '443';
}

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
    // Si la racine web est le parent de www/, l'URI peut arriver en /www/... : enlever le préfixe pour Drupal
    if (str_starts_with($path, '/www/')) {
      $path = '/' . substr($path, 5) ?: '/';
    } elseif ($path === '/www' || $path === '/www/') {
      $path = '/';
    }
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
