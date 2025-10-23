<?php
require __DIR__.'/../../src/auth.php';
require_login();
require __DIR__.'/../../src/db.php';

if (current_user()['role'] !== 'admin') {
  http_response_code(403);
  exit('Erişim reddedildi.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['name'];
  $email = $_POST['email'];
  $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
  $firm_id = $_POST['firm_id'];

  $st = db()->prepare("INSERT INTO users (name, email, password, role, firm_id) VALUES (?, ?, ?, 'firm_admin', ?)");
  $st->execute([$name, $email, $pass, $firm_id]);
  header("Location: index.php");
  exit;
}

$firms = db()->query("SELECT * FROM firms")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Firma Admin Ata</title></head>
<body>
<h1>Yeni Firma Admin Kullanıcısı Ata</h1>
<form method="post">
  Ad Soyad: <input name="name" required><br>
  E-posta: <input type="email" name="email" required><br>
  Şifre: <input type="password" name="password" required><br>
  Firma: 
  <select name="firm_id" required>
    <option value="">Seçiniz</option>
    <?php foreach($firms as $f): ?>
      <option value="<?=$f['id']?>"><?=$f['name']?></option>
    <?php endforeach; ?>
  </select><br>
  <button>Kaydet</button>
</form>
<p><a href="index.php">← Geri dön</a></p>
</body>
</html>
