<?php
require_once __DIR__ . '/../src/db.php';
$pdo = db();
$pdo->exec("UPDATE users SET credit = 20000 WHERE role = 'user'");
echo "✅ Tüm kullanıcıların kredisi 20 000₺ olarak ayarlandı!";
