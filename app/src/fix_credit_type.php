<?php
require __DIR__ . '/db.php';
$pdo = db();


$pdo->exec("UPDATE users SET credit = 20000 WHERE credit IS NULL OR credit = '';");


$pdo->exec("
CREATE TABLE users_new (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT,
    email TEXT UNIQUE,
    password TEXT,
    role TEXT DEFAULT 'user',
    credit REAL DEFAULT 20000,
    firm_id INTEGER
);
");


$pdo->exec("
INSERT INTO users_new (id, name, email, password, role, credit, firm_id)
SELECT id, name, email, password, role, COALESCE(credit, 20000), firm_id FROM users;
");


$pdo->exec("DROP TABLE users;");
$pdo->exec("ALTER TABLE users_new RENAME TO users;");

echo "✅ Kullanıcıların tümü artık varsayılan 20000₺ krediye sahip!";
