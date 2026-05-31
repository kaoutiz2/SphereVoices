<?php

/**
 * @file
 * Diagnostic publicités (config + rendu des 3 emplacements).
 *
 * URL: https://www.spherevoices.com/check-ads.php?token=spherevoices2026
 */

$security_token = 'spherevoices2026';
$provided_token = $_GET['token'] ?? '';

if (!hash_equals($security_token, $provided_token)) {
  http_response_code(403);
  header('Content-Type: text/plain; charset=utf-8');
  echo "Token requis. Exemple : ?token=spherevoices2026\n";
  exit;
}

header('Content-Type: text/html; charset=utf-8');

/**
 * Lit spherevoices_core.ads sans getRawData() (compatibilité maximale).
 */
function _check_ads_config_array(): array {
  $config = \Drupal::config('spherevoices_core.ads');
  $keys = [
    'adsense_client',
    'header_enabled', 'header_type', 'header_ad_slot', 'header_ad_format',
    'sidebar_enabled', 'sidebar_type', 'sidebar_ad_slot', 'sidebar_ad_format',
    'grid_enabled', 'grid_type', 'grid_ad_slot', 'grid_ad_format',
  ];
  $raw = ['_config_exists' => !$config->isNew()];
  foreach ($keys as $key) {
    $raw[$key] = $config->get($key);
  }
  return $raw;
}

/**
 * Résumé du rendu attendu sans appeler AdSlotManager (évite les écarts de déploiement).
 */
function _check_ads_placement_summary(string $placement, array $raw, string $client): array {
  $enabled = !empty($raw[$placement . '_enabled']);
  $type = $raw[$placement . '_type'] ?: 'image';
  $slot_raw = (string) ($raw[$placement . '_ad_slot'] ?? '');
  $slot = '';
  $preview_mode = TRUE;
  $expected_mode = 'disabled';
  $expected_empty = TRUE;

  if (class_exists(\Drupal\spherevoices_core\Service\AdSenseHelper::class)) {
    $slot = \Drupal\spherevoices_core\Service\AdSenseHelper::sanitizeSlotId($slot_raw);
    $preview_mode = \Drupal\spherevoices_core\Service\AdSenseHelper::shouldUsePreviewPlaceholders($client, $slot);
  }

  if ($enabled) {
    $expected_empty = FALSE;
    if ($type === 'adsense') {
      if ($client !== '' && $slot !== '' && !$preview_mode) {
        $expected_mode = 'adsense';
      }
      else {
        $expected_mode = 'placeholder (aperçu)';
      }
    }
    elseif ($type === 'image') {
      $fid = $raw[$placement . '_image'] ?? NULL;
      $expected_mode = empty($fid) ? 'placeholder (image manquante)' : 'image';
    }
    else {
      $expected_mode = $type;
    }
  }

  return [
    'enabled' => $enabled,
    'type' => $type,
    'slot' => $slot_raw,
    'slot_sanitized' => $slot,
    'preview_mode' => $preview_mode,
    'expected_mode' => $expected_mode,
    'expected_empty' => $expected_empty,
  ];
}

try {
  if (empty($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = 'www.spherevoices.com';
  }

  require_once __DIR__ . '/spherevoices-ops-bootstrap.inc.php';
  spherevoices_ops_bootstrap_drupal(__DIR__);

  $raw = _check_ads_config_array();
  $has_service = \Drupal::hasService('spherevoices_core.ad_slot_manager');

  $client = class_exists(\Drupal\spherevoices_core\Service\AdSenseHelper::class)
    ? \Drupal\spherevoices_core\Service\AdSenseHelper::sanitizeClientId((string) ($raw['adsense_client'] ?? ''))
    : trim((string) ($raw['adsense_client'] ?? ''));

  $placements = ['header', 'sidebar', 'grid'];
  $builds = [];
  foreach ($placements as $placement) {
    $builds[$placement] = _check_ads_placement_summary($placement, $raw, $client);
  }

  // Test optionnel du service (ne bloque pas la page si erreur interne).
  $service_build = [];
  $html_preview = [];
  if ($has_service) {
    try {
      $renderer = \Drupal::service('renderer');
      $manager = \Drupal::service('spherevoices_core.ad_slot_manager');
      foreach ($placements as $placement) {
        $build = $manager->build($placement);
        $service_build[$placement] = [
          'render_empty' => empty($build),
          'has_markup' => is_array($build) && isset($build['#markup']),
          'has_theme' => is_array($build) && isset($build['#theme']),
          'mode' => is_array($build) ? ($build['#mode'] ?? NULL) : NULL,
          'preview_only' => is_array($build) ? ($build['#preview_only'] ?? NULL) : NULL,
        ];
        if (!empty($build)) {
          $html_preview[$placement] = (string) $renderer->renderPlain($build);
        }
      }
    }
    catch (\Throwable $serviceError) {
      $service_build['_error'] = $serviceError->getMessage();
    }
  }

  $cookie_consent_path = __DIR__ . '/modules/custom/spherevoices_core/js/cookie-consent.js';
  $cookie_consent_has_preview = is_readable($cookie_consent_path)
    && str_contains((string) file_get_contents($cookie_consent_path), 'previewOnly');

  $report = [
    'generated_at' => date('c'),
    'maintenance_mode' => spherevoices_ops_maintenance_enabled(),
    'maintenance_note' => 'Le mode maintenance utilise system.maintenance_mode (state). Seuls les comptes avec la permission « accéder au site en maintenance » voient le site complet.',
    'theme_default' => (string) \Drupal::config('system.theme')->get('default'),
    'ads_config' => $raw,
    'adsense_client_sanitized' => $client,
    'ad_slot_manager_service' => $has_service,
    'cookie_consent_js_has_previewOnly' => $cookie_consent_has_preview,
    'placements' => $builds,
    'service_build' => $service_build,
    'html_preview' => $html_preview ?? [],
  ];
}
catch (\Throwable $e) {
  http_response_code(500);
  echo '<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><title>Erreur</title></head><body>';
  echo '<h1>Erreur</h1><pre>';
  echo htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "\n\n";
  echo htmlspecialchars($e->getFile(), ENT_QUOTES, 'UTF-8') . ':' . (int) $e->getLine() . "\n\n";
  echo htmlspecialchars($e->getTraceAsString(), ENT_QUOTES, 'UTF-8');
  echo '</pre></body></html>';
  exit;
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Diagnostic publicités — SphereVoices</title>
  <link rel="stylesheet" href="/modules/custom/spherevoices_core/css/ads.css">
  <style>
    body { font-family: system-ui, sans-serif; max-width: 960px; margin: 2rem auto; padding: 0 1rem; background: #f5f5f5; }
    .card { background: #fff; border-radius: 8px; padding: 1.25rem; margin-bottom: 1rem; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
    h1 { margin-top: 0; }
    table { width: 100%; border-collapse: collapse; }
    th, td { text-align: left; padding: .5rem .75rem; border-bottom: 1px solid #eee; vertical-align: top; }
    .ok { color: #198754; font-weight: 600; }
    .warn { color: #b45309; font-weight: 600; }
    .bad { color: #c20017; font-weight: 600; }
    pre { background: #f8f9fa; padding: 1rem; overflow: auto; border-radius: 4px; font-size: .85rem; }
    a { color: #003366; }
    .preview-box { border: 1px solid #ddd; border-radius: 4px; padding: 1rem; margin: .75rem 0; background: #fafafa; }
    .preview-box h3 { margin: 0 0 .5rem; font-size: 1rem; }
  </style>
</head>
<body>
  <div class="card">
    <h1>Diagnostic publicités</h1>
    <p>Généré le <?php echo htmlspecialchars($report['generated_at'], ENT_QUOTES, 'UTF-8'); ?></p>
    <ul>
      <li>Config <code>spherevoices_core.ads</code> :
        <?php echo !empty($report['ads_config']['_config_exists']) ? '<span class="ok">présente</span>' : '<span class="bad">absente (defauts Drupal)</span>'; ?>
      </li>
      <li>Mode maintenance Drupal :
        <?php if ($report['maintenance_mode']): ?>
          <span class="warn">activé</span> — visiteurs anonymes voient la page maintenance (normal si voulu)
        <?php else: ?>
          <span class="ok">désactivé</span>
        <?php endif; ?>
      </li>
      <li>Thème actif : <strong><?php echo htmlspecialchars($report['theme_default'], ENT_QUOTES, 'UTF-8'); ?></strong></li>
      <li>Service <code>ad_slot_manager</code> :
        <?php echo $report['ad_slot_manager_service'] ? '<span class="ok">OK</span>' : '<span class="bad">absent</span>'; ?>
      </li>
      <li>JS <code>cookie-consent.js</code> avec fix <code>previewOnly</code> :
        <?php echo $report['cookie_consent_js_has_previewOnly'] ? '<span class="ok">oui</span>' : '<span class="bad">non (fichier pas déployé)</span>'; ?>
      </li>
    </ul>
  </div>

  <div class="card">
    <h2>Emplacements (config)</h2>
    <table>
      <thead>
        <tr>
          <th>Zone</th>
          <th>Activé</th>
          <th>Type</th>
          <th>Slot</th>
          <th>Mode aperçu</th>
          <th>Rendu attendu</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($report['placements'] as $name => $row): ?>
          <tr>
            <td><strong><?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?></strong></td>
            <td><?php echo !empty($row['enabled']) ? '<span class="ok">oui</span>' : '<span class="bad">non</span>'; ?></td>
            <td><?php echo htmlspecialchars((string) $row['type'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><code><?php echo htmlspecialchars((string) $row['slot'], ENT_QUOTES, 'UTF-8'); ?></code></td>
            <td><?php echo !empty($row['preview_mode']) ? '<span class="ok">oui</span>' : '<span class="warn">non (AdSense réel)</span>'; ?></td>
            <td>
              <?php if (!empty($row['expected_empty'])): ?>
                <span class="bad">rien (désactivé)</span>
              <?php else: ?>
                <span class="ok"><?php echo htmlspecialchars((string) $row['expected_mode'], ENT_QUOTES, 'UTF-8'); ?></span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if (!empty($report['service_build'])): ?>
  <div class="card">
    <h2>Test service PHP (optionnel)</h2>
    <?php if (!empty($report['service_build']['_error'])): ?>
      <p class="bad">Erreur AdSlotManager : <?php echo htmlspecialchars((string) $report['service_build']['_error'], ENT_QUOTES, 'UTF-8'); ?></p>
    <?php else: ?>
      <pre><?php echo htmlspecialchars(json_encode($report['service_build'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?></pre>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <?php if (!empty($report['html_preview'])): ?>
  <div class="card">
    <h2>Aperçu HTML rendu (PHP → renderer)</h2>
    <p>Si le texte « Publicité » apparaît ici mais pas sur le site, le problème vient des templates Twig du thème ou du cache de page.</p>
    <?php foreach ($report['html_preview'] as $name => $html): ?>
      <div class="preview-box">
        <h3><?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?></h3>
        <?php echo $html; ?>
        <details style="margin-top:.75rem;">
          <summary>Code source</summary>
          <pre><?php echo htmlspecialchars($html, ENT_QUOTES, 'UTF-8'); ?></pre>
        </details>
      </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <div class="card">
    <h2>Config complète</h2>
    <pre><?php echo htmlspecialchars(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?></pre>
  </div>

  <div class="card">
    <h2>Autres outils</h2>
    <ul>
      <li><a href="/check-homepage-ads.php?token=spherevoices2026"><strong>Diagnostic HTML accueil (rendu Drupal réel)</strong></a></li>
      <li><a href="/drush-cr.php?token=spherevoices2026"><strong>Vider le cache (recommandé)</strong></a></li>
      <li><a href="/clear-cache-web.php?token=spherevoices2026">Vider le cache (clear-cache-web)</a></li>
      <li><a href="/exec-drush.php?token=spherevoices2026">Exécuter drush cr (fallback auto)</a></li>
      <li><a href="/am-i-logged-in.php?token=spherevoices2026">Vérifier la session Drupal</a></li>
    </ul>
  </div>
</body>
</html>
