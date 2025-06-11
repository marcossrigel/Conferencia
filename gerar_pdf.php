<?php
session_start();
require_once 'libs/dompdf/autoload.inc.php';

include("config.php");

use Dompdf\Dompdf;

if (!isset($_SESSION['id_fornecedor'])) {
    echo "Acesso negado.";
    exit;
}

$id_fornecedor = $_SESSION['id_fornecedor'];
$tipo_usuario = $_SESSION['tipo_usuario'] ?? 'fornecedor';

if ($tipo_usuario === 'admin') {
    $query = "SELECT * FROM entregas ORDER BY id DESC";
    $stmt = $conexao->prepare($query);
    $stmt->execute();
} else {
    $query = "SELECT * FROM entregas WHERE id_fornecedores = ? ORDER BY id DESC";
    $stmt = $conexao->prepare($query);
    $stmt->bind_param("i", $id_fornecedor);
    $stmt->execute();
}

$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    echo "Nenhuma entrega encontrada.";
    exit;
}

ob_start();
?>

<style>

img {
  display: block;
  margin: 5px auto;
  max-width: 250px;
  height: auto;
}

</style>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Entregas</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h1 { text-align: center; margin-bottom: 20px; }
        .entrega { margin-bottom: 30px; page-break-inside: avoid; }
        .entrega h2 { margin-bottom: 10px; }
        .entrega p { margin: 3px 0; }
        img { margin-top: 5px; max-width: 250px; height: auto; }
        hr { margin: 20px 0; }
    </style>
</head>
<body>

<h1>Relatório de Entregas - <?= htmlspecialchars($_SESSION['fornecedor']) ?></h1>

<?php while ($entrega = $resultado->fetch_assoc()): ?>
    <div class="entrega">
        <h2>Produto: <?= htmlspecialchars($entrega['produto']) ?></h2>
        <p><strong>Responsável:</strong> <?= htmlspecialchars($entrega['responsavel_recebimento']) ?></p>
        <p><strong>Quantidade:</strong> <?= htmlspecialchars($entrega['quantidade_pedida']) ?></p>
        <p><strong>Peso Etiqueta:</strong> <?= htmlspecialchars($entrega['peso_etiqueta']) ?> |
           <strong>Peso Balança:</strong> <?= htmlspecialchars($entrega['peso_balanca']) ?></p>
        <p><strong>Tara:</strong> <?= htmlspecialchars($entrega['tara']) ?> |
           <strong>Peso Líquido:</strong> <?= htmlspecialchars($entrega['peso_liquido']) ?></p>
        <p><strong>Divergência:</strong> <?= htmlspecialchars($entrega['divergencia']) ?></p>
        <p><strong>Observações:</strong> <?= htmlspecialchars($entrega['observacoes']) ?></p>
        <p><strong>Data:</strong> <?= htmlspecialchars($entrega['data_registro']) ?></p>

        <?php if (!empty($entrega['foto']) && file_exists("uploads/{$entrega['foto']}")): ?>
        <?php
            $foto_path = "uploads/{$entrega['foto']}";
            $foto_data = base64_encode(file_get_contents($foto_path));
            $foto_src = 'data:image/png;base64,' . $foto_data;
        ?>
        <p><strong>Foto:</strong><br>
        <img src="<?= $foto_src ?>" alt="Foto do produto"></p>
    <?php endif; ?>

    <?php if (!empty($entrega['assinatura_base64']) && file_exists("uploads/{$entrega['assinatura_base64']}")): ?>
        <?php
            $assinatura_path = "uploads/{$entrega['assinatura_base64']}";
            $assinatura_data = base64_encode(file_get_contents($assinatura_path));
            $assinatura_src = 'data:image/png;base64,' . $assinatura_data;
        ?>
        <p><strong>Assinatura:</strong><br>
        <img src="<?= $assinatura_src ?>" alt="Assinatura"></p>
    <?php endif; ?>
        

    </div>
    <hr>
<?php endwhile; ?>

</body>
</html>

<?php
$html = ob_get_clean();

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("relatorio_entregas.pdf", ["Attachment" => false]);
?>
