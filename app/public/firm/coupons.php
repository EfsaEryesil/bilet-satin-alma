<?php
require __DIR__.'/../../src/auth.php';
require_login();
require __DIR__.'/../../src/db.php';

$user = current_user();
if ($user['role'] !== 'firm_admin') {
  http_response_code(403);
  exit('Erişim reddedildi.');
}

$pdo = db();
$firm_id = $user['firm_id'];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $code = trim($_POST['code']);
  $percent = (int)$_POST['percent'];
  $limit = (int)$_POST['limit'];
  $expires = $_POST['expires'];

  if ($code && $percent > 0 && $limit > 0) {

    $check = $pdo->prepare("SELECT COUNT(*) FROM coupons WHERE code = ?");
    $check->execute([$code]);
    $exists = $check->fetchColumn();

    if ($exists > 0) {
      echo "<script>alert('❌ Bu kupon kodu zaten mevcut! Lütfen farklı bir kod girin.');</script>";
    } else {
     
      $st = $pdo->prepare("INSERT INTO coupons(code, percent, usage_limit, expires_at, firm_id) VALUES (?,?,?,?,?)");
      $st->execute([$code, $percent, $limit, $expires, $firm_id]);
      echo "<script>alert('✅ Kupon başarıyla eklendi!');</script>";
    }
  }
}


if (isset($_GET['del'])) {
  $id = (int)$_GET['del'];
  $pdo->prepare("DELETE FROM coupons WHERE id=? AND firm_id=?")->execute([$id, $firm_id]);
  header("Location: coupons.php");
  exit;
}


$st = $pdo->prepare("SELECT * FROM coupons WHERE firm_id = ?");
$st->execute([$firm_id]);
$coupons = $st->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <title>Kupon Yönetimi - Firma Paneli</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #e9f3ff;
      margin: 0;
      padding: 0;
      color: #002b5c;
      text-align: center;
    }

    header {
      background: linear-gradient(135deg, #004AAD, #007BFF);
      color: #fff;
      padding: 25px 0;
      font-size: 24px;
      font-weight: bold;
      box-shadow: 0 3px 8px rgba(0,0,0,0.25);
    }

    .topbar {
      display: flex;
      justify-content: center;
      align-items: center;
      margin-top: 20px;
      gap: 10px;
    }

    .btn {
      background: linear-gradient(135deg, #FFD500, #FF7B00);
      color: #003366;
      border: none;
      border-radius: 8px;
      padding: 10px 18px;
      font-weight: bold;
      cursor: pointer;
      transition: 0.3s;
    }

    .btn:hover {
      transform: scale(1.05);
      background: linear-gradient(135deg, #FFB000, #FF7B00);
    }

    table {
      border-collapse: collapse;
      width: 85%;
      margin: 30px auto;
      background: #fff;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    th {
      background: #004AAD;
      color: #FFD500;
      padding: 12px;
    }

    td {
      padding: 10px;
      border-bottom: 1px solid #eee;
    }

    tr:nth-child(even) { background: #f7faff; }
    tr:hover { background: #e9f2ff; }

    .delete {
      color: red;
      text-decoration: none;
      font-weight: bold;
    }

    form {
      background: #fff;
      width: 85%;
      margin: 20px auto;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      display: flex;
      justify-content: center;
      gap: 10px;
      flex-wrap: wrap;
    }

    input {
      padding: 8px;
      border: 1px solid #ccc;
      border-radius: 6px;
      width: 160px;
    }

    footer {
      margin-top: 30px;
      font-size: 14px;
      color: #444;
    }

    a.back {
      display: inline-block;
      margin-top: 15px;
      color: #004AAD;
      font-weight: bold;
      text-decoration: none;
    }
    a.back:hover { text-decoration: underline; }
  </style>
</head>
<body>

<header>🎟 Kupon Yönetimi - <?=htmlspecialchars($user['firm_name'] ?? 'Firmanız')?></header>

<div class="topbar">
  <a href="index.php" class="btn">← Firma Paneline Dön</a>
</div>

<form id="addForm" method="post">
  <input type="text" name="code" placeholder="Kod" required>
  <input type="number" name="percent" placeholder="İndirim %" required min="1" max="100">
  <input type="number" name="limit" placeholder="Kullanım Limiti" required min="1">
  <input type="date" name="expires" required>
  <button class="btn" type="submit">Kaydet</button>
</form>

<table>
  <tr>
    <th>ID</th>
    <th>Kod</th>
    <th>İndirim %</th>
    <th>Limit</th>
    <th>Kullanılan</th>
    <th>Son Tarih</th>
    <th>İşlem</th>
  </tr>
  <?php foreach ($coupons as $c): ?>
  <tr>
    <td><?=$c['id']?></td>
    <td><?=htmlspecialchars($c['code'])?></td>
    <td><?=$c['percent']?>%</td>
    <td><?=$c['usage_limit']?></td>
    <td><?=$c['used']?></td>
    <td><?=$c['expires_at']?></td>
    <td>
  <a href="edit_coupon.php?id=<?=$c['id']?>" style="color:#007bff;font-weight:bold;text-decoration:none;">Düzenle</a> |
  <a href="?del=<?=$c['id']?>" class="delete">Sil</a>
</td>

  </tr>
  <?php endforeach; ?>
</table>

<footer>© 2025 Yavuzlar Bilet Platformu</footer>
</body>
</html>
