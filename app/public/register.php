<?php
require __DIR__.'/../src/db.php';
require __DIR__.'/../src/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';

  if (!$name || !$email || !$password) {
    $error = "Lütfen tüm alanları doldurun.";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "Geçerli bir e-posta adresi girin.";
  } else {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $st = db()->prepare("
      INSERT INTO users(name, email, password, role, credit) 
      VALUES(?, ?, ?, 'user', 20000)
    ");
    try {
      $st->execute([$name, $email, $hash]);
      header("Location: login.php");
      exit;
    } catch (PDOException $e) {
      $error = "Bu e-posta zaten kayıtlı.";
    }
  }
}
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <title>Kayıt Ol - Yavuzlar Bilet Platformu</title>
  <style>
    body {
      margin: 0;
      background: linear-gradient(to bottom, #e6f3ff, #cce5ff);
      font-family: 'Segoe UI', Tahoma, sans-serif;
      color: #003366;
    }

    header {
      background: linear-gradient(135deg, #004AAD, #007BFF);
      color: #fff;
      text-align: center;
      padding: 40px 0;
      border-bottom: 4px solid #FFD500;
      box-shadow: 0 4px 15px rgba(0,0,0,0.25);
    }

    header img {
      width: 220px;
      border: 3px solid #FFD500;
      border-radius: 10px;
      margin-top: 10px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.4);
    }

    .register-box {
      background: #fff;
      max-width: 420px;
      margin: 60px auto;
      padding: 35px;
      border-radius: 15px;
      box-shadow: 0 6px 20px rgba(0, 74, 173, 0.2);
    }

    h2 {
      text-align: center;
      color: #004AAD;
      margin-bottom: 25px;
    }

    label {
      display: block;
      margin-top: 12px;
      font-weight: 600;
      color: #003366;
    }

    input {
      width: 100%;
      padding: 10px;
      margin-top: 6px;
      border: 1px solid #007BFF;
      border-radius: 8px;
      font-size: 15px;
      transition: 0.3s;
    }

    input:focus {
      outline: none;
      border-color: #FF7B00;
      box-shadow: 0 0 6px rgba(255, 123, 0, 0.6);
    }

    button {
      width: 100%;
      margin-top: 20px;
      padding: 10px;
      background: linear-gradient(135deg, #FF7B00, #FFD500);
      color: #003366;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      font-weight: bold;
      cursor: pointer;
      transition: 0.3s;
    }

    button:hover {
      background: linear-gradient(135deg, #FFD500, #FFB000);
      transform: scale(1.03);
    }

    .error {
      color: #cc0000;
      margin-top: 10px;
      text-align: center;
      font-weight: bold;
    }

    p {
      text-align: center;
      margin-top: 20px;
      color: #003366;
    }

    a {
      color: #FF7B00;
      font-weight: bold;
      text-decoration: none;
    }

    a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

<header>
  <h1>Yavuzlar Bilet Platformu</h1>
  <img src="css/bus.png" alt="Otobüs Görseli">
</header>

<div class="register-box">
  <h2>Kayıt Ol</h2>

  <?php if (!empty($error)): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="post">
    <label>Ad Soyad:</label>
    <input type="text" name="name" required>

    <label>Email:</label>
    <input type="email" name="email" required>

    <label>Şifre:</label>
    <input type="password" name="password" required>

    <button type="submit">Kaydol</button>
  </form>

  <p>Zaten hesabım var? <a href="login.php">Giriş Yap</a></p>
</div>

</body>
</html>
