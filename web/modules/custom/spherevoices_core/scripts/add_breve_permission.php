<?php

/**
 * @file
 * Script pour ajouter la permission de créer des brèves au rôle éditeur.
 *
 * Usage: drush php:script add_breve_permission.php
 * Ou via l'interface: Configuration > Développement > Exécuter PHP
 */

use Drupal\user\Entity\Role;

// Charger le rôle éditeur (essayer différents noms possibles)
$role_names = ['content_editor', 'editor', 'éditeur', 'editeur'];
$role = NULL;

foreach ($role_names as $role_name) {
  $role = Role::load($role_name);
  if ($role) {
    echo "✅ Rôle trouvé : {$role->label()} ({$role->id()})\n";
    break;
  }
}

if (!$role) {
  echo "❌ Aucun rôle éditeur trouvé. Rôles disponibles :\n";
  $all_roles = Role::loadMultiple();
  foreach ($all_roles as $r) {
    echo "  - {$r->id()} : {$r->label()}\n";
  }
  exit(1);
}

// Vérifier si la permission existe déjà
$permissions = $role->getPermissions();
$permission_name = 'create breve content';

if (in_array($permission_name, $permissions)) {
  echo "ℹ️  La permission '{$permission_name}' existe déjà pour ce rôle.\n";
  exit(0);
}

// Ajouter la permission
$role->grantPermission($permission_name);
$role->save();

echo "✅ Permission '{$permission_name}' ajoutée au rôle '{$role->label()}'.\n";
echo "\n";
echo "📝 N'oubliez pas de vider le cache Drupal pour que les changements prennent effet.\n";
echo "   Via Drush : drush cr\n";
echo "   Via l'interface : Configuration > Développement > Performance > Vider tous les caches\n";

