<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'conferencia_entregas';
$port = 3306;

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}
?>