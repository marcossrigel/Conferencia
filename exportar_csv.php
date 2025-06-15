<?php
session_start();
include("config.php");

$id_fornecedor = $_SESSION['id_fornecedor'] ?? null;
$tipo_usuario = $_SESSION['tipo_usuario'] ?? 'fornecedor';

if (!$id_fornecedor && $tipo_usuario !== 'admin') {
    die("Acesso negado.");
}

if ($tipo_usuario === 'admin') {
    $query = "SELECT * FROM entregas ORDER BY id DESC";
    $stmt = $conn->prepare($query);
} else {
    $query = "SELECT * FROM entregas WHERE id_fornecedor = ? ORDER BY id DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_fornecedor);
}

$stmt->execute();
$resultado = $stmt->get_result();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="lista_entregas.csv"');
$output = fopen('php://output', 'w');

fputcsv($output, [
    'Fornecedor',
    'Produto',
    'Quantidade',
    'Peso Etiqueta',
    'Peso Balança',
    'Tara',
    'Peso Líquido',
    'Divergência',
    'Status',
    'Observações',
    'Data Registro'
], ';');

while ($linha = $resultado->fetch_assoc()) {
    $etiqueta = floatval($linha['peso_etiqueta']);
    $balanca = floatval($linha['peso_balanca']);
    $tara = floatval($linha['tara']);
    $liquido = $balanca - $tara;
    $div = $etiqueta - $balanca;
    $status = abs($div) < 0.01 ? "OK" : "Divergente";

    fputcsv($output, [
        $linha['nome'],              // nome do fornecedor
        $linha['produto'],
        $linha['quantidade'],
        number_format($etiqueta, 2, ',', ''),
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
