<?php
require __DIR__ . '/../src/db.php';
$pdo = db();

$users = $pdo->query("SELECT id, name, email, credit FROM users")->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>Kullanıcı Bakiyeleri</h2>";
echo "<table border='1' cellpadding='6' cellspacing='0'>";
echo "<tr><th>ID</th><th>İsim</th><th>Email</th><th>Bakiye (₺)</th></tr>";
foreach ($users as $u) {
    echo "<tr>
            <td>{$u['id']}</td>
            <td>{$u['name']}</td>
            <td>{$u['email']}</td>
            <td>{$u['credit']}</td>
          </tr>";
}
echo "</table>";
