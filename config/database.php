<?php
$host     = $_ENV['DB_HOST']     ?? 'mariadb';
$dbname   = $_ENV['DB_NAME']     ?? 'vite-et-gourmand';
$user     = $_ENV['DB_USER']     ?? 'jose';
$password = $_ENV['DB_PASSWORD'] ?? 'admin';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}