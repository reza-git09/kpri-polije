<?php
session_start();
session_destroy();

// Karena logout.php dan login.php satu folder, langsung panggil namanya
header("Location: login.php"); 
exit();
?>