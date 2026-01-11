#!/bin/bash

# Script d'installation du module Agenda
# Usage: ./install-agenda.sh

echo "🚀 Installation du module Agenda..."
echo ""

# Aller dans le répertoire www
cd "$(dirname "$0")/www" || exit 1

echo "📦 Étape 1: Réinstallation du module spherevoices_core..."
../vendor/bin/drush pm:uninstall spherevoices_core -y 2>/dev/null || true
../vendor/bin/drush pm:enable spherevoices_core -y

if [ $? -eq 0 ]; then
    echo "✅ Module spherevoices_core activé avec succès"
else
    echo "❌ Erreur lors de l'activation du module"
    exit 1
fi

echo ""
echo "🗑️  Étape 2: Nettoyage du cache..."
../vendor/bin/drush cr

echo ""
echo "📝 Étape 3: Génération des événements de démonstration..."
../vendor/bin/drush php:script modules/custom/spherevoices_core/scripts/generate_events.php

echo ""
echo "🔄 Étape 4: Reconstruction du routage..."
../vendor/bin/drush router:rebuild

echo ""
echo "🗑️  Étape 5: Nettoyage final du cache..."
../vendor/bin/drush cr

echo ""
echo "✨ Installation terminée !"
echo ""
echo "📌 Pages disponibles :"
echo "   - Page d'accueil : Bloc Agenda dans la sidebar"
echo "   - /agenda : Liste complète avec recherche et filtres"
echo "   - /agenda-mois : Navigation par mois"
echo ""
echo "📚 Pour plus d'informations, consultez AGENDA_MODULE.md"

