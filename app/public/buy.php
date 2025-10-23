<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/trips.php';


if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}


if (empty($_SESSION['csrf_token']) || $_SERVER['REQUEST_METHOD'] === 'GET') {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

$pdo = db();


if (empty($_SESSION['user']['id'])) {
    header("Location: login.php");
    exit;
}


$st = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$st->execute([$_SESSION['user']['id']]);
$user = $st->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit;
}


$_SESSION['user'] = $user;


$trip_id = $_GET['id'] ?? null;
if (!$trip_id) exit('Geçersiz sefer.');
$trip = get_trip($pdo, $trip_id);
if (!$trip) exit('Sefer bulunamadı.');


$couponCode = '';
$discount = 0;
$finalPrice = $trip['price'];
$message = '';
$currentCredit = (float)$user['credit'];


function find_valid_coupon(PDO $pdo, string $code, int $firmId) {
    $st = $pdo->prepare("
        SELECT * FROM coupons
        WHERE code = ?
          AND (firm_id IS NULL OR firm_id = ?)
          AND (expires_at IS NULL OR DATE(expires_at) >= DATE('now'))
          AND (usage_limit IS NULL OR used < usage_limit)
        LIMIT 1
    ");
    $st->execute([$code, $firmId]);
    return $st->fetch(PDO::FETCH_ASSOC) ?: null;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        
        header("Location: buy.php?id=" . urlencode($trip_id));
        exit;
    }

    
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    
    if (isset($_POST['check_coupon'])) {
        $couponCode = trim($_POST['coupon_code']);
        if ($couponCode === '') {
            $message = "⚠️ Kupon kodu boş olamaz.";
        } else {
            $coupon = find_valid_coupon($pdo, $couponCode, (int)$trip['firm_id']);
            if ($coupon) {
                $discount = (int)$coupon['percent'];
                $finalPrice = $trip['price'] - ($trip['price'] * $discount / 100);
                $_SESSION['coupon'] = [
                    'code' => $couponCode,
                    'discount' => $discount,
                    'finalPrice' => $finalPrice,
                    'id' => $coupon['id']
                ];
                $message = "✅ Kupon uygulandı: %$discount indirim! Yeni fiyat: {$finalPrice}₺";
            } else {
                unset($_SESSION['coupon']);
                $message = "❌ Geçersiz veya süresi dolmuş kupon kodu.";
            }
        }
    }

    
    if (isset($_POST['buy'])) {
        $seat = (int)$_POST['seat_no'];
        $couponData = $_SESSION['coupon'] ?? null;

        if ($couponData) {
            $couponCode = $couponData['code'];
            $discount = $couponData['discount'];
            $finalPrice = $couponData['finalPrice'];
            $couponId = $couponData['id'];
        } else {
            $couponCode = '';
            $finalPrice = $trip['price'];
        }

        
        $st = $pdo->prepare("SELECT credit FROM users WHERE id = ?");
        $st->execute([$user['id']]);
        $currentCredit = (float)$st->fetchColumn();

        if ($currentCredit < $finalPrice) {
            $message = "💳 Yetersiz bakiye! Hesabınızda {$currentCredit}₺ var, bilet ücreti {$finalPrice}₺.";
        } else {
            try {
                $pdo->beginTransaction();

                
                $pdo->prepare("UPDATE users SET credit = credit - ? WHERE id = ?")
                    ->execute([$finalPrice, $user['id']]);

                
                $pdo->prepare("
                    INSERT INTO tickets(user_id, trip_id, seat_no, price_paid, coupon_code, created_at)
                    VALUES(?,?,?,?,?,datetime('now'))
                ")->execute([$user['id'], $trip_id, $seat, $finalPrice, $couponCode]);

                
                if (!empty($couponId)) {
                    $pdo->prepare("UPDATE coupons SET used = used + 1 WHERE id = ?")
                        ->execute([$couponId]);
                    unset($_SESSION['coupon']);
                }

                $pdo->commit();

                $newCredit = $currentCredit - $finalPrice;
                $_SESSION['user']['credit'] = $newCredit;
                $user['credit'] = $newCredit;

                $message = "🎉 Bilet başarıyla satın alındı! Ödenen tutar: {$finalPrice}₺";
            } catch (Exception $e) {
                $pdo->rollBack();
                $message = "⚠️ İşlem başarısız oldu. " . $e->getMessage();
            }
        }
    }
}
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <title>Bilet Satın Al</title>
  <style>
    body { font-family:'Segoe UI',sans-serif;background:#e8f0ff;text-align:center;margin:0; }
    header { background:linear-gradient(135deg,#007bff,#0056b3);color:white;padding:25px 0; }
    .trip-box { background:white;width:400px;margin:40px auto;border-radius:16px;
      box-shadow:0 3px 12px rgba(0,0,0,0.2);padding:25px; }
    h2 { color:#003366; }
    label { display:block;font-weight:600;margin-top:10px; }
    input, select { width:90%;padding:8px;border-radius:6px;border:1px solid #ccc; }
    button { background:linear-gradient(135deg,#ff9900,#ff6600);border:none;border-radius:8px;
      color:white;padding:10px 25px;font-weight:bold;cursor:pointer;margin-top:15px; }
    button:hover { background:linear-gradient(135deg,#ff6600,#e65c00); }
    .msg { margin-top:15px;font-weight:bold; }
    .success { color:green; }
    .error { color:red; }
  </style>
</head>
<body>
<header>
  <h1>Yavuzlar Bilet Platformu</h1>
  <img src="css/bus.png" width="160" alt="Otobüs">
</header>

<div class="trip-box">
  <h2><?=htmlspecialchars($trip['from_city'])?> → <?=htmlspecialchars($trip['to_city'])?></h2>
  <p><b>Firma:</b> <?=htmlspecialchars($trip['firm_name'])?></p>
  <p><b>Tarih:</b> <?=$trip['date']?> | <b>Saat:</b> <?=$trip['time']?></p>
  <p><b>Fiyat:</b> <?=$trip['price']?>₺</p>
  <p><b>Mevcut Bakiye:</b> <?=htmlspecialchars($user['credit'])?>₺</p>

  <form method="post">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
    <?php

$st = $pdo->prepare("SELECT seat_no FROM tickets WHERE trip_id = ?");
$st->execute([$trip_id]);
$takenSeats = $st->fetchAll(PDO::FETCH_COLUMN);
?>

<label>Koltuk No:</label>
<select name="seat_no" required>
  <?php for ($i = 1; $i <= $trip['seat_count']; $i++): ?>
    <?php if (in_array($i, $takenSeats)): ?>
      <option value="<?=$i?>" disabled><?=$i?> (Dolu)</option>
    <?php else: ?>
      <option value="<?=$i?>"><?=$i?></option>
    <?php endif; ?>
  <?php endfor; ?>
</select>


    <label>Kupon Kodu:</label>
    <input type="text" name="coupon_code" placeholder="Kupon kodunu gir" value="<?=htmlspecialchars($couponCode)?>">

    <button type="submit" name="check_coupon">Kuponu Kontrol Et</button>
    <button type="submit" name="buy">🎟️ Bileti Satın Al</button>
  </form>

  <?php if ($message): ?>
    <div class="msg <?= (strpos($message,'🎉')!==false || strpos($message,'✅')!==false) ? 'success':'error' ?>">
      <?=htmlspecialchars($message)?>
    </div>
  <?php endif; ?>

  <p><a href="index.php">← Ana Sayfaya Dön</a></p>
</div>
</body>
</html>
