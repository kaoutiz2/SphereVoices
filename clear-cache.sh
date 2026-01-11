#!/bin/bash

# Script de vidage de cache simple pour OVH
# Peut être exécuté manuellement après un déploiement

echo "🔄 Vidage du cache Drupal..."

# Se placer dans le bon répertoire
cd "$(dirname "$0")"

# Méthode 1: Essayer avec le script PHP
if [ -f "post-deploy.php" ]; then
    echo "🚀 Tentative avec post-deploy.php..."
    php post-deploy.php
    if [ $? -eq 0 ]; then
        echo "✅ Cache vidé avec succès!"
        exit 0
    fi
fi

# Méthode 2: Essayer avec drush
if [ -f "vendor/bin/drush" ]; then
    echo "🚀 Tentative avec drush..."
    vendor/bin/drush cr
    if [ $? -eq 0 ]; then
        echo "✅ Cache vidé avec drush!"
        exit 0
    fi
fi

# Méthode 3: Créer un fichier trigger pour vidage manuel
echo "<?php
require_once 'post-deploy.php';
header('Content-Type: text/plain');
echo 'Cache vidé avec succès!';
" > www/clear-cache-now.php

echo "⚠️  Visitez https://www.spherevoices.com/clear-cache-now.php pour vider le cache"


