<?php
// db.php
$host = "localhost";
$db   = "AXISBD";
$user = "root";
$pass = "root"; // troque se sua senha do MySQL for diferente

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
