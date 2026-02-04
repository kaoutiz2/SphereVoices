<?php

/**
 * @file
 * Page agenda simple - sans bootstrap Drupal complet.
 */

// Rediriger vers la route Drupal pour afficher le thème et le menu.
// Évite la boucle si /agenda est réécrit vers ce script en amont.
if (PHP_SAPI !== 'cli') {
    $request_path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    if ($request_path === '/agenda.php') {
        header('Location: /agenda', true, 302);
        exit;
    }
}

// Charger les paramètres
$title_search = $_GET['title'] ?? '';
$month_filter = $_GET['month'] ?? '';

// Charger les variables d'environnement (.env / .env.production)
$project_root = dirname(__DIR__);
$env_file_path = null;

if (PHP_SAPI === 'cli') {
    $env_type = getenv('DRUPAL_ENV') ?: 'development';
    $env_file_path = ($env_type === 'production')
        ? $project_root . '/.env.production'
        : $project_root . '/.env';
} else {
    $request_host = $_SERVER['HTTP_HOST'] ?? '';
    $is_production = preg_match('/^(www\.)?spherevoices\.com(:[0-9]+)?$/', $request_host);
    $env_file_path = $is_production
        ? $project_root . '/.env.production'
        : $project_root . '/.env';
}

if ($env_file_path && file_exists($env_file_path)) {
    $env_file = @file($env_file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($env_file !== false) {
        foreach ($env_file as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0) {
                continue;
            }
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                $value = trim($value, '"\'');
                if (!getenv($key)) {
                    putenv("$key=$value");
                    $_ENV[$key] = $value;
                    $_SERVER[$key] = $value;
                }
            }
        }
    }
}

// Se connecter à la base de données directement
$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: '3306';
$dbname = getenv('DB_NAME') ?: 'spherevoices';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Construire la requête SQL
    $sql = "SELECT n.nid, n.title, nfd.field_event_date_value, b.body_value 
            FROM node_field_data n
            LEFT JOIN node__field_event_date nfd ON n.nid = nfd.entity_id
            LEFT JOIN node__body b ON n.nid = b.entity_id
            WHERE n.type = 'event' AND n.status = 1";
    
    $params = [];
    
    if (!empty($title_search)) {
        $sql .= " AND n.title LIKE :title";
        $params[':title'] = '%' . $title_search . '%';
    }
    
    if (!empty($month_filter)) {
        $start_date = $month_filter . '-01';
        $end_date = date('Y-m-t', strtotime($start_date));
        $sql .= " AND nfd.field_event_date_value >= :start_date AND nfd.field_event_date_value <= :end_date";
        $params[':start_date'] = $start_date;
        $params[':end_date'] = $end_date;
    }
    
    $sql .= " ORDER BY nfd.field_event_date_value ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = "Erreur de connexion : " . $e->getMessage();
    $events = [];
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda des événements - SphereVoices</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --color-primary: #cc0000;
            --color-secondary: #333;
            --color-border: #ddd;
            --color-bg-light: #f5f5f5;
            --color-text: #333;
            --color-text-light: #666;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body { 
            font-family: 'Roboto', Arial, sans-serif; 
            background: #fff;
            color: var(--color-text);
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }
        
        .page-title { 
            font-size: 2.5rem; 
            font-weight: 700;
            color: var(--color-secondary); 
            border-bottom: 3px solid var(--color-primary); 
            padding-bottom: 1rem;
            margin-bottom: 0.5rem;
        }
        
        .page-subtitle {
            color: var(--color-text-light);
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }
        
        .search-form { 
            background: var(--color-bg-light); 
            padding: 2rem; 
            border-radius: 8px; 
            margin: 2rem 0;
            border: 1px solid var(--color-border);
        }
        
        .form-row { 
            display: flex; 
            gap: 1rem; 
            flex-wrap: wrap;
            align-items: flex-end;
        }
        
        .form-item { 
            flex: 1; 
            min-width: 200px; 
        }
        
        label { 
            display: block; 
            font-weight: 600; 
            margin-bottom: 0.5rem;
            color: var(--color-secondary);
        }
        
        input { 
            width: 100%; 
            padding: 0.75rem 1rem; 
            border: 1px solid var(--color-border); 
            border-radius: 4px;
            font-size: 1rem;
            font-family: inherit;
        }
        
        input:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(204, 0, 0, 0.1);
        }
        
        .form-actions {
            display: flex;
            gap: 0.5rem;
            flex: 0 0 auto;
        }
        
        button { 
            padding: 0.75rem 2rem; 
            background: var(--color-primary); 
            color: white; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            font-weight: 600;
            font-size: 1rem;
            transition: background-color 0.2s ease;
        }
        
        button:hover { 
            background: #990000; 
        }
        
        .reset-btn { 
            background: var(--color-bg-light); 
            color: var(--color-text); 
            text-decoration: none; 
            display: inline-block;
            padding: 0.75rem 2rem;
            border-radius: 4px;
            font-weight: 600;
            border: 1px solid var(--color-border);
            transition: background-color 0.2s ease;
        }
        
        .reset-btn:hover {
            background: #e0e0e0;
        }
        
        .events-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); 
            gap: 1.5rem; 
            margin-top: 2rem; 
        }
        
        .event-card { 
            border: 1px solid var(--color-border); 
            border-radius: 8px; 
            padding: 1.5rem; 
            background: white;
            transition: box-shadow 0.2s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .event-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .event-date { 
            color: var(--color-primary); 
            font-weight: 700; 
            font-size: 1.1rem; 
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .event-date:before {
            content: "📅";
        }
        
        .event-title { 
            font-size: 1.3rem; 
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: var(--color-secondary);
        }
        
        .event-body { 
            color: var(--color-text-light); 
            line-height: 1.7;
        }
        
        .no-events {
            text-align: center;
            padding: 3rem;
            background: var(--color-bg-light);
            border-radius: 8px;
            color: var(--color-text-light);
            font-size: 1.2rem;
        }
        
        .error-message {
            background: #fee;
            color: #c00;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        
        @media (max-width: 768px) {
            .page-title { font-size: 2rem; }
            .form-row { flex-direction: column; }
            .form-item, .form-actions { width: 100%; }
            .events-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="page-title">Agenda des événements</h1>
        <p class="page-subtitle">Retrouvez tous les événements à venir</p>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="search-form">
            <form method="get">
                <div class="form-row">
                    <div class="form-item">
                        <label for="title">Rechercher un événement</label>
                        <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($title_search); ?>" placeholder="Rechercher par titre...">
                    </div>
                    
                    <div class="form-item">
                        <label for="month">Mois</label>
                        <input type="month" name="month" id="month" value="<?php echo htmlspecialchars($month_filter); ?>">
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit">Rechercher</button>
                        <a href="?" class="reset-btn">Réinitialiser</a>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="events-grid">
            <?php if (empty($events)): ?>
                <div class="no-events">
                    <p>Aucun événement trouvé.</p>
                </div>
            <?php else: ?>
                <?php foreach ($events as $event): ?>
                    <div class="event-card">
                        <div class="event-date">
                            <?php 
                            if ($event['field_event_date_value']) {
                                echo date('d/m/Y', strtotime($event['field_event_date_value']));
                            }
                            ?>
                        </div>
                        <h3 class="event-title"><?php echo htmlspecialchars($event['title']); ?></h3>
                        <div class="event-body">
                            <?php 
                            $body = strip_tags($event['body_value']);
                            echo htmlspecialchars(substr($body, 0, 200)) . (strlen($body) > 200 ? '...' : '');
                            ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--color-border); text-align: center; color: var(--color-text-light);">
            <p>Nombre d'événements affichés : <strong><?php echo count($events); ?></strong></p>
        </div>
    </div>
</body>
</html>
