<?php
require __DIR__ . '/../../src/db.php';
require __DIR__ . '/../../src/auth.php';
require __DIR__ . '/../../src/firms.php';

$user = current_user();
if (!$user || $user['role'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

$pdo = db();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = trim($_POST['password'] ?? '');
  $firm_id = $_POST['firm_id'] ?? '';

  if ($name && $email && $password && $firm_id) {
    
    $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $check->execute([$email]);
    $exists = $check->fetchColumn();

    if ($exists > 0) {
      $error = "⚠ Bu e-posta adresi zaten kayıtlı!";
    } else {
      $hash = password_hash($password, PASSWORD_DEFAULT);
      $st = $pdo->prepare("INSERT INTO users (name, email, password, role, firm_id) VALUES (?, ?, ?, 'firm_admin', ?)");
      $st->execute([$name, $email, $hash, $firm_id]);
      $success = true;
    }
  } else {
    $error = "Lütfen tüm alanları doldurun!";
  }
}

$firms = list_firms();
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <title>Firma Admin Ata - Admin Paneli</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(to bottom, #E6F3FF, #FFFFFF);
      text-align: center;
      color: #003366;
      margin: 0;
    }

    header {
      background: linear-gradient(135deg, #004AAD, #007BFF);
      color: white;
      padding: 20px;
      border-bottom: 4px solid #FFD500;
    }

    form {
      width: 400px;
      margin: 40px auto;
      background: white;
      padding: 25px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
      text-align: left;
    }

    label {
      font-weight: bold;
      color: #004AAD;
      display: block;
      margin-top: 10px;
    }

    input, select {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      border-radius: 8px;
      border: 1px solid #ccc;
      font-size: 15px;
    }

    button {
      margin-top: 20px;
      width: 100%;
      background: linear-gradient(135deg, #FF7B00, #FFD500);
      border: none;
      padding: 12px;
      border-radius: 8px;
      color: #003366;
      font-weight: bold;
      cursor: pointer;
      transition: 0.3s;
    }

    button:hover {
      background: linear-gradient(135deg, #FFD500, #FFB000);
      transform: scale(1.05);
    }

    .message {
      font-weight: bold;
      margin-top: 15px;
      color: green;
    }

    .error {
      font-weight: bold;
      margin-top: 15px;
      color: red;
    }

    a {
      display: inline-block;
      margin-top: 20px;
      color: #004AAD;
      font-weight: bold;
      text-decoration: none;
    }

    a:hover {
      text-decoration: underline;
      color: #FF7B00;
    }
  </style>
</head>
<body>

<header>
  <h1>Yeni Firma Admin Ata</h1>
</header>

<?php if (!empty($success)): ?>
  <p class="message">✅ Firma admini başarıyla eklendi!</p>
<?php elseif (!empty($error)): ?>
  <p class="error">⚠ <?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="post">
  <label>Ad Soyad:</label>
  <input type="text" name="name" required>

  <label>Email:</label>
  <input type="email" name="email" required>

  <label>Şifre:</label>
  <input type="password" name="password" required>

  <label>Firma Seç:</label>
  <select name="firm_id" required>
    <option value="">-- Firma Seçin --</option>
    <?php foreach ($firms as $f): ?>
      <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['name']) ?></option>
    <?php endforeach; ?>
  </select>

  <button type="submit">Kaydet</button>
</form>

<a href="index.php">← Admin Paneline Dön</a>

</body>
</html>
