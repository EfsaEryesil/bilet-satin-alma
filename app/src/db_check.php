<?php
require __DIR__ . '/../src/db.php';
$pdo = db();
echo "<h3>Aktif Veritabanı:</h3>";
echo realpath(__DIR__ . '/../data/database.sqlite');

echo "<h3>Users Tablosu:</h3><pre>";
print_r($pdo->query("SELECT id, email, credit FROM users")->fetchAll(PDO::FETCH_ASSOC));
