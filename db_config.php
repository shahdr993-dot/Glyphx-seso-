<?php
$host = "localhost";
$dbname = "glyphx_db";
$username = "root"; 
$password = ""; // غالباً بيكون فاضي في XAMPP

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("فشل الاتصال: " . $e->getMessage());
}
?>