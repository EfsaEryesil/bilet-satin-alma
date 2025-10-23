<?php
require_once __DIR__ . '/../../src/auth.php';
require_login();

$user = current_user();
if ($user['role'] !== 'firm_admin') exit("Erişim reddedildi.");

require_once __DIR__ . '/../../src/db.php';
$pdo = db();

$id = $_GET['id'] ?? null;
if (!$id) exit("Kupon bulunamadı.");


$st = $pdo->prepare("SELECT * FROM coupons WHERE id = ? AND firm_id = ?");
$st->execute([$id, $user['firm_id']]);
$coupon = $st->fetch(PDO::FETCH_ASSOC);

if (!$coupon) exit("Kupon bulunamadı.");

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $percent = (int)$_POST['percent'];
    $limit = (int)$_POST['limit'];
    $expires = $_POST['expires_at'];

    if ($percent < 1 || $percent > 100) {
        $message = "⚠️ Geçersiz indirim yüzdesi.";
    } else {
        $st = $pdo->prepare("UPDATE coupons SET percent = ?, usage_limit = ?, expires_at = ? WHERE id = ?");
        $st->execute([$percent, $limit, $expires, $id]);
        $message = "✅ Kupon başarıyla güncellendi!";
        
        $st->execute([$percent, $limit, $expires, $id]);
        $coupon = $st->fetch(PDO::FETCH_ASSOC);
    }
}
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <title>Kupon Düzenle</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(to bottom, #e6f3ff, #cce5ff);
      margin: 0;
      color: #003366;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .card {
      background: #ffffff;
      padding: 35px 40px;
      border-radius: 14px;
      box-shadow: 0 6px 20px rgba(0, 74, 173, 0.15);
      max-width: 420px;
      width: 90%;
      text-align: center;
      border: 2px solid #004AAD20;
    }

    h1 {
      font-size: 1.8em;
      font-weight: bold;
      color: #004AAD;
      margin-bottom: 25px;
      text-shadow: 0 2px 6px rgba(0, 74, 173, 0.1);
    }

    label {
      display: block;
      text-align: left;
      font-weight: 600;
      color: #003366;
      margin-top: 12px;
      margin-bottom: 4px;
    }

    input[type="text"],
    input[type="number"],
    input[type="date"] {
      width: 100%;
      padding: 10px;
      border-radius: 8px;
      border: 1px solid #007BFF;
      font-size: 15px;
      box-sizing: border-box;
      transition: all 0.3s ease;
    }

    input:focus {
      border-color: #FF7B00;
      box-shadow: 0 0 6px rgba(255, 123, 0, 0.6);
      outline: none;
    }

    button {
      width: 100%;
      background: linear-gradient(135deg, #FF7B00, #FFD500);
      color: #003366;
      border: none;
      border-radius: 8px;
      padding: 12px 0;
      font-weight: bold;
      font-size: 16px;
      margin-top: 20px;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 8px rgba(0, 74, 173, 0.25);
    }

    button:hover {
      background: linear-gradient(135deg, #FFD500, #FFB000);
      transform: translateY(-2px);
      box-shadow: 0 6px 12px rgba(0, 74, 173, 0.35);
    }

    a {
      display: inline-block;
      margin-top: 15px;
      text-decoration: none;
      color: #004AAD;
      font-weight: 600;
    }

    a:hover {
      color: #FF7B00;
    }
  </style>
</head>
<body>
  <div class="card">
    <h1>Kupon Düzenle</h1>
    <form method="post">
      <label>İndirim %:</label>
      <input type="number" name="percent" value="<?=htmlspecialchars($coupon['percent'])?>" required>

      <label>Kullanım Limiti:</label>
      <input type="number" name="limit" value="<?=htmlspecialchars($coupon['usage_limit'])?>" required>

      <label>Son Kullanma Tarihi:</label>
      <input type="date" name="expires" value="<?=htmlspecialchars($coupon['expires_at'])?>" required>

      <button type="submit">💾 Kaydet</button>
    </form>
    <a href="coupons.php">← Kupon Listesine Dön</a>
  </div>
</body>
</html>
