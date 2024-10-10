<?php
// Database connection
$host = 'localhost';
$db = 'store_inventory';
$user = 'root'; // Change this to your DB user
$pass = '';     // Change this to your DB password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}
?>
