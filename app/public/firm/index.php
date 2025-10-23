<?php
require __DIR__.'/../../src/auth.php';
require_login();
require __DIR__.'/../../src/db.php';
require __DIR__.'/../../src/trips.php';

$user = current_user();
if ($user['role'] !== 'firm_admin') {
  http_response_code(403);
  exit('Erişim reddedildi.');
}

$pdo = db();

delete_expired_trips($pdo);
$firm_id = $user['firm_id'];

$st = $pdo->prepare("SELECT * FROM trips WHERE firm_id = ?");
$st->execute([$firm_id]);
$trips = $st->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <title><?=$user['name']?> - Firma Paneli</title>
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
      font-size: 26px;
      font-weight: bold;
      box-shadow: 0 3px 8px rgba(0,0,0,0.25);
    }

    .topbar {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 15px;
      margin-top: 20px;
    }

    .btn {
      background: linear-gradient(135deg, #FFD500, #FF7B00);
      color: #003366;
      border: none;
      border-radius: 8px;
      padding: 10px 20px;
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

    .edit { color: #007BFF; font-weight: bold; text-decoration: none; }
    .delete { color: red; text-decoration: none; font-weight: bold; }

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

<header>👤 <?=$user['name']?> - Firma Paneli</header>

<div class="topbar">
  <a href="trip_new.php" class="btn">➕ Yeni Sefer Ekle</a>
  <a href="coupons.php" class="btn">🎟 Kupon Yönetimi</a>
  <a href="../logout.php" class="btn" style="background:linear-gradient(135deg,#ff4d4d,#cc0000);color:white;">🚪 Çıkış</a>
</div>

<table>
  <tr>
    <th>ID</th>
    <th>Kalkış</th>
    <th>Varış</th>
    <th>Tarih</th>
    <th>Saat</th>
    <th>Fiyat</th>
    <th>Koltuk</th>
    <th>İşlem</th>
  </tr>
  <?php foreach($trips as $t): ?>
  <tr>
    <td><?=$t['id']?></td>
    <td><?=$t['from_city']?></td>
    <td><?=$t['to_city']?></td>
    <td><?=$t['date']?></td>
    <td><?=$t['time']?></td>
    <td><?=$t['price']?>₺</td>
    <td><?=$t['seat_count']?></td>
    <td>
      <a class="edit" href="trip_edit.php?id=<?=$t['id']?>">✏️ Düzenle</a> |
      <a class="delete" href="delete.php?id=<?=$t['id']?>">🗑 Sil</a>
    </td>
  </tr>
  <?php endforeach; ?>
</table>

<a href="../index.php" class="back">← Ana Sayfaya Dön</a>

<footer>© 2025 Yavuzlar Bilet Platformu</footer>

</body>
</html>
