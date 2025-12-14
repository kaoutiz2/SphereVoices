#!/bin/bash

# Script d'installation pour SphereVoices Drupal 10
# Usage: ./install.sh

set -e

echo "🚀 Installation de SphereVoices Drupal 10..."

# Vérifier que Composer est installé
if ! command -v composer &> /dev/null; then
    echo "❌ Composer n'est pas installé. Veuillez l'installer d'abord."
    exit 1
fi

# Installer les dépendances
echo "📦 Installation des dépendances Composer..."
composer install

# Créer les répertoires nécessaires
echo "📁 Création des répertoires..."
mkdir -p www/sites/default/files
chmod 777 www/sites/default/files

# Copier les fichiers de configuration
if [ ! -f www/sites/default/settings.php ]; then
    echo "⚙️  Configuration des fichiers de paramètres..."
    cp www/sites/default/default.settings.php www/sites/default/settings.php
    cp www/sites/default/default.services.yml www/sites/default/services.yml
fi

echo "✅ Installation terminée!"
echo ""
echo "📝 Prochaines étapes:"
echo "1. Configurez votre base de données dans www/sites/default/settings.php"
echo "2. Lancez l'installation Drupal via:"
echo "   drush site:install --db-url=mysql://user:password@localhost/database_name"
echo "   ou via l'interface web: http://localhost/www/"
echo "3. Activez les modules:"
echo "   drush en -y admin_toolbar pathauto token metatag paragraphs media image file field views ctools scheduler redirect simple_sitemap spherevoices_core"
echo "4. Activez le thème:"
echo "   drush theme:enable spherevoices_theme"
echo "   drush config:set system.theme default spherevoices_theme -y"

