<?php
// admin/includes/db.php

$host = 'localhost';
$dbname = 'mysite_db';
$user = 'root';
$pass = ''; // XAMPP default password is empty

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>