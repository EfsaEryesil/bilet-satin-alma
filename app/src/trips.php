<?php
require_once __DIR__ . '/db.php';

function delete_expired_trips(PDO $pdo) {
    $today = date('Y-m-d');
    $st = $pdo->prepare("DELETE FROM trips WHERE date < ?");
    $st->execute([$today]);
}

function list_trips(PDO $pdo, $from = null, $to = null, $date = null) {
    $sql = "SELECT tr.*, f.name AS firm_name 
            FROM trips tr 
            JOIN firms f ON tr.firm_id = f.id 
            WHERE 1=1";
    $params = [];

    if ($from) { 
        $sql .= " AND LOWER(tr.from_city) LIKE LOWER(?)"; 
        $params[] = "%$from%"; 
    }
    if ($to) { 
        $sql .= " AND LOWER(tr.to_city) LIKE LOWER(?)";   
        $params[] = "%$to%"; 
    }
    if ($date) { 
        $sql .= " AND tr.date = ?"; 
        $params[] = $date; 
    }

    $sql .= " ORDER BY tr.date, tr.time";
    $st = $pdo->prepare($sql);
    $st->execute($params);
    return $st->fetchAll(PDO::FETCH_ASSOC);
}


function get_trip(PDO $pdo, $id) {
    $st = $pdo->prepare("
        SELECT tr.*, f.name AS firm_name
        FROM trips tr
        JOIN firms f ON tr.firm_id = f.id
        WHERE tr.id = ?
    ");
    $st->execute([$id]);
    return $st->fetch(PDO::FETCH_ASSOC);
}
