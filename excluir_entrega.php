<?php
session_start();
include("config.php");

if (!isset($_GET['id']) || !isset($_SESSION['id_usuario'])) {
    header("Location: visualizar.php");
    exit;
}

$id = intval($_GET['id']);
$id_fornecedor = $_SESSION['id_usuario'];

$stmt = $conn->prepare("DELETE FROM entregas WHERE id = ? AND id_fornecedor = ?");
$stmt->bind_param("ii", $id, $id_fornecedor);
$stmt->execute();

header("Location: visualizar.php");
exit;
?>
