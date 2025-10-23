<?php
require __DIR__ . '/../src/auth.php';
require_login();
require __DIR__ . '/../src/db.php';
require __DIR__ . '/../../vendor/autoload.php';
use Mpdf\Mpdf;


$ticketId = (int)($_GET['id'] ?? 0);
$userId   = (int)(current_user()['id'] ?? 0);
if ($ticketId <= 0) { exit('Geçersiz bilet.'); }


$st = db()->prepare("
  SELECT ti.*, tr.from_city, tr.to_city, tr.date, tr.time, f.name AS firm_name
  FROM tickets ti
  JOIN trips   tr ON tr.id = ti.trip_id
  JOIN firms    f ON f.id = tr.firm_id
  WHERE ti.id = ? AND ti.user_id = ?
");
$st->execute([$ticketId, $userId]);
$T = $st->fetch(PDO::FETCH_ASSOC);
if (!$T) { exit('Bilet bulunamadı.'); }


$fullName = htmlspecialchars(current_user()['name']);
$firm     = htmlspecialchars($T['firm_name']);
$from     = htmlspecialchars($T['from_city']);
$to       = htmlspecialchars($T['to_city']);
$date     = htmlspecialchars($T['date']);
$time     = htmlspecialchars($T['time']);
$seat     = htmlspecialchars((string)$T['seat_no']);
$paid     = number_format((float)$T['price_paid'], 0, ',', '.'); 
$coupon   = htmlspecialchars($T['coupon_code'] ?? '—');


$html = <<<HTML
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<style>
  body{font-family: DejaVu Sans, sans-serif; color:#222;}
  .card{border:1px solid #ddd; border-radius:10px; padding:16px;}
  h1{color:#004AAD; font-size:20px; margin:0 0 10px;}
  .row{margin:6px 0;}
  .badge{background:#e9f2ff; padding:2px 8px; border-radius:6px; font-size:12px; color:#004AAD; display:inline-block}
  .footer{font-size:11px; color:#666; margin-top:15px; border-top:1px solid #eee; padding-top:8px}
</style>
</head>
<body>
  <div class="card">
     <h1>Yavuzlar Bilet Platformu</h1>
    <h2>Yolcu Biletiniz</h2>
    <div class="row"><b>Yolcu:</b> {$fullName}</div>
    <div class="row"><b>Firma:</b> {$firm}</div>
    <div class="row"><b>Rota:</b> {$from} → {$to}</div>
    <div class="row"><b>Tarih / Saat:</b> {$date} &nbsp; {$time}</div>
    <div class="row"><b>Koltuk:</b> {$seat}</div>
    <div class="row"><b>Ödenen Tutar:</b> {$paid}₺</div>
    <div class="footer">
      Bu çıktı e-bilettir; kimlik ibrazı ile geçerlidir. İptal/iadelerde son 1 saat kuralı uygulanır.
      İyi yolculuklar dileriz.
    </div>
  </div>
</body>
</html>
HTML;


$mpdf = new Mpdf(['tempDir' => __DIR__ . '/../tmp']);
$mpdf->WriteHTML($html);
$mpdf->SetTitle("bilet-{$ticketId}.pdf");
$mpdf->Output("bilet-{$ticketId}.pdf", \Mpdf\Output\Destination::INLINE);
