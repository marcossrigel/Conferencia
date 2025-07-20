<?php
session_start();

include_once("config.php");

if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$query = "SELECT entregas.*, usuarios.nome AS nome_usuario FROM entregas 
          JOIN usuarios ON entregas.id_usuario = usuarios.id 
          WHERE entregas.id_usuario = ? 
          ORDER BY entregas.id DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_usuario);

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
.container {
    max-width: 800px;
    margin: 0 auto;
}
.card {
  background-color: #fff;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.1);
  margin-bottom: 20px;
}
.accordion {
background-color: white;
border-radius: 10px;
margin-bottom: 10px;
padding: 15px;
cursor: pointer;
box-shadow: 0 2px 5px rgba(0,0,0,0.1);
width: 100%;
}


img.foto-entrega {
    max-width: 300px;
    border-radius: 5px;
}

img.assinatura {
    max-width: 300px;
    border: 1px solid #ccc;
    margin-top: 10px;
    border-radius: 5px;
}

.excluir-btn {
    background-color: #dc3545;
    color: white;
    font-weight: bold;
    margin-top: 10px;
    padding: 10px 15px;
    border-radius: 6px;
    border: none;
}

.accordion-content {
    display: none;
    background: #fff;
    padding: 10px 15px;
    border-radius: 0 0 10px 10px;
    margin-bottom: 20px; /* <<< Aqui está o espaçamento desejado */
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
.tables-container {
    display: flex;
    gap: 20px;
    margin-top: 10px;
    flex-wrap: wrap;
}

.accordion-content {
    display: none;
    transition: max-height 0.3s ease-out;
}

.accordion-content.active {
    display: block;
}
.table-wrapper {
    flex: 1;
    min-width: 300px;
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

@media (max-width: 600px) {
    .tables-container {
        flex-direction: column;
        gap: 10px;
    }

    .table-wrapper {
        min-width: 100%;
    }

    img.foto-entrega,
    img.assinatura {
        max-width: 100%;
        height: auto;
    }

    button, .excluir-btn {
        width: 100%;
        margin: 8px 0;
    }

    .accordion, .accordion-content {
        padding: 12px;
    }

    .container {
        padding: 10px;
    }

    h2 {
        font-size: 1.2rem;
    }
}

</style>
</head>
<body>

<div class="container">

<h2>Minhas Entregas</h2>

<?php foreach ($entregas as $entrega): ?>
    <div class="accordion"><strong><?= strtoupper($entrega['produto']) ?></strong></div>
    <div class="accordion-content">
        <p><strong>Responsável:</strong> <?= htmlspecialchars($entrega['nome_usuario'] ?? '-') ?></p>
        <p><strong>Fornecedor:</strong> <?= htmlspecialchars($entrega['fornecedor']) ?></p>
        <p><strong>Quantidade:</strong> <?= htmlspecialchars($entrega['quant_nf']) ?></p>

        <div class="tables-container">
            <div class="table-wrapper">
                <p><strong>Etiquetas:</strong></p>
                <table>
                    <tr><th>Volume</th><th>Peso da Etiqueta (kg)</th></tr>
                    <?php 
                    $etiquetas = json_decode($entrega['etiquetas'], true) ?? [];
                    $total_etiquetas = 0;
                    foreach ($etiquetas as $index => $peso):
                        $total_etiquetas += $peso;
                    ?>
                        <tr><td><?= $index + 1 ?></td><td><?= number_format($peso, 1, ',', '.') ?></td></tr>
                    <?php endforeach; ?>
                    <tr><td><strong>Total</strong></td><td><strong><?= number_format($total_etiquetas, 2, ',', '.') ?></strong></td></tr>
                </table>
            </div>

            <div class="table-wrapper">
                <p><strong>Pesos da Balança:</strong></p>
                <table>
                    <tr><th>Volume</th><th>Peso Bruto (kg)</th></tr>
                    <?php 
                    $pesos = json_decode($entrega['pesos_liquidos'], true) ?? [];
                    $total_pesos = 0;
                    foreach ($pesos as $index => $peso):
                        $total_pesos += $peso;
                    ?>
                        <tr><td><?= $index + 1 ?></td><td><?= number_format($peso, 1, ',', '.') ?></td></tr>
                    <?php endforeach; ?>
                    <tr><td><strong>Total</strong></td><td><strong><?= number_format($total_pesos, 2, ',', '.') ?></strong></td></tr>
                </table>
            </div>
        </div>
           <p>
               <strong>Diferença:</strong> <?= number_format($entrega['diferenca'], 2, ',', '.') ?> |
                <strong>Tara:</strong> <?= number_format($entrega['tara_volume'], 1, ',', '.') ?> |
                <strong>Peso Líquido:</strong> <?= number_format($entrega['total_balanca'] - ($entrega['num_volumes'] * $entrega['tara_volume']), 2, ',', '.') ?> 
            </p>

        <p><strong>Divergência:</strong> 
           <span class="<?= ($entrega['diferenca'] < 0) ? 'divergencia-alerta' : 'divergencia-ok' ?>">
               <?= ($entrega['diferenca'] < 0) ? '⚠️ Ha divergencia' : '(OK)' ?>
           </span>
        </p>

        <p><strong>Observações:</strong> <?= nl2br(htmlspecialchars($entrega['observacoes'])) ?></p>

        <?php if (!empty($entrega['foto_nome'])): ?>
            <p><strong>Foto:</strong><br>
               <img src="uploads/<?= $entrega['foto_nome'] ?>" alt="Foto" class="foto-entrega">
            </p>
        <?php endif; ?>

        <?php if (!empty($entrega['assinatura_base64'])): ?>
            <p><strong>Assinatura:</strong><br>
                <img src="<?= $entrega['assinatura_base64'] ?>" class="assinatura" alt="Assinatura">
            </p>
        <?php endif; ?>

        <div style="text-align: right; margin-top: 15px;">
        <form action="excluir_entrega.php" method="post" onsubmit="return confirm('Tem certeza que deseja excluir esta entrega?');">
            <input type="hidden" name="id" value="<?= $entrega['id'] ?>">
            <button type="submit" class="excluir-btn">Excluir</button>
        </form>
</div>

    </div>

<?php endforeach; ?>

<div style="text-align:center; margin-top: 30px;">
    <button onclick="window.location.href='exportar_csv.php'">📦 Exportar CSV</button>
    <button onclick="window.location.href='home.php'">&lt; Voltar para Home</button>
</div>

<script>
    document.querySelectorAll('.accordion').forEach((btn, index) => {
        btn.addEventListener('click', () => {
            btn.classList.toggle('active');
            const content = btn.nextElementSibling;
            content.classList.toggle('active');
        });
    });
</script>

</body>
</html>
