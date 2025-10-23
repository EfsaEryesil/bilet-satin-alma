<?php
require __DIR__.'/../../src/auth.php';
require_login();
require __DIR__.'/../../src/db.php';

if (current_user()['role'] !== 'admin') {
  http_response_code(403);
  exit('Erişim reddedildi.');
}

$id = $_GET['id'] ?? null;
if (!$id) exit('Geçersiz ID.');

$st = db()->prepare("DELETE FROM firms WHERE id=?");
$st->execute([$id]);

header("Location: index.php");
exit;
?>
