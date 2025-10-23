<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (!function_exists('db')) {
    function db() {
        static $pdo;
        if ($pdo) return $pdo;

       
        $dbFile = __DIR__ . '/../data/database.sqlite';
        if (!file_exists($dbFile)) {
            die("❌ Veritabanı bulunamadı: $dbFile");
        }

        $pdo = new PDO('sqlite:' . $dbFile);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return $pdo;
    }
}
?>
