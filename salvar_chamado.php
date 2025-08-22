<?php
header('Content-Type: application/json; charset=utf-8');

ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once 'config.php';

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

$id_chamado = $stmt->insert_id;

try {
    $notifyUrl = 'https://SEU_DOMINIO/notificacao_teams.php';
    $tokenSeguranca = 'COLOQUE_UM_TOKEN_FORTE_AQUI';

    $post = http_build_query([
        'token'      => $tokenSeguranca,
        'id'         => $id_chamado,
        'titulo'     => $titulo,
        'descricao'  => $descricao,
        'quem_abriu' => $quem_abriu,
    ]);

    $ch = curl_init($notifyUrl);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $post,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 2,
        CURLOPT_TIMEOUT        => 3,
    ]);
    $resp = curl_exec($ch);
    curl_close($ch);
} catch (\Throwable $e) {
    
}

echo json_encode(['ok' => true, 'id' => $id_chamado]);
