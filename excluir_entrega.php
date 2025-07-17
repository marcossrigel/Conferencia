<?php
session_start();
include("config.php");

if (!isset($_GET['id']) || !isset($_SESSION['id_usuario'])) {
    header("Location: visualizar.php");
    exit;
}

$id = intval($_GET['id']);
$id_usuario = $_SESSION['id_usuario'];

$stmt = $conn->prepare("DELETE FROM entregas WHERE id = ? AND id_usuario = ?");
$stmt->bind_param("ii", $id, $id_usuario);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    // Exclusão bem-sucedida
    header("Location: visualizar.php?msg=sucesso");
    exit;
} else {
    // Nenhuma linha foi afetada (id inválido ou tentativa de excluir entrega de outro usuário)
    header("Location: visualizar.php?msg=erro");
    exit;
}
