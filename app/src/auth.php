<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';


function current_user() {
    if (!isset($_SESSION['user'])) return null;

    $pdo = db();
    $userId = $_SESSION['user']['id'];

    
    $st = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $st->execute([$userId]);
    $freshUser = $st->fetch(PDO::FETCH_ASSOC);

    if ($freshUser) {
        $_SESSION['user'] = $freshUser;
        return $freshUser;
    }

    return null;
}


function require_login() {
    if (!isset($_SESSION['user'])) {
        header('Location: login.php');
        exit;
    }
}


function login($email, $password) {
    $pdo = db();
    $st = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $st->execute([$email]);
    $user = $st->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;
        return true;
    }
    return false;
}


function logout() {
    unset($_SESSION['user']);
    session_destroy();
    header("Location: /");  
    exit;
}



