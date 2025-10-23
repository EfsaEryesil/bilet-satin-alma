<?php
require __DIR__ . '/../src/db.php';
$pdo = db();

echo "<pre>USERS TABLOSU:\n";
print_r($pdo->query("PRAGMA table_info(users)")->fetchAll(PDO::FETCH_ASSOC));

echo "\nTICKETS TABLOSU:\n";
print_r($pdo->query("PRAGMA table_info(tickets)")->fetchAll(PDO::FETCH_ASSOC));
