<?php
header('Content-Type: application/json; charset=utf-8');

$TOKEN_ESPERADO = 'COLOQUE_UM_TOKEN_FORTE_AQUI';
$token = $_POST['token'] ?? ($_GET['token'] ?? '');
if ($TOKEN_ESPERADO && $token !== $TOKEN_ESPERADO) {
  http_response_code(401);
  echo json_encode(['ok'=>false,'erro'=>'unauthorized']);
  exit;
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);
if (!is_array($payload)) {
  $payload = $_POST;
}

$titulo     = trim($payload['titulo']     ?? '');
$descricao  = trim($payload['descricao']  ?? '');
$quem_abriu = trim($payload['quem_abriu'] ?? '');
$id_chamado = trim($payload['id']         ?? '');

if ($titulo === '' || $descricao === '' || $quem_abriu === '') {
  http_response_code(400);
  echo json_encode(['ok'=>false,'erro'=>'campos obrigatórios ausentes']);
  exit;
}

$WEBHOOK_URL = 'https://outlook.office.com/webhook/COLE_AQUI';

$card = [
  "@type"    => "MessageCard",
  "@context" => "http://schema.org/extensions",
  "summary"  => "Novo chamado criado",
  "themeColor" => "0078D7",
  "title"    => "📢 Novo Chamado Criado",
  "sections" => [[
    "activityTitle"    => ($id_chamado ? "Chamado #$id_chamado — " : "") . $titulo,
    "activitySubtitle" => "Aberto por $quem_abriu",
    "activityText"     => $descricao,
    "facts" => [
      ["name"=>"Status","value"=>"aberto"],
      ["name"=>"Data"  ,"value"=> date('d/m/Y H:i')],
    ],
  ]],
];

$ch = curl_init($WEBHOOK_URL);
curl_setopt_array($ch, [
  CURLOPT_POST           => true,
  CURLOPT_POSTFIELDS     => json_encode($card, JSON_UNESCAPED_UNICODE),
  CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_TIMEOUT        => 10,
]);
$res  = curl_exec($ch);
$err  = curl_error($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($err || $code >= 300) {
  echo json_encode(['ok'=>false,'http_code'=>$code, 'erro'=>$err ?: $res]);
} else {
  echo json_encode(['ok'=>true]);
}
