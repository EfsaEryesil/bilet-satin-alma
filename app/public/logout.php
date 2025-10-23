<?php
session_start();
session_destroy();
header('Location: /bilet-satin-alma/app/public/index.php');
exit;
?>
