<?php
$host = 'localhost';
$dbname = 'cooperativa';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;port=3307;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("SET CHARACTER SET utf8");
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>