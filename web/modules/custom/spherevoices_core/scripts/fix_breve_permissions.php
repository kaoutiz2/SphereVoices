<?php

/**
 * @file
 * Script pour diagnostiquer et corriger les permissions pour les brèves.
 *
 * Usage: drush php:script fix_breve_permissions.php
 */

use Drupal\user\Entity\Role;
use Drupal\node\Entity\NodeType;

echo "🔍 Diagnostic des permissions pour les brèves\n";
echo str_repeat("=", 60) . "\n\n";

// 1. Vérifier que le type de contenu "breve" existe
echo "1️⃣  Vérification du type de contenu 'breve'...\n";
$breve_type = NodeType::load('breve');
if (!$breve_type) {
  echo "❌ ERREUR : Le type de contenu 'breve' n'existe pas !\n";
  echo "   Veuillez réinstaller le module spherevoices_core.\n";
  exit(1);
}
echo "✅ Le type de contenu 'breve' existe.\n";
echo "   Nom : {$breve_type->label()}\n";
echo "   ID : {$breve_type->id()}\n\n";

// 2. Lister tous les rôles et leurs permissions
echo "2️⃣  Analyse des rôles et permissions...\n";
$all_roles = Role::loadMultiple();
$permission_name = 'create breve content';

$roles_with_permission = [];
$roles_without_permission = [];

foreach ($all_roles as $role) {
  $permissions = $role->getPermissions();
  if (in_array($permission_name, $permissions)) {
    $roles_with_permission[] = $role;
  } else {
    $roles_without_permission[] = $role;
  }
}

echo "   Rôles avec la permission '{$permission_name}' :\n";
if (empty($roles_with_permission)) {
  echo "   ⚠️  AUCUN rôle n'a cette permission !\n";
} else {
  foreach ($roles_with_permission as $role) {
    echo "   ✅ {$role->label()} ({$role->id()})\n";
  }
}

echo "\n   Rôles SANS la permission '{$permission_name}' :\n";
foreach ($roles_without_permission as $role) {
  // Ignorer les rôles système
  if (in_array($role->id(), ['anonymous', 'authenticated'])) {
    continue;
  }
  echo "   ❌ {$role->label()} ({$role->id()})\n";
}

// 3. Identifier les rôles éditeurs potentiels
echo "\n3️⃣  Identification des rôles éditeurs...\n";
$editor_roles = [];
foreach ($all_roles as $role) {
  $role_id = strtolower($role->id());
  $role_label = strtolower($role->label());
  
  if (
    strpos($role_id, 'editor') !== false ||
    strpos($role_id, 'éditeur') !== false ||
    strpos($role_id, 'editeur') !== false ||
    strpos($role_label, 'editor') !== false ||
    strpos($role_label, 'éditeur') !== false ||
    strpos($role_label, 'editeur') !== false ||
    strpos($role_label, 'content') !== false ||
    strpos($role_label, 'contenu') !== false
  ) {
    $editor_roles[] = $role;
    echo "   📝 Rôle éditeur identifié : {$role->label()} ({$role->id()})\n";
  }
}

if (empty($editor_roles)) {
  echo "   ⚠️  Aucun rôle éditeur identifié automatiquement.\n";
  echo "   Voici tous les rôles disponibles :\n";
  foreach ($all_roles as $role) {
    if (!in_array($role->id(), ['anonymous', 'authenticated'])) {
      echo "      - {$role->label()} ({$role->id()})\n";
    }
  }
}

// 4. Ajouter la permission aux rôles éditeurs qui ne l'ont pas
echo "\n4️⃣  Ajout de la permission aux rôles éditeurs...\n";
$added_count = 0;
foreach ($editor_roles as $role) {
  $permissions = $role->getPermissions();
  if (!in_array($permission_name, $permissions)) {
    $role->grantPermission($permission_name);
    // Ajouter aussi les autres permissions de base pour les brèves
    $role->grantPermission('edit own breve content');
    $role->grantPermission('delete own breve content');
    $role->grantPermission('view breve revisions');
    $role->save();
    echo "   ✅ Permission ajoutée à : {$role->label()} ({$role->id()})\n";
    $added_count++;
  } else {
    echo "   ℹ️  {$role->label()} ({$role->id()}) a déjà la permission.\n";
  }
}

// 5. Vérifier aussi le rôle "content_editor" spécifiquement
echo "\n5️⃣  Vérification du rôle 'content_editor'...\n";
$content_editor = Role::load('content_editor');
if ($content_editor) {
  $permissions = $content_editor->getPermissions();
  if (!in_array($permission_name, $permissions)) {
    $content_editor->grantPermission($permission_name);
    $content_editor->grantPermission('edit own breve content');
    $content_editor->grantPermission('delete own breve content');
    $content_editor->grantPermission('view breve revisions');
    $content_editor->save();
    echo "   ✅ Permission ajoutée au rôle 'content_editor'.\n";
    $added_count++;
  } else {
    echo "   ✅ Le rôle 'content_editor' a déjà la permission.\n";
  }
} else {
  echo "   ℹ️  Le rôle 'content_editor' n'existe pas sur ce site.\n";
}

// 6. Résumé et instructions
echo "\n" . str_repeat("=", 60) . "\n";
echo "📊 RÉSUMÉ\n";
echo str_repeat("=", 60) . "\n";

if ($added_count > 0) {
  echo "✅ {$added_count} rôle(s) mis à jour avec la permission 'create breve content'.\n";
  echo "\n⚠️  ACTION REQUISE : Videz le cache Drupal !\n";
  echo "   Option 1 - Via Drush :\n";
  echo "      drush cr\n";
  echo "\n   Option 2 - Via l'interface :\n";
  echo "      Configuration > Développement > Performance > Vider tous les caches\n";
  echo "\n   Option 3 - Via la ligne de commande :\n";
  echo "      cd web && ../vendor/bin/drush cr\n";
} else {
  echo "ℹ️  Aucune modification nécessaire.\n";
  echo "   Si vous ne voyez toujours pas 'Brèves' dans /node/add :\n";
  echo "   1. Vérifiez que vous êtes connecté avec un utilisateur ayant un rôle éditeur\n";
  echo "   2. Videz le cache Drupal (drush cr)\n";
  echo "   3. Déconnectez-vous et reconnectez-vous\n";
}

echo "\n📋 Permissions ajoutées pour chaque rôle éditeur :\n";
echo "   - create breve content\n";
echo "   - edit own breve content\n";
echo "   - delete own breve content\n";
echo "   - view breve revisions\n";

// 7. Vider le cache automatiquement
if ($added_count > 0) {
  echo "\n6️⃣  Vidage du cache...\n";
  try {
    drupal_flush_all_caches();
    echo "   ✅ Cache vidé avec succès.\n";
  } catch (\Exception $e) {
    echo "   ⚠️  Erreur lors du vidage du cache : " . $e->getMessage() . "\n";
    echo "   Veuillez vider le cache manuellement.\n";
  }
}



