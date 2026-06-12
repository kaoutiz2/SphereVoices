<?php
/**
 * Script de post-déploiement complet.
 * Exécute : updatedb + config:import + cache:rebuild
 *
 * URL : https://www.spherevoices.com/drush-post-deploy.php?token=spherevoices2026
 * Appelé automatiquement par GitHub Actions après chaque déploiement.
 */

$security_token = 'spherevoices2026';
$provided_token = $_GET['token'] ?? $_SERVER['HTTP_X_DEPLOY_TOKEN'] ?? '';
$json_mode       = isset($_GET['json']) || isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;

if ($json_mode) {
  header('Content-Type: application/json; charset=utf-8');
} else {
  header('Content-Type: text/plain; charset=utf-8');
}

if ($provided_token !== $security_token) {
  http_response_code(403);
  echo $json_mode ? json_encode(['error' => 'Forbidden']) : "403 Forbidden\n";
  exit;
}

// Trouver Drush
$drush_candidates = [
  __DIR__ . '/../vendor/bin/drush',
  __DIR__ . '/../../vendor/bin/drush',
  '/usr/local/bin/drush',
  '/usr/bin/drush',
];
$drush = null;
foreach ($drush_candidates as $candidate) {
  $real = realpath($candidate);
  if ($real && is_executable($real)) {
    $drush = $real;
    break;
  }
}

if (!$drush) {
  http_response_code(500);
  $msg = "ERREUR : Drush introuvable.";
  echo $json_mode ? json_encode(['success' => false, 'error' => $msg]) : $msg . "\n";
  exit;
}

$root = realpath(__DIR__);
$results = [];
$overall_success = true;

/**
 * Trouve le binaire PHP CLI sur OVH.
 *
 * OVH : PHP_BINARY pointe vers php-fpm (sbin/php-fpm).
 * On dérive le CLI en remplaçant sbin/php-fpm par bin/php,
 * puis on essaie plusieurs chemins de secours.
 */
function find_php_cli(): string {
  // 1. Dériver depuis PHP_BINARY : .../sbin/php-fpm → .../bin/php
  $fpm = PHP_BINARY;
  $cli = preg_replace('#/sbin/php-fpm[\d.]*$#', '/bin/php', $fpm);
  if ($cli !== $fpm && is_executable($cli)) {
    return $cli;
  }

  // 2. Variante avec numéro de version : bin/php8.1
  $version = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;
  $cli_versioned = preg_replace('#/sbin/php-fpm[\d.]*$#', '/bin/php' . $version, $fpm);
  if ($cli_versioned !== $fpm && is_executable($cli_versioned)) {
    return $cli_versioned;
  }

  // 3. Liste de secours (chemins OVH connus)
  $base = preg_replace('#/sbin/.*$#', '', $fpm);
  $candidates = array_filter([
    $base ? $base . '/bin/php' : null,
    $base ? $base . '/bin/php' . $version : null,
    '/usr/local/php' . $version . '/bin/php',
    '/usr/local/bin/php' . $version,
    '/usr/local/bin/php',
    '/usr/bin/php' . $version,
    '/usr/bin/php',
  ]);
  foreach ($candidates as $c) {
    if ($c && is_executable($c)) {
      return $c;
    }
  }

  return '';
}

/**
 * Exécute une commande Drush avec le bon interpréteur PHP CLI.
 */
function run_drush(string $drush, string $root, string $cmd): array {
  $php = find_php_cli();
  if (!$php) {
    return [1, 'PHP CLI introuvable. PHP_BINARY=' . PHP_BINARY];
  }
  $full = escapeshellarg($php) . ' ' . escapeshellarg($drush)
        . ' --root=' . escapeshellarg($root)
        . ' ' . $cmd . ' 2>&1';
  $output = [];
  $code   = 0;
  exec($full, $output, $code);
  return [$code, implode("\n", $output)];
}

// Afficher les infos de debug
$php_cli = find_php_cli();
$results['debug'] = [
  'success' => $php_cli !== '',
  'output'  => 'PHP_BINARY=' . PHP_BINARY . ' | PHP CLI=' . ($php_cli ?: 'INTROUVABLE') . ' | Drush=' . $drush,
];

// 1. updatedb
[$code, $out] = run_drush($drush, $root, 'updatedb --yes');
$results['updatedb'] = ['success' => $code === 0, 'output' => $out];
if ($code !== 0) $overall_success = false;

// 2. config:import
[$code, $out] = run_drush($drush, $root, 'config:import --yes');
$results['config_import'] = ['success' => $code === 0, 'output' => $out];
if ($code !== 0) $overall_success = false;

// 3. cache:rebuild
[$code, $out] = run_drush($drush, $root, 'cache:rebuild');
$results['cache_rebuild'] = ['success' => $code === 0, 'output' => $out];
if ($code !== 0) $overall_success = false;

http_response_code($overall_success ? 200 : 500);

if ($json_mode) {
  echo json_encode([
    'success' => $overall_success,
    'steps'   => $results,
    'drush'   => $drush,
  ], JSON_PRETTY_PRINT);
} else {
  foreach ($results as $step => $info) {
    $status = $info['success'] ? 'OK' : 'ERREUR';
    echo "[{$status}] {$step}\n";
    if ($info['output']) {
      echo $info['output'] . "\n";
    }
    echo "---\n";
  }
  echo $overall_success ? "SUCCES : post-déploiement terminé.\n" : "ECHEC : certaines étapes ont échoué.\n";
}
