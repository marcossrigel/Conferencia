<?php
session_start(); // Necessário para acessar $_SESSION
error_reporting(E_ALL);
ini_set('display_errors', 1);
include_once("config.php"); // conexão $conn

// Recupera o ID do usuário logado
$id_usuario = $_SESSION['id_usuario'] ?? null;
if (!$id_usuario) {
    die("Usuário não autenticado.");
}

// Recebe os dados do formulário
$fornecedor = $_POST['fornecedor'];
$nota_fiscal = $_POST['nota_fiscal'];
$produto = $_POST['produto'];
$quant_nf = str_replace(',', '.', $_POST['quant_nf']);
$num_volumes = str_replace(',', '.', $_POST['num_volumes']);
$tara_volume = str_replace(',', '.', $_POST['tara_volume']);
$diferenca = str_replace(',', '.', preg_replace('/[^0-9,.-]/', '', $_POST['campoDiferenca']));
$divergencia = $_POST['campoDivergencia'];
$observacoes = $_POST['observacoes'];
$assinatura_base64 = $_POST['assinatura_base64'];
$nome_completo = $_POST['nome_completo'];

// Converte vetores para JSON (se vierem como variáveis JS, insira via hidden inputs depois)
$etiquetas = isset($_POST['etiquetas']) ? $_POST['etiquetas'] : '[]';
$pesos_liquidos = isset($_POST['pesos_liquidos']) ? $_POST['pesos_liquidos'] : '[]';

$tara = floatval($tara_volume);
$pesos_liquidos_array = json_decode($pesos_liquidos, true);

$pesos_liquidos_corrigidos = array_map(function($peso) use ($tara) {
    return floatval($peso) - $tara;
}, $pesos_liquidos_array);

// Re-encode para JSON antes de salvar no banco
$pesos_liquidos = json_encode($pesos_liquidos_corrigidos);

$total_etiquetas = str_replace(',', '.', preg_replace('/[^0-9,.-]/', '', $_POST['total_etiquetas'] ?? 0));
$total_balanca = str_replace(',', '.', preg_replace('/[^0-9,.-]/', '', $_POST['total_balanca'] ?? 0));

// Upload da foto
$foto_nome = null;
if (!empty($_FILES['foto']['name'])) {
    $foto_nome = uniqid() . "_" . basename($_FILES["foto"]["name"]);
    $destino = "uploads/" . $foto_nome;
    move_uploaded_file($_FILES["foto"]["tmp_name"], $destino);
}

// Insere no banco
$stmt = $conn->prepare("INSERT INTO entregas (
  id_usuario, fornecedor, nota_fiscal, produto, quant_nf, etiquetas, pesos_liquidos,
  total_etiquetas, total_balanca, num_volumes, tara_volume,
  diferenca, divergencia, observacoes, foto_nome, nome_completo
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param(
  "issssssdddddssss",
  $id_usuario,
  $fornecedor,
  $nota_fiscal,
  $produto,
  $quant_nf,
  $etiquetas,
  $pesos_liquidos,
  $total_etiquetas,
  $total_balanca,
  $num_volumes,
  $tara_volume,
  $diferenca,
  $divergencia,
  $observacoes,
  $foto_nome,
  $nome_completo
);

if ($stmt->execute()) {
    echo "Entrega registrada com sucesso.";
    header("Location: home.php");
} else {
    echo "Erro ao salvar: " . $stmt->error;
}
?>