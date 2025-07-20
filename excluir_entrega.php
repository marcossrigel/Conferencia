<?php
session_start();
include("config.php");

if (!isset($_POST['id']) || !isset($_SESSION['id_usuario'])) {
    header("Location: visualizar.php");
    exit;
}

$id = intval($_POST['id']);
$id_usuario = $_SESSION['id_usuario'];

$stmt = $conn->prepare("DELETE FROM entregas WHERE id = ? AND id_usuario = ?");
$stmt->bind_param("ii", $id, $id_usuario);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    header("Location: visualizar.php?msg=sucesso");
    exit;
} else {
    header("Location: visualizar.php?msg=erro");
    exit;
}
