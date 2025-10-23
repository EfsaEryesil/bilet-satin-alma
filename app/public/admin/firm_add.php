<?php
require __DIR__ . '/../../src/db.php';
require __DIR__ . '/../../src/auth.php';
require __DIR__ . '/../../src/firms.php';

$user = current_user();
if (!$user || $user['role'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  if ($name) {
    if (add_firm($name)) {
      $success = "✅ Firma başarıyla eklendi!";
    } else {
      $error = "⚠ Firma eklenirken hata oluştu. (Aynı isimli firma olabilir.)";
    }
  } else {
    $error = "⚠ Lütfen firma adını girin!";
  }
}
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <title>Yeni Firma Ekle - Admin Paneli</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(to bottom, #007BFF, #E6F3FF);
      margin: 0;
      padding: 0;
      color: #003366;
      text-align: center;
    }

    header {
      background: linear-gradient(135deg, #004AAD, #007BFF);
      color: white;
      padding: 25px 0;
      border-bottom: 4px solid #FFD500;
      box-shadow: 0 3px 8px rgba(0,0,0,0.2);
    }

    header h1 {
      font-size: 2em;
      margin: 0;
      text-shadow: 1px 1px 4px rgba(0,0,0,0.2);
    }

    form {
      width: 400px;
      background: white;
      margin: 60px auto;
      padding: 30px;
      border-radius: 14px;
      box-shadow: 0 6px 20px rgba(0,0,0,0.2);
      text-align: left;
      animation: fadeIn 0.6s ease-in-out;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    label {
      display: block;
      font-weight: bold;
      color: #004AAD;
      margin-bottom: 10px;
      font-size: 1.1em;
    }

    input {
      width: 100%;
      padding: 10px;
      border: 2px solid #007BFF;
      border-radius: 8px;
      font-size: 15px;
      transition: 0.3s;
      margin-bottom: 20px;
    }

    input:focus {
      border-color: #FFD500;
      outline: none;
      box-shadow: 0 0 5px #FFD500;
    }

    button {
      width: 100%;
      background: linear-gradient(135deg, #FF7B00, #FFD500);
      color: #003366;
      font-weight: bold;
      border: none;
      border-radius: 8px;
      padding: 12px;
      font-size: 1em;
      cursor: pointer;
      transition: 0.3s;
    }

    button:hover {
      background: linear-gradient(135deg, #FFD500, #FFB000);
      transform: scale(1.05);
    }

    .message, .error {
      margin-top: 15px;
      font-weight: bold;
      text-align: center;
    }

    .message { color: green; }
    .error { color: red; }

    a {
      display: inline-block;
      margin-top: 20px;
      color: #004AAD;
      font-weight: bold;
      text-decoration: none;
    }

    a:hover {
      color: #FF7B00;
      text-decoration: underline;
    }
  </style>
</head>
<body>

<header>
  <h1>Yeni Firma Ekle</h1>
</header>

<form method="post">
  <label for="name">Firma Adı:</label>
  <input type="text" name="name" id="name" placeholder="Örn: Kamil Koç, Pamukkale, Metro..." required>
  <button type="submit">Kaydet</button>

  <?php if (!empty($success)): ?>
    <p class="message"><?= $success ?></p>
  <?php elseif (!empty($error)): ?>
    <p class="error"><?= $error ?></p>
  <?php endif; ?>

  <a href="index.php">← Admin Paneline Dön</a>
</form>

</body>
</html>
