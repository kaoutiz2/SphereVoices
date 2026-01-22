<?php

use Drupal\views\Views;

echo "=== TEST RENDU VUE AGENDA ===\n\n";

$view = Views::getView('agenda');
if (!$view) {
  echo "❌ Vue non trouvée\n";
  exit(1);
}

// Définir le display
$view->setDisplay('page_agenda');
$view->preExecute();
$view->execute();

echo "Display actif: " . $view->current_display . "\n";
echo "Titre: " . $view->getTitle() . "\n\n";

// Vérifier si le formulaire exposé est présent
$exposed_form = $view->display_handler->getPlugin('exposed_form');
echo "Plugin exposed_form: " . ($exposed_form ? get_class($exposed_form) : 'NULL') . "\n";

// Vérifier les filtres exposés
$filters = $view->display_handler->getHandlers('filter');
echo "\nFiltres dans le display:\n";
foreach ($filters as $id => $filter) {
  $is_exposed = $filter->isExposed();
  echo "  - $id: " . ($is_exposed ? "EXPOSÉ" : "non exposé") . "\n";
  if ($is_exposed) {
    echo "    Label: " . $filter->options['expose']['label'] . "\n";
    echo "    Identifier: " . $filter->options['expose']['identifier'] . "\n";
  }
}

// Tenter de rendre la vue
echo "\n=== Tentative de rendu ===\n";
$render = $view->render();

if (isset($render['#exposed'])) {
  echo "✅ Section #exposed présente dans le rendu\n";
  echo "Type: " . (is_array($render['#exposed']) ? "array" : gettype($render['#exposed'])) . "\n";
} else {
  echo "❌ Section #exposed ABSENTE du rendu\n";
}

if (isset($render['#rows'])) {
  echo "✅ Section #rows présente\n";
} else {
  echo "❌ Section #rows absente\n";
}

echo "\nClés disponibles dans le rendu:\n";
foreach (array_keys($render) as $key) {
  if (is_string($key)) {
    echo "  - $key\n";
  }
}

echo "\n=== FIN TEST ===\n";

