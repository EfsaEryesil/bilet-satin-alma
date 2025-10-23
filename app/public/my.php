<?php
require __DIR__.'/../src/db.php';
require __DIR__.'/../src/auth.php';
require_login();

$user = current_user();
$pdo = db();

$st = $pdo->prepare("
  SELECT t.id AS ticket_id, tr.from_city, tr.to_city, tr.date, tr.time, t.price_paid, t.seat_no, t.status
  FROM tickets t
  JOIN trips tr ON t.trip_id = tr.id
  WHERE t.user_id = ?
  ORDER BY t.id DESC
");
$st->execute([$user['id']]);
$tickets = $st->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <title>Biletlerim - Yavuzlar Bilet Platformu</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #e8f0ff;
      margin: 0;
      text-align: center;
    }
    header {
      background: linear-gradient(135deg, #007bff, #0056b3);
      color: white;
      padding: 25px 0;
    }
    h1 { margin: 0; }

    .top-buttons {
      margin-top: 15px;
    }
    .btn-balance {
      background: linear-gradient(135deg, #FFD500, #FF9900);
      padding: 10px 18px;
      border-radius: 8px;
      color: #003366;
      font-weight: bold;
      text-decoration: none;
      margin: 5px;
      display: inline-block;
      transition: 0.3s;
    }
    .btn-balance:hover {
      transform: scale(1.05);
      background: linear-gradient(135deg, #FF9900, #FFD500);
    }

    table {
      width: 90%;
      margin: 30px auto;
      border-collapse: collapse;
      background: white;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    }
    th {
      background: #0056b3;
      color: white;
      padding: 12px;
      font-size: 15px;
    }
    td {
      padding: 10px;
      border-bottom: 1px solid #ddd;
    }
    tr:hover { background: #f2f8ff; }
    .status-active {
      color: #28a745;
      font-weight: bold;
    }
    .status-cancelled {
      color: #d9534f;
      font-weight: bold;
    }
    .btn {
      padding: 6px 12px;
      border-radius: 6px;
      font-weight: bold;
      text-decoration: none;
    }
    .btn-pdf {
      background: #ff9900;
      color: white;
    }
    .btn-cancel {
      background: #007bff;
      color: white;
    }
    .btn:hover {
      opacity: 0.85;
    }

    a.return {
      display: inline-block;
      margin: 20px 0;
      color: #004AAD;
      text-decoration: none;
      font-weight: bold;
    }
    a.return:hover { text-decoration: underline; }
  </style>
</head>
<body>

<header>
  <h1>Yavuzlar Bilet Platformu</h1>
  <img src="css/bus.png" width="150" alt="Yavuzlar Turizm Otobüsü">
</header>



<h2>Biletlerim</h2>

<?php if (count($tickets) > 0): ?>
<table>
  <tr>
    <th>SEFER</th>
    <th>KOLTUK</th>
    <th>TARİH</th>
    <th>SAAT</th>
    <th>FİYAT</th>
    <th>DURUM</th>
    <th>PDF</th>
    <th>İPTAL</th>
  </tr>
  <?php foreach($tickets as $t): ?>
    <tr>
      <td><?=htmlspecialchars($t['from_city'])?> → <?=htmlspecialchars($t['to_city'])?></td>
      <td><?=$t['seat_no']?></td>
      <td><?=$t['date']?></td>
      <td><?=$t['time']?></td>
      <td><?=$t['price_paid']?>₺</td>
      <td>
        <?php if ($t['status'] === 'active'): ?>
          <span class="status-active">Aktif</span>
        <?php else: ?>
          <span class="status-cancelled">İptal</span>
        <?php endif; ?>
      </td>
      <td><a class="btn btn-pdf" href="ticket_pdf.php?id=<?=$t['ticket_id']?>">İndir</a></td>
      <td>
        <?php if ($t['status'] === 'active'): ?>
          <a class="btn btn-cancel" href="cancel_ticket.php?id=<?=$t['ticket_id']?>" onclick="return confirm('Bu bileti iptal etmek istediğine emin misin?')">İptal</a>
        <?php else: ?>
          -
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
</table>
<?php else: ?>
  <p>Henüz bir bilet satın almadınız.</p>
<?php endif; ?>

<a class="return" href="index.php">← Ana Sayfaya Dön</a>

</body>
</html>
