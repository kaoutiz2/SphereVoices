<?php
/**
 * Formulaire de login custom SANS Drupal
 * URL: https://www.spherevoices.com/login-direct.php
 */

session_start();

// Vérifier si déjà connecté via Drupal
try {
    require_once __DIR__ . '/autoload.php';
    $autoloader = require __DIR__ . '/autoload.php';
    
    $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
    $kernel = \Drupal\Core\DrupalKernel::createFromRequest($request, $autoloader, 'prod');
    $kernel->boot();
    $kernel->prepareLegacyRequest($request);
    
    $current_user = \Drupal::currentUser();
    if ($current_user->isAuthenticated()) {
        header('Location: /');
        exit;
    }
} catch (\Exception $e) {
    // Ignorer les erreurs au chargement
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!empty($username) && !empty($password)) {
        // Charger la config DB
        $databases = [];
        include __DIR__ . '/sites/default/settings.php';
        
        if (!empty($databases['default']['default'])) {
            $db = $databases['default']['default'];
            
            try {
                $pdo = new PDO(
                    "mysql:host={$db['host']};dbname={$db['database']};charset=utf8mb4",
                    $db['username'],
                    $db['password'],
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
                
                // Chercher l'utilisateur
                $stmt = $pdo->prepare("SELECT uid, name, pass, status FROM users_field_data WHERE name = :username AND status = 1 LIMIT 1");
                $stmt->execute(['username' => $username]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user) {
                    // Charger Drupal complètement pour créer une vraie session
                    require_once __DIR__ . '/autoload.php';
                    $autoloader = require __DIR__ . '/autoload.php';
                    
                    $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
                    $kernel = \Drupal\Core\DrupalKernel::createFromRequest($request, $autoloader, 'prod');
                    $kernel->boot();
                    $kernel->prepareLegacyRequest($request);
                    
                    // Vérifier le mot de passe avec l'API Drupal
                    $password_hasher = \Drupal::service('password');
                    
                    if ($password_hasher->check($password, $user['pass'])) {
                        // Charger l'utilisateur Drupal
                        $drupal_user = \Drupal\user\Entity\User::load($user['uid']);
                        
                        if ($drupal_user) {
                            // Connecter l'utilisateur avec l'API Drupal
                            user_login_finalize($drupal_user);
                            
                            // Rediriger vers la page d'accueil
                            header('Location: /');
                            exit;
                        } else {
                            $error = 'Impossible de charger l\'utilisateur.';
                        }
                    } else {
                        $error = 'Nom d\'utilisateur ou mot de passe incorrect.';
                    }
                } else {
                    $error = 'Nom d\'utilisateur ou mot de passe incorrect.';
                }
                
            } catch (PDOException $e) {
                $error = 'Erreur de connexion : ' . $e->getMessage();
            }
        } else {
            $error = 'Configuration de base de données introuvable.';
        }
    } else {
        $error = 'Veuillez remplir tous les champs.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - SphereVoices</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo h1 {
            color: #667eea;
            font-size: 32px;
            font-weight: bold;
        }
        .logo p {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
        }
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
        }
        .btn-login:active {
            transform: translateY(0);
        }
        .error {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #c33;
        }
        .success {
            background: #efe;
            color: #3c3;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #3c3;
        }
        .info {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }
        .info a {
            color: #667eea;
            text-decoration: none;
        }
        .info a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>🌍 SphereVoices</h1>
            <p>Connexion administrateur</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error">❌ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success">✅ <?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Nom d'utilisateur</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    required 
                    autofocus
                    placeholder="Entrez votre nom d'utilisateur"
                    value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                >
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required
                    placeholder="Entrez votre mot de passe"
                >
            </div>
            
            <button type="submit" class="btn-login">
                🔐 Se connecter
            </button>
        </form>
        
        <div class="info">
            <p><a href="/">← Retour à l'accueil</a></p>
            <p style="margin-top: 10px; font-size: 12px;">Login custom - Bypass Drupal</p>
        </div>
    </div>
</body>
</html>

