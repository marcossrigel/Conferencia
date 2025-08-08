<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';

$id = intval($_POST['id'] ?? 0);
if ($id <= 0) {
  echo json_encode(['ok'=>false,'erro'=>'ID inválido']);
  exit;
}

// Mostra qual DB (debug)
$dbRes = $conn->query("SELECT DATABASE() AS db");
$dbRow = $dbRes ? $dbRes->fetch_assoc() : ['db'=>null];
$dbName = $dbRow['db'] ?? '(desconhecido)';

$stmt = $conn->prepare("UPDATE chamados SET status='finalizado', atualizado_em=NOW() WHERE id=?");
$stmt->bind_param("i", $id);
$ok = $stmt->execute();
$mudou = $stmt->affected_rows;

// Lê de volta sem get_result()
$statusAtual = null;
$check = $conn->prepare("SELECT status FROM chamados WHERE id=?");
$check->bind_param("i", $id);
$check->execute();
$check->bind_result($statusAtual);
$check->fetch();
$check->close();

echo json_encode([
  'ok' => (bool)$ok,
  'linhas_afetadas' => $mudou,
  'status_atual' => $statusAtual,
  'db' => $dbName
]);
