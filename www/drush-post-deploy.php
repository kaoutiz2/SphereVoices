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
 * Exécute une commande Drush et retourne [exitCode, output].
 */
function run_drush(string $drush, string $root, string $cmd): array {
  $full = escapeshellarg($drush) . ' --root=' . escapeshellarg($root) . ' ' . $cmd . ' 2>&1';
  $output = [];
  $code   = 0;
  exec($full, $output, $code);
  return [$code, implode("\n", $output)];
}

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
