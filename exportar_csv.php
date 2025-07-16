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
    $etiquetas_array = json_decode($linha['etiquetas'], true);
    $etiquetas_str = is_array($etiquetas_array) ? implode(', ', $etiquetas_array) : $linha['etiquetas'];

    $peso_bruto = floatval($linha['peso_bruto']);
    $tara = floatval($linha['tara']);
    $peso_liquido = floatval($linha['peso_liquido']);
    $div = floatval($linha['diferenca']);
    $status = $linha['divergencia'];

    // Monta os dados como pares [Campo, Valor]
    $dados = [
        ['Responsavel', $linha['nome_usuario']],
        ['Fornecedor', $linha['fornecedor']],
        ['Produto', $linha['produto']],
        ['Quantidade', $linha['quant_nf']],
        ['Etiquetas', $etiquetas_str],
        ['Peso Balanca', number_format($peso_bruto, 2, ',', '')],
        ['Tara', number_format($tara, 3, ',', '')],
        ['Peso Liquido', number_format($peso_liquido, 2, ',', '')],
        ['Divergencia', number_format($div, 2, ',', '')],
        ['Status', $status],
        ['Observacoes', $linha['observacoes']],
        ['Data Registro', date("d/m/Y H:i", strtotime($linha['data_registro']))],
    ];

    foreach ($dados as $linha_csv) {
        // Remove aspas duplicadas e espaços extras
        $linha_limpa = array_map(function($campo) {
            return trim(str_replace('"', '', $campo));
        }, $linha_csv);

        fputcsv($output, $linha_limpa);
    }
}

fclose($output);
exit;
