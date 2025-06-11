<?php
session_start();
include("config.php");

$registro_inserido = false;

$assinatura_arquivo = '';
$assinatura = $_POST['assinatura_base64'] ?? '';
if (!empty($assinatura)) {
    $data = explode(',', $assinatura);
    if (count($data) == 2) {
        $base64 = base64_decode($data[1]);
        $assinatura_nome = 'assinatura_' . uniqid() . '.png';
        file_put_contents('uploads/' . $assinatura_nome, $base64);
        $assinatura_arquivo = $assinatura_nome;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_fornecedor = $_SESSION['id_fornecedor'] ?? null;
    $fornecedor = $_SESSION['fornecedor'] ?? 'Não identificado';

    $responsavel = $_POST['responsavel'] ?? '';
    $produto = $_POST['produto'] ?? '';
    $quantidade = $_POST['quantidade'] ?? '';
    $peso_etiqueta = $_POST['peso_etiqueta'] ?? '';
    $peso_balanca = $_POST['peso_balanca'] ?? '';
    $tara = $_POST['tara'] ?? '';
    $peso_liquido = $_POST['peso_liquido'] ?? '';
    $divergencia = $_POST['divergencia'] ?? '';
    $observacoes = $_POST['observacoes'] ?? '';
    $data_hora = date("Y-m-d H:i:s");
    $data_registro = date("Y-m-d H:i:s");

    $foto = '';
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $foto_nome = uniqid() . '_' . $_FILES['foto']['name'];
        move_uploaded_file($_FILES['foto']['tmp_name'], 'uploads/' . $foto_nome);
        $foto = $foto_nome;
    }

    $stmt = $conexao->prepare("INSERT INTO entregas 
    (id_fornecedores, fornecedor, responsavel_recebimento, produto, quantidade_pedida, peso_etiqueta, peso_balanca, tara, peso_liquido, divergencia, observacoes, foto, assinatura_base64, data_hora, data_registro)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("isssddddddsssss",
        $id_fornecedor, $fornecedor, $responsavel, $produto, $quantidade, $peso_etiqueta,
        $peso_balanca, $tara, $peso_liquido, $divergencia,
        $observacoes, $foto, $assinatura_arquivo, $data_hora, $data_registro);

    $registro_inserido = false;

    if ($stmt->execute()) {
        $registro_inserido = true;
    }

    $stmt->close();
    $conexao->close();
}
$nome_fornecedor = $_SESSION['fornecedor'] ?? 'Não identificado';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Conferência de Entrega</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">

  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #e3e8ec;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 40px 20px;
      min-height: 100vh;
      box-sizing: border-box;
    }
    .container {
      background-color: #fff;
      border-radius: 15px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      padding: 30px;
      max-width: 500px;
      width: 100%;
      box-sizing: border-box;
    }
    .main-title {
      font-size: 22px;
      font-weight: bold;
      text-align: center;
      margin-bottom: 20px;
    }
    label {
      display: block;
      margin-top: 15px;
      margin-bottom: 5px;
      font-size: 15px;
    }
    input[type="text"],
    input[type="number"],
    input[type="file"],
    textarea {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 15px;
      box-sizing: border-box;
    }
    .row {
      display: flex;
      flex-direction: row;
      justify-content: space-between;
      gap: 16px;
      flex-wrap: wrap;
    }

    textarea[name="observacoes"] {
      height: 120px;       /* Aumenta a altura vertical */
      resize: vertical;    /* Permite redimensionar apenas na vertical */
    }

    .row .col {
      flex: 1 1 100%;
      display: flex;
      flex-direction: column;
    }
    .modal {
      position: fixed;
      z-index: 9999;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.4);
      display: flex;
      justify-content: center;
      align-items: center;
    }
    .modal-content {
      background-color: white;
      padding: 20px 30px;
      border-radius: 10px;
      text-align: center;
      font-family: 'Poppins', sans-serif;
      box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    }
    .modal-content button {
      margin-top: 15px;
      padding: 8px 20px;
      font-weight: bold;
      background-color: #4da6ff;
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
    }

    @media (min-width: 600px) {
      .row .col {
        flex: 1;
      }
    }
    .button-group {
      text-align: center;
      margin-top: 25px;
    }
    .button-group button {
      padding: 12px 20px;
      font-size: 16px;
      font-weight: bold;
      color: #fff;
      background-color: #4da6ff;
      border: none;
      border-radius: 10px;
      cursor: pointer;
    }
    .button-group button:hover {
      background-color: #3399ff;
    }
    .cancelar-link {
      text-align: center;
      margin-top: 15px;
    }
    .cancelar-link a {
      color: red;
      font-weight: bold;
      text-decoration: none;
    }
    #divergencia {
      display: inline-block;
      padding: 8px 12px;
      margin-top: 5px;
      font-weight: bold;
      background-color: #f0f0f0;
      border-radius: 8px;
    }
    canvas {
      width: 100% !important;
      height: auto;
      border: 1px solid #ccc;
      border-radius: 10px;
      margin-top: 10px;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="main-title">Conferência de Entrega</div>

    <form method="POST" action="formulario.php" enctype="multipart/form-data" onsubmit="return handleSubmit()">
      <p style="text-align: right; font-size: 12px; color: gray;">
        Registro em: <?= date("d/m/Y H:i:s") ?>
      </p>
      <p><strong>Fornecedor:</strong> <?= htmlspecialchars($nome_fornecedor) ?></p>

      <label>Responsável Recebimento</label>
      <input type="text" name="responsavel" placeholder="Nome">

      <label>Produto</label>
      <input type="text" name="produto" placeholder="Nome do produto">

      <label>Quantidade Pedida</label>
      <input type="number" name="quantidade">

      <div class="row">
        <div class="col">
          <label>Peso da Balança</label>
          <input type="number" name="peso_balanca" id="peso_balanca" step="0.01">
        </div>
        <div class="col">
          <label>tara</label>
          <input type="number" name="tara" id="tara">
        </div>
      </div>

      <div class="row">
        <div class="col">
          <label>Peso Líquido</label>
          <input type="text" name="peso_liquido" id="peso_liquido" readonly>
        </div>
        <div class="col">
          <label>Peso da Etiqueta</label>
          <input type="number" name="peso_etiqueta" id="peso_etiqueta" step="0.01">
        </div>
      </div>

      <label>Divergência</label>
      <label id="divergencia">---</label>
      <input type="hidden" name="divergencia" id="divergencia_oculto">

      <label>Observações</label>
      <textarea name="observacoes" rows="4" placeholder="Digite aqui..."></textarea>

      <label>Foto</label>
      <input type="file" name="foto">

      <label>Assinatura Digital</label>
      <canvas id="signature-pad" width="400" height="150"></canvas>
      <input type="hidden" id="assinatura_base64" name="assinatura_base64">
      <div style="margin-top: 10px;">
        <button type="button" onclick="clearSignature()">Limpar</button>
      </div>

      <div class="button-group">
        <button type="submit">Confirmar Entrega</button>
      </div>

      <div class="cancelar-link">
        <a href="home.php">Cancelar</a>
      </div>
    </form>

    
  </div>
  
    <div id="sucessoModal" class="modal" style="display:none;">
    <div class="modal-content">
      <p>Registro inserido com sucesso.</p>
      <button onclick="fecharModal()">OK</button>
    </div>
    </div>

  <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.6/dist/signature_pad.umd.min.js"></script>
  <script>
    const taraInput = document.getElementById('tara');
    taraInput.addEventListener('input', atualizarDivergencia);

    const pesoEtiquetaInput = document.getElementById('peso_etiqueta');
    const pesoBalancaInput = document.getElementById('peso_balanca');
    const pesoLiquidoInput = document.getElementById('peso_liquido');
    const divergenciaLabel = document.getElementById('divergencia');

    function atualizarDivergencia() {
      const etiqueta = parseFloat(pesoEtiquetaInput.value.replace(',', '.')) || 0;
      const balanca = parseFloat(pesoBalancaInput.value.replace(',', '.')) || 0;
      const tara = parseFloat(taraInput.value.replace(',', '.')) || 0;
      const liquido = balanca - tara;

      pesoLiquidoInput.value = liquido.toFixed(1).replace('.', ',');

      const diferenca = etiqueta - balanca;
      if (diferenca < 0) {
        divergenciaLabel.textContent = "Não está ok";
        divergenciaLabel.style.color = "red";
      } else {
        divergenciaLabel.textContent = "OK";
        divergenciaLabel.style.color = "green";
      }
    }

    pesoEtiquetaInput.addEventListener('input', atualizarDivergencia);
    pesoBalancaInput.addEventListener('input', atualizarDivergencia);

    const canvas = document.getElementById('signature-pad');
    const signaturePad = new SignaturePad(canvas);

    function clearSignature() {
      signaturePad.clear();
    }

    function handleSubmit() {
      if (signaturePad.isEmpty()) {
        alert("Por favor, assine antes de confirmar.");
        return false;
      }

      const etiqueta = parseFloat(pesoEtiquetaInput.value.replace(',', '.')) || 0;
      const balanca = parseFloat(pesoBalancaInput.value.replace(',', '.')) || 0;
      const diferenca = etiqueta - balanca;

      document.getElementById('divergencia_oculto').value = diferenca.toFixed(2); // ← valor numérico

      const assinatura = signaturePad.toDataURL();
      document.getElementById('assinatura_base64').value = assinatura;

      return true;
    }

    function saveSignature() {
      document.getElementById('divergencia_oculto').value = divergenciaLabel.textContent;

      if (signaturePad.isEmpty()) {
        alert("Por favor, assine antes de confirmar.");
        return false;
      }

      const assinatura = signaturePad.toDataURL();
      document.getElementById('assinatura_base64').value = assinatura;
      return true;
    }

    function fecharModal() {
      document.getElementById("sucessoModal").style.display = "none";
    }
  </script>

<?php if ($registro_inserido): ?>
<script>
  document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("sucessoModal").style.display = "flex";
  });
</script>
<?php endif; ?>

</body>
</html>