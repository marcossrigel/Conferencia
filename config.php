<?php
$dbHost = 'localhost';
$dbUsername = 'root';
$dbPassword = 'root1234';
$dbName = 'conferencia';
$dbPort = 3306; 

$conexao = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName, $dbPort);

if ($conexao->connect_error) {
    die("Erro na conexão: " . $conexao->connect_error);
}
?>
