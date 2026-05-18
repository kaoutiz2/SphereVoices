#!/bin/bash

# Script de démarrage du serveur de développement SphereVoices

set -e

# Couleurs pour les messages
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
HOST="${HOST:-127.0.0.1}"
PORT="${PORT:-8888}"
PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
WWW_DIR="$PROJECT_DIR/www"

echo -e "${BLUE}🚀 Démarrage du serveur de développement SphereVoices${NC}"
echo ""

# Vérifier si PHP est installé
if ! command -v php &> /dev/null; then
    echo -e "${YELLOW}❌ PHP n'est pas installé. Veuillez installer PHP 8.1 ou supérieur.${NC}"
    exit 1
fi

# Vérifier la version de PHP
PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
echo -e "${GREEN}✓ PHP version: $PHP_VERSION${NC}"

# Vérifier si le répertoire www existe
if [ ! -d "$WWW_DIR" ]; then
    echo -e "${YELLOW}❌ Le répertoire www/ n'existe pas.${NC}"
    exit 1
fi

# Vérifier si .ht.router.php existe
if [ ! -f "$WWW_DIR/.ht.router.php" ]; then
    echo -e "${YELLOW}❌ Le fichier www/.ht.router.php n'existe pas.${NC}"
    exit 1
fi

# Vérifier si le port est déjà utilisé
if lsof -Pi :$PORT -sTCP:LISTEN -t >/dev/null 2>&1 ; then
    echo -e "${YELLOW}⚠️  Le port $PORT est déjà utilisé.${NC}"
    echo -e "${YELLOW}   Arrêt du processus existant...${NC}"
    lsof -ti:$PORT | xargs kill -9 2>/dev/null || true
    sleep 1
fi

# Changer vers le répertoire www
cd "$WWW_DIR"

echo ""
echo -e "${GREEN}✓ Serveur démarré sur http://$HOST:$PORT${NC}"
echo -e "${BLUE}  Appuyez sur Ctrl+C pour arrêter le serveur${NC}"
echo ""

# Démarrer le serveur PHP (limites upload : vidéos > 8 Mo / médiathèque AJAX)
# Sans cela : post_max_size / upload_max_filesize trop bas → erreur AJAX même en HTTP 200.
exec php \
  -d upload_max_filesize=128M \
  -d post_max_size=128M \
  -d max_execution_time=300 \
  -d max_input_time=300 \
  -d memory_limit=512M \
  -S "$HOST:$PORT" .ht.router.php
