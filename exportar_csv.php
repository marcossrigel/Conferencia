<?php
session_start();
include("config.php");

if (!isset($_SESSION['id_usuario'])) {
    die("Acesso negado.");
}

$id_usuario = $_SESSION['id_usuario'];
$tipo_usuario = $_SESSION['tipo_usuario'] ?? 'fornecedor';

if ($tipo_usuario === 'admin') {
    $query = "SELECT entregas.*, usuarios.nome AS nome_usuario 
              FROM entregas 
              JOIN usuarios ON entregas.id_usuario = usuarios.id
              ORDER BY entregas.id DESC LIMIT 1";
    $stmt = $conn->prepare($query);
} else {
    $query = "SELECT entregas.*, usuarios.nome AS nome_usuario 
              FROM entregas 
              JOIN usuarios ON entregas.id_usuario = usuarios.id
              WHERE entregas.id_usuario = ? 
              ORDER BY entregas.id DESC LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_usuario);
}

$stmt->execute();
$resultado = $stmt->get_result();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="entrega_detalhada.csv"');
echo "\xEF\xBB\xBF"; // UTF-8 BOM

$output = fopen('php://output', 'w');

while ($linha = $resultado->fetch_assoc()) {
    // Decodifica os vetores de etiquetas e pesos
    $etiquetas_array = json_decode($linha['etiquetas'], true) ?? [];
    $pesos_array = json_decode($linha['pesos_liquidos'], true) ?? [];

    $etiquetas_total = array_sum($etiquetas_array);
    $pesos_total = array_sum($pesos_array);

    $etiquetas_str = implode(', ', array_map(fn($n) => number_format($n, 2, ',', ''), $etiquetas_array)) . ", " . number_format($etiquetas_total, 2, ',', '');
    $pesos_str = implode(', ', array_map(fn($n) => number_format($n, 2, ',', ''), $pesos_array)) . ", " . number_format($pesos_total, 2, ',', '');

    // Outras variáveis
    $tara_float = floatval($linha['tara_volume']);
    $tara = fmod($tara_float, 1) == 0.0 ? number_format($tara_float, 0, ',', '') : number_format($tara_float, 3, ',', '');
    $peso_liquido = number_format($linha['total_balanca'] - ($linha['num_volumes'] * $linha['tara_volume']), 2, ',', '');
    $div = number_format($linha['diferenca'] ?? 0, 2, ',', '');
    $status = $linha['divergencia'] ?? '-';

    // Dados do CSV
    $dados = [
        ['Responsavel', $linha['nome_usuario']],
        ['Fornecedor', $linha['fornecedor']],
        ['Produto', $linha['produto']],
        ['Quantidade', $linha['quant_nf']],
        ['Etiquetas', $etiquetas_str],
        ['Pesos da Balança', $pesos_str],
        ['Tara por Volume', $tara],
        ['Peso Líquido', $peso_liquido],
        ['Divergência', $div],
        ['Status', $status],
        ['Observacoes', $linha['observacoes']],
        ['Data Registro', date("d/m/Y H:i", strtotime($linha['data_registro']))],
    ];

    foreach ($dados as $linha_csv) {
        fputcsv($output, $linha_csv);
    }
}

fclose($output);
exit;
