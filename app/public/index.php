<?php
require_once __DIR__.'/../src/db.php';
require_once __DIR__.'/../src/auth.php';
require_once __DIR__.'/../src/trips.php';


$pdo = db(); 

function normalize_date(?string $s): ?string {
  if (!$s) return null;
  $s = trim($s);
  if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) return $s;
  $s = str_replace(['.', ',', '\\'], '/', $s);
  if (preg_match('/^(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{4})$/', $s, $m)) 
 {
    $a = (int)$m[1]; $b = (int)$m[2]; $y = (int)$m[3];
    if ($a > 12) { $gun = $a; $ay = $b; } else { $ay = $a; $gun = $b; }
    return sprintf('%04d-%02d-%02d', $y, $ay, $gun);
  }
  if (preg_match('/^(\d{4}-\d{2}-\d{2})/', $s, $m)) return $m[1];
  return null;
}

$from = trim($_GET['from'] ?? '');
$to   = trim($_GET['to'] ?? '');
$dateInput = $_GET['date'] ?? '';

if (!empty($date)) {
    $timestamp = strtotime($date);
    if ($timestamp) {
        $date = date('Y-m-d', $timestamp);
    }
}

$date = normalize_date($dateInput);


$trips = list_trips($pdo, $from ?: null, $to ?: null, $date ?: null);
$user  = current_user();
?>

<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <title>Yavuzlar Bilet Platformu</title>
  <link rel="stylesheet" href="css/style.css?v=4">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(to bottom, #e6f3ff, #cce5ff);
      margin: 0;
      color: #003366;
      text-align: center;
    }

  
    .topbar {
      background: #004AAD;
      color: #FFD500;
      padding: 12px 25px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-weight: 500;
    }
    .topbar a {
      color: #fff;
      text-decoration: none;
      margin-left: 10px;
    }
    .topbar a:hover { color: #FFD500; }

    
    header {
      background: linear-gradient(135deg, #004AAD, #007BFF);
      color: white;
      padding: 25px 0 35px;
      border-bottom: 4px solid #FFD500;
      box-shadow: 0 4px 15px rgba(0,0,0,0.25);
    }
    header img {
      width: 220px;
      border-radius: 10px;
      border: 3px solid #FFD500;
      margin-top: 10px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.4);
    }
    header h1 {
      font-size: 2.4em;
      margin: 10px 0;
      text-shadow: 0 2px 5px rgba(0,0,0,0.4);
    }

    
    form {
      width: 90%;
      max-width: 480px;
      margin: 30px auto;
      background: #fff;
      padding: 25px;
      border-radius: 12px;
      box-shadow: 0 6px 15px rgba(0, 74, 173, 0.15);
    }
    input, button {
      width: 100%;
      padding: 10px;
      margin-top: 10px;
      border-radius: 8px;
      border: 1px solid #007BFF;
      font-size: 15px;
      transition: 0.3s;
    }
    input:focus {
      outline: none;
      border-color: #FF7B00;
      box-shadow: 0 0 6px rgba(255, 123, 0, 0.6);
    }
    button {
      background: linear-gradient(135deg, #FF7B00, #FFD500);
      color: #003366;
      border: none;
      font-weight: bold;
      cursor: pointer;
      margin-top: 15px;
    }
    button:hover {
      background: linear-gradient(135deg, #FFD500, #FFB000);
      transform: scale(1.03);
    }

    
    table {
      border-collapse: collapse;
      width: 90%;
      margin: 30px auto;
      background: #ffffff;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    th, td { padding: 12px; text-align: center; }
    th {
      background-color: #004AAD;
      color: #FFD500;
      text-transform: uppercase;
    }
    tr:nth-child(even) { background-color: #f2f6ff; }
    tr:hover { background-color: #d9ecff; }

    a { color: #FF7B00; font-weight: bold; text-decoration: none; }
    a:hover { color: #FFD500; }

    footer {
      text-align: center;
      font-size: 14px;
      padding: 15px;
      color: #003366;
      margin-top: 40px;
    }
  </style>
</head>
<body>


<?php if ($user): ?>
  <div class="topbar">
    <?php if ($user['role'] === 'firm_admin'): ?>
      <span>👤 Hoş geldiniz, <?=htmlspecialchars($user['name'])?> (Firma Yetkilisi)</span>
      <div>
        <a href="firm/index.php">Firma Paneli</a> | <a href="logout.php">Çıkış</a>
      </div>
    <?php elseif ($user['role'] === 'admin'): ?>
      <span>👤 Hoş geldiniz, <?=htmlspecialchars($user['name'])?> (Admin)</span>
      <div>
        <a href="admin/index.php">Admin Paneli</a> | <a href="logout.php">Çıkış</a>
      </div>
    <?php else: ?>
  <span>👤 Hoş geldiniz, <?=htmlspecialchars($user['name'])?></span>
  <div>
    <a href="my.php">Biletlerim</a>  |
    <a href="logout.php">Çıkış</a>
  </div>
<?php endif; ?>

  </div>
<?php endif; ?>

<header>
  <h1>Yavuzlar Bilet Platformu</h1>
  <img src="css/bus.png" alt="Yavuzlar Turizm Otobüsü">
</header>

<h2>Sefer Ara</h2>
<form method="get" class="trip-search">
  <div class="form-row">
  <div class="form-group">
    <label for="from">Kalkış Şehri:</label>
    <select name="from" id="from" required>
      <option value="">Şehir Seç</option>
      <?php
      $cities = ["Adana","Adıyaman","Afyonkarahisar","Ağrı","Amasya","Ankara","Antalya","Artvin","Aydın",
                "Balıkesir","Bilecik","Bingöl","Bitlis","Bolu","Burdur","Bursa","Çanakkale","Çankırı",
                "Çorum","Denizli","Diyarbakır","Edirne","Elazığ","Erzincan","Erzurum","Eskişehir",
                "Gaziantep","Giresun","Gümüşhane","Hakkari","Hatay","Isparta","Mersin","İstanbul",
                "İzmir","Kars","Kastamonu","Kayseri","Kırklareli","Kırşehir","Kocaeli","Konya","Kütahya",
                "Malatya","Manisa","Kahramanmaraş","Mardin","Muğla","Muş","Nevşehir","Niğde","Ordu","Rize",
                "Sakarya","Samsun","Siirt","Sinop","Sivas","Tekirdağ","Tokat","Trabzon","Tunceli","Şanlıurfa",
                "Uşak","Van","Yozgat","Zonguldak","Aksaray","Bayburt","Karaman","Kırıkkale","Batman","Şırnak",
                "Bartın","Ardahan","Iğdır","Yalova","Karabük","Kilis","Osmaniye","Düzce"];
      foreach ($cities as $city): 
        $selected = ($from === $city) ? 'selected' : '';
        echo "<option value=\"$city\" $selected>$city</option>";
      endforeach;
      ?>
    </select>
  </div>

  <div class="form-group">
    <label for="to">Varış Şehri:</label>
    <select name="to" id="to" required>
      <option value="">Şehir Seç</option>
      <?php
      foreach ($cities as $city): 
        $selected = ($to === $city) ? 'selected' : '';
        echo "<option value=\"$city\" $selected>$city</option>";
      endforeach;
      ?>
    </select>
  </div>

  <div class="form-group">
    <label for="date">Tarih:</label>
    <input type="date" name="date" id="date" value="<?=htmlspecialchars($date)?>">
  </div>
  </div>

  <button type="submit">🔍 Ara</button>
</form>



<table>
<tr><th>Firma</th><th>Rota</th><th>Tarih</th><th>Saat</th><th>Fiyat</th><th></th></tr>
<?php foreach($trips as $t): ?>
<tr>
  <td><?=htmlspecialchars($t['firm_name'])?></td>
  <td><?=htmlspecialchars($t['from_city'])?> → <?=htmlspecialchars($t['to_city'])?></td>
  <td><?=$t['date']?></td>
  <td><?=$t['time']?></td>
  <td><?=$t['price']?>₺</td>
  <td><a href="trips.php?id=<?=$t['id']?>">Detay</a></td>
</tr>
<?php endforeach; ?>
</table>

<?php if (!$user): ?>
  <p><a href="login.php">Giriş Yap</a> | <a href="register.php">Kayıt Ol</a></p>
<?php endif; ?>

<footer>© 2025 Yavuzlar Bilet Platformu | Tüm hakları saklıdır.</footer>
</body>
</html>

