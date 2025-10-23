
services:
  web:
    build: .
    ports:
      - "8080:80"
    volumes:
      - ./app:/var/www/html
    environment:
      - APP_ENV=local
      - TZ=Europe/Istanbul


FROM php:8.3-apache
RUN a2enmod rewrite \
 && docker-php-ext-install pdo pdo_sqlite

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
RUN chown -R www-data:www-data /var/www/html


{
  "require": {
    "dompdf/dompdf": "^2.0"
  }
}


<?php
declare(strict_types=1);
function db(): PDO {
  static $pdo = null;
  if ($pdo) return $pdo;
  $path = __DIR__ . '/../data/bilet.sqlite';
  if (!is_dir(dirname($path))) mkdir(dirname($path), 0777, true);
  $pdo = new PDO('sqlite:' . $path);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  return $pdo;
}
?>


<?php
require __DIR__.'/vendor/autoload.php';
require __DIR__.'/src/db.php';
$pdo = db();
$sql = [
  
  "CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY, name TEXT, email TEXT UNIQUE, password TEXT, role TEXT CHECK(role in ('user','firm_admin','admin')) DEFAULT 'user', credit INTEGER DEFAULT 2000, firm_id INTEGER)",

  "CREATE TABLE IF NOT EXISTS firms (id INTEGER PRIMARY KEY, name TEXT UNIQUE)",

  "CREATE TABLE IF NOT EXISTS trips (id INTEGER PRIMARY KEY, firm_id INTEGER, from_city TEXT, to_city TEXT, date TEXT, time TEXT, price INTEGER, seat_count INTEGER)",
 
  "CREATE TABLE IF NOT EXISTS tickets (id INTEGER PRIMARY KEY, user_id INTEGER, trip_id INTEGER, seat_no INTEGER, price_paid INTEGER, created_at TEXT, status TEXT CHECK(status in ('active','canceled')) DEFAULT 'active')",
  
  "CREATE TABLE IF NOT EXISTS coupons (id INTEGER PRIMARY KEY, code TEXT UNIQUE, percent INTEGER, usage_limit INTEGER, used INTEGER DEFAULT 0, expires_at TEXT)"
];
foreach ($sql as $s) { $pdo->exec($s); }

$hash = password_hash('123456', PASSWORD_DEFAULT);
$pdo->exec("INSERT OR IGNORE INTO users(id,name,email,password,role) VALUES(1,'Admin','admin@example.com','$hash','admin')");
$pdo->exec("INSERT OR IGNORE INTO firms(id,name) VALUES(1,'Yavuzlar Turizm')");
$pdo->exec("INSERT OR IGNORE INTO users(id,name,email,password,role,firm_id) VALUES(2,'Firma Yetkilisi','firma@example.com','$hash','firm_admin',1)");
$pdo->exec("INSERT OR IGNORE INTO users(id,name,email,password,role,credit) VALUES(3,'Miço','m@m.com','$hash','user',2500)");
$pdo->exec("INSERT OR IGNORE INTO trips(id,firm_id,from_city,to_city,date,time,price,seat_count) VALUES
 (1,1,'Konya','Ankara','2025-10-15','10:30',300,10),
 (2,1,'Ankara','İstanbul','2025-10-16','08:00',600,15)");
echo "✔ Veritabanı hazır"; 
?>


<?php
session_start();
function current_user(){ return $_SESSION['user'] ?? null; }
function require_login(){ if(!current_user()){ header('Location: /login.php'); exit; } }
function require_role($role){ $u=current_user(); if(!$u || $u['role']!==$role){ http_response_code(403); echo '403'; exit; } }
?>


function is_admin(){ return (current_user()['role'] ?? '')==='admin'; }
function is_firm_admin(){ return (current_user()['role'] ?? '')==='firm_admin'; }
?>


<?php
require_once __DIR__.'/db.php';
function list_trips($from=null,$to=null,$date=null){
  $pdo=db();
  $q="SELECT t.*, f.name firm_name FROM trips t JOIN firms f ON f.id=t.firm_id WHERE 1=1";
  $p=[];
  if($from){ $q.=" AND from_city = :f"; $p[':f']=$from; }
  if($to){ $q.=" AND to_city = :t"; $p[':t']=$to; }
  if($date){ $q.=" AND date = :d"; $p[':d']=$date; }
  $st=$pdo->prepare($q); $st->execute($p); return $st->fetchAll(PDO::FETCH_ASSOC);
}
function taken_seats($trip_id){
  $st=db()->prepare("SELECT seat_no FROM tickets WHERE trip_id=? AND status='active'");
  $st->execute([$trip_id]);
  return array_column($st->fetchAll(PDO::FETCH_ASSOC),'seat_no');
}
?>


<?php
require_once __DIR__.'/db.php';
function buy_ticket($user_id,$trip_id,$seat_no,$coupon_code=null){
  $pdo=db();
  $t=$pdo->prepare("SELECT * FROM trips WHERE id=?"); $t->execute([$trip_id]); $trip=$t->fetch(PDO::FETCH_ASSOC);
  if(!$trip) throw new Exception('Sefer yok');

  $st=$pdo->prepare("SELECT COUNT(*) FROM tickets WHERE trip_id=? AND seat_no=? AND status='active'");
  $st->execute([$trip_id,$seat_no]);
  if($st->fetchColumn()>0) throw new Exception('Koltuk dolu');
  $price=(int)$trip['price'];
  if($coupon_code){
    $c=$pdo->prepare("SELECT * FROM coupons WHERE code=?"); $c->execute([$coupon_code]); $cp=$c->fetch(PDO::FETCH_ASSOC);
    if($cp && $cp['used'] < $cp['usage_limit'] && (empty($cp['expires_at']) || $cp['expires_at']>=date('Y-m-d'))){
      $price = (int)round($price * (100 - (int)$cp['percent'])/100);
      $pdo->prepare("UPDATE coupons SET used=used+1 WHERE id=?")->execute([$cp['id']]);
    }
  }
  
  $u=$pdo->prepare("SELECT credit FROM users WHERE id=?"); $u->execute([$user_id]); $credit=(int)$u->fetchColumn();
  if($credit < $price) throw new Exception('Yetersiz kredi');
  $pdo->beginTransaction();
  $pdo->prepare("UPDATE users SET credit=credit-? WHERE id=?")->execute([$price,$user_id]);
  $pdo->prepare("INSERT INTO tickets(user_id,trip_id,seat_no,price_paid,created_at) VALUES(?,?,?,?,datetime('now'))")
      ->execute([$user_id,$trip_id,$seat_no,$price]);
  $pdo->commit();
}
function cancel_ticket($ticket_id,$user_id){
  $pdo=db();
  $t=$pdo->prepare("SELECT ti.*, tr.date, tr.time FROM tickets ti JOIN trips tr ON tr.id=ti.trip_id WHERE ti.id=? AND ti.user_id=?");
  $t->execute([$ticket_id,$user_id]); $ti=$t->fetch(PDO::FETCH_ASSOC);
  if(!$ti || $ti['status']!=='active') throw new Exception('Bilet bulunamadı');
  $dt=strtotime($ti['date'].' '.$ti['time']);
  if($dt - time() < 3600) throw new Exception('Kalkışa 1 saatten az kala iptal edilemez');
  $pdo->beginTransaction();
  $pdo->prepare("UPDATE tickets SET status='canceled' WHERE id=?")->execute([$ticket_id]);
  $pdo->prepare("UPDATE users SET credit=credit+? WHERE id=?")->execute([(int)$ti['price_paid'],$user_id]);
  $pdo->commit();
}
?>


<?php
require_once __DIR__.'/db.php';
function create_coupon($code,$percent,$limit,$expires){
  db()->prepare("INSERT INTO coupons(code,percent,usage_limit,expires_at) VALUES(?,?,?,?)")
     ->execute([$code,$percent,$limit,$expires]);
}
?>


<?php
require __DIR__.'/../src/auth.php';
require __DIR__.'/../src/trips.php';
$from=$_GET['from']??''; $to=$_GET['to']??''; $date=$_GET['date']??'';
$trips=list_trips($from?:null,$to?:null,$date?:null);
?><!doctype html><html><head><meta charset="utf-8"><title>Bilet Platformu</title>
<style>body{font-family:sans-serif;max-width:900px;margin:24px auto}input,select{padding:6px;margin:4px}</style></head>
<body>
<h1>Sefer Ara</h1>
<form method="get">
  Kalkış <input name="from" value="<?=htmlspecialchars($from)?>">
  Varış <input name="to" value="<?=htmlspecialchars($to)?>">
  Tarih <input type="date" name="date" value="<?=htmlspecialchars($date)?>">
  <button>Ara</button>
</form>
<table border="1" cellpadding="6" cellspacing="0">
<tr><th>Firma</th><th>Rota</th><th>Tarih</th><th>Saat</th><th>Fiyat</th><th></th></tr>
<?php foreach($trips as $t): ?>
<tr>
  <td><?=htmlspecialchars($t['firm_name'])?></td>
  <td><?=htmlspecialchars($t['from_city'])?>→<?=htmlspecialchars($t['to_city'])?></td>
  <td><?=$t['date']?></td><td><?=$t['time']?></td><td><?=$t['price']?>₺</td>
  <td><a href="/trips.php?id=<?=$t['id']}">Detay</a></td>
</tr>
<?php endforeach; ?>
</table>
<p>
<?php if(current_user()): ?>
  Merhaba, <?=htmlspecialchars(current_user()['name'])?> — <a href="/my.php">Biletlerim</a> | <a href="/logout.php">Çıkış</a>
  <?php if(is_admin()): ?> | <a href="/admin/">Admin Paneli</a><?php endif; ?>
  <?php if(is_firm_admin()): ?> | <a href="/firm/">Firma Paneli</a><?php endif; ?>
<?php else: ?>
  <a href="/login.php">Giriş Yap</a> | <a href="/register.php">Kayıt Ol</a>
<?php endif; ?>
</p>
</body></html>


<?php
require __DIR__.'/../src/auth.php';
require __DIR__.'/../src/db.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $st=db()->prepare("SELECT * FROM users WHERE email=?"); $st->execute([$_POST['email']]);
  $u=$st->fetch(PDO::FETCH_ASSOC);
  if($u && password_verify($_POST['password'],$u['password'])){ $_SESSION['user']=$u; header('Location: /'); exit; }
  $err='Hatalı bilgiler';
}
?><!doctype html><html><body>
<h1>Giriş</h1>
<?php if(!empty($err)) echo '<p style="color:red">'.$err.'</p>'; ?>
<form method="post">
Email <input name="email"><br>
Şifre <input type="password" name="password"><br>
<button>Giriş</button>
</form>
</body></html>


<?php
require __DIR__.'/../src/db.php';
session_start();
if($_SERVER['REQUEST_METHOD']==='POST'){
  $st=db()->prepare("INSERT INTO users(name,email,password,role,credit) VALUES(?,?,?,?,?)");
  $st->execute([$_POST['name'],$_POST['email'],password_hash($_POST['password'],PASSWORD_DEFAULT),'user',2000]);
  header('Location: /login.php'); exit;
}
?><!doctype html><html><body>
<h1>Kayıt Ol</h1>
<form method="post">
Ad <input name="name"><br>Email <input name="email"><br>
Şifre <input type="password" name="password"><br>
<button>Kaydol</button>
</form>
</body></html>


<?php
require __DIR__.'/../src/auth.php';
require __DIR__.'/../src/db.php';
require __DIR__.'/../src/trips.php';
$id=(int)($_GET['id']??0);
$st=db()->prepare("SELECT * FROM trips WHERE id=?"); $st->execute([$id]); $trip=$st->fetch(PDO::FETCH_ASSOC);
$seats=taken_seats($id);
?><!doctype html><html><body>
<h1>Sefer Detayı</h1>
<p><?=$trip['from_city']?>→<?=$trip['to_city']?> / <?=$trip['date']?> <?=$trip['time']?> — <?=$trip['price']?>₺</p>
<?php if(!current_user()): ?>
  <p><b>Lütfen Giriş Yapın.</b> <a href="/login.php">Giriş</a></p>
<?php else: ?>
  <form method="post" action="/buy.php">
    <input type="hidden" name="trip_id" value="<?=$id?>">
    Koltuk:
    <select name="seat_no">
      <?php for($i=1;$i<=(int)$trip['seat_count'];$i++): ?>
        <option value="<?=$i?>" <?=in_array($i,$seats)?'disabled':''?>><?=$i?> <?=in_array($i,$seats)?'(Dolu)':''?></option>
      <?php endfor; ?>
    </select>
    Kupon <input name="coupon" placeholder="KOD">
    <button>Satın Al</button>
  </form>
<?php endif; ?>
</body></html>


<?php
require __DIR__.'/../src/auth.php'; require_login();
require __DIR__.'/../src/tickets.php';
try{ buy_ticket(current_user()['id'], (int)$_POST['trip_id'], (int)$_POST['seat_no'], $_POST['coupon']??null);
  header('Location: /my.php');
}catch(Throwable $e){ echo 'Hata: '.$e->getMessage(); }
?>


<?php
require __DIR__.'/../src/auth.php'; require_login();
require __DIR__.'/../src/tickets.php';
try{ cancel_ticket((int)$_GET['id'], current_user()['id']); header('Location: /my.php'); }
catch(Throwable $e){ echo 'Hata: '.$e->getMessage(); }
?>


<?php
require __DIR__.'/../src/auth.php'; require_login();
require __DIR__.'/../src/db.php';
require __DIR__.'/../vendor/autoload.php';
use Dompdf\Dompdf;
$uid=current_user()['id'];
if(isset($_GET['pdf'])){
  $id=(int)$_GET['pdf'];
  $st=db()->prepare("SELECT ti.*, tr.from_city, tr.to_city, tr.date, tr.time FROM tickets ti JOIN trips tr ON tr.id=ti.trip_id WHERE ti.id=? AND ti.user_id=?");
  $st->execute([$id,$uid]); $b=$st->fetch(PDO::FETCH_ASSOC);
  $html = "<h1>Bilet</h1><p>{$b['from_city']}→{$b['to_city']} {$b['date']} {$b['time']} Koltuk {$b['seat_no']} Fiyat {$b['price_paid']}₺</p>";
  $dompdf = new Dompdf(); $dompdf->loadHtml($html); $dompdf->render(); $dompdf->stream('bilet.pdf'); exit;
}
$st=db()->prepare("SELECT * FROM tickets WHERE user_id=? ORDER BY created_at DESC"); $st->execute([$uid]); $rows=$st->fetchAll(PDO::FETCH_ASSOC);
?><!doctype html><html><body>
<h1>Biletlerim</h1>
<table border="1" cellpadding="6"><tr><th>ID</th><th>Trip</th><th>Koltuk</th><th>Durum</th><th>PDF</th><th>İptal</th></tr>
<?php foreach($rows as $r): ?>
<tr>
  <td><?=$r['id']?></td>
  <td><?=$r['trip_id']?></td>
  <td><?=$r['seat_no']?></td>
  <td><?=$r['status']?></td>
  <td><a href="?pdf=<?=$r['id']?>">İndir</a></td>
  <td><?php if($r['status']==='active'): ?><a href="/cancel.php?id=<?=$r['id']?>">İptal</a><?php endif; ?></td>
</tr>
<?php endforeach; ?>
</table>
</body></html>


<?php
require __DIR__.'/../../src/auth.php'; require_login(); require_role('admin');
?><!doctype html><html><body>
<h1>Admin Paneli</h1>
<ul>
<li><a href="/admin/firms.php">Firmalar</a></li>
<li><a href="/admin/firm_admins.php">Firma Adminleri</a></li>
<li><a href="/admin/coupons.php">Kuponlar</a></li>
</ul>
</body></html>

