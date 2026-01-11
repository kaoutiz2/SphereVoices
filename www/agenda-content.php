<?php

/**
 * @file
 * Génère le contenu de la page agenda (sans HTML wrapper).
 */

// Se connecter à la base de données
$host = 'localhost';
$dbname = 'spherevoices';
$user = 'root';
$pass = '';

// Récupérer les paramètres
$title_search = $_GET['title'] ?? '';
$month_filter = $_GET['month'] ?? '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
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

