<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Conferência de Entrega</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
  <style>
    body {
      background-color: #f0f2f5;
      font-family: 'Poppins', sans-serif;
      display: flex;
      justify-content: center;
      padding: 20px;
    }
    .container {
      background-color: white;
      padding: 20px 30px;
      border-radius: 10px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      max-width: 480px;
      width: 100%;
      box-sizing: border-box;
    }
    h2 {
      text-align: center;
      margin-bottom: 20px;
      font-weight: 500;
    }
    label {
      display: block;
      margin-top: 15px;
      color: #242424ff;
      font-weight: 400;
      font-size: 15px;
    }
    input, textarea, button {
      width: 100%;
      padding: 12px;
      margin-top: 5px;
      border: 1px solid #ccc;
      border-radius: 5px;
      font-family: 'Poppins', sans-serif;
      font-size: 15px;
      box-sizing: border-box;
    }
    button {
      background-color: #007bff;
      color: white;
      border: none;
      font-weight: 500;
      cursor: pointer;
      margin-top: 15px;
      transition: background-color 0.3s ease;
    }
    button:hover {
      background-color: #0056b3;
    }
    .btn-red {
      background-color: #dc3545;
    }
    .btn-red:hover {
      background-color: #c82333;
    }

    @media (max-width: 500px) {
      .container {
        padding: 20px 15px;
      }
      input, textarea, button {
        font-size: 14px;
        padding: 10px;
      }
    }
  </style>
</head>
<body>
  <form class="container" method="post" action="salvar_entrega.php" enctype="multipart/form-data">
    <h2>Conferência de Entrega</h2>

    <!-- Campos ocultos -->
    <input type="hidden" name="etiquetas" id="inputEtiquetas">
    <input type="hidden" name="pesos_liquidos" id="inputPesosLiquidos">
    <input type="hidden" name="total_etiquetas" id="hiddenTotalEtiquetas">
    <input type="hidden" name="total_balanca" id="hiddenTotalBalanca">
    <input type="hidden" name="campoDiferenca" id="hiddenDiferenca">
    <input type="hidden" name="campoDivergencia" id="hiddenDivergencia">

    <!-- Campos visíveis -->
    <label>Fornecedor</label>
    <input type="text" name="fornecedor" required>

    <label>Número da Nota Fiscal</label>
    <input type="text" name="nota_fiscal" required>

    <label>Produto</label>
    <input type="text" name="produto">

    <label>Quantidade a Receber (NF)</label>
    <input type="text" name="quant_nf" inputmode="decimal" pattern="[0-9]*[.,]?[0-9]*">

    <div id="etiquetasContainer">
      <label>Peso das Etiquetas</label>
      <input type="text" class="etiqueta" placeholder="Ex: 12,5" inputmode="decimal" pattern="[0-9]*[.,]?[0-9]*">
    </div>

    <label>Peso Líquido da Balança</label>
    <input type="text" id="pesoBalanca" inputmode="decimal" pattern="[0-9]*[.,]?[0-9]*">

    <button type="button" onclick="adicionarCampoEtiqueta()">+ Adicionar</button>
    <button type="button" onclick="salvarResultadoParcial()">Salvar Resultado Parcial</button>

    <label>Total acumulado etiquetas</label>
    <input type="text" id="totalEtiquetas" readonly value="0,00 kg">

    <label>Total acumulado Peso Líquido</label>
    <input type="text" id="totalBalanca" readonly value="0,00 kg">

    <label>Número de Volumes</label>
    <input type="text" name="num_volumes" inputmode="numeric" pattern="[0-9]*">

    <label>Tara por Volume</label>
    <input type="text" name="tara_volume" inputmode="decimal" pattern="[0-9]*[.,]?[0-9]*">

    <label>Diferença (Peso Líquido - Total Etiquetas)</label>
    <input type="text" id="campoDiferenca" readonly>

    <label>Divergência</label>
    <input type="text" id="campoDivergencia" readonly>

    <label>Observações</label>
    <textarea name="observacoes" rows="3" placeholder="Digite aqui..." style="resize: vertical;"></textarea>

    <label>Foto</label>
    <input type="file" name="foto">

    <div class="form-group">
      <label for="nome_completo">Nome Completo do Responsável:</label>
      <input type="text" id="nome_completo" name="nome_completo" required>
    </div>
    
    <button type="submit">Confirmar Entrega</button>
    <button type="button" style="margin-top: 10px;" onclick="window.location.href='home.php'">&lt; Voltar</button>

  </form>

  <script>
    let etiquetas = [];
    let balancas = [];

    function parseNumero(valor) {
      return parseFloat(valor.replace(",", "."));
    }

    function formatarNumero(num) {
      return num.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function adicionarCampoEtiqueta() {
      const container = document.getElementById('etiquetasContainer');
      const novoInput = document.createElement('input');
      novoInput.type = 'text';
      novoInput.className = 'etiqueta';
      novoInput.placeholder = 'Ex: 12,5';
      novoInput.inputMode = 'decimal';
      novoInput.pattern = '[0-9]*[.,]?[0-9]*';
      novoInput.style.marginTop = '5px';
      container.appendChild(novoInput);
    }

    function salvarResultadoParcial() {
      // Coletar etiquetas
      const inputsEtiquetas = document.querySelectorAll('.etiqueta');
      inputsEtiquetas.forEach(input => {
        const valor = parseNumero(input.value);
        if (!isNaN(valor)) etiquetas.push(valor);
      });

      // Coletar peso da balança
      const valorBalanca = parseNumero(document.getElementById('pesoBalanca').value);
      const taraVolume = parseNumero(document.querySelector('[name="tara_volume"]').value) || 0;

      if (!isNaN(valorBalanca)) {
        const pesoLiquido = valorBalanca - taraVolume;
        balancas.push(pesoLiquido);
      }

      // Cálculo total
      const totalEtiquetas = etiquetas.reduce((a, b) => a + b, 0);
      const totalBalanca = balancas.reduce((a, b) => a + b, 0);
      const diferenca = totalBalanca - totalEtiquetas;

      // Exibição nos campos de visualização
      document.getElementById('totalEtiquetas').value = formatarNumero(totalEtiquetas) + " kg";
      document.getElementById('totalBalanca').value = formatarNumero(totalBalanca) + " kg";
      document.getElementById('campoDiferenca').value = formatarNumero(diferenca) + " kg";
      document.getElementById('campoDivergencia').value = diferenca < 0 ? "⚠️ Há divergência" : "OK";
      document.getElementById('campoDivergencia').style.color = diferenca < 0 ? "red" : "green";

      // Atualizar campos ocultos para envio
      document.getElementById('inputEtiquetas').value = JSON.stringify(etiquetas);
      document.getElementById('inputPesosLiquidos').value = JSON.stringify(balancas);
      document.getElementById('hiddenTotalEtiquetas').value = totalEtiquetas;
      document.getElementById('hiddenTotalBalanca').value = totalBalanca;
      document.getElementById('hiddenDiferenca').value = diferenca.toFixed(2);
      document.getElementById('hiddenDivergencia').value = diferenca < 0 ? "Divergente" : "OK";

      // Reset visual dos campos
      document.getElementById('etiquetasContainer').innerHTML = `
      <label>Peso das Etiquetas</label>
      <input type="text" class="etiqueta" placeholder="Ex: 12,5" inputmode="decimal" pattern="[0-9]*[.,]?[0-9]*">
    `;

      document.getElementById('pesoBalanca').value = '';
        console.log("Vetor de etiquetas:", etiquetas);
        console.log("Vetor de pesos líquidos:", balancas);
        console.log("JSON etiquetas:", JSON.stringify(etiquetas));
        console.log("JSON pesos líquidos:", JSON.stringify(balancas));
      }
      
  </script>
</body>
</html>