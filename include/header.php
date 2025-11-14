<?php
// ถ้าไม่มีระบบ login ให้บังคับ user_id = 1
$user_id = $_SESSION['user_id'] ?? 1;

// เชื่อม DB
require_once 'connect.php';
?>
<?php include('nav.php'); ?>