<?php
require __DIR__.'/../../src/auth.php';
require_login();
require __DIR__.'/../../src/db.php';

$user = current_user();
if ($user['role'] !== 'admin') {
  http_response_code(403);
  exit('Erişim reddedildi.');
}

$pdo = db();
$id = (int)($_GET['id'] ?? 0);


$st = $pdo->prepare("SELECT * FROM coupons WHERE id = ?");
$st->execute([$id]);
$coupon = $st->fetch(PDO::FETCH_ASSOC);

if (!$coupon) {
  exit("❌ Kupon bulunamadı.");
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $code = trim($_POST['code']);
  $percent = (int)$_POST['percent'];
  $limit = (int)$_POST['limit'];
  $expires = $_POST['expires'];

  if ($code === '') {
    $message = "⚠️ Kupon kodu boş olamaz.";
  } elseif ($percent < 1 || $percent > 100) {
    $message = "⚠️ İndirim yüzdesi 1-100 arası olmalıdır.";
  } elseif ($limit < 1) {
    $message = "⚠️ Kullanım limiti geçersiz.";
  } else {
    $st = $pdo->prepare("UPDATE coupons SET code=?, percent=?, usage_limit=?, expires_at=? WHERE id=?");
    $st->execute([$code, $percent, $limit, $expires, $id]);
    $message = "✅ Kupon başarıyla güncellendi!";

 
    $st = $pdo->prepare("SELECT * FROM coupons WHERE id=?");
    $st->execute([$id]);
    $coupon = $st->fetch(PDO::FETCH_ASSOC);
  }
}
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <title>Kupon Düzenle - Admin</title>
  <style>
    body {
      font-family:'Segoe UI',sans-serif;
      background:#eaf2ff;
      margin:0;
      text-align:center;
      color:#003366;
    }
    .box {
      background:white;
      width:420px;
      margin:60px auto;
      padding:25px;
      border-radius:12px;
      box-shadow:0 4px 12px rgba(0,0,0,0.15);
    }
    h2 { color:#004AAD; margin-bottom:10px; }
    label { display:block; margin-top:10px; font-weight:bold; }
    input {
      width:85%;
      padding:8px;
      margin-top:5px;
      border-radius:6px;
      border:1px solid #ccc;
    }
    button {
      background:linear-gradient(135deg,#FF7B00,#FFD500);
      border:none;
      border-radius:8px;
      padding:10px 18px;
      color:#003366;
      font-weight:bold;
      cursor:pointer;
      margin-top:15px;
    }
    button:hover { background:linear-gradient(135deg,#FFD500,#FFB000); }
    .msg { margin-top:15px;font-weight:bold; }
  </style>
</head>
<body>

<div class="box">
  <h2>Kupon Düzenle (Admin)</h2>

  <form method="post">
    <label>Kod:</label>
    <input type="text" name="code" value="<?= htmlspecialchars($coupon['code']) ?>" required>

    <label>İndirim %:</label>
    <input type="number" name="percent" value="<?= htmlspecialchars($coupon['percent']) ?>" min="1" max="100" required>

    <label>Kullanım Limiti:</label>
    <input type="number" name="limit" value="<?= htmlspecialchars($coupon['usage_limit']) ?>" min="1" required>

    <label>Bitiş Tarihi:</label>
    <input type="date" name="expires" value="<?= htmlspecialchars($coupon['expires_at']) ?>" required>

    <button type="submit">Kaydet</button>
  </form>

  <?php if ($message): ?>
    <div class="msg"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <p><a href="coupons.php" style="color:#004AAD;font-weight:bold;">← Kupon Listesine Dön</a></p>
</div>

</body>
</html>
