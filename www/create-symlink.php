<?php
/**
 * Script pour créer un lien symbolique www/www -> www/
 * À exécuter UNE FOIS puis SUPPRIMER
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Création lien symbolique www/www</h1>";
echo "<pre>";

$root = dirname(dirname(__FILE__));
$wwwDir = $root . '/www';
$symlinkTarget = $wwwDir . '/www';

echo "Racine: $root\n";
echo "Dossier www: $wwwDir\n";
echo "Cible du lien: $symlinkTarget\n\n";

// Vérifier que www/ existe
if (!is_dir($wwwDir)) {
    die("ERREUR: Le dossier www/ n'existe pas à: $wwwDir\n");
}

echo "✅ Dossier www/ existe\n\n";

// Vérifier si le lien existe déjà
if (is_link($symlinkTarget)) {
    echo "⚠️  Le lien symbolique existe déjà\n";
    $target = readlink($symlinkTarget);
    echo "   Il pointe vers: $target\n";
    if ($target === '.' || $target === $wwwDir) {
        echo "✅ Le lien est correct, rien à faire!\n";
        exit;
    } else {
        echo "⚠️  Le lien pointe vers un mauvais endroit, on va le recréer\n";
        unlink($symlinkTarget);
    }
} elseif (is_dir($symlinkTarget)) {
    echo "⚠️  Un dossier www/www existe déjà (pas un lien)\n";
    echo "   On ne peut pas créer le lien sans supprimer le dossier\n";
    echo "   Vous devez soit:\n";
    echo "   1. Supprimer le dossier www/www/ via FTP\n";
    echo "   2. OU déplacer son contenu vers www/ puis le supprimer\n";
    exit;
}

// Créer le lien symbolique
echo "Création du lien symbolique...\n";
if (symlink('.', $symlinkTarget)) {
    echo "✅ Lien symbolique créé avec succès!\n";
    echo "   www/www -> www/ (pointant vers .)\n\n";
    echo "Le DocumentRoot /www/www devrait maintenant fonctionner!\n";
    echo "Testez: https://www.spherevoices.com/simple-test.php\n";
} else {
    echo "❌ ERREUR: Impossible de créer le lien symbolique\n";
    echo "   Raisons possibles:\n";
    echo "   - Permissions insuffisantes\n";
    echo "   - Fonction symlink() désactivée sur le serveur\n";
    echo "   - Un dossier www/www existe déjà\n\n";
    echo "Solution alternative:\n";
    echo "1. Via SSH: cd /home/spheree/www && ln -s . www\n";
    echo "2. OU contactez le support OVH pour activer symlink()\n";
}

echo "\n⚠️  SUPPRIMEZ CE FICHIER APRÈS UTILISATION !\n";
echo "</pre>";
?>
