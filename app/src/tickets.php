<?php
require_once __DIR__.'/db.php';

function buy_ticket($user_id, $trip_id, $seat_no, $coupon_code = null) {
  $pdo = db();
  $trip = $pdo->query("SELECT * FROM trips WHERE id = $trip_id")->fetch(PDO::FETCH_ASSOC);
  if (!$trip) throw new Exception("Sefer bulunamadı!");

  $check = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE trip_id = ? AND seat_no = ? AND status = 'active'");
  $check->execute([$trip_id, $seat_no]);
  if ($check->fetchColumn() > 0) throw new Exception("Koltuk dolu!");

  $price = (int)$trip['price'];

  if ($coupon_code) {
    $c = $pdo->prepare("SELECT * FROM coupons WHERE code = ?");
    $c->execute([$coupon_code]);
    $cp = $c->fetch(PDO::FETCH_ASSOC);
    if ($cp && $cp['used'] < $cp['usage_limit']) {
      $price = (int)round($price * (100 - (int)$cp['percent']) / 100);
      $pdo->prepare("UPDATE coupons SET used = used + 1 WHERE id = ?")->execute([$cp['id']]);
    }
  }

  $u = $pdo->prepare("SELECT credit FROM users WHERE id = ?");
  $u->execute([$user_id]);
  $credit = (int)$u->fetchColumn();
  if ($credit < $price) throw new Exception("Yetersiz kredi!");

  $pdo->beginTransaction();
  $pdo->prepare("UPDATE users SET credit = credit - ? WHERE id = ?")->execute([$price, $user_id]);
  $pdo->prepare("INSERT INTO tickets(user_id, trip_id, seat_no, price_paid, created_at) VALUES(?,?,?,?,datetime('now'))")
      ->execute([$user_id, $trip_id, $seat_no, $price]);
  $pdo->commit();
}

function cancel_ticket($ticket_id, $user_id) {
  $pdo = db();
  $t = $pdo->prepare("SELECT ti.*, tr.date, tr.time FROM tickets ti JOIN trips tr ON tr.id = ti.trip_id WHERE ti.id = ? AND ti.user_id = ?");
  $t->execute([$ticket_id, $user_id]);
  $ti = $t->fetch(PDO::FETCH_ASSOC);
  if (!$ti || $ti['status'] !== 'active') throw new Exception("Bilet bulunamadı!");

  $dt = strtotime($ti['date'].' '.$ti['time']);
  if ($dt - time() < 3600) throw new Exception("Kalkışa 1 saatten az kala iptal edilemez!");

  $pdo->beginTransaction();
  $pdo->prepare("UPDATE tickets SET status='canceled' WHERE id=?")->execute([$ticket_id]);
  $pdo->prepare("UPDATE users SET credit=credit+? WHERE id=?")->execute([$ti['price_paid'], $user_id]);
  $pdo->commit();
}
?>
