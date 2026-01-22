#!/bin/bash

# Arrêter le serveur PHP actuel
echo "🛑 Arrêt du serveur PHP en cours..."
pkill -f "php -S localhost:8000"
sleep 2

# Démarrer le serveur avec le fichier de routeur personnalisé
echo "🚀 Démarrage du serveur PHP avec routeur personnalisé..."
cd /Users/bryangast/Documents/Kaoutiz.dev/SphereVoices/site/www
php -S localhost:8000 -t . router.php &

echo "✅ Serveur redémarré sur http://localhost:8000"
echo "📍 La page /agenda est maintenant accessible !"

