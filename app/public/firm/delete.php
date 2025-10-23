<?php
require __DIR__.'/../../src/auth.php';
require_login();
require __DIR__.'/../../src/db.php';


if (current_user()['role'] !== 'firm_admin') {
  http_response_code(403);
  exit('Erişim reddedildi.');
}

$id = $_GET['id'] ?? null;
if (!$id) exit('Geçersiz ID.');


$st = db()->prepare("SELECT * FROM trips WHERE id=? AND firm_id=?");
$st->execute([$id, current_user()['firm_id']]);
$trip = $st->fetch(PDO::FETCH_ASSOC);

if (!$trip) {
  exit('Bu sefer size ait değil veya bulunamadı.');
}


$del = db()->prepare("DELETE FROM trips WHERE id=? AND firm_id=?");
$del->execute([$id, current_user()['firm_id']]);

header("Location: index.php");
exit;
?>
