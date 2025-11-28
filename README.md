# SphereVoices - Site d'Information

Site d'information moderne inspiré de CNN, développé avec Drupal 10.

## 📋 Description

Plateforme média professionnelle permettant la publication d'articles, la gestion de contenus multimédias et une expérience utilisateur optimale sur tous supports.

## 🚀 Installation

### Prérequis

- PHP 8.1 ou supérieur
- Composer
- MySQL/MariaDB ou PostgreSQL
- Drush (inclus via Composer)

### Étapes d'installation

1. **Installer les dépendances Composer :**
```bash
composer install
```

2. **Créer le fichier de configuration :**
```bash
cp web/sites/default/default.settings.php web/sites/default/settings.php
cp web/sites/default/default.services.yml web/sites/default/services.yml
```

3. **Créer le répertoire des fichiers :**
```bash
mkdir -p web/sites/default/files
chmod 777 web/sites/default/files
```

4. **Installer Drupal via Drush :**
```bash
drush site:install --db-url=mysql://user:password@localhost/database_name
```

Ou via l'interface web : `http://localhost/site/web/`

5. **Activer les modules recommandés :**
```bash
drush en -y admin_toolbar pathauto token metatag paragraphs media image file field views ctools scheduler redirect simple_sitemap
```

## 📁 Structure du Projet

```
site/
├── web/                    # Racine web (DocumentRoot)
│   ├── core/              # Core Drupal
│   ├── modules/           # Modules
│   │   ├── contrib/       # Modules tiers
│   │   └── custom/        # Modules personnalisés
│   ├── themes/            # Thèmes
│   │   ├── contrib/       # Thèmes tiers
│   │   └── custom/        # Thème personnalisé SphereVoices
│   └── sites/             # Configuration par site
├── composer.json          # Dépendances Composer
└── README.md             # Documentation
```

## 🎨 Thème Personnalisé

Le thème `spherevoices_theme` sera développé pour répondre aux besoins du cahier des charges :
- Design moderne type CNN
- Responsive mobile-first
- Accessibilité WCAG 2.1 AA
- Performance optimisée

## 🔧 Configuration

### Rôles Utilisateurs

- **Administrateur** : Accès complet
- **Éditeur/Chef d'édition** : Validation et mise en avant
- **Rédacteur** : Création et modification d'articles

### Types de Contenu

- **Article** : Contenu principal avec tous les champs requis (titre, sous-titre, corps, images, vidéos, embeds, catégories, tags, etc.)

### Taxonomies

- **Catégories** : International, Europe, Politique, Culture, Sport, etc.
- **Tags** : Mots-clés libres pour le référencement

## 📝 Fonctionnalités Principales

### Back-office
- Gestion des rôles et permissions
- Éditeur riche pour les articles
- Media Library centralisée
- Workflow de publication (brouillon, relecture, publié, archivé)

### Front-office
- Page d'accueil avec Hero Zone, Breaking News, Carrousel
- Pages catégories et tags
- Recherche avancée
- Partage social
- Articles liés

## 🛠️ Développement

### Commandes Drush utiles

```bash
# Vider le cache
drush cr

# Mettre à jour la base de données
drush updb

# Exporter la configuration
drush config:export

# Importer la configuration
drush config:import

# Activer un module
drush en module_name

# Désactiver un module
drush pmu module_name
```

## 📦 Modules Principaux

- **admin_toolbar** : Amélioration de l'interface d'administration
- **pathauto** : Génération automatique d'URLs propres
- **metatag** : Gestion des métadonnées SEO
- **paragraphs** : Création de contenus structurés
- **media** : Gestion des médias
- **scheduler** : Publication programmée
- **simple_sitemap** : Génération de sitemap XML
- **social_share** : Partage sur les réseaux sociaux

## 🔒 Sécurité

- HTTPS obligatoire en production
- Mises à jour de sécurité régulières
- Permissions strictes sur les fichiers
- Backup automatique recommandé

## 📞 Support

Pour toute question ou problème, contactez : contact@spherevoices.com

## 📄 Licence

GPL-2.0-or-later

