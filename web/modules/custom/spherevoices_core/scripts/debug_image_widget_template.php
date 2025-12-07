<?php

/**
 * @file
 * Script pour déboguer le template du widget d'image.
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

// Simuler le preprocess image_widget
if (isset($form['field_image']['widget'][0])) {
  $element = $form['field_image']['widget'][0];
  
  // Simuler template_preprocess_image_widget
  $variables = [
    'element' => $element,
    'attributes' => ['class' => ['image-widget', 'js-form-managed-file', 'form-managed-file', 'clearfix']],
    'data' => [],
  ];
  
  foreach (\Drupal\Core\Render\Element::children($element) as $child) {
    $variables['data'][$child] = $element[$child];
  }
  
  echo "Variables après template_preprocess_image_widget:\n";
  echo "  - data.preview présent: " . (isset($variables['data']['preview']) ? 'OUI' : 'NON') . "\n";
  
  if (isset($variables['data']['preview'])) {
    $preview = $variables['data']['preview'];
    echo "  - preview.#access: " . (isset($preview['#access']) ? ($preview['#access'] ? 'TRUE' : 'FALSE') : 'NON DÉFINI') . "\n";
    echo "  - preview.#theme: " . ($preview['#theme'] ?? 'NON DÉFINI') . "\n";
    echo "  - preview.#uri: " . ($preview['#uri'] ?? 'NON DÉFINI') . "\n";
  }
  
  // Simuler claro_preprocess_image_widget
  if (isset($variables['data']['preview']['#access']) && $variables['data']['preview']['#access'] === FALSE) {
    echo "\n⚠ claro_preprocess_image_widget supprimerait le preview (access = FALSE)\n";
    unset($variables['data']['preview']);
  }
  else {
    echo "\n✓ claro_preprocess_image_widget garderait le preview\n";
  }
  
  echo "\nVariables après claro_preprocess_image_widget:\n";
  echo "  - data.preview présent: " . (isset($variables['data']['preview']) ? 'OUI' : 'NON') . "\n";
  
  // Vérifier has_meta
  $has_meta = isset($variables['data']['alt']) || isset($variables['data']['title']);
  echo "  - has_meta: " . ($has_meta ? 'OUI' : 'NON') . "\n";
  echo "  - data.preview présent: " . (isset($variables['data']['preview']) ? 'OUI' : 'NON') . "\n";
  
  // Condition dans le template: {% if has_meta or data.preview %}
  $should_show_meta_wrapper = $has_meta || isset($variables['data']['preview']);
  echo "\nCondition template (has_meta or data.preview): " . ($should_show_meta_wrapper ? 'VRAI' : 'FAUX') . "\n";
  
  if ($should_show_meta_wrapper && isset($variables['data']['preview'])) {
    echo "✓ Le preview devrait s'afficher dans image-preview__img-wrapper\n";
  }
  else {
    echo "⚠ Le preview ne s'affichera PAS\n";
    if (!$has_meta) {
      echo "  → Raison: has_meta est FALSE et data.preview n'est pas présent\n";
    }
    elseif (!isset($variables['data']['preview'])) {
      echo "  → Raison: data.preview n'est pas présent\n";
    }
  }
}


