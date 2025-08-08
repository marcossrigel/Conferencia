<?php
session_start();
include_once("config.php");

if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit;
}

$id_fornecedor = $_SESSION['id_usuario'];
$tipo_usuario = $_SESSION['tipo_usuario'] ?? 'fornecedor';

if ($tipo_usuario === 'admin') {
    $query = "SELECT entregas.*, usuarios.nome AS nome_usuario 
              FROM entregas 
              JOIN usuarios ON entregas.id_usuario = usuarios.id 
              ORDER BY entregas.id DESC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
} else {
    $query = "SELECT entregas.*, usuarios.nome AS nome_usuario 
              FROM entregas 
              JOIN usuarios ON entregas.id_usuario = usuarios.id 
              WHERE entregas.id_usuario = ? 
              ORDER BY entregas.id DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_fornecedor);
    $stmt->execute();
}

$resultado = $stmt->get_result();
?>


<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Minhas Entregas</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #e9eef1;
      margin: 0;
      padding: 20px;
    }

    .container {
      max-width: 800px;
      margin: auto;
    }

    h1 {
      font-size: 24px;
      text-align: center;
      margin-bottom: 20px;
      color: #000;
    }
    
    .btn-acao {
      display: inline-block;
      background-color: #4da6ff;
      color: white;
      padding: 10px 18px;
      margin: 5px 8px;
      border: none;
      border-radius: 8px;
      font-weight: bold;
      font-size: 15px;
      text-decoration: none;
      transition: background-color 0.3s ease;
    }

    .btn-acao:hover {
      background-color: #3399ff;
    }

    .accordion {
      background-color: #fff;
      cursor: pointer;
      padding: 18px;
      width: 100%;
      border: none;
      text-align: left;
      outline: none;
      font-size: 18px;
      border-radius: 10px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      margin-bottom: 10px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .accordion:hover {
      background-color: #f9f9f9;
    }

    .panel {
      padding: 0 0 15px 0;
      display: none;
      background-color: white;
      overflow: hidden;
      border-radius: 0 0 10px 10px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      margin-bottom: 15px;
    }

    .panel p {
      margin: 10px 18px;
      font-size: 15px;
      line-height: 1.5;
    }

    .modal-overlay {
      display: none;
      position: fixed;
      z-index: 999;
      left: 0; top: 0;
      width: 100%; height: 100%;
      background-color: rgba(0,0,0,0.5);
      justify-content: center;
      align-items: center;
    }

    .modal {
      background: white;
      padding: 30px;
      border-radius: 10px;
      text-align: center;
      max-width: 300px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.3);
    }

    .modal button {
      margin: 10px 5px;
      padding: 10px 20px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-weight: bold;
    }

    .modal .confirm {
      background-color: #e74c3c;
      color: white;
    }

    .modal .cancel {
      background-color: #bdc3c7;
      color: black;
    }


    .seta {
      font-size: 22px;
      transform: rotate(0deg);
      transition: transform 0.3s ease;
    }

    .accordion.active .seta {
      transform: rotate(180deg);
    }

    .botao-voltar {
      text-align: center;
      margin-top: 40px;
    }

    .botao-voltar button {
      padding: 10px 20px;
      background-color: #4da6ff;
      color: white;
      border: none;
      border-radius: 10px;
      font-weight: bold;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    .botao-voltar button:hover {
      background-color: #3399ff;
    }
  </style>
</head>

<body>

<div class="container">
  <h1>Minhas Entregas</h1>

  <?php while ($entrega = $resultado->fetch_assoc()): ?>
    <button class="accordion">
      <strong><?= htmlspecialchars($entrega['produto']) ?></strong>
      <span class="seta">⌄</span>
    </button>

  <div class="panel">
  <p><strong>Responsável:</strong> <?= htmlspecialchars($entrega['nome_usuario']) ?></p>
  <p><strong>Fornecedor:</strong> <?= htmlspecialchars($entrega['fornecedor']) ?></p>
  <p><strong>Quantidade:</strong> <?= htmlspecialchars($entrega['quant_nf']) ?></p>

  <?php
  // Arrays seguros
  $etiquetas_array = json_decode($entrega['etiquetas'] ?? '[]', true) ?: [];
  $pesos_brutos_array = json_decode($entrega['pesos_liquidos'] ?? '[]', true) ?: [];
?>

<div style="display:flex; gap:24px; flex-wrap:wrap; align-items:flex-start; margin-left:18px; margin-top:10px;">

  <!-- Tabela Etiquetas -->
  <div>
    <p style="margin:0 0 6px 0;"><strong>Etiquetas:</strong></p>
    <?php if ($etiquetas_array): ?>
      <table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; min-width:260px;">
        <thead>
          <tr style="background-color:#f2f2f2;">
            <th>Volume</th>
            <th>Peso da Etiqueta (kg)</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($etiquetas_array as $i => $peso): ?>
            <tr>
              <td style="text-align:center;"><?= $i+1 ?></td>
              <td style="text-align:center;"><?= number_format((float)$peso, 1, ',', '') ?></td>
            </tr>
          <?php endforeach; ?>
          <tr>
            <td style="text-align:right; font-weight:bold;" colspan="1">Total</td>
            <td style="text-align:center; font-weight:bold;">
              <?= number_format(array_sum(array_map('floatval',$etiquetas_array)), 2, ',', '') ?>
            </td>
          </tr>
        </tbody>
      </table>
    <?php else: ?>
      <p>Nenhuma etiqueta registrada.</p>
    <?php endif; ?>
  </div>

  <!-- Tabela Pesos Brutos (a partir de pesos_liquidos) -->
  <div>
    <p style="margin:0 0 6px 0;"><strong>Pesos Brutos:</strong></p>
    <?php if ($pesos_brutos_array): ?>
      <table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; min-width:260px;">
        <thead>
          <tr style="background-color:#f2f2f2;">
            <th>Volume</th>
            <th>Peso Bruto (kg)</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($pesos_brutos_array as $i => $peso): ?>
            <tr>
              <td style="text-align:center;"><?= $i+1 ?></td>
              <td style="text-align:center;"><?= number_format((float)$peso, 1, ',', '') ?></td>
            </tr>
          <?php endforeach; ?>
          <tr>
            <td style="text-align:right; font-weight:bold;" colspan="1">Total</td>
            <td style="text-align:center; font-weight:bold;">
              <?= number_format(array_sum(array_map('floatval',$pesos_brutos_array)), 2, ',', '') ?>
            </td>
          </tr>
        </tbody>
      </table>
    <?php else: ?>
      <p>Nenhum peso bruto registrado.</p>
    <?php endif; ?>
  </div>

</div>

  <?php
    // Diferenca (decimal 10,2)
    $diferenca = isset($entrega['diferenca']) ? (float)$entrega['diferenca'] : 0;

    // Tara (vem de tara_volume)
    $tara = isset($entrega['tara_volume']) ? (float)$entrega['tara_volume'] : 0;

    // Peso Líquido (somatório do JSON pesos_liquidos)
    $pesos_liq_arr = [];
    if (!empty($entrega['pesos_liquidos'])) {
        $tmp = json_decode($entrega['pesos_liquidos'], true);
        if (is_array($tmp)) {
            $pesos_liq_arr = array_map('floatval', $tmp);
        }
    }
    $peso_liquido_total = array_sum($pesos_liq_arr);

    // Divergência (na sua tabela é VARCHAR: "OK" ou "Divergente")
    $divergencia = trim($entrega['divergencia'] ?? '');
    $cor_div = ($divergencia === 'OK') ? 'green' : 'red';
  ?>
  <p><strong>Diferença:</strong> <?= number_format($diferenca, 2, ',', '') ?></p>
  <p>
    <strong>Tara:</strong> <?= number_format($tara, 2, ',', '') ?> |
    <strong>Peso Líquido (total):</strong> <?= number_format($peso_liquido_total, 2, ',', '') ?>
  </p>
  <p>
    <strong>Divergência:</strong>
    <span style="color:<?= $cor_div ?>; font-weight: bold;"><?= htmlspecialchars($divergencia) ?></span>
  </p>

  <p><strong>Observações:</strong> <?= htmlspecialchars($entrega['observacoes']) ?></p>

  <?php if (!empty($entrega['foto_nome'])): ?>
  <p><strong>Foto:</strong><br>
    <img src="uploads/<?= htmlspecialchars($entrega['foto_nome']) ?>" 
         alt="Foto da entrega" 
         style="max-width:300px; margin-top:10px; border-radius:6px; box-shadow:0 2px 8px rgba(0,0,0,0.15);">
  </p>
  <?php else: ?>
    <p><strong>Foto:</strong> Nenhuma foto registrada.</p>
  <?php endif; ?>

  <?php if (!empty($entrega['foto'])): ?>
    <p><strong>Foto:</strong><br><img src="uploads/<?= $entrega['foto'] ?>" width="200" style="margin-top:10px;"></p>
  <?php endif; ?>

  <p><strong>Assinado por:</strong> <?= htmlspecialchars($entrega['nome_completo']) ?></p>

    <button 
      type="button"
      class="btn-excluir"
      data-id="<?= $entrega['id'] ?>"
      style="margin: 10px 18px; padding: 8px 12px; background-color: #ff4d4d; color: white; border: none; border-radius: 8px; cursor: pointer;">
      🗑 Excluir
    </button>
     
    </div>
    <?php endwhile; ?>
    
    <?php if ($tipo_usuario === 'comum'): ?>
      <div style="text-align: center; margin-top: 20px;">
        <a href="exportar_csv.php" class="btn-acao">📥 Exportar CSV</a>
      </div>
    <?php endif; ?>

  <div class="botao-voltar">
    <button onclick="window.location.href='home.php';">&lt; Voltar para Home</button>
  </div>
    
</div>

<div class="modal-overlay" id="confirmModal">
  <div class="modal">
    <p>Tem certeza que deseja excluir esta entrega?</p>
    <form id="deleteForm" method="get" action="excluir_entrega.php">
      <input type="hidden" name="id" id="deleteId">
      <button type="submit" class="confirm">Sim, excluir</button>
      <button type="button" class="cancel" onclick="fecharModal()">Cancelar</button>
    </form>
  </div>
</div>

  <script>
    const accordions = document.querySelectorAll(".accordion");
    const botoesExcluir = document.querySelectorAll(".btn-excluir");
    const modal = document.getElementById("confirmModal");
    const deleteId = document.getElementById("deleteId");
  
    accordions.forEach((acc) => {
      acc.addEventListener("click", function () {
        this.classList.toggle("active");
        const panel = this.nextElementSibling;
        panel.style.display = (panel.style.display === "block") ? "none" : "block";
      });
    });
  
    botoesExcluir.forEach(botao => {
      botao.addEventListener("click", function () {
        const entregaId = this.getAttribute("data-id");
        deleteId.value = entregaId;
        modal.style.display = "flex";
      });
    });
  
    function fecharModal() {
      modal.style.display = "none";
    }
  
  </script>
</body>
</html>