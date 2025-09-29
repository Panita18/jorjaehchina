<?php
// Base URL
$base_url = 'http://localhost/jorjaeh_tour';

// Database connection
$db_host = 'localhost';
$db_user = 'root';
$db_pass = 'SciKU@2025';
$db_name = 'jorjaeh_tour';

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// ตรวจสอบการเชื่อมต่อ
if (!$conn) {
    die('Connection failed: ' . mysqli_connect_error());
}

// Session Prefix
define('WP', 'jorjaeh_tour2025');
?>
