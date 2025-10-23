<?php
require __DIR__.'/../../src/auth.php';
require_login();
require __DIR__.'/../../src/db.php';

if (current_user()['role'] !== 'firm_admin') {
  http_response_code(403);
  exit('Erişim reddedildi.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $firm_id = current_user()['firm_id'];
  $from = $_POST['from'];
  $to = $_POST['to'];
  $date = $_POST['date'];
  $time = $_POST['time'];
  $price = $_POST['price'];
  $seat_count = $_POST['seat_count'];

  $st = db()->prepare("INSERT INTO trips (firm_id, from_city, to_city, date, time, price, seat_count)
                       VALUES (?, ?, ?, ?, ?, ?, ?)");
  $st->execute([$firm_id, $from, $to, $date, $time, $price, $seat_count]);

  header("Location: index.php");
  exit;
}


$cities = [
  "Adana","Adıyaman","Afyonkarahisar","Ağrı","Aksaray","Amasya","Ankara","Antalya",
  "Ardahan","Artvin","Aydın","Balıkesir","Bartın","Batman","Bayburt","Bilecik","Bingöl",
  "Bitlis","Bolu","Burdur","Bursa","Çanakkale","Çankırı","Çorum","Denizli","Diyarbakır",
  "Düzce","Edirne","Elazığ","Erzincan","Erzurum","Eskişehir","Gaziantep","Giresun",
  "Gümüşhane","Hakkari","Hatay","Iğdır","Isparta","İstanbul","İzmir","Kahramanmaraş",
  "Karabük","Karaman","Kars","Kastamonu","Kayseri","Kırıkkale","Kırklareli","Kırşehir",
  "Kilis","Kocaeli","Konya","Kütahya","Malatya","Manisa","Mardin","Mersin","Muğla",
  "Muş","Nevşehir","Niğde","Ordu","Osmaniye","Rize","Sakarya","Samsun","Siirt","Sinop",
  "Sivas","Şanlıurfa","Şırnak","Tekirdağ","Tokat","Trabzon","Tunceli","Uşak","Van",
  "Yalova","Yozgat","Zonguldak"
];
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <title>Yeni Sefer Ekle</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #e8f0ff;
      color: #003366;
      text-align: center;
      margin: 0;
      padding: 0;
    }

    header {
      background: linear-gradient(135deg, #007bff, #0056b3);
      color: white;
      padding: 25px 0;
      font-size: 24px;
      font-weight: bold;
    }

    .form-box {
      background: #fff;
      width: 400px;
      margin: 50px auto;
      border-radius: 16px;
      box-shadow: 0 3px 12px rgba(0,0,0,0.2);
      padding: 30px;
      text-align: left;
    }

    label {
      font-weight: 600;
      display: block;
      margin-top: 10px;
      margin-bottom: 4px;
      color: #003366;
    }

    select, input {
      width: 100%;
      padding: 8px;
      border-radius: 6px;
      border: 1px solid #ccc;
      margin-bottom: 10px;
      font-size: 15px;
    }

    button {
      background: linear-gradient(135deg, #ff9900, #ff6600);
      border: none;
      border-radius: 8px;
      color: white;
      padding: 10px 25px;
      font-weight: bold;
      cursor: pointer;
      margin-top: 15px;
      width: 100%;
    }

    button:hover {
      background: linear-gradient(135deg, #ff6600, #e65c00);
    }

    a.back {
      display: block;
      text-align: center;
      margin-top: 15px;
      color: #ff7b00;
      font-weight: bold;
      text-decoration: none;
    }

    a.back:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <header>🚌 Yeni Sefer Ekle</header>

  <div class="form-box">
    <form method="post">
      <label for="from">Kalkış Şehri:</label>
      <select name="from" id="from" required>
        <option value="">Şehir Seç</option>
        <?php foreach ($cities as $city): ?>
          <option value="<?=$city?>"><?=$city?></option>
        <?php endforeach; ?>
      </select>

      <label for="to">Varış Şehri:</label>
      <select name="to" id="to" required>
        <option value="">Şehir Seç</option>
        <?php foreach ($cities as $city): ?>
          <option value="<?=$city?>"><?=$city?></option>
        <?php endforeach; ?>
      </select>

      <label for="date">Tarih:</label>
      <input type="date" name="date" id="date" required>

      <label for="time">Saat:</label>
      <input type="time" name="time" id="time" required>

      <label for="price">Fiyat (₺):</label>
      <input type="number" name="price" id="price" min="1" required>

      <label for="seat_count">Koltuk Sayısı:</label>
      <input type="number" name="seat_count" id="seat_count" min="1" required>

      <button type="submit">Kaydet</button>
    </form>

    <a class="back" href="index.php">← Geri dön</a>
  </div>
</body>
</html>
