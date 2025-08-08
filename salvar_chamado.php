<?php
// salvar_chamado.php
header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', 0);        // não vaze avisos/HTML na resposta
error_reporting(E_ALL);

require_once 'config.php';           // $conn = new mysqli(...)

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'erro' => 'Payload inválido (JSON).']);
    exit;
}

$titulo     = trim($data['titulo']     ?? '');
$descricao  = trim($data['descricao']  ?? '');
$quem_abriu = trim($data['quem_abriu'] ?? '');

if ($titulo === '' || $descricao === '' || $quem_abriu === '') {
    http_response_code(422);
    echo json_encode(['ok' => false, 'erro' => 'Preencha título, descrição e quem abriu.']);
    exit;
}

$stmt = $conn->prepare("
    INSERT INTO chamados (titulo, descricao, quem_abriu, `status`, criado_em, atualizado_em)
    VALUES (?, ?, ?, 'aberto', NOW(), NOW())
");
$stmt->bind_param('sss', $titulo, $descricao, $quem_abriu);
$stmt->execute();

echo json_encode(['ok' => true, 'id' => $stmt->insert_id]);
