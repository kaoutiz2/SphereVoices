<?php

/**
 * @file
 * Script pour ajouter les permissions nécessaires aux éditeurs de contenu.
 *
 * Permissions ajoutées :
 * - administer comments : pour voir/modifier les paramètres de commentaires
 * - administer nodes : pour voir les informations de publication et options de publication
 *
 * Usage: drush php:script add_editor_permissions.php
 */

use Drupal\user\Entity\Role;

echo "🔧 Ajout des permissions pour les éditeurs de contenu\n";
echo str_repeat("=", 60) . "\n\n";

// Permissions à ajouter
$permissions_to_add = [
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
    echo "✅ Rôle éditeur trouvé : {$role->label()} ({$role->id()})\n";
  }
}

if (empty($editor_roles)) {
  echo "❌ Aucun rôle éditeur trouvé. Rôles disponibles :\n";
  $all_roles = Role::loadMultiple();
  foreach ($all_roles as $r) {
    if (!in_array($r->id(), ['anonymous', 'authenticated'])) {
      echo "   - {$r->id()} : {$r->label()}\n";
    }
  }
  exit(1);
}

echo "\n";

// Ajouter les permissions à chaque rôle éditeur
$added_count = 0;
foreach ($editor_roles as $role) {
  echo "📝 Mise à jour du rôle : {$role->label()} ({$role->id()})\n";
  
  foreach ($permissions_to_add as $permission => $description) {
    $permissions = $role->getPermissions();
    
    if (!in_array($permission, $permissions)) {
      $role->grantPermission($permission);
      echo "   ✅ Permission ajoutée : {$permission}\n";
      echo "      → {$description}\n";
      $added_count++;
    } else {
      echo "   ℹ️  Permission déjà présente : {$permission}\n";
    }
  }
  
  $role->save();
  echo "\n";
}

// Résumé
echo str_repeat("=", 60) . "\n";
echo "📊 RÉSUMÉ\n";
echo str_repeat("=", 60) . "\n";

if ($added_count > 0) {
  echo "✅ {$added_count} permission(s) ajoutée(s) aux rôles éditeurs.\n";
  echo "\n⚠️  ACTION REQUISE : Videz le cache Drupal !\n";
  echo "   Option 1 - Via Drush :\n";
  echo "      drush cr\n";
  echo "\n   Option 2 - Via l'interface :\n";
  echo "      Configuration > Développement > Performance > Vider tous les caches\n";
  
  // Vider le cache automatiquement
  echo "\n🔄 Vidage du cache...\n";
  try {
    drupal_flush_all_caches();
    echo "   ✅ Cache vidé avec succès.\n";
  } catch (\Exception $e) {
    echo "   ⚠️  Erreur lors du vidage du cache : " . $e->getMessage() . "\n";
    echo "   Veuillez vider le cache manuellement.\n";
  }
} else {
  echo "ℹ️  Toutes les permissions sont déjà présentes.\n";
  echo "   Si vous ne voyez toujours pas les sections dans le formulaire :\n";
  echo "   1. Videz le cache Drupal (drush cr)\n";
  echo "   2. Déconnectez-vous et reconnectez-vous\n";
  echo "   3. Vérifiez que vous utilisez un compte avec un rôle éditeur\n";
}

echo "\n📋 Permissions ajoutées :\n";
foreach ($permissions_to_add as $permission => $description) {
  echo "   - {$permission} : {$description}\n";
}

echo "\n💡 Note : La permission 'administer nodes' est puissante et donne accès à :\n";
echo "   - Promouvoir du contenu en page d'accueil\n";
echo "   - Épingler du contenu en haut des listes\n";
echo "   - Modifier les informations de publication\n";
echo "   - Voir les informations de statut\n";



