<?php
session_start();

include_once("config.php");

if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$tipo_usuario = $_SESSION['tipo_usuario'] ?? 'fornecedor';

if ($tipo_usuario === 'admin') {
    $query = "SELECT entregas.*, usuarios.nome AS nome_usuario FROM entregas 
              JOIN usuarios ON entregas.id_usuario = usuarios.id 
              ORDER BY entregas.id DESC";
    $stmt = $conn->prepare($query);
} else {
    $query = "SELECT entregas.*, usuarios.nome AS nome_usuario FROM entregas 
              JOIN usuarios ON entregas.id_usuario = usuarios.id 
              WHERE entregas.id_usuario = ? 
              ORDER BY entregas.id DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_usuario);
}

$stmt->execute();
$result = $stmt->get_result();
$entregas = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Visualizar Entregas</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #e9eff4;
            padding: 20px;
        }
        h2 {
            text-align: center;
            margin-bottom: 30px;
        }
        .accordion {
            background-color: white;
            border-radius: 10px;
            margin-bottom: 10px;
            padding: 15px;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .accordion-content {
            display: none;
            background: #fff;
            padding: 10px 15px;
            border-radius: 0 0 10px 10px;
        }
        .active + .accordion-content {
            display: block;
        }
        button {
            padding: 10px 15px;
            margin: 10px 5px;
            border: none;
            border-radius: 6px;
            background-color: #007bff;
            color: white;
            font-weight: 600;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .btn-red {
            background-color: #dc3545;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 6px;
            border: 1px solid #ccc;
            text-align: center;
        }
        .divergencia-ok {
            color: green;
            font-weight: bold;
        }
        .divergencia-alerta {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>

<h2>Minhas Entregas</h2>

<?php foreach ($entregas as $entrega): ?>
    <div class="accordion"><strong><?= strtoupper($entrega['produto']) ?></strong></div>
    <div class="accordion-content">
        <p><strong>Responsável:</strong> <?= htmlspecialchars($entrega['nome_usuario']) ?></p>
        <p><strong>Fornecedor:</strong> <?= htmlspecialchars($entrega['fornecedor']) ?></p>
        <p><strong>Quantidade:</strong> <?= htmlspecialchars($entrega['quant_nf']) ?></p>

        <p><strong>Etiquetas:</strong></p>
        <table>
            <tr><th>Volume</th><th>Peso da Etiqueta (kg)</th></tr>
            <?php $etiquetas = json_decode($entrega['etiquetas'], true) ?? [];
                  foreach ($etiquetas as $index => $peso): ?>
                <tr><td><?= $index + 1 ?></td><td><?= number_format($peso, 1, ',', '.') ?></td></tr>
            <?php endforeach; ?>
        </table>

        <p><strong>Peso Balança:</strong> <?= number_format($entrega['total_balanca'], 2, ',', '.') ?> |
           <strong>Tara:</strong> <?= number_format($entrega['tara_volume'], 3, ',', '.') ?> |
           <strong>Peso Líquido:</strong> <?= number_format($entrega['total_balanca'] - ($entrega['num_volumes'] * $entrega['tara_volume']), 2, ',', '.') ?>
        </p>

        <p><strong>Divergência:</strong> 
           <?= number_format($entrega['diferenca'], 2, ',', '.') ?> 
           <span class="<?= ($entrega['diferenca'] < 0) ? 'divergencia-alerta' : 'divergencia-ok' ?>">
               <?= ($entrega['diferenca'] < 0) ? '⚠️' : '(OK)' ?>
           </span>
        </p>

        <p><strong>Observações:</strong> <?= nl2br(htmlspecialchars($entrega['observacoes'])) ?></p>

        <?php if (!empty($entrega['foto_nome'])): ?>
            <p><strong>Foto:</strong><br><img src="uploads/<?= $entrega['foto_nome'] ?>" alt="Foto" style="max-width:100%;border-radius:5px;"></p>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

<div style="text-align:center; margin-top: 30px;">
    <button onclick="window.location.href='exportar_csv.php'">📦 Exportar CSV</button>
    <button onclick="window.location.href='home.php'">&lt; Voltar para Home</button>
</div>

<script>
    document.querySelectorAll('.accordion').forEach(btn => {
        btn.addEventListener('click', () => {
            btn.classList.toggle('active');
            const content = btn.nextElementSibling;
            content.style.display = (content.style.display === 'block') ? 'none' : 'block';
        });
    });
</script>

</body>
</html>
