<?php
include_once('config.php'); 
session_start();
if (!isset($_SESSION['id_usuario'])) {
    die("Acesso negado. Faça login.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id_usuario = $_SESSION['id_usuario'];

    $fornecedor = $_POST['fornecedor'] ?? '';
    $nota_fiscal = $_POST['nota_fiscal'] ?? '';
    $produto = $_POST['produto'] ?? '';
    $quant_nf = $_POST['quant_nf'] ?? 0;
    
    $etiquetas = [];

    if (!empty($_POST['etiquetas_ocultas'])) {
        $etiquetas = json_decode($_POST['etiquetas_ocultas'], true);
    }
    $etiquetas_formatadas = array_map(function($v) {
        return number_format((float)str_replace(',', '.', $v), 1, '.', '');
    }, $etiquetas);

    $etiquetas_json = json_encode($etiquetas_formatadas);
    $total_etiquetas = array_sum($etiquetas_formatadas);
    $num_volumes = isset($_POST['num_volumes']) ? intval($_POST['num_volumes']) : 0;
    $peso_bruto = isset($_POST['peso_bruto']) ? floatval(str_replace(',', '.', $_POST['peso_bruto'])) : 0;

    $tara = isset($_POST['tara']) ? floatval(str_replace(',', '.', $_POST['tara'])) : 0;
    $tara_total = $tara * $num_volumes;
    $peso_liquido = $peso_bruto - $tara_total;
    $diferenca = $peso_liquido - $total_etiquetas;

    $observacoes = $_POST['observacoes'] ?? '';
    $assinatura_base64 = $_POST['assinatura_base64'] ?? '';

    $nome_arquivo = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $pasta_destino = "uploads/";
        if (!is_dir($pasta_destino)) {
            mkdir($pasta_destino, 0777, true);
        }
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $nome_arquivo = uniqid("foto_") . '.' . $ext;
        move_uploaded_file($_FILES['foto']['tmp_name'], $pasta_destino . $nome_arquivo);
    }
    
    $divergencia = ($diferenca < 0) ? "⚠️ Há Divergência" : "OK";

    $query = "INSERT INTO entregas (
        id_usuario, fornecedor, numero_nf, produto, quant_nf, etiquetas, total_etiquetas, volumes,
        peso_bruto, tara, tara_total, peso_liquido, diferenca, divergencia, observacoes,
        foto, assinatura_base64
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($query); 

    $stmt->bind_param("isssissidddddssss",
        $id_usuario, $fornecedor, $nota_fiscal, $produto, $quant_nf,
        $etiquetas_json, $total_etiquetas, $num_volumes,
        $peso_bruto, $tara, $tara_total, $peso_liquido, $diferenca,
        $divergencia, $observacoes, $nome_arquivo, $assinatura_base64
    );

    if ($stmt->execute()) {
        echo "<script>alert('Entrega registrada com sucesso!'); window.location.href='formulario.php';</script>";
        exit;
    } else {
        echo "Erro ao salvar: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Conferência de Entrega</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      padding: 40px 10px;
      font-family: 'Segoe UI', sans-serif;
      background-color: #f0f2f5;
      display: flex;
      justify-content: center;
      min-height: 100vh;
    }

    .container {
      background-color: #fff;
      padding: 30px 20px;
      border-radius: 12px;
      box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 500px;
    }

    h2 {
      text-align: center;
      margin-bottom: 25px;
      color: #333;
    }

    label {
      font-weight: 500;
      margin-bottom: 5px;
      display: block;
      color: #444;
    }

    input, select, textarea, button {
      width: 100%;
      padding: 10px 12px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 14px;
    }

    textarea {
      resize: vertical;
    }

    input:focus, textarea:focus, select:focus {
      outline: none;
      border-color: #007bff;
    }

    button {
      font-weight: bold;
      cursor: pointer;
      transition: 0.3s;
    }

    .btn-primary {
      background-color: #007bff;
      color: white;
      border: none;
    }

    .btn-primary:hover {
      background-color: #0056b3;
    }

    .btn-danger {
      background-color: #dc3545;
      color: white;
      border: none;
    }

    .btn-danger:hover {
      background-color: #a71d2a;
    }

    canvas {
      border: 1px solid #ccc;
      border-radius: 6px;
      width: 100%;
      height: 150px;
    }

    .button-group {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    @media (min-width: 480px) {
      .button-group {
        flex-direction: row;
        justify-content: space-between;
      }
    }
  </style>
</head>
<body>

<div class="container">
  <h2>Conferência de Entrega</h2>
  <form action="formulario.php" method="POST" enctype="multipart/form-data">

    <label>Fornecedor</label>
    <input type="text" name="fornecedor" required>

    <label>Número da Nota Fiscal</label>
    <input type="text" name="nota_fiscal" required>

    <label>Produto</label>
    <input type="text" name="produto" required>

    <label>Quantidade a Receber (NF)</label>
    <input type="text" name="quant_nf" required>

    <label>Peso das Etiquetas (por volume)</label>
    <div id="etiquetas">
       <input type="text" name="etiquetas[]" class="etiqueta" step="0.01" inputmode="decimal" pattern="[0-9]+([.,][0-9]+)?" oninput="atualizarCalculos()">
    </div>
    <button type="button" class="btn-primary" onclick="adicionarEtiqueta()">+ Adicionar Etiqueta</button>

    <input type="text" id="totalEtiquetas" placeholder="Total Etiquetas (soma)" readonly step="any">

    <input type="hidden" name="etiquetas_ocultas" id="etiquetas_ocultas">

    <button type="button" class="btn-primary" onclick="salvarParcialEtiqueta()">Salvar Resultado Parcial</button>
    <p><strong>Total acumulado:</strong> <span id="totalAcumulado">0.00</span> kg</p>


    <label>Número de Volumes</label>
    <input type="text" id="numVolumes" name="num_volumes" step="any" inputmode="decimal" oninput="atualizarCalculos()">

    <label>Peso Bruto da Balança</label>
    <input type="text" name="peso_bruto" id="pesoBruto" oninput="atualizarCalculos()">

    <label>Tara por Volume</label>
    <input type="text" id="tara" name="tara" step="0.001" min="0" oninput="atualizarCalculos()">

    <label>Peso Líquido da Balança</label>
    <input type="number" id="pesoLiquido" readonly>

    <label>Diferença (Peso Líquido - Total Etiquetas)</label>
    <input type="number" id="diferenca" readonly>

    <label>Divergência</label>
    <input type="text" id="campoDivergencia" readonly>

    <label>Observações</label>
    <textarea name="observacoes" placeholder="Digite aqui..."></textarea>

    <label>Foto</label>
    <input type="file" name="foto" accept="image/*">

    <label>Assinatura Digital</label>
    <canvas id="assinatura"></canvas>
    <input type="hidden" name="assinatura_base64" id="assinatura_base64">
    <button type="button" class="btn-danger" onclick="limparAssinatura()">Limpar Assinatura</button>

    <div class="button-group">
      <button type="reset" class="btn-danger">Cancelar</button>
      <button type="submit" class="btn-primary" onclick="capturarAssinatura()">Confirmar Entrega</button>
    </div>

    <div class="button-group" style="margin-top: 20px;">
      <a href="home.php" class="btn-primary" style="text-align:center; display:block; text-decoration:none; padding:10px 12px; border-radius:6px;">< Voltar</a>
    </div>

    <div id="modalSucesso" style="display:<?= isset($sucesso) && $sucesso ? 'flex' : 'none' ?>; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:999; justify-content:center; align-items:center;">
      <div style="background:white; padding:30px; border-radius:12px; text-align:center; box-shadow:0 0 10px rgba(0,0,0,0.3);">
        <h2 style="color:green;">✅ Entrega registrada com sucesso!</h2>
        <button onclick="fecharModal()" style="margin-top:20px; padding:10px 20px; font-weight:bold; background:#007bff; color:white; border:none; border-radius:6px;">OK</button>
      </div>
    </div>

  </form>
</div>

<script>

  function fecharModal() {
    document.getElementById("modalSucesso").style.display = "none";
  }

  function adicionarEtiqueta() {
  const div = document.createElement('div');
  const input = document.createElement('input');
  input.type = 'text';
  input.name = 'etiquetas[]';
  input.className = 'etiqueta';
  input.placeholder = 'Peso etiqueta';
  input.step = 'any'; // <-- permite decimais
  input.oninput = atualizarCalculos;
  div.appendChild(input);
  document.getElementById('etiquetas').appendChild(div);
}

  function atualizarCalculos() {
  const etiquetas = document.querySelectorAll(".etiqueta");
  let total = 0;
  etiquetas.forEach(input => {
    const val = parseFloat(input.value.replace(",", ".")) || 0;
    total += val;
  });
  document.getElementById("totalEtiquetas").value = total.toFixed(2);

  const taraInput = document.getElementById("tara");
  const bruto = parseFloat(document.getElementById("pesoBruto").value.replace(",", ".")) || 0;
  const tara = parseFloat(taraInput.value.replace(",", ".")) || 0;
  const volumes = parseInt(document.getElementById("numVolumes").value) || 0;
  const liquido = bruto - (tara * volumes);
  document.getElementById("pesoLiquido").value = liquido.toFixed(2);

  const diferenca = liquido - total;
  document.getElementById("diferenca").value = diferenca.toFixed(2);

  const campoDivergencia = document.getElementById("campoDivergencia");
  campoDivergencia.value = diferenca < 0 ? "⚠️ Há Divergência" : "OK";
}

  let etiquetasAcumuladas = [];

function salvarParcialEtiqueta() {
  const inputs = document.querySelectorAll(".etiqueta");
  let somaAtual = 0;
  const valoresAtual = [];

  inputs.forEach(input => {
    const val = parseFloat(input.value.replace(",", ".")) || 0;
    valoresAtual.push(val);
    somaAtual += val;
  });

  if (somaAtual === 0) {
    alert("Adicione valores válidos antes de salvar.");
    return;
  }

  etiquetasAcumuladas = etiquetasAcumuladas.concat(valoresAtual);

  // Atualiza o acumulado total
  const total = etiquetasAcumuladas.reduce((a, b) => a + b, 0);
  document.getElementById("totalAcumulado").innerText = total.toFixed(2);

  // Atualiza o campo oculto
  document.getElementById("etiquetas_ocultas").value = JSON.stringify(etiquetasAcumuladas);

  // Limpa os campos visuais
  document.getElementById("etiquetas").innerHTML = `
    <input type="text" name="etiquetas[]" class="etiqueta" step="0.01" inputmode="decimal" 
           pattern="[0-9]+([.,][0-9]+)?" oninput="atualizarCalculos()">
  `;
  document.getElementById("totalEtiquetas").value = "";
}

  const canvas = document.getElementById("assinatura");
  const ctx = canvas.getContext("2d");
  let desenhando = false;

  canvas.addEventListener("mousedown", () => desenhando = true);
  canvas.addEventListener("mouseup", () => {
    desenhando = false;
    ctx.beginPath();
  });
  canvas.addEventListener("mousemove", desenhar);

  function desenhar(e) {
    if (!desenhando) return;
    const rect = canvas.getBoundingClientRect();
    ctx.lineWidth = 2;
    ctx.lineCap = "round";
    ctx.strokeStyle = "#000";
    ctx.lineTo(e.clientX - rect.left, e.clientY - rect.top);
    ctx.stroke();
    ctx.beginPath();
    ctx.moveTo(e.clientX - rect.left, e.clientY - rect.top);
  }

  function capturarAssinatura() {
    document.getElementById("assinatura_base64").value = canvas.toDataURL();
  }

  function limparAssinatura() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.beginPath();
  }
  
</script>

</body>
</html>
