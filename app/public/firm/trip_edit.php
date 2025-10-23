<?php
require __DIR__.'/../../src/auth.php';
require_login();
require __DIR__.'/../../src/db.php';

$user = current_user();
if ($user['role'] !== 'firm_admin') {
  http_response_code(403);
  exit('Erişim reddedildi.');
}

$firm_id = $user['firm_id'];
$id = (int)($_GET['id'] ?? 0);


$st = db()->prepare("SELECT * FROM trips WHERE id=? AND firm_id=?");
$st->execute([$id, $firm_id]);
$trip = $st->fetch(PDO::FETCH_ASSOC);
if (!$trip) {
  exit("❌ Sefer bulunamadı.");
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $from = trim($_POST['from_city']);
  $to = trim($_POST['to_city']);
  $date = $_POST['date'];
  $time = $_POST['time'];
  $price = (int)$_POST['price'];
  $seat = (int)$_POST['seat_count'];

  $up = db()->prepare("UPDATE trips SET from_city=?, to_city=?, date=?, time=?, price=?, seat_count=? WHERE id=? AND firm_id=?");
  $up->execute([$from, $to, $date, $time, $price, $seat, $id, $firm_id]);

  header("Location: index.php?updated=1");
  exit;
}
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <title>Sefer Düzenle</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(to bottom, #007BFF, #E6F3FF);
      margin: 0;
      padding: 0;
      color: #003366;
    }

    header {
      background: linear-gradient(135deg, #004AAD, #007BFF);
      color: white;
      padding: 25px 0;
      text-align: center;
      border-bottom: 4px solid #FFD500;
      box-shadow: 0 3px 10px rgba(0,0,0,0.2);
    }

    header h1 {
      margin: 0;
      font-size: 2em;
      text-shadow: 1px 1px 4px rgba(0,0,0,0.3);
    }

    .container {
      background: white;
      width: 500px;
      margin: 50px auto;
      padding: 30px;
      border-radius: 14px;
      box-shadow: 0 6px 20px rgba(0,0,0,0.2);
    }

    label {
      display: block;
      font-weight: bold;
      margin-top: 15px;
      color: #004AAD;
    }

    input {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      border: 2px solid #007BFF;
      border-radius: 8px;
      font-size: 15px;
      transition: 0.3s;
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
      margin-top: 20px;
      transition: 0.3s;
    }

    button:hover {
      background: linear-gradient(135deg, #FFD500, #FFB000);
      transform: scale(1.05);
    }

    a {
      display: block;
      text-align: center;
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
  <h1>🚌 Sefer Düzenle</h1>
</header>

<div class="container">
  <form method="post">
    <label for="from_city">Kalkış:</label>
    <input type="text" name="from_city" id="from_city" value="<?=htmlspecialchars($trip['from_city'])?>" required>

    <label for="to_city">Varış:</label>
    <input type="text" name="to_city" id="to_city" value="<?=htmlspecialchars($trip['to_city'])?>" required>

    <label for="date">Tarih:</label>
    <input type="date" name="date" id="date" value="<?=$trip['date']?>" required>

    <label for="time">Saat:</label>
    <input type="time" name="time" id="time" value="<?=$trip['time']?>" required>

    <label for="price">Fiyat:</label>
    <input type="number" name="price" id="price" value="<?=$trip['price']?>" min="0" required>

    <label for="seat_count">Koltuk Sayısı:</label>
    <input type="number" name="seat_count" id="seat_count" value="<?=$trip['seat_count']?>" min="1" required>

    <button type="submit">💾 Güncelle</button>
  </form>

  <a href="index.php">← Geri Dön</a>
</div>

</body>
</html>
