<?php
/**
 * Vérifie les sessions Drupal actives
 * URL: https://www.spherevoices.com/check-sessions.php?token=spherevoices2026
 */

$security_token = 'spherevoices2026';
$provided_token = $_GET['token'] ?? '';

if ($provided_token !== $security_token) {
    die('Token requis');
}

header('Content-Type: text/plain; charset=utf-8');

echo "=== SESSIONS DRUPAL ACTIVES ===\n\n";

// Définir $app_root
$app_root = dirname(__DIR__);

// Charger la config DB
$databases = [];
include __DIR__ . '/sites/default/settings.php';

if (empty($databases['default']['default'])) {
    die("❌ Config DB introuvable\n");
}

$db = $databases['default']['default'];

try {
    $pdo = new PDO(
        "mysql:host={$db['host']};dbname={$db['database']};charset=utf8mb4",
        $db['username'],
        $db['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "✅ Connecté à MySQL\n\n";
    
    // Lister les sessions
    $stmt = $pdo->query("SELECT s.uid, s.sid, s.hostname, s.timestamp, u.name 
                         FROM sessions s 
                         LEFT JOIN users_field_data u ON s.uid = u.uid 
                         WHERE s.uid > 0
                         ORDER BY s.timestamp DESC 
                         LIMIT 10");
    $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Sessions actives (utilisateurs connectés):\n";
    echo "==========================================\n\n";
    
    if (empty($sessions)) {
        echo "⚠️ Aucune session active\n";
    } else {
        foreach ($sessions as $session) {
            echo "User: " . $session['name'] . " (uid: " . $session['uid'] . ")\n";
            echo "  SID: " . $session['sid'] . "\n";
            echo "  IP: " . $session['hostname'] . "\n";
            echo "  Date: " . date('Y-m-d H:i:s', $session['timestamp']) . "\n";
            echo "  Age: " . round((time() - $session['timestamp']) / 60) . " minutes\n";
            echo "\n";
        }
    }
    
    // Vérifier les cookies reçus
    echo "\n=== COOKIES REÇUS ===\n\n";
    
    $drupal_cookie = null;
    foreach ($_COOKIE as $name => $value) {
        if (strpos($name, 'SSESS') === 0) {
            echo "Cookie Drupal: $name\n";
            echo "  Valeur: $value\n";
            $drupal_cookie = $value;
            
            // Chercher cette session dans la DB
            $stmt = $pdo->prepare("SELECT s.uid, s.timestamp, u.name 
                                   FROM sessions s 
                                   LEFT JOIN users_field_data u ON s.uid = u.uid 
                                   WHERE s.sid = :sid");
            $stmt->execute(['sid' => $value]);
            $found = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($found) {
                echo "  ✅ Session trouvée dans la DB !\n";
                echo "  User: " . $found['name'] . " (uid: " . $found['uid'] . ")\n";
                echo "  Dernière activité: " . date('Y-m-d H:i:s', $found['timestamp']) . "\n";
            } else {
                echo "  ❌ Session NON TROUVÉE dans la DB\n";
                echo "  Le cookie existe mais la session n'est pas en base\n";
            }
        }
    }
    
    if (!$drupal_cookie) {
        echo "⚠️ Aucun cookie de session Drupal reçu\n";
    }
    
    // Structure de la table sessions
    echo "\n\n=== STRUCTURE TABLE SESSIONS ===\n\n";
    $stmt = $pdo->query("DESCRIBE sessions");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $col) {
        echo $col['Field'] . " - " . $col['Type'] . "\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Erreur MySQL: " . $e->getMessage() . "\n";
}

