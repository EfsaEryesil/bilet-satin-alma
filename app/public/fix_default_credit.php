<?php
require_once __DIR__ . '/../src/db.php';
$pdo = db();

$pdo->exec("
    CREATE TABLE IF NOT EXISTS users_new (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT,
        email TEXT UNIQUE,
        password TEXT,
        role TEXT DEFAULT 'user',
        credit INTEGER DEFAULT 20000,
        firm_id INTEGER
    );
");

$pdo->exec("
    INSERT INTO users_new (id, name, email, password, role, credit, firm_id)
    SELECT id, name, email, password, role,
           CASE WHEN credit IS NULL OR credit = 0 THEN 20000 ELSE credit END,
           firm_id
    FROM users;
");

$pdo->exec("DROP TABLE users;");
$pdo->exec("ALTER TABLE users_new RENAME TO users;");

echo '✅ Tüm kullanıcılar artık varsayılan 20.000₺ krediye sahip!';
?>

