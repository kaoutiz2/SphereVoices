<?php
/**
 * Teste la page de login et affiche le HTML brut
 * URL: https://www.spherevoices.com/test-login-page.php?token=spherevoices2026
 */

$security_token = 'spherevoices2026';
$provided_token = $_GET['token'] ?? '';

if ($provided_token !== $security_token) {
    die('Token requis');
}

// Essayer de charger la page de login
$login_url = 'https://www.spherevoices.com/user/login';

$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => "User-Agent: Mozilla/5.0\r\n",
        'timeout' => 10,
        'ignore_errors' => true, // Important pour voir les erreurs
    ],
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
    ]
]);

$html = @file_get_contents($login_url, false, $context);

// Récupérer les headers de réponse
$headers = $http_response_header ?? [];

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>🔍 Test Login Page</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #d4d4d4; }
        .success { color: #4ec9b0; }
        .error { color: #f48771; }
        .warning { color: #dcdcaa; }
        .info { color: #9cdcfe; }
        pre { background: #2d2d2d; padding: 15px; border-radius: 4px; overflow-x: auto; border: 1px solid #3e3e3e; }
        h2 { color: #569cd6; border-bottom: 2px solid #569cd6; padding-bottom: 5px; }
    </style>
</head>
<body>
    <h1>🔍 Test Page de Login</h1>
    
    <h2>📡 Headers HTTP</h2>
    <pre><?php
    if (!empty($headers)) {
        foreach ($headers as $header) {
            echo htmlspecialchars($header) . "\n";
        }
    } else {
        echo "Aucun header reçu\n";
    }
    ?></pre>
    
    <h2>📄 Contenu HTML (premiers 5000 caractères)</h2>
    <?php if ($html === false): ?>
        <pre class="error">❌ Impossible de récupérer la page</pre>
    <?php elseif (strlen($html) === 0): ?>
        <pre class="warning">⚠️ Page vide</pre>
    <?php else: ?>
        <pre class="success">✅ <?php echo number_format(strlen($html)); ?> bytes reçus</pre>
        <pre><?php echo htmlspecialchars(substr($html, 0, 5000)); ?></pre>
        
        <?php if (strlen($html) > 5000): ?>
            <p class="info">... et <?php echo number_format(strlen($html) - 5000); ?> bytes de plus</p>
        <?php endif; ?>
        
        <h2>🔍 Analyse</h2>
        <pre><?php
        echo "Présence de &lt;form&gt; : " . (strpos($html, '<form') !== false ? "✅ OUI" : "❌ NON") . "\n";
        echo "Présence de #user-login-form : " . (strpos($html, 'user-login-form') !== false ? "✅ OUI" : "❌ NON") . "\n";
        echo "Présence de input name=\"name\" : " . (preg_match('/<input[^>]*name=["\']name["\']/i', $html) ? "✅ OUI" : "❌ NON") . "\n";
        echo "Présence de input name=\"pass\" : " . (preg_match('/<input[^>]*name=["\']pass["\']/i', $html) ? "✅ OUI" : "❌ NON") . "\n";
        echo "Présence de 'error' : " . (stripos($html, 'error') !== false ? "⚠️ OUI" : "✅ NON") . "\n";
        echo "Présence de 'exception' : " . (stripos($html, 'exception') !== false ? "⚠️ OUI" : "✅ NON") . "\n";
        echo "Présence de 'maintenance' : " . (stripos($html, 'maintenance') !== false ? "⚠️ OUI" : "✅ NON") . "\n";
        ?></pre>
    <?php endif; ?>
    
    <h2>🌐 Test Direct (dans iframe)</h2>
    <iframe src="/user/login" style="width: 100%; height: 600px; border: 2px solid #569cd6; background: white;"></iframe>
    
</body>
</html>

