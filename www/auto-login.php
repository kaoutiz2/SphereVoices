<?php
/**
 * Script de connexion automatique Drupal
 * Génère un lien one-time login pour l'utilisateur spécifié
 * URL: https://www.spherevoices.com/auto-login.php?token=spherevoices2026
 */

$security_token = 'spherevoices2026';
$provided_token = $_GET['token'] ?? '';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>🔐 Auto Login</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #28a745; padding: 10px; background: #d4edda; border-radius: 4px; margin: 10px 0; }
        .error { color: #dc3545; padding: 10px; background: #f8d7da; border-radius: 4px; margin: 10px 0; }
        .warning { color: #856404; padding: 10px; background: #fff3cd; border-radius: 4px; margin: 10px 0; }
        .info { color: #0c5460; padding: 10px; background: #d1ecf1; border-radius: 4px; margin: 10px 0; }
        .btn { display: inline-block; padding: 15px 30px; margin: 10px 5px; background: #28a745; color: white; text-decoration: none; border-radius: 4px; font-size: 18px; font-weight: bold; }
        .btn:hover { background: #218838; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Connexion Automatique Drupal</h1>
        
        <?php
        if ($provided_token === $security_token) {
            echo '<div class="info">🚀 Génération du lien de connexion one-time...</div>';
            
            $drupal_root = __DIR__;
            
            if (!file_exists($drupal_root . '/autoload.php')) {
                echo '<div class="error">❌ Drupal non trouvé</div>';
                exit;
            }
            
            try {
                // Charger Drupal
                require_once $drupal_root . '/autoload.php';
                $autoloader = require $drupal_root . '/autoload.php';
                
                $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
                $kernel = \Drupal\Core\DrupalKernel::createFromRequest($request, $autoloader, 'prod');
                $kernel->boot();
                $kernel->prepareLegacyRequest($request);
                
                echo '<div class="success">✅ Drupal chargé</div>';
                
                // Chercher l'utilisateur "Kaoutiz"
                $users = \Drupal::entityTypeManager()
                    ->getStorage('user')
                    ->loadByProperties(['name' => 'Kaoutiz']);
                
                if (empty($users)) {
                    // Essayer avec uid 1 (admin)
                    $user = \Drupal\user\Entity\User::load(1);
                    echo '<div class="warning">⚠️ Utilisateur "Kaoutiz" non trouvé, utilisation de l\'admin (uid 1)</div>';
                } else {
                    $user = reset($users);
                    echo '<div class="success">✅ Utilisateur "' . htmlspecialchars($user->getAccountName()) . '" trouvé (uid: ' . $user->id() . ')</div>';
                }
                
                if ($user) {
                    // Générer le lien one-time login
                    $timestamp = time();
                    $login_url = user_pass_reset_url($user);
                    
                    echo '<div class="success">✅ Lien de connexion généré !</div>';
                    
                    echo '<h2 class="success">🎉 LIEN DE CONNEXION PRÊT !</h2>';
                    
                    echo '<div class="info">';
                    echo '<p><strong>Utilisateur :</strong> ' . htmlspecialchars($user->getAccountName()) . '</p>';
                    echo '<p><strong>Email :</strong> ' . htmlspecialchars($user->getEmail()) . '</p>';
                    echo '<p><strong>UID :</strong> ' . $user->id() . '</p>';
                    echo '</div>';
                    
                    echo '<div class="warning">';
                    echo '<h3>⚠️ IMPORTANT :</h3>';
                    echo '<p>Ce lien est <strong>valable UNE SEULE FOIS</strong> et expire après utilisation.</p>';
                    echo '<p>Il vous connecte automatiquement et vous demande de changer le mot de passe.</p>';
                    echo '</div>';
                    
                    echo '<div style="text-align: center; margin: 30px 0;">';
                    echo '<a href="' . htmlspecialchars($login_url) . '" class="btn">🔐 SE CONNECTER MAINTENANT</a>';
                    echo '</div>';
                    
                    echo '<div class="info">';
                    echo '<h3>📋 Lien complet :</h3>';
                    echo '<pre>' . htmlspecialchars($login_url) . '</pre>';
                    echo '<p><small>Copiez ce lien si le bouton ne fonctionne pas.</small></p>';
                    echo '</div>';
                    
                } else {
                    echo '<div class="error">❌ Impossible de charger l\'utilisateur</div>';
                }
                
            } catch (\Exception $e) {
                echo '<div class="error">❌ Erreur : ' . htmlspecialchars($e->getMessage()) . '</div>';
                echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
            }
            
        } else {
            ?>
            <div class="warning">⚠️ Ce script génère un lien de connexion one-time pour Drupal.</div>
            
            <form method="get">
                <label for="token">Token de sécurité:</label><br>
                <input type="text" id="token" name="token" value="" style="width: 300px; padding: 5px; margin: 10px 0;">
                <br>
                <button type="submit" class="btn">Générer le lien</button>
            </form>
            
            <h3>📝 Ce que fait ce script :</h3>
            <ol>
                <li>Charge Drupal</li>
                <li>Trouve l'utilisateur "Kaoutiz" (ou admin uid 1)</li>
                <li>Génère un lien one-time login</li>
                <li>Vous pouvez cliquer dessus pour vous connecter automatiquement</li>
            </ol>
            
            <h3>🔗 URL :</h3>
            <pre>https://www.spherevoices.com/auto-login.php?token=spherevoices2026</pre>
            <?php
        }
        ?>
    </div>
</body>
</html>

