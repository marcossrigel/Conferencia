<?php
$host = 'sql305.infinityfree.com';
$user = 'if0_39208841';
$pass = 'lokUCgRd5REis';
$db   = 'if0_39208841_conferencia';
$port = 3306;

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}
?>
