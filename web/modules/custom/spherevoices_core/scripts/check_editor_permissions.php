<?php

/**
 * @file
 * Script pour vérifier les permissions des éditeurs de contenu.
 *
 * Usage: drush php:script check_editor_permissions.php
 */

use Drupal\user\Entity\Role;

echo "🔍 Vérification des permissions des éditeurs de contenu\n";
echo str_repeat("=", 60) . "\n\n";

// Permissions requises
$required_permissions = [
  'administer comments' => 'Paramètre de commentaire',
  'administer nodes' => 'Information de publication et Option de publication',
];

// Identifier les rôles éditeurs
$role_names = ['content_editor', 'editor', 'éditeur', 'editeur'];
$editor_roles = [];

foreach ($role_names as $role_name) {
  $role = Role::load($role_name);
  if ($role) {
    $editor_roles[] = $role;
  }
}

if (empty($editor_roles)) {
  echo "❌ Aucun rôle éditeur trouvé.\n";
  exit(1);
}

// Vérifier chaque rôle
foreach ($editor_roles as $role) {
  echo "📋 Rôle : {$role->label()} ({$role->id()})\n";
  echo str_repeat("-", 60) . "\n";
  
  $permissions = $role->getPermissions();
  $missing_permissions = [];
  
  foreach ($required_permissions as $permission => $description) {
    if (in_array($permission, $permissions)) {
      echo "✅ {$permission}\n";
      echo "   → {$description}\n";
    } else {
      echo "❌ {$permission} (MANQUANTE)\n";
      echo "   → {$description}\n";
      $missing_permissions[] = $permission;
    }
  }
  
  if (!empty($missing_permissions)) {
    echo "\n⚠️  Permissions manquantes pour ce rôle :\n";
    foreach ($missing_permissions as $perm) {
      echo "   - {$perm}\n";
    }
    echo "\n💡 Pour ajouter ces permissions, exécutez :\n";
    echo "   drush php:script web/modules/custom/spherevoices_core/scripts/add_editor_permissions.php\n";
  } else {
    echo "\n✅ Toutes les permissions requises sont présentes !\n";
  }
  
  echo "\n";
}

// Vérifier aussi dans la liste complète des permissions
echo "📝 Liste complète des permissions du rôle 'content_editor' :\n";
$content_editor = Role::load('content_editor');
if ($content_editor) {
  $all_perms = $content_editor->getPermissions();
  sort($all_perms);
  foreach ($all_perms as $perm) {
    if (strpos($perm, 'administer') !== false || strpos($perm, 'node') !== false || strpos($perm, 'comment') !== false) {
      echo "   - {$perm}\n";
    }
  }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "💡 Note : La permission 'administer nodes' contrôle :\n";
echo "   - Les options de publication (Promouvoir, Épingler)\n";
echo "   - Les informations de publication (Statut, Auteur, Date)\n";
echo "   - Le changement de propriétaire du contenu\n";
echo "   - L'accès aux révisions\n";

