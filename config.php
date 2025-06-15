<?php
$host = 'localhost';
$user = 'root';
$pass = 'root1234';  // ajuste se sua senha for diferente
$db = 'conferencia';
$port = 3306;

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}
?>