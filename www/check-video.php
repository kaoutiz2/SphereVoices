<?php
/**
 * FIX vidéo — à supprimer après utilisation.
 * 1. Corrige le form display media.video.media_library
 * 2. Simule la requête AJAX Media Library pour attraper l'erreur 500
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

$cwd = getcwd();
chdir(__DIR__);
$autoloader = require_once __DIR__ . '/autoload.php';
chdir($cwd);

use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;

$request = Request::createFromGlobals();
$kernel = new DrupalKernel('prod', $autoloader);
$kernel->handle($request);

echo '<h2>FIX Vidéo — Media Library</h2>';

// ============================================================
// FIX 1 : Corriger le form display media.video.media_library
// ============================================================
echo '<h3>FIX 1: Form display media.video.media_library</h3>';
try {
  $fd = \Drupal::entityTypeManager()->getStorage('entity_form_display')->load('media.video.media_library');
  if (!$fd) {
    echo '<p style="color:red;">Form display INTROUVABLE ! Création...</p>';
    $fd = \Drupal::entityTypeManager()->getStorage('entity_form_display')->create([
      'targetEntityType' => 'media',
      'bundle' => 'video',
      'mode' => 'media_library',
      'status' => TRUE,
    ]);
  }

  // Rendre field_media_video_file visible
  $component = $fd->getComponent('field_media_video_file');
  if (empty($component) || (($component['region'] ?? 'hidden') === 'hidden')) {
    $fd->setComponent('field_media_video_file', [
      'type' => 'file_generic',
      'weight' => -1,
      'region' => 'content',
      'settings' => [
        'progress_indicator' => 'throbber',
      ],
      'third_party_settings' => [],
    ]);
    echo '<p>field_media_video_file: CACHÉ → VISIBLE (corrigé)</p>';
  } else {
    echo '<p style="color:green;">field_media_video_file: déjà visible</p>';
  }

  // Cacher les champs inutiles en mode media_library
  foreach (['name', 'path', 'uid', 'created', 'status', 'revision_log_message'] as $field_name) {
    if ($fd->getComponent($field_name)) {
      $fd->removeComponent($field_name);
      echo '<p>' . $field_name . ': masqué</p>';
    }
  }

  $fd->save();
  echo '<p style="color:green;"><b>Form display SAUVÉ !</b></p>';
} catch (\Throwable $e) {
  echo '<p style="color:red;">ERREUR: ' . $e->getMessage() . '</p>';
  echo '<pre>' . $e->getFile() . ':' . $e->getLine() . "\n" . $e->getTraceAsString() . '</pre>';
}

// ============================================================
// FIX 2 : Vérifier et reconstruire les routes
// ============================================================
echo '<h3>FIX 2: Routes</h3>';

// Vérifier si la route existe
$route_provider = \Drupal::service('router.route_provider');
try {
  $route = $route_provider->getRouteByName('spherevoices_core.inline_public_video');
  echo '<p style="color:green;">Route spherevoices_core.inline_public_video: EXISTE</p>';
} catch (\Throwable $e) {
  echo '<p style="color:red;">Route spherevoices_core.inline_public_video: INTROUVABLE !</p>';

  // Vérifier le fichier routing.yml sur la prod
  $module_path = \Drupal::service('extension.list.module')->getPath('spherevoices_core');
  $routing_file = DRUPAL_ROOT . '/' . $module_path . '/spherevoices_core.routing.yml';
  echo '<p>Fichier routing: ' . $routing_file . '</p>';
  echo '<p>Existe: ' . (file_exists($routing_file) ? '<span style="color:green;">OUI</span>' : '<span style="color:red;">NON !</span>') . '</p>';

  if (file_exists($routing_file)) {
    $content = file_get_contents($routing_file);
    $has_inline = str_contains($content, 'inline_public_video');
    echo '<p>Route inline_public_video dans le fichier: ' . ($has_inline ? '<span style="color:green;">OUI</span>' : '<span style="color:red;">NON !</span>') . '</p>';
  }

  // Lister toutes les routes spherevoices_core
  try {
    $all_routes = $route_provider->getAllRoutes();
    $sv_routes = [];
    foreach ($all_routes as $name => $route) {
      if (str_starts_with($name, 'spherevoices_core.')) {
        $sv_routes[] = $name;
      }
    }
    echo '<p>Routes spherevoices_core enregistrées (' . count($sv_routes) . '): ' . implode(', ', $sv_routes) . '</p>';
  } catch (\Throwable $e2) {
    echo '<p style="color:red;">Impossible de lister les routes: ' . $e2->getMessage() . '</p>';
  }
}

// Reconstruire le router
echo '<p>Reconstruction du router...</p>';
try {
  \Drupal::service('router.builder')->rebuild();
  echo '<p style="color:green;">Router reconstruit !</p>';

  // Revérifier
  try {
    $route = $route_provider->getRouteByName('spherevoices_core.inline_public_video');
    echo '<p style="color:green;">Route spherevoices_core.inline_public_video: EXISTE MAINTENANT !</p>';
  } catch (\Throwable $e) {
    echo '<p style="color:red;">Route toujours INTROUVABLE après rebuild !</p>';
  }
} catch (\Throwable $e) {
  echo '<p style="color:red;">ERREUR rebuild: ' . $e->getMessage() . '</p>';
}

// Vider les caches
echo '<p>Vidage des caches...</p>';
try {
  \Drupal::cache('discovery')->deleteAll();
  \Drupal::cache('bootstrap')->deleteAll();
  \Drupal::cache('config')->deleteAll();
  \Drupal::cache('default')->deleteAll();
  \Drupal::cache('render')->deleteAll();
  echo '<p style="color:green;">Caches vidés</p>';
} catch (\Throwable $e) {
  echo '<p style="color:red;">ERREUR: ' . $e->getMessage() . '</p>';
}

// ============================================================
// TEST : Simuler la requête AJAX Media Library
// ============================================================
echo '<h3>TEST: Simulation requête AJAX Media Library (vidéo)</h3>';
try {
  $state = \Drupal\media_library\MediaLibraryState::create(
    'media_library.opener.field_widget',
    ['image', 'video'],
    'video',
    1,
    [
      'field_widget_id' => 'field_cover_media',
      'entity_type_id' => 'node',
      'bundle' => 'article',
      'field_name' => 'field_cover_media',
    ]
  );

  // Construire le formulaire d'ajout via FileUploadForm
  $form_object = \Drupal::service('entity_type.manager')->getStorage('media')->create(['bundle' => 'video']);

  // Simuler la construction du formulaire comme le fait la Media Library
  $ui_builder = \Drupal::service('media_library.ui_builder');
  $build = $ui_builder->buildUi($state);
  echo '<p style="color:green;">buildUi() OK</p>';

  // Essayer de rendre le render array
  $rendered = \Drupal::service('renderer')->renderRoot($build);
  echo '<p style="color:green;">Rendu OK (' . strlen($rendered) . ' chars)</p>';

  // Vérifier les validateurs d'upload dans le formulaire
  if (is_array($build) && isset($build['content'])) {
    echo '<p>Content keys: ' . implode(', ', array_keys($build['content'] ?? [])) . '</p>';
  }

} catch (\Throwable $e) {
  echo '<p style="color:red;">ERREUR: ' . $e->getMessage() . '</p>';
  echo '<pre>' . $e->getFile() . ':' . $e->getLine() . "\n" . $e->getTraceAsString() . '</pre>';
}

// ============================================================
// TEST 2 : Vérifier les validateurs d'upload
// ============================================================
echo '<h3>TEST 2: Validateurs upload pour vidéo</h3>';
try {
  $media_type = \Drupal::entityTypeManager()->getStorage('media_type')->load('video');
  $source = $media_type->getSource();
  $source_field_def = $source->getSourceFieldDefinition($media_type);
  $extensions = $source_field_def->getSetting('file_extensions');
  echo '<p><b>Extensions autorisées:</b> ' . $extensions . '</p>';

  // Créer un formulaire d'upload comme le fait FileUploadForm
  $media = \Drupal::entityTypeManager()->getStorage('media')->create(['bundle' => 'video']);
  $form = \Drupal::service('entity.form_builder')->getForm($media, 'media_library');
  echo '<p style="color:green;">Formulaire média vidéo OK</p>';

  // Chercher les validateurs dans le formulaire
  $found_upload = FALSE;
  array_walk_recursive($form, function ($value, $key) use (&$found_upload) {
    if ($key === 'file_validate_extensions' || (is_string($key) && str_contains($key, 'upload_validators'))) {
      echo '<p><b>Validator trouvé:</b> ' . $key . ' = ' . print_r($value, TRUE) . '</p>';
      $found_upload = TRUE;
    }
  });
  if (!$found_upload) {
    echo '<p>Pas de validateur d\'upload trouvé dans le formulaire</p>';
  }

} catch (\Throwable $e) {
  echo '<p style="color:red;">ERREUR: ' . $e->getMessage() . '</p>';
  echo '<pre>' . $e->getFile() . ':' . $e->getLine() . "\n" . $e->getTraceAsString() . '</pre>';
}

// ============================================================
// TEST 3 : Vérifier le form display après fix
// ============================================================
echo '<h3>TEST 3: Form display après correction</h3>';
try {
  $fd = \Drupal::entityTypeManager()->getStorage('entity_form_display')->load('media.video.media_library');
  $components = $fd->getComponents();
  echo '<p><b>Visible:</b> ' . implode(', ', array_keys($components)) . '</p>';
  $hidden = $fd->get('hidden');
  echo '<p><b>Caché:</b> ' . implode(', ', array_keys($hidden ?? [])) . '</p>';
  if (isset($components['field_media_video_file'])) {
    echo '<p style="color:green;"><b>field_media_video_file est VISIBLE !</b></p>';
  } else {
    echo '<p style="color:red;"><b>field_media_video_file est toujours CACHÉ !</b></p>';
  }
} catch (\Throwable $e) {
  echo '<p style="color:red;">ERREUR: ' . $e->getMessage() . '</p>';
}

echo '<hr><p><b>⚠️ SUPPRIME CE FICHIER (check-video.php) DE LA PROD MAINTENANT !</b></p>';
