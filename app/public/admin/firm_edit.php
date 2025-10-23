<?php
require __DIR__.'/../../src/auth.php';
require_login();
require __DIR__.'/../../src/db.php';

if (current_user()['role'] !== 'admin') {
  http_response_code(403);
  exit('Erişim reddedildi.');
}

$id = $_GET['id'] ?? null;
if (!$id) exit('Geçersiz ID.');


$pdo = db();
$st = $pdo->prepare("SELECT * FROM firms WHERE id=?");
$st->execute([$id]);
$firma = $st->fetch(PDO::FETCH_ASSOC);
if (!$firma) exit('Firma bulunamadı.');


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['name'] ?? '';
  $st = $pdo->prepare("UPDATE firms SET name=? WHERE id=?");
  $st->execute([$name, $id]);
  header("Location: index.php");
  exit;
}
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <title>Firma Düzenle</title>
  <link rel="stylesheet" href="../css/style.css">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #e8f0ff;
      color: #003366;
      text-align: center;
    }
    .form-box {
      background: #fff;
      width: 400px;
      margin: 60px auto;
      border-radius: 12px;
      padding: 25px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    input, button {
      width: 90%;
      padding: 10px;
      margin: 8px 0;
      border-radius: 8px;
      border: 1px solid #007BFF;
      font-size: 15px;
    }
    button {
      background: linear-gradient(135deg, #FF7B00, #FFD500);
      border: none;
      color: #003366;
      font-weight: bold;
      cursor: pointer;
    }
    button:hover {
      background: linear-gradient(135deg, #FFD500, #FFB000);
    }
  </style>
</head>
<body>
  <div class="form-box">
    <h2>Firma Düzenle</h2>
    <form method="post">
      <input type="text" name="name" value="<?=htmlspecialchars($firma['name'])?>" required><br>
      <button type="submit">Kaydet</button>
    </form>
    <a href="index.php" style="color:#007BFF; text-decoration:none;">← Geri dön</a>
  </div>
</body>
</html>
