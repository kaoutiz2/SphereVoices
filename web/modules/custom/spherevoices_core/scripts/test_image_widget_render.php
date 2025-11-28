<?php

/**
 * @file
 * Script pour tester le rendu du widget d'image dans un formulaire.
 */

// Récupérer un article avec une image
$query = \Drupal::entityQuery('node')
  ->condition('type', 'article')
  ->condition('status', 1)
  ->condition('field_image', NULL, 'IS NOT NULL')
  ->range(0, 1)
  ->accessCheck(FALSE);

$nids = $query->execute();

if (empty($nids)) {
  echo "Aucun article avec image trouvé.\n";
  exit(0);
}

$node = \Drupal::entityTypeManager()->getStorage('node')->load(reset($nids));
echo "Article: " . $node->getTitle() . " (ID: " . $node->id() . ")\n\n";

// Créer un formulaire d'édition
$form_object = \Drupal::entityTypeManager()
  ->getFormObject('node', 'edit')
  ->setEntity($node);

$form_state = new \Drupal\Core\Form\FormState();
$form = \Drupal::formBuilder()->buildForm($form_object, $form_state);

// Vérifier le champ field_image dans le formulaire
if (isset($form['field_image']['widget'][0])) {
  $widget = &$form['field_image']['widget'][0];
  
  echo "Widget field_image trouvé:\n";
  echo "  - Theme: " . ($widget['#theme'] ?? 'N/A') . "\n";
  echo "  - Preview image style: " . ($widget['#preview_image_style'] ?? 'NON DÉFINI') . "\n";
  echo "  - Files: " . (isset($widget['#files']) && !empty($widget['#files']) ? count($widget['#files']) . " fichier(s)" : 'AUCUN') . "\n";
  
  if (isset($widget['preview'])) {
    echo "  - Preview: PRÉSENT\n";
    echo "    - Theme: " . ($widget['preview']['#theme'] ?? 'N/A') . "\n";
    echo "    - Style: " . ($widget['preview']['#style_name'] ?? 'N/A') . "\n";
    echo "    - URI: " . ($widget['preview']['#uri'] ?? 'N/A') . "\n";
    
    // Rendre le preview
    $renderer = \Drupal::service('renderer');
    $rendered = $renderer->renderPlain($widget['preview']);
    echo "    - Rendu: " . (empty(trim($rendered)) ? 'VIDE' : 'CONTENU PRÉSENT (' . strlen($rendered) . ' caractères)') . "\n";
  }
  else {
    echo "  - ⚠ Preview: ABSENT\n";
    
    // Vérifier pourquoi le preview n'est pas généré
    if (empty($widget['#files'])) {
      echo "    → Raison: Aucun fichier dans #files\n";
    }
    elseif (empty($widget['#preview_image_style'])) {
      echo "    → Raison: preview_image_style non défini\n";
    }
    else {
      echo "    → Raison: Inconnue (fichier et style présents)\n";
    }
  }
}
else {
  echo "⚠ Widget field_image non trouvé dans le formulaire\n";
}

