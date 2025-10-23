<?php
require __DIR__ . '/../src/db.php';
$pdo = db();

$pdo->exec("
CREATE TABLE IF NOT EXISTS users (
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
CREATE TABLE IF NOT EXISTS firms (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL
);
");

$pdo->exec("
CREATE TABLE IF NOT EXISTS trips (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    firm_id INTEGER,
    from_city TEXT,
    to_city TEXT,
    date TEXT,
    time TEXT,
    price REAL,
    seat_count INTEGER,
    FOREIGN KEY (firm_id) REFERENCES firms(id)
);
");

$pdo->exec("
CREATE TABLE IF NOT EXISTS tickets (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    trip_id INTEGER,
    seat_no INTEGER,
    price_paid REAL,
    coupon_code TEXT,
    status TEXT DEFAULT 'active',
    created_at TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (trip_id) REFERENCES trips(id)
);
");

$pdo->exec("
CREATE TABLE IF NOT EXISTS coupons (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    code TEXT,
    percent INTEGER,
    firm_id INTEGER,
    used INTEGER DEFAULT 0,
    usage_limit INTEGER,
    expires_at TEXT
);
");

echo "✅ Database migration completed!\n";
