<?php
/**
 * Script de nettoyage Git pour OVH
 * À exécuter UNE FOIS puis SUPPRIMER
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Nettoyage Git OVH</h1>";
echo "<pre>";

$root = dirname(dirname(__FILE__));
$gitDir = $root . '/.git';

echo "=== Nettoyage du dépôt Git ===\n";
echo "Racine: $root\n\n";

if (!is_dir($gitDir)) {
    echo "ERREUR: .git n'existe pas\n";
    echo "Le dépôt Git n'est pas initialisé sur le serveur.\n";
    exit;
}

// Vérifier les fichiers non trackés
echo "Vérification des fichiers non trackés...\n";
chdir($root);
exec('git status --porcelain 2>&1', $output, $return);

if ($return !== 0) {
    echo "ERREUR lors de la vérification Git\n";
    echo implode("\n", $output) . "\n";
    exit;
}

$untracked = array();
foreach ($output as $line) {
    if (preg_match('/^\?\? (.+)$/', $line, $matches)) {
        $untracked[] = $matches[1];
    }
}

if (empty($untracked)) {
    echo "Aucun fichier non tracké trouvé\n";
} else {
    echo "Fichiers non trackés trouvés:\n";
    foreach ($untracked as $file) {
        echo "  - $file\n";
    }
    echo "\n";
    echo "Solution: Supprimez ces fichiers via FTP ou exécutez:\n";
    echo "  git clean -fd\n";
    echo "\n";
    echo "OU modifiez la configuration Git OVH pour forcer le checkout.\n";
}

echo "\n=== Instructions ===\n";
echo "1. Via FTP: Supprimez les fichiers listés ci-dessus\n";
echo "2. OU dans OVH Git: Activez l'option 'Nettoyer avant déploiement'\n";
echo "3. Relancez le déploiement Git\n";
echo "\n";

echo "</pre>";
echo "<p style='color: red;'>⚠️ SUPPRIMEZ CE FICHIER APRÈS UTILISATION !</p>";
?>
