<?php

/**
 * @file
 * Rendu interne de la page d'accueil (contourne cache navigateur / maintenance anonyme).
 *
 * URL: https://www.spherevoices.com/check-homepage-ads.php?token=spherevoices2026
 */

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

$security_token = 'spherevoices2026';
$provided_token = $_GET['token'] ?? '';

if (!hash_equals($security_token, $provided_token)) {
  http_response_code(403);
  exit('Token requis. Exemple : ?token=spherevoices2026');
}

header('Content-Type: text/html; charset=utf-8');

try {
  require_once __DIR__ . '/spherevoices-ops-bootstrap.inc.php';
  $kernel = spherevoices_ops_bootstrap_drupal(__DIR__);

  $account = \Drupal\user\Entity\User::load(1);
  if ($account) {
    \Drupal::service('account_switcher')->switchTo($account);
  }

  $request = Request::create('/', 'GET');
  $request->headers->set('Host', $_SERVER['HTTP_HOST'] ?? 'www.spherevoices.com');
  $response = $kernel->handle($request, HttpKernelInterface::MAIN_REQUEST, FALSE);
  $html = (string) $response->getContent();
  $kernel->terminate($request, $response);

  $markers = [
    'maintenance-page' => str_contains($html, 'maintenance-page'),
    'ad-slot--header' => str_contains($html, 'ad-slot--header'),
    'ad-slot--sidebar' => str_contains($html, 'ad-slot--sidebar'),
    'ad-slot--grid' => str_contains($html, 'ad-slot--grid'),
    'placeholder-label' => str_contains($html, 'ad-slot__placeholder-label'),
    'placeholder-hint' => str_contains($html, 'ad-slot__placeholder-hint'),
    'page-top-ad' => str_contains($html, 'spherevoices_ad_header') || preg_match('/page-top[^>]*ad-slot--header/s', $html),
  ];

  $files = [
    'AdSlotManager.php' => __DIR__ . '/modules/custom/spherevoices_core/src/Service/AdSlotManager.php',
    'spherevoices_core.module' => __DIR__ . '/modules/custom/spherevoices_core/spherevoices_core.module',
    'page--front.html.twig' => __DIR__ . '/themes/custom/spherevoices_theme/templates/layout/page--front.html.twig',
    'ads.css' => __DIR__ . '/modules/custom/spherevoices_core/css/ads.css',
  ];
  $deploy = [];
  foreach ($files as $label => $path) {
    $deploy[$label] = [
      'exists' => is_readable($path),
      'mtime' => is_readable($path) ? date('c', (int) filemtime($path)) : NULL,
      'has_page_top' => ($label === 'spherevoices_core.module' && is_readable($path))
        ? str_contains((string) file_get_contents($path), 'spherevoices_core_page_top')
        : NULL,
      'has_markup_build' => ($label === 'AdSlotManager.php' && is_readable($path))
        ? str_contains((string) file_get_contents($path), 'buildVisiblePlaceholder')
        : NULL,
    ];
  }

  $snippet_header = '';
  if (preg_match('/<div class="ad-slot ad-slot--header"[^>]*>.*?<\/div>\s*<\/div>/s', $html, $m)) {
    $snippet_header = $m[0];
  }
}
catch (\Throwable $e) {
  http_response_code(500);
  echo '<pre>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</pre>';
  exit;
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Diagnostic HTML accueil — SphereVoices</title>
  <style>
    body { font-family: system-ui, sans-serif; max-width: 960px; margin: 2rem auto; padding: 0 1rem; }
    .ok { color: #198754; font-weight: 600; }
    .bad { color: #c20017; font-weight: 600; }
    pre { background: #f5f5f5; padding: 1rem; overflow: auto; font-size: .8rem; }
    table { width: 100%; border-collapse: collapse; margin: 1rem 0; }
    th, td { text-align: left; padding: .4rem .6rem; border-bottom: 1px solid #eee; }
  </style>
</head>
<body>
  <h1>Diagnostic HTML page d'accueil</h1>
  <p>Rendu Drupal interne en tant qu'admin (uid 1) — <?php echo date('c'); ?></p>

  <h2>Marqueurs dans le HTML</h2>
  <table>
    <?php foreach ($markers as $name => $found): ?>
      <tr>
        <td><code><?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?></code></td>
        <td><?php echo $found ? '<span class="ok">trouvé</span>' : '<span class="bad">absent</span>'; ?></td>
      </tr>
    <?php endforeach; ?>
  </table>

  <?php if ($markers['maintenance-page']): ?>
    <p class="bad"><strong>La page renvoyée est encore la maintenance</strong> — vérifiez la permission « accéder au site en maintenance » pour l'admin.</p>
  <?php endif; ?>

  <h2>Fichiers déployés sur le serveur</h2>
  <table>
    <thead><tr><th>Fichier</th><th>Présent</th><th>Modifié</th><th>Check</th></tr></thead>
    <tbody>
      <?php foreach ($deploy as $label => $row): ?>
        <tr>
          <td><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></td>
          <td><?php echo !empty($row['exists']) ? '<span class="ok">oui</span>' : '<span class="bad">non</span>'; ?></td>
          <td><?php echo htmlspecialchars((string) ($row['mtime'] ?? '—'), ENT_QUOTES, 'UTF-8'); ?></td>
          <td>
            <?php if ($row['has_page_top'] !== NULL): ?>
              page_top: <?php echo $row['has_page_top'] ? '<span class="ok">oui</span>' : '<span class="bad">non</span>'; ?>
            <?php elseif ($row['has_markup_build'] !== NULL): ?>
              markup: <?php echo $row['has_markup_build'] ? '<span class="ok">oui</span>' : '<span class="bad">non</span>'; ?>
            <?php else: ?>
              —
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <?php if ($snippet_header !== ''): ?>
    <h2>Extrait bandeau header</h2>
    <pre><?php echo htmlspecialchars($snippet_header, ENT_QUOTES, 'UTF-8'); ?></pre>
  <?php endif; ?>

  <p><a href="/check-ads.php?token=spherevoices2026">← Diagnostic config / PHP</a> ·
     <a href="/drush-cr.php?token=spherevoices2026">Vider le cache</a></p>
</body>
</html>
