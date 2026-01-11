<?php
// Debug script pour voir l'état de la vue agenda
use Drupal\views\Views;

echo "=== DEBUG VUE AGENDA ===\n\n";

$view = Views::getView('agenda');
if (!$view) {
  echo "❌ La vue 'agenda' n'existe pas!\n";
  exit(1);
}

echo "✅ Vue 'agenda' trouvée\n\n";

// Afficher les displays disponibles
echo "Displays disponibles:\n";
$displays = $view->storage->get('display');
foreach ($displays as $display_id => $display) {
  echo "  - $display_id: " . $display['display_title'] . "\n";
}

echo "\n";

// Vérifier le display page_agenda
if (isset($displays['page_agenda'])) {
  echo "✅ Display 'page_agenda' existe\n";
  $page_display = $displays['page_agenda'];
  
  echo "\nPath: " . ($page_display['display_options']['path'] ?? 'N/A') . "\n";
  echo "Title: " . ($page_display['display_options']['title'] ?? 'N/A') . "\n";
  
  // Vérifier exposed_form
  if (isset($page_display['display_options']['exposed_form'])) {
    echo "✅ Exposed form configuré\n";
    echo "  Type: " . $page_display['display_options']['exposed_form']['type'] . "\n";
  } else if (isset($page_display['display_options']['defaults']['exposed_form']) && $page_display['display_options']['defaults']['exposed_form'] === false) {
    echo "⚠️  Exposed form devrait être configuré mais n'est pas présent\n";
  } else {
    echo "ℹ️  Exposed form utilise la configuration par défaut\n";
  }
  
  // Vérifier les filtres
  echo "\nFiltres configurés:\n";
  if (isset($page_display['display_options']['filters'])) {
    foreach ($page_display['display_options']['filters'] as $filter_id => $filter) {
      $exposed = isset($filter['exposed']) && $filter['exposed'] ? 'EXPOSÉ' : 'non exposé';
      echo "  - $filter_id ($exposed)\n";
      if (isset($filter['expose']['label'])) {
        echo "    Label: " . $filter['expose']['label'] . "\n";
      }
    }
  }
} else {
  echo "❌ Display 'page_agenda' n'existe pas!\n";
}

echo "\n=== FIN DEBUG ===\n";

