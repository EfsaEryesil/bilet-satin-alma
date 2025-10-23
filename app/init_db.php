<?php
require __DIR__.'/src/db.php';
$pdo = db();
$sql = [
  "CREATE TABLE IF NOT EXISTS users (
      id INTEGER PRIMARY KEY,
      name TEXT,
      email TEXT UNIQUE,
      password TEXT,
      role TEXT,
      credit INTEGER DEFAULT 2000,
      firm_id INTEGER
  )",
  "CREATE TABLE IF NOT EXISTS firms (
      id INTEGER PRIMARY KEY,
      name TEXT UNIQUE
  )",
  "CREATE TABLE IF NOT EXISTS trips (
      id INTEGER PRIMARY KEY,
      firm_id INTEGER,
      from_city TEXT,
      to_city TEXT,
      date TEXT,
      time TEXT,
      price INTEGER,
      seat_count INTEGER
  )",
  "CREATE TABLE IF NOT EXISTS tickets (
      id INTEGER PRIMARY KEY,
      user_id INTEGER,
      trip_id INTEGER,
      seat_no INTEGER,
      price_paid INTEGER,
      coupon_code TEXT,   
      created_at TEXT,
      status TEXT DEFAULT 'active'
  )",
  "CREATE TABLE IF NOT EXISTS coupons (
      id INTEGER PRIMARY KEY,
      code TEXT UNIQUE,
      percent INTEGER,
      usage_limit INTEGER,
      used INTEGER DEFAULT 0,
      expires_at TEXT,
      firm_id INTEGER
  )"
  
];
foreach ($sql as $s) { $pdo->exec($s); }

$hash = password_hash('123456', PASSWORD_DEFAULT);
$pdo->exec("INSERT OR IGNORE INTO users(id,name,email,password,role) VALUES(1,'Admin','admin@example.com','$hash','admin')");
$pdo->exec("INSERT OR IGNORE INTO firms(id,name) VALUES(1,'Yavuzlar Turizm')");
$pdo->exec("INSERT OR IGNORE INTO users(id,name,email,password,role,firm_id) VALUES(2,'Firma Yetkilisi','firma@example.com','$hash','firm_admin',1)");
$pdo->exec("INSERT OR IGNORE INTO users(id,name,email,password,role,credit) VALUES(3,'Miço','m@m.com','$hash','user',2500)");
$pdo->exec("INSERT OR IGNORE INTO trips(id,firm_id,from_city,to_city,date,time,price,seat_count) VALUES
 (1,1,'Konya','Ankara','2025-10-15','10:30',300,10),
 (2,1,'Ankara','İstanbul','2025-10-16','08:00',600,15)");
echo "✔ Veritabanı hazır";
?>

