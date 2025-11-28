# Quick Start - SphereVoices

Guide rapide pour démarrer le projet.

## 🚀 Démarrage Rapide

### 1. Installation

```bash
# Installer les dépendances
composer install

# Ou utiliser le script d'installation
./install.sh
```

### 2. Configuration

```bash
# Créer les fichiers de configuration
cp web/sites/default/default.settings.php web/sites/default/settings.php
mkdir -p web/sites/default/files
chmod 777 web/sites/default/files
```

### 3. Installation Drupal

```bash
drush site:install standard \
  --db-url=mysql://user:password@localhost/spherevoices \
  --site-name="SphereVoices" \
  --account-name=admin \
  --account-pass=admin123
```

### 4. Activation des Modules

```bash
drush en -y admin_toolbar pathauto token metatag paragraphs media image file field views ctools scheduler redirect simple_sitemap spherevoices_core
```

### 5. Activation du Thème

```bash
drush theme:enable spherevoices_theme
drush config:set system.theme default spherevoices_theme -y
```

### 6. Vider le Cache

```bash
drush cr
```

## 📋 Prochaines Étapes

1. **Créer le type de contenu Article** avec tous les champs requis
2. **Configurer les taxonomies** (Catégories et Tags)
3. **Créer les rôles** (Éditeur, Rédacteur)
4. **Créer les vues** (Page d'accueil, Carrousel, Breaking News, etc.)
5. **Configurer Pathauto** pour les URLs propres
6. **Ajouter du contenu de test**

## 📚 Documentation

- **README.md** : Documentation générale
- **INSTALLATION.md** : Guide d'installation détaillé
- **DOCUMENTATION.md** : Documentation technique complète

## 🎨 Personnalisation

Le thème `spherevoices_theme` est prêt à être personnalisé :
- Modifiez les variables CSS dans `css/style.css`
- Personnalisez les templates Twig dans `templates/`
- Ajoutez vos propres styles dans `css/`

## 🔧 Commandes Utiles

```bash
# Vider le cache
drush cr

# Mettre à jour la base de données
drush updb

# Exporter la configuration
drush config:export

# Importer la configuration
drush config:import

# Générer du contenu de test
drush devel-generate:content --types=article --num=10
```

## ✅ Checklist de Déploiement

- [ ] Installation des dépendances
- [ ] Configuration de la base de données
- [ ] Installation de Drupal
- [ ] Activation des modules
- [ ] Activation du thème
- [ ] Création du type de contenu Article
- [ ] Configuration des taxonomies
- [ ] Création des rôles et permissions
- [ ] Création des vues
- [ ] Configuration SEO (Metatag, Pathauto)
- [ ] Configuration du Media Library
- [ ] Tests de fonctionnalités
- [ ] Optimisation des performances
- [ ] Configuration HTTPS
- [ ] Backup automatique

## 🆘 Support

En cas de problème, consultez :
- La documentation Drupal : https://www.drupal.org/docs
- Les fichiers de documentation du projet
- Les logs Drupal : `drush watchdog:show`

