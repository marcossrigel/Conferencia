<?php

session_start();
include("config.php"); // conexão com o banco

$registro_inserido = false;
$nome_fornecedor = '';

// Buscar o nome do usuário logado se ele for fornecedor
if (isset($_SESSION['id_usuario'])) {
    $id = $_SESSION['id_usuario'];
    $sql = "SELECT nome FROM usuarios WHERE id = ? AND tipo = 'fornecedor'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $nome_fornecedor = $row['nome'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Dados principais
    $id_fornecedor = $_SESSION['id_usuario'] ?? null;
    $fornecedor = $_POST['fornecedor'] ?? '';

    $produto = $_POST['produto'] ?? '';
    $quantidade = $_POST['quantidade'] ?? 0;
    $peso_etiqueta = $_POST['peso_etiqueta'] ?? 0;
    $tara = $_POST['tara'] ?? 0;
    $peso_balanca = $_POST['peso_balanca'] ?? 0;
    $peso_liquido = $_POST['peso_liquido'] ?? 0;
    $diferenca = $_POST['diferenca'] ?? 0;
    $divergencia = $_POST['divergencia'] ?? '';
    $observacoes = $_POST['observacoes'] ?? '';

    if (!$id_fornecedor) {
        die("Usuário não autenticado.");
    }

    // Upload da foto
    $foto_nome = '';
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $foto_nome = 'foto_' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['foto']['tmp_name'], 'uploads/' . $foto_nome);
    }

    // Processa assinatura (base64)
    $assinatura_base64 = $_POST['assinatura_base64'] ?? '';
    $assinatura_nome = '';
    if (!empty($assinatura_base64)) {
        $partes = explode(',', $assinatura_base64);
        if (count($partes) === 2) {
            $imagem_binaria = base64_decode($partes[1]);
            $assinatura_nome = 'assinatura_' . uniqid() . '.png';
            file_put_contents('uploads/' . $assinatura_nome, $imagem_binaria);
        }
    }

    // Inserir no banco
    $sql = "INSERT INTO entregas (
        id_fornecedor, nome, produto, quantidade,
        peso_etiqueta, tara, peso_balanca, peso_liquido,
        diferenca, divergencia, observacoes, foto, assinatura
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issidddddssss",
        $id_fornecedor, $fornecedor, $produto, $quantidade,
        $peso_etiqueta, $tara, $peso_balanca, $peso_liquido,
        $diferenca, $divergencia, $observacoes, $foto_nome, $assinatura_nome
    );

    if ($stmt->execute()) {
        $registro_inserido = true;
    } else {
        echo "Erro ao inserir: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
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

      <p><strong>Nome:</strong> <?= htmlspecialchars($nome_fornecedor) ?></p>

      <label>Fornecedor</label>
      <input type="text" name="fornecedor" placeholder="Nome do fornecedor">

      <label>Produto</label>
      <input type="text" name="produto" placeholder="Nome do produto">

      <label>Quantidade Pedida</label>
      <input type="number" name="quantidade">

      <div class="row">

        <div class="col">
          <label>Peso da Etiqueta</label>
          <input type="number" name="peso_etiqueta" id="peso_etiqueta" step="0.01">
        </div>

        <div class="col">
          <label>tara</label>
          <input type="number" name="tara" id="tara">
        </div>
      </div>

      <div class="row">

        <div class="col">
          <label>Peso da Balança</label>
          <input type="number" name="peso_balanca" id="peso_balanca" step="0.01">
        </div>

      </div>

      <label>Diferença (Calculada)</label>
        <div id="diferenca_exibida">0,00</div>
        <input type="hidden" name="peso_liquido" id="peso_liquido_oculto">

      <input type="hidden" id="diferenca_oculta">
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
    const pesoLiquidoInput = document.getElementById('peso_liquido_oculto');
    const divergenciaLabel = document.getElementById('divergencia');

    function atualizarDivergencia() {
      const etiqueta = parseFloat(pesoEtiquetaInput.value.replace(',', '.')) || 0;
      const balanca = parseFloat(pesoBalancaInput.value.replace(',', '.')) || 0;
      const tara = parseFloat(taraInput.value.replace(',', '.')) || 0;

      const liquido = balanca - tara;
      const diferenca = etiqueta - balanca - tara;

      // Atualiza campo oculto de peso líquido
      const pesoLiquidoInput = document.getElementById('peso_liquido_oculto');
      pesoLiquidoInput.value = liquido.toFixed(2);

      // Atualiza exibição de diferença
      const divDif = document.getElementById('diferenca_exibida');
      divDif.textContent = diferenca.toFixed(2).replace('.', ',');

      // Atualiza campo oculto para envio no formulário
      const diferencaOcultaInput = document.getElementById('diferenca_oculta');
      diferencaOcultaInput.value = diferenca.toFixed(2);

      // Atualiza status de divergência
      const divergenciaLabel = document.getElementById('divergencia');
      if (diferenca > 0) {
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

      atualizarDivergencia();

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

    pesoEtiquetaInput.addEventListener('input', atualizarDivergencia);
    pesoBalancaInput.addEventListener('input', atualizarDivergencia);
    taraInput.addEventListener('input', atualizarDivergencia);

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