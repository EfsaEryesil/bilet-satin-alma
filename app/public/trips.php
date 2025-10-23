<?php
require_once __DIR__.'/../src/db.php';

require __DIR__.'/../src/auth.php';
require __DIR__.'/../src/trips.php';

$pdo = db(); 

$id = $_GET['id'] ?? null;
$trip = $id ? get_trip($pdo, $id) : null;
if (!$trip) { echo "Sefer bulunamadı."; exit; }

$user = current_user();
?>

<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <title>Sefer Detayı - Yavuzlar Bilet Platformu</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(to bottom, #e6f3ff, #cce5ff);
      margin: 0;
      color: #003366;
      text-align: center;
    }

    header {
      background: linear-gradient(135deg, #004AAD, #007BFF);
      color: white;
      padding: 25px 0 35px;
      border-bottom: 4px solid #FFD500;
      box-shadow: 0 4px 15px rgba(0,0,0,0.25);
    }

    header h1 {
      font-size: 2.3em;
      margin: 10px 0;
      text-shadow: 0 2px 5px rgba(0,0,0,0.4);
    }

    header img {
      width: 220px;
      border: 3px solid #FFD500;
      border-radius: 10px;
      margin-top: 10px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.4);
    }

 
    h2 {
      text-align: center;
      color: #004AAD;
      margin-top: 40px;
      font-size: 1.8em;
    }

    
    .trip-card {
      background: #fff;
      width: 420px;
      margin: 40px auto;
      border-radius: 15px;
      box-shadow: 0 6px 15px rgba(0,0,0,0.15);
      padding: 25px 35px;
    }

    .trip-card h3 {
      color: #800020;
      font-size: 1.7em;
      margin-bottom: 20px;
    }

    .trip-card p {
      font-size: 1em;
      color: #003366;
      line-height: 1.6;
      margin: 8px 0;
    }

    .price {
      font-size: 1.3em;
      font-weight: bold;
      color: #800020;
      margin-top: 15px;
    }

   
    .btn {
      display: inline-block;
      background: linear-gradient(135deg, #FF7B00, #FFD500);
      color: #003366;
      padding: 10px 20px;
      border-radius: 8px;
      font-weight: bold;
      text-decoration: none;
      margin-top: 15px;
      transition: 0.3s;
    }

    .btn:hover {
      background: linear-gradient(135deg, #FFD500, #FFB000);
      transform: scale(1.05);
    }

    .back {
      display: inline-block;
      margin-top: 40px;
      font-weight: bold;
      text-decoration: none;
      color: #004AAD;
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
  <h1>Yavuzlar Bilet Platformu</h1>
  <img src="css/bus.png" alt="Yavuzlar Turizm Otobüsü">
</header>

<h2>Sefer Detayı</h2>

<div class="trip-card">
  <h3><?=htmlspecialchars($trip['from_city'])?> → <?=htmlspecialchars($trip['to_city'])?></h3>
  <p><b>Firma:</b> <?=htmlspecialchars($trip['firm_name'])?></p>
  <p><b>Tarih:</b> <?=$trip['date']?></p>
  <p><b>Saat:</b> <?=$trip['time']?></p>
  <p><b>Koltuk Sayısı:</b> <?=$trip['seat_count']?></p>
  <p class="price">Fiyat: <?=$trip['price']?>₺</p>

  <?php if ($user): ?>
    <a href="buy.php?id=<?=$trip['id']?>" class="btn">🚌 Bilet Satın Al</a>
  <?php else: ?>
   <a href="#" class="btn" onclick="alert('Lütfen giriş yapın!'); window.location.href='login.php'; return false;">
 Satın Al
</a>

  <?php endif; ?>
</div>

<a href="index.php" class="back">← Ana Sayfaya Dön</a>

</body>
</html>
