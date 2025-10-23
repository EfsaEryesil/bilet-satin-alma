<?php
require __DIR__.'/../src/auth.php';
require_login();
require_once __DIR__.'/../src/db.php';

$user = current_user();
$id = (int)($_GET['id'] ?? 0);
$pdo = db();
$message = '';
$color = '#28a745'; 

$st = $pdo->prepare("SELECT * FROM tickets WHERE id = ? AND user_id = ?");
$st->execute([$id, $user['id']]);
$ticket = $st->fetch(PDO::FETCH_ASSOC);

if (!$ticket) exit('❌ Bilet bulunamadı.');

$st2 = $pdo->prepare("SELECT date, time FROM trips WHERE id = ?");
$st2->execute([$ticket['trip_id']]);
$trip = $st2->fetch(PDO::FETCH_ASSOC);

$tripDateTime = strtotime($trip['date'] . ' ' . $trip['time']);


if ($tripDateTime - time() < 3600) {
    $message = "❌ Seferin kalkışına 1 saatten az kaldığı için iptal edilemez.";
    $color = "#dc3545"; 
} else {
    if ($ticket['status'] === 'active') {
        $pdo->beginTransaction();
        try {
            
            $pdo->prepare("UPDATE tickets SET status = 'cancelled' WHERE id = ?")
                ->execute([$id]);

            
            $refund = (float)$ticket['price_paid'];
            $pdo->prepare("UPDATE users SET credit = credit + ? WHERE id = ?")
                ->execute([$refund, $user['id']]);

            $pdo->commit();
            $message = "✅ Bilet iptal edildi. {$refund}₺ iade hesabınıza yatırıldı.";
        } catch (Exception $e) {
            $pdo->rollBack();
            $message = "⚠️ İşlem başarısız oldu: " . $e->getMessage();
            $color = "#dc3545";
        }
    } else {
        $message = "⚠️ Bu bilet zaten iptal edilmiş veya kullanılmış.";
        $color = "#ffc107"; 
    }
}
?>
<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<title>Bilet İptali</title>
<style>
body {
    font-family: 'Segoe UI', sans-serif;
    background: linear-gradient(180deg, #007BFF, #FFD700);
    color: #333;
    text-align: center;
    margin: 0;
    padding: 0;
}
.container {
    background: #fff;
    border-radius: 12px;
    max-width: 500px;
    margin: 100px auto;
    padding: 40px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}
h1 {
    color: #0047AB;
    margin-bottom: 15px;
}
.message {
    background: <?= $color ?>20;
    border-left: 6px solid <?= $color ?>;
    padding: 15px;
    font-size: 1.1em;
    border-radius: 6px;
    margin-bottom: 25px;
}
a.button {
    display: inline-block;
    background: #0047AB;
    color: white;
    text-decoration: none;
    padding: 10px 18px;
    border-radius: 6px;
    font-weight: bold;
    transition: background 0.3s;
}
a.button:hover {
    background: #002f6c;
}
</style>
</head>
<body>
  <div class="container">
    <h1>🎫 Bilet İptal İşlemi</h1>
    <div class="message"><?= $message ?></div>
    <a href="my.php" class="button">← Biletlerime Dön</a>
  </div>
</body>
</html>

