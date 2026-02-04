<?php

/**
 * @file
 * Génère le contenu de la page agenda (sans HTML wrapper).
 */

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

// Se connecter à la base de données
$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: '3306';
$dbname = getenv('DB_NAME') ?: 'spherevoices';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: '';

// Récupérer les paramètres
$title_search = $_GET['title'] ?? '';
$month_filter = $_GET['month'] ?? '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
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
    $events = [];
}

// Construire les cartes d'événements
$events_html = '';
if (empty($events)) {
    $events_html = '<div class="no-events"><p>Aucun événement trouvé.</p></div>';
} else {
    foreach ($events as $event) {
        $event_date = '';
        if ($event['field_event_date_value']) {
            $event_date = date('d/m/Y', strtotime($event['field_event_date_value']));
        }
        
        $body = strip_tags($event['body_value']);
        $body_excerpt = htmlspecialchars(substr($body, 0, 200)) . (strlen($body) > 200 ? '...' : '');
        
        $events_html .= sprintf(
            '<div class="event-card">
                <div class="event-date">%s</div>
                <h3 class="event-title">%s</h3>
                <div class="event-body">%s</div>
            </div>',
            $event_date,
            htmlspecialchars($event['title']),
            $body_excerpt
        );
    }
}

$count = count($events);

// Retourner le contenu HTML (sera intégré dans le thème Drupal)
return [
    'title_search' => $title_search,
    'month_filter' => $month_filter,
    'events_html' => $events_html,
    'count' => $count,
];

