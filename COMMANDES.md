# Commandes Utiles - SphereVoices

## 🚀 Démarrage du Serveur

### Méthode 1 : Script shell (Recommandé)
```bash
./start.sh
```

### Méthode 2 : Composer
```bash
composer start
# ou
composer server
# ou
composer serve
```

### Méthode 3 : PHP directement
```bash
cd www
php -S 127.0.0.1:8888 .ht.router.php
```

### Méthode 4 : Drush
```bash
vendor/bin/drush server --host=127.0.0.1 --port=8888
```

**URL du site :** http://127.0.0.1:8888

## 📦 Commandes Composer

### Installation
```bash
# Installer les dépendances
composer install

# Mettre à jour les dépendances
composer update
```

### Démarrage du serveur
```bash
composer start          # Démarrer le serveur de développement
composer server         # Alias de start
composer serve          # Alias de start
```

### Cache
```bash
composer cache-clear    # Vider le cache Drupal
composer cache-rebuild  # Reconstruire le cache Drupal
```

### Base de données
```bash
composer update-db      # Mettre à jour la base de données
```

### Configuration
```bash
composer config-export  # Exporter la configuration
composer config-import  # Importer la configuration
```

## 🔧 Commandes Drush

### Accès direct à Drush
```bash
# Via Composer
composer drush [commande]

# Directement
vendor/bin/drush [commande]
```

### Commandes Drush courantes
```bash
# Vider le cache
drush cr
# ou
composer cache-clear

# Mettre à jour la base de données
drush updb
# ou
composer update-db

# Exporter la configuration
drush config:export
# ou
composer config-export

# Importer la configuration
drush config:import
# ou
composer config-import

# Activer un module
drush en nom_du_module

# Désactiver un module
drush pmu nom_du_module

# Installer Drupal
drush site:install standard \
  --db-url=mysql://user:password@localhost/database_name \
  --site-name="SphereVoices" \
  --account-name=admin \
  --account-pass=admin123

# Activer les modules recommandés
drush en -y admin_toolbar pathauto token metatag paragraphs media image file field views ctools scheduler redirect simple_sitemap

# Activer le thème
drush theme:enable spherevoices_theme
drush config:set system.theme default spherevoices_theme -y

# Voir les logs
drush watchdog:show

# Générer du contenu de test
drush devel-generate:content --types=article --num=10
```

## 🛠️ Commandes Système

### Arrêter le serveur
```bash
# Trouver le processus sur le port 8888
lsof -ti:8888

# Arrêter le processus
lsof -ti:8888 | xargs kill -9
```

### Vérifier les permissions
```bash
# Vérifier les permissions du dossier files
ls -la www/sites/default/files/

# Corriger les permissions si nécessaire
chmod -R 777 www/sites/default/files/
```

### Vérifier les logs
```bash
# Logs PHP (si configuré)
tail -f /var/log/php_errors.log

# Logs Drupal via Drush
drush watchdog:show
```

## 📋 Commandes Git

### Workflow de développement
```bash
# Créer une nouvelle branche
git checkout -b feature/nom-de-la-fonctionnalite

# Commiter les changements
git add .
git commit -m "Description des changements"

# Pousser vers GitHub
git push origin feature/nom-de-la-fonctionnalite

# Déployer sur production (déclenche le déploiement automatique)
git checkout production
git merge main
git push origin production
```

## 🔍 Commandes de Debug

### Vérifier la configuration PHP
```bash
php -v
php -m  # Liste des modules PHP chargés
php -i  # Informations complètes PHP
```

### Vérifier la configuration Drupal
```bash
drush status
drush core-status
```

### Tester la connexion à la base de données
```bash
drush sql-connect
```

## 📝 Variables d'environnement pour start.sh

Vous pouvez personnaliser le serveur avec des variables d'environnement :

```bash
# Changer le port
PORT=8080 ./start.sh

# Changer l'hôte
HOST=localhost ./start.sh

# Les deux
HOST=localhost PORT=8080 ./start.sh
```

## 🆘 Dépannage

### Le serveur ne démarre pas
```bash
# Vérifier si le port est utilisé
lsof -ti:8888

# Vérifier que PHP est installé
php -v

# Vérifier que le fichier .ht.router.php existe
ls -la www/.ht.router.php
```

### Erreur de permissions
```bash
# Donner les permissions au dossier files
chmod -R 777 www/sites/default/files/
```

### Erreur de cache
```bash
# Vider le cache
composer cache-clear

# Ou manuellement
rm -rf www/sites/default/files/php/twig/*
rm -rf www/sites/default/files/css/*
```
