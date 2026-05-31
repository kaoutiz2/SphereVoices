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

try {
  require_once __DIR__ . '/autoload.php';
  $autoloader = require __DIR__ . '/autoload.php';
  $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
  $kernel = \Drupal\Core\DrupalKernel::createFromRequest($request, $autoloader, 'prod');
  $kernel->boot();
  \Drupal::setContainer($kernel->getContainer());
  $kernel->getContainer()->get('request_stack')->push($request);

  $config = \Drupal::config('spherevoices_core.ads');
  $raw = $config->getRawData();
  $has_service = \Drupal::hasService('spherevoices_core.ad_slot_manager');
  $manager = $has_service ? \Drupal::service('spherevoices_core.ad_slot_manager') : NULL;

  $client = \Drupal\spherevoices_core\Service\AdSenseHelper::sanitizeClientId((string) ($raw['adsense_client'] ?? ''));

  $placements = ['header', 'sidebar', 'grid'];
  $builds = [];
  $preview_by_placement = [];
  if ($manager) {
    foreach ($placements as $placement) {
      $slot = \Drupal\spherevoices_core\Service\AdSenseHelper::sanitizeSlotId((string) ($raw[$placement . '_ad_slot'] ?? ''));
      $preview_by_placement[$placement] = \Drupal\spherevoices_core\Service\AdSenseHelper::shouldUsePreviewPlaceholders($client, $slot);
      $build = $manager->build($placement);
      $builds[$placement] = [
        'enabled' => !empty($raw[$placement . '_enabled']),
        'type' => $raw[$placement . '_type'] ?? 'image',
        'slot' => $raw[$placement . '_ad_slot'] ?? '',
        'preview_mode' => $preview_by_placement[$placement],
        'render_empty' => empty($build),
        'mode' => $build['#mode'] ?? NULL,
        'preview_only' => $build['#preview_only'] ?? NULL,
        'theme' => $build['#theme'] ?? NULL,
      ];
    }
  }

  $cookie_consent_path = __DIR__ . '/modules/custom/spherevoices_core/js/cookie-consent.js';
  $cookie_consent_has_preview = is_readable($cookie_consent_path)
    && str_contains((string) file_get_contents($cookie_consent_path), 'previewOnly');

  $report = [
    'generated_at' => date('c'),
    'maintenance_mode' => (bool) \Drupal::config('system.maintenance')->get('enabled'),
    'theme_default' => \Drupal::config('system.theme')->get('default'),
    'ads_config' => $raw,
    'adsense_client_sanitized' => $client,
    'ad_slot_manager_service' => $has_service,
    'cookie_consent_js_has_previewOnly' => $cookie_consent_has_preview,
    'placements' => $builds,
  ];
}
catch (\Throwable $e) {
  http_response_code(500);
  echo '<!DOCTYPE html><html lang="fr"><body><h1>Erreur</h1><pre>';
  echo htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
  echo '</pre></body></html>';
  exit;
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Diagnostic publicités — SphereVoices</title>
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
  </style>
</head>
<body>
  <div class="card">
    <h1>Diagnostic publicités</h1>
    <p>Généré le <?php echo htmlspecialchars($report['generated_at'], ENT_QUOTES, 'UTF-8'); ?></p>
    <ul>
      <li>Mode maintenance Drupal :
        <?php if ($report['maintenance_mode']): ?>
          <span class="warn">activé</span> (visiteurs anonymes voient la page maintenance)
        <?php else: ?>
          <span class="ok">désactivé</span>
        <?php endif; ?>
      </li>
      <li>Thème actif : <strong><?php echo htmlspecialchars((string) $report['theme_default'], ENT_QUOTES, 'UTF-8'); ?></strong></li>
      <li>Service <code>ad_slot_manager</code> :
        <?php echo $report['ad_slot_manager_service'] ? '<span class="ok">OK</span>' : '<span class="bad">absent</span>'; ?>
      </li>
      <li>JS <code>cookie-consent.js</code> avec fix <code>previewOnly</code> :
        <?php echo $report['cookie_consent_js_has_previewOnly'] ? '<span class="ok">oui</span>' : '<span class="bad">non (fichier pas déployé)</span>'; ?>
      </li>
    </ul>
  </div>

  <div class="card">
    <h2>Emplacements</h2>
    <table>
      <thead>
        <tr>
          <th>Zone</th>
          <th>Activé</th>
          <th>Type</th>
          <th>Slot</th>
          <th>Mode aperçu</th>
          <th>Rendu PHP</th>
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
              <?php if (!empty($row['render_empty'])): ?>
                <span class="bad">vide</span>
              <?php else: ?>
                <span class="ok"><?php echo htmlspecialchars((string) ($row['mode'] ?? '?'), ENT_QUOTES, 'UTF-8'); ?></span>
                <?php if (!empty($row['preview_only'])): ?> (aperçu)<?php endif; ?>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="card">
    <h2>Config complète</h2>
    <pre><?php echo htmlspecialchars(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?></pre>
  </div>

  <div class="card">
    <h2>Autres outils</h2>
    <ul>
      <li><a href="/clear-cache-web.php?token=spherevoices2026">Vider le cache Drupal</a></li>
      <li><a href="/exec-drush.php?token=spherevoices2026">Exécuter drush cr</a></li>
      <li><a href="/am-i-logged-in.php?token=spherevoices2026">Vérifier la session Drupal</a></li>
    </ul>
  </div>
</body>
</html>
