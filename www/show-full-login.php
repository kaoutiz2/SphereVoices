<?php
/**
 * Affiche le HTML COMPLET de la page de login
 * URL: https://www.spherevoices.com/show-full-login.php?token=spherevoices2026
 */

$security_token = 'spherevoices2026';
$provided_token = $_GET['token'] ?? '';

if ($provided_token !== $security_token) {
    die('Token requis');
}

$login_url = 'https://www.spherevoices.com/user/login';

$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => "User-Agent: Mozilla/5.0\r\n",
        'timeout' => 10,
        'ignore_errors' => true,
    ],
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
    ]
]);

$html = @file_get_contents($login_url, false, $context);

header('Content-Type: text/plain; charset=utf-8');

if ($html === false) {
    echo "ERREUR : Impossible de récupérer la page\n";
} elseif (strlen($html) === 0) {
    echo "ERREUR : Page vide\n";
} else {
    echo "============================================\n";
    echo "HTML COMPLET DE /user/login\n";
    echo "============================================\n";
    echo "Taille : " . number_format(strlen($html)) . " bytes\n";
    echo "============================================\n\n";
    echo $html;
    echo "\n\n============================================\n";
    echo "FIN DU HTML\n";
    echo "============================================\n";
}

