<?php
require __DIR__ . '/../../src/db.php';
require __DIR__ . '/../../src/auth.php';
require __DIR__ . '/../../src/firms.php';
require __DIR__.'/../../src/trips.php';


require_login();
$user = current_user();
$pdo = db();

delete_expired_trips($pdo);

if (!$user || $user['role'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

$firms = list_firms();
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <title>Admin Paneli - Yavuzlar Bilet Platformu</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(to bottom, #E6F3FF, #FFFFFF);
      color: #003366;
      text-align: center;
      margin: 0;
    }

    header {
      background: linear-gradient(135deg, #004AAD, #007BFF);
      color: white;
      padding: 20px 0;
      border-bottom: 4px solid #FFD500;
      box-shadow: 0 4px 10px rgba(0,0,0,0.25);
    }

    header h1 {
      margin: 0;
      font-size: 2.2em;
    }

    header p {
      margin: 5px 0 15px;
      font-weight: 500;
    }

    .actions {
      margin: 20px 0;
    }

    .btn {
      background: linear-gradient(135deg, #FF7B00, #FFD500);
      border: none;
      padding: 10px 18px;
      border-radius: 8px;
      color: #003366;
      font-weight: bold;
      text-decoration: none;
      cursor: pointer;
      margin: 0 5px;
      transition: 0.3s;
      box-shadow: 0 3px 6px rgba(0,0,0,0.2);
    }

    .btn:hover {
      background: linear-gradient(135deg, #FFD500, #FFB000);
      transform: scale(1.05);
    }

    table {
      width: 80%;
      margin: 30px auto;
      border-collapse: collapse;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 5px 15px rgba(0,0,0,0.15);
      background: white;
    }

    th {
      background: #004AAD;
      color: white;
      padding: 12px;
      font-size: 1.1em;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    td {
      padding: 12px;
      border-bottom: 1px solid #ddd;
    }

    tr:nth-child(even) {
      background-color: #f8faff;
    }

    tr:hover {
      background-color: #e8f1ff;
      transition: 0.2s;
    }

    .delete {
      color: #a30000;
      font-weight: bold;
      text-decoration: none;
      transition: 0.2s;
    }

    .delete:hover {
      color: red;
      text-decoration: underline;
    }

    .back {
      display: inline-block;
      margin: 30px 0;
      font-weight: bold;
      color: #004AAD;
      text-decoration: none;
      transition: 0.3s;
    }

    .back:hover {
      color: #FF7B00;
      text-decoration: underline;
    }

  </style>
</head>
<body>

<header>
  <h1>Admin Paneli</h1>
  <p>Hoş geldin, <b><?= htmlspecialchars($user['name']) ?></b></p>
</header>

<div class="actions">
  <a href="firm_add.php" class="btn">+ Yeni Firma Ekle</a>
  <a href="assign_firm_admin.php" class="btn">+ Yeni Firma Admin Ata</a>
  <a href="coupons.php" style="text-decoration:none;">
  <button style="
    background: linear-gradient(90deg, #0047AB, #007BFF);
    color:white;
    border:none;
    border-radius:6px;
    padding:10px 16px;
    margin-left:10px;
    font-weight:bold;
    cursor:pointer;">
    🎟 Genel Kupon Yönetimi
  </button>
</a>

</div>

<table>
  <tr>
    <th>ID</th>
    <th>Firma Adı</th>
    <th>İşlem</th>
  </tr>
  <?php foreach ($firms as $f): ?>
  <tr>
    <td><?= $f['id'] ?></td>
    <td><?= htmlspecialchars($f['name']) ?></td>
    <td>
        <a href="firm_edit.php?id=<?= $f['id'] ?>" style="color:#007BFF; text-decoration:none;">✏️ Düzenle</a> | 
    <a href="delete_firm.php?id=<?= $f['id'] ?>" style="color:red; text-decoration:none;">🗑️ Sil</a>

  </tr>
  <?php endforeach; ?>
</table>

<a href="../index.php" class="back">← Ana sayfaya dön</a>

</body>
</html>
