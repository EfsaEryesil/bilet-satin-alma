<?php
require __DIR__ . '/../../src/auth.php';
require_login();
require __DIR__ . '/../../src/db.php';

$user = current_user();
if ($user['role'] !== 'admin') {
  http_response_code(403);
  exit('Erişim reddedildi.');
}

$pdo = db();
$msg = '';

/* ✅ Kupon silme işlemi */
if (isset($_GET['del'])) {
  $id = (int)$_GET['del'];
  if ($id > 0) {
    $st = $pdo->prepare("DELETE FROM coupons WHERE id = ?");
    $st->execute([$id]);
  }
  header("Location: coupons.php");
  exit;
}

/* ✅ Kupon ekleme işlemi */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $code = trim($_POST['code']);
  $percent = (int)$_POST['percent'];
  $limit = (int)$_POST['limit'];
  $expires = $_POST['expires'] ?? null;

  if ($code && $percent > 0 && $limit > 0) {
    $check = $pdo->prepare("SELECT COUNT(*) FROM coupons WHERE code = ?");
    $check->execute([$code]);
    if ($check->fetchColumn() > 0) {
      $msg = "⚠️ Bu kupon kodu zaten mevcut!";
    } else {
      $st = $pdo->prepare("INSERT INTO coupons (code, percent, usage_limit, expires_at, firm_id) VALUES (?,?,?,?,NULL)");
      $st->execute([$code, $percent, $limit, $expires]);
      $msg = "✅ Kupon başarıyla eklendi!";
    }
  } else {
    $msg = "⚠️ Tüm alanları doğru doldurun.";
  }
}

/* ✅ Kuponları listele */
$coupons = $pdo->query("SELECT * FROM coupons WHERE firm_id IS NULL ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <title>🎟 Genel Kupon Yönetimi - Admin</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f8faff;
      margin: 0;
      padding: 0;
      color: #222;
      text-align: center;
    }

    header {
      background: linear-gradient(135deg, #007bff, #0056b3);
      color: white;
      padding: 25px 0;
      box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    }

    h1 {
      margin: 0;
      font-size: 28px;
      letter-spacing: 0.5px;
    }

    form {
      margin: 20px auto;
      background: #fff;
      width: 80%;
      max-width: 800px;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.15);
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 10px;
    }

    input {
      padding: 8px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 14px;
    }

    button {
      background: linear-gradient(135deg, #ff9900, #ff6600);
      color: white;
      border: none;
      border-radius: 8px;
      padding: 10px 20px;
      font-weight: bold;
      cursor: pointer;
      transition: 0.2s;
    }

    button:hover { background: linear-gradient(135deg, #ff6600, #e65c00); }

    table {
      border-collapse: collapse;
      width: 80%;
      margin: 20px auto;
      background: white;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 2px 10px rgba(0,0,0,0.15);
    }

    th {
      background: #007bff;
      color: white;
      padding: 10px;
    }

    td {
      padding: 10px;
      border-bottom: 1px solid #eee;
    }

    tr:hover { background: #f1f7ff; }

    .del {
      color: #d9534f;
      font-weight: bold;
      text-decoration: none;
    }

    .del:hover {
      text-decoration: underline;
    }

    .back-link {
      display: inline-block;
      margin: 15px;
      color: #ff6600;
      font-weight: bold;
      text-decoration: none;
    }

    .back-link:hover { text-decoration: underline; }
  </style>
</head>
<body>
  <header>
    <h1>🎟 Genel Kupon Yönetimi - Admin</h1>
  </header>

  <form method="post">
    <input type="text" name="code" placeholder="Kod" required>
    <input type="number" name="percent" placeholder="İndirim %" required min="1" max="100">
    <input type="number" name="limit" placeholder="Kullanım Limiti" required min="1">
    <input type="date" name="expires" required>
    <button type="submit">➕ Ekle</button>
  </form>

  <table>
    <tr>
      <th>Kod</th>
      <th>İndirim %</th>
      <th>Limit</th>
      <th>Kullanılan</th>
      <th>Bitiş Tarihi</th>
      <th>İşlem</th>
    </tr>
    <?php foreach ($coupons as $c): ?>
    <tr>
      <td><?=htmlspecialchars($c['code'])?></td>
      <td><?=$c['percent']?>%</td>
      <td><?=$c['usage_limit']?></td>
      <td><?=$c['used']?></td>
      <td><?=$c['expires_at']?></td>
      <td>
        <a href="edit_coupon.php?id=<?=$c['id']?>" style="color:#007bff;font-weight:bold;text-decoration:none;">✏️ Düzenle</a> |
        <a href="?del=<?=$c['id']?>" class="delete" style="color:red; text-decoration:none;">🗑️ Sil</a>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>

  <a href="index.php" class="back-link">← Admin Paneline Dön</a>
</body>
</html>
