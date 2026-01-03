<?php
/**
 * Vérifie le contenu de settings.php ligne 933
 * URL: https://www.spherevoices.com/check-settings.php?token=spherevoices2026
 */

$security_token = 'spherevoices2026';
$provided_token = $_GET['token'] ?? '';

if ($provided_token !== $security_token) {
    die('Token requis');
}

header('Content-Type: text/plain; charset=utf-8');

echo "=== VÉRIFICATION SETTINGS.PHP ===\n\n";

$settings_path = __DIR__ . '/sites/default/settings.php';

echo "Chemin: $settings_path\n";
echo "Existe: " . (file_exists($settings_path) ? "OUI" : "NON") . "\n";
echo "Modifié: " . date('Y-m-d H:i:s', filemtime($settings_path)) . "\n\n";

$lines = file($settings_path);

echo "LIGNES 920-940:\n";
echo "===============\n\n";

for ($i = 919; $i < 940 && $i < count($lines); $i++) {
    $line_num = $i + 1;
    echo sprintf("%4d: %s", $line_num, $lines[$i]);
}

echo "\n\n=== RECHERCHE DE \$app_root ===\n\n";

$found = false;
foreach ($lines as $num => $line) {
    if (strpos($line, '$app_root') !== false && strpos($line, '//') !== 0) {
        echo sprintf("%4d: %s", $num + 1, $line);
        $found = true;
        if ($num > 925 && $num < 950) {
            echo "      ^^^ CETTE LIGNE EST DANS LA ZONE PROBLÉMATIQUE\n";
        }
    }
}

if (!$found) {
    echo "Aucune référence à \$app_root trouvée\n";
}

echo "\n\n=== DIAGNOSTIC ===\n\n";

// Vérifier si la ligne 933 contient $app_root
if (isset($lines[932])) {
    $line_933 = $lines[932];
    echo "Ligne 933 actuelle:\n";
    echo "  " . $line_933 . "\n";
    
    if (strpos($line_933, '$app_root') !== false) {
        echo "\n❌ PROBLÈME: La ligne 933 utilise toujours \$app_root !\n";
        echo "Le fichier n'est PAS encore déployé.\n";
        echo "\nAttendu: if (!isset(\$app_root)) {\n";
        echo "Trouvé: " . trim($line_933) . "\n";
    } else if (strpos($line_933, 'isset') !== false && strpos($line_933, '$app_root') !== false) {
        echo "\n✅ BON: La ligne 933 définit \$app_root avant de l'utiliser !\n";
        echo "Le fichier EST déployé.\n";
    } else {
        echo "\n⚠️ La ligne 933 ne contient ni \$app_root ni isset\n";
        echo "Contenu: " . trim($line_933) . "\n";
    }
}

