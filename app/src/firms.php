<?php
require_once __DIR__ . '/db.php';


function list_firms() {
  $st = db()->query("SELECT * FROM firms ORDER BY id DESC");
  return $st->fetchAll(PDO::FETCH_ASSOC);
}


function add_firm($name) {
  $st = db()->prepare("INSERT INTO firms (name) VALUES (?)");
  return $st->execute([$name]);
}


function delete_firm($id) {
  $st = db()->prepare("DELETE FROM firms WHERE id = ?");
  return $st->execute([$id]);
}


function get_firm($id) {
  $st = db()->prepare("SELECT * FROM firms WHERE id = ?");
  $st->execute([$id]);
  return $st->fetch(PDO::FETCH_ASSOC);
}
?>
