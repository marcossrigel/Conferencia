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
              JJOIN usuarios ON entregas.id_usuario = usuarios.id
              ORDER BY entregas.id DESC";
    $stmt = $conn->prepare($query);
} else {
    $query = "SELECT entregas.*, usuarios.nome AS nome_usuario 
              FROM entregas 
              JOIN usuarios ON entregas.id_usuario = usuarios.id
              WHERE entregas.id_usuario = ? 
              ORDER BY entregas.id DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_usuario);
}

$stmt->execute();
$resultado = $stmt->get_result();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="lista_entregas.csv"');
$output = fopen('php://output', 'w');

fputcsv($output, [
    'Responsável',
    'Fornecedor',
    'Produto',
    'Quantidade',
    'Etiquetas',
    'Peso Balança',
    'Tara',
    'Peso Líquido',
    'Divergência',
    'Status',
    'Observações',
    'Data Registro'
], ';');

while ($linha = $resultado->fetch_assoc()) {
    $etiquetas = $linha['etiquetas'];
    $balanca = floatval($linha['peso_bruto']);
    $tara = floatval($linha['tara']);
    $liquido = $balanca - $tara;
    $div = floatval($linha['divergencia']);
    $status = abs($div) < 0.01 ? "OK" : "Divergente";

    fputcsv($output, [
        $linha['nome_usuario'],
        $linha['fornecedor'],
        $linha['produto'],
        $linha['quant_nf'],
        $etiquetas,
        number_format($balanca, 2, ',', ''),
        number_format($tara, 2, ',', ''),
        number_format($liquido, 2, ',', ''),
        number_format($div, 2, ',', ''),
        $status,
        $linha['observacoes'],
        date("d/m/Y H:i", strtotime($linha['data_registro']))
    ], ';');
}

fclose($output);
exit;
