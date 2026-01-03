<?php
/**
 * Patch settings.php pour définir $app_root
 * URL: https://www.spherevoices.com/patch-settings.php?token=spherevoices2026
 */

$security_token = 'spherevoices2026';
$provided_token = $_GET['token'] ?? '';

if ($provided_token !== $security_token) {
    die('Token requis');
}

header('Content-Type: text/plain; charset=utf-8');

echo "=== PATCH SETTINGS.PHP ===\n\n";

$settings_path = __DIR__ . '/sites/default/settings.php';

if (!file_exists($settings_path)) {
    die("❌ settings.php introuvable\n");
}

if (!is_writable($settings_path)) {
    die("❌ settings.php non modifiable (permissions)\n");
}

echo "Lecture du fichier...\n";
$content = file_get_contents($settings_path);

// Vérifier si le patch est déjà appliqué
if (strpos($content, "// Définir \$app_root s'il n'est pas défini") !== false) {
    echo "✅ Le patch est DÉJÀ appliqué !\n\n";
    echo "Le fichier contient déjà la définition de \$app_root.\n";
    exit;
}

echo "Application du patch...\n\n";

// Chercher la ligne "$env_file_path = NULL;"
$search = '$env_file_path = NULL;';
$replace = '$env_file_path = NULL;

// Définir $app_root s\'il n\'est pas défini (pour les scripts qui chargent settings.php directement)
if (!isset($app_root)) {
  $app_root = dirname(dirname(__DIR__));
}';

$new_content = str_replace($search, $replace, $content, $count);

if ($count === 0) {
    echo "❌ Impossible de trouver la ligne à remplacer\n";
    echo "Recherché: $search\n";
    exit;
}

// Faire une sauvegarde
$backup_path = $settings_path . '.backup.' . date('Y-m-d_H-i-s');
if (!copy($settings_path, $backup_path)) {
    echo "⚠️ Impossible de créer la sauvegarde\n";
} else {
    echo "✅ Sauvegarde créée : " . basename($backup_path) . "\n";
}

// Écrire le nouveau contenu
if (file_put_contents($settings_path, $new_content) === false) {
    echo "❌ Erreur lors de l'écriture du fichier\n";
    exit;
}

echo "✅ Fichier modifié avec succès !\n\n";

// Vérifier le résultat
$lines = file($settings_path);
echo "LIGNES 920-945 (après patch):\n";
echo "==============================\n\n";

for ($i = 919; $i < 945 && $i < count($lines); $i++) {
    $line_num = $i + 1;
    echo sprintf("%4d: %s", $line_num, $lines[$i]);
}

echo "\n\n=== RÉSULTAT ===\n\n";
echo "✅ Le patch est appliqué\n";
echo "✅ \$app_root est maintenant défini avant utilisation\n";
echo "✅ Le warning devrait disparaître\n\n";

echo "⚠️ IMPORTANT : Videz le cache PHP !\n";
echo "URL: https://www.spherevoices.com/full-reset.php?token=spherevoices2026\n";

