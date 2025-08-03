<?php
// Database connection using PDO

$host = 'localhost';  // update as needed
$dbname = 'fhyath2';
$user = 'fhyath2';
$pass = 'fhyath2';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
