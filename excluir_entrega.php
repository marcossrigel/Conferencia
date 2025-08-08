<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['id_usuario'])) {
    header("Location: visualizar.php?msg=auth");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: visualizar.php?msg=method");
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$id_usuario = (int)$_SESSION['id_usuario'];

if ($id <= 0) {
    header("Location: visualizar.php?msg=erro");
    exit;
}

// Busca a entrega pra checar se é do usuário e pegar a foto
$stmt = $conn->prepare("SELECT id, id_usuario, foto_nome FROM entregas WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$entrega = $res->fetch_assoc();

if (!$entrega || (int)$entrega['id_usuario'] !== $id_usuario) {
    header("Location: visualizar.php?msg=sem_permissao");
    exit;
}

// Apaga a foto (se existir)
if (!empty($entrega['foto_nome'])) {
    $path = __DIR__ . "/uploads/" . $entrega['foto_nome'];
    if (is_file($path)) { @unlink($path); }
}

// Exclui do banco
$del = $conn->prepare("DELETE FROM entregas WHERE id = ? AND id_usuario = ?");
$del->bind_param("ii", $id, $id_usuario);
$del->execute();

if ($del->affected_rows > 0) {
    header("Location: visualizar.php?msg=sucesso");
} else {
    header("Location: visualizar.php?msg=erro");
}
exit;
