<?php
$host = 'mariadb';
$dbname = 'restaurant';
$user = 'user';
$password = 'password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connexion BDD OK !";
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}