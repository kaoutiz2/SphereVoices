<?php
/**
 * Affiche le nom correct du cookie de session Drupal
 * URL: https://www.spherevoices.com/get-cookie-name.php?token=spherevoices2026
 */

$security_token = 'spherevoices2026';
$provided_token = $_GET['token'] ?? '';

if ($provided_token !== $security_token) {
    die('Token requis');
}

header('Content-Type: text/plain; charset=utf-8');

echo "=== NOM DU COOKIE DE SESSION DRUPAL ===\n\n";

// Méthode 1 : Calculer comme Drupal le fait
$base_url = 'https://www.spherevoices.com';
$cookie_name = 'SSESS' . substr(hash('sha256', $base_url), 0, 32);

echo "Méthode 1 (base_url):\n";
echo "  base_url: $base_url\n";
echo "  Cookie name: $cookie_name\n\n";

// Méthode 2 : Regarder les cookies actuels
echo "Méthode 2 (cookies reçus):\n";
if (!empty($_COOKIE)) {
    foreach ($_COOKIE as $name => $value) {
        if (strpos($name, 'SSESS') === 0 || strpos($name, 'SESS') === 0) {
            echo "  ✅ Cookie Drupal trouvé: $name\n";
            echo "     Valeur: " . substr($value, 0, 20) . "...\n";
        }
    }
    
    if (empty(array_filter(array_keys($_COOKIE), function($k) {
        return strpos($k, 'SESS') === 0;
    }))) {
        echo "  ⚠️ Aucun cookie de session Drupal trouvé\n";
        echo "  Cookies présents:\n";
        foreach ($_COOKIE as $name => $value) {
            echo "    - $name\n";
        }
    }
} else {
    echo "  Aucun cookie reçu\n";
}

echo "\n=== COOKIES À DÉFINIR ===\n\n";
echo "Pour créer une session Drupal, utilisez:\n";
echo "setcookie('$cookie_name', \$session_id, [\n";
echo "  'expires' => 0,\n";
echo "  'path' => '/',\n";
echo "  'domain' => '',\n";
echo "  'secure' => true,\n";
echo "  'httponly' => true,\n";
echo "  'samesite' => 'Lax'\n";
echo "]);\n";

