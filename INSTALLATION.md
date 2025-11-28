# Guide d'Installation - SphereVoices

Ce guide vous accompagne dans l'installation complète du site SphereVoices sur Drupal 10.

## Prérequis

- PHP 8.1 ou supérieur avec extensions : `php-xml`, `php-gd`, `php-mbstring`, `php-mysql` (ou `php-pgsql`)
- Composer 2.x
- MySQL 5.7+ / MariaDB 10.3+ ou PostgreSQL 10+
- Drush (inclus via Composer)
- Serveur web (Apache/Nginx) ou serveur de développement PHP

## Installation

### 1. Installation des dépendances

```bash
composer install
```

### 2. Configuration de la base de données

Créez une base de données pour le projet :

```sql
CREATE DATABASE spherevoices CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3. Configuration des fichiers

```bash
# Créer les fichiers de configuration
cp web/sites/default/default.settings.php web/sites/default/settings.php
cp web/sites/default/default.services.yml web/sites/default/services.yml

# Créer le répertoire des fichiers
mkdir -p web/sites/default/files
chmod 777 web/sites/default/files
```

### 4. Installation de Drupal

#### Option A : Via Drush (recommandé)

```bash
drush site:install standard \
  --db-url=mysql://user:password@localhost/spherevoices \
  --site-name="SphereVoices" \
  --site-mail=admin@spherevoices.com \
  --account-name=admin \
  --account-mail=admin@spherevoices.com \
  --account-pass=admin123
```

#### Option B : Via l'interface web

1. Accédez à `http://localhost/site/web/`
2. Suivez l'assistant d'installation
3. Configurez la base de données et le compte administrateur

### 5. Activation des modules

```bash
# Modules essentiels
drush en -y \
  admin_toolbar \
  pathauto \
  token \
  metatag \
  paragraphs \
  entity_reference_revisions \
  media \
  image \
  file \
  field \
  views \
  ctools \
  scheduler \
  redirect \
  simple_sitemap \
  spherevoices_core
```

### 6. Activation du thème

```bash
drush theme:enable spherevoices_theme
drush config:set system.theme default spherevoices_theme -y
```

### 7. Configuration des rôles

Dans l'interface d'administration (`/admin/people/roles`), créez les rôles suivants :

- **Éditeur/Chef d'édition** : Peut valider et publier des articles
- **Rédacteur** : Peut créer et modifier des articles

Configurez les permissions appropriées pour chaque rôle.

### 8. Création du type de contenu Article

1. Allez dans `/admin/structure/types/add`
2. Créez un type de contenu "Article" avec les champs suivants :
   - `field_subtitle` (Texte, 255 caractères)
   - `field_summary` (Texte long)
   - `field_image` (Image)
   - `field_gallery` (Galerie d'images)
   - `field_video` (Média - Vidéo)
   - `field_embeds` (Texte long formaté)
   - `field_category` (Référence de terme - Catégorie)
   - `field_tags` (Référence de terme - Tags)
   - `field_author` (Référence d'entité - Utilisateur)
   - `field_related_articles` (Référence d'entité - Article)
   - `field_breaking_news` (Booléen)
   - `field_featured` (Booléen)

### 9. Configuration des taxonomies

1. Créez le vocabulaire "Catégories" (`/admin/structure/taxonomy/add`)
2. Créez le vocabulaire "Tags" (`/admin/structure/taxonomy/add`)
3. Ajoutez les catégories principales : International, Europe, Politique, Culture, Sport, Économie, etc.

### 10. Configuration de Pathauto

```bash
drush config:set pathauto.settings default_pattern '[node:content-type]/[node:title]' -y
```

### 11. Configuration du Media Library

1. Allez dans `/admin/config/media/media-types`
2. Configurez les types de médias : Image, Vidéo, Audio, Document
3. Configurez les formats d'image acceptés dans `/admin/config/media/image-styles`

### 12. Création des vues

Créez les vues suivantes dans `/admin/structure/views` :

- **Page d'accueil** : Vue de page affichant les articles mis en avant
- **Carrousel** : Bloc de carrousel pour les articles en vedette
- **Breaking News** : Bloc affichant le dernier article marqué comme breaking news
- **Trending** : Bloc affichant les articles les plus populaires
- **Dernières nouvelles** : Liste chronologique des derniers articles
- **Articles par catégorie** : Vue de page pour chaque catégorie

### 13. Configuration SEO

```bash
# Activer Metatag
drush en -y metatag

# Configurer les métadonnées par défaut
# Allez dans /admin/config/search/metatag
```

### 14. Configuration du cache

```bash
# Vider le cache
drush cr

# Activer le cache de production (si en production)
drush config:set system.performance cache.page.max_age 3600 -y
```

## Configuration de développement

### Activer les modules de développement

```bash
drush en -y devel devel_generate
```

### Désactiver le cache en développement

```bash
drush config:set system.performance cache.page.max_age 0 -y
drush config:set system.performance css.preprocess 0 -y
drush config:set system.performance js.preprocess 0 -y
```

## Vérification

Vérifiez que tout fonctionne :

1. Accédez à la page d'accueil : `http://localhost/site/web/`
2. Connectez-vous en tant qu'administrateur
3. Créez un article de test
4. Vérifiez l'affichage sur le front-office

## Problèmes courants

### Erreur de permissions

```bash
chmod -R 777 web/sites/default/files
```

### Erreur de base de données

Vérifiez que :
- La base de données existe
- Les identifiants sont corrects dans `settings.php`
- L'utilisateur MySQL a les droits nécessaires

### Erreur de mémoire PHP

Augmentez la limite dans `php.ini` :
```ini
memory_limit = 256M
```

## Support

Pour toute question, consultez la documentation Drupal ou contactez l'équipe de développement.

