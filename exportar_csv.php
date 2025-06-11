<?php
session_start();
include("config.php");

// Verifica login
$id_fornecedor = $_SESSION['id_fornecedor'] ?? null;
$tipo_usuario = $_SESSION['tipo_usuario'] ?? 'fornecedor';

if (!$id_fornecedor) {
    die("Acesso negado.");
}

// Consulta entregas
if ($tipo_usuario === 'admin') {
    $query = "SELECT * FROM entregas ORDER BY id DESC";
    $stmt = $conexao->prepare($query);
} else {
    $query = "SELECT * FROM entregas WHERE id_fornecedores = ? ORDER BY id DESC";
    $stmt = $conexao->prepare($query);
    $stmt->bind_param("i", $id_fornecedor);
}

$stmt->execute();
$resultado = $stmt->get_result();

// Prepara o CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="lista_entregas.csv"');

$output = fopen('php://output', 'w');

// Cabeçalhos
fputcsv($output, ['Produto', 'Quantidade Pedida', 'Peso Etiqueta', 'Peso Balança', 'Divergência', 'Status'], ';');

// Linhas
while ($linha = $resultado->fetch_assoc()) {
    $etiqueta = floatval($linha['peso_etiqueta']);
    $balanca = floatval($linha['peso_balanca']);
    $div = $etiqueta - $balanca;
    $status = abs($div) < 0.01 ? "OK" : "Divergente";

    fputcsv($output, [
        $linha['produto'],
        $linha['quantidade_pedida'],
        number_format($etiqueta, 2, ',', ''),
        number_format($balanca, 2, ',', ''),
        number_format($div, 2, ',', ''),
        $status
    ], ';');
}

fclose($output);
exit;
