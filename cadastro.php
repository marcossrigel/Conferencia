<?php
include_once("config.php"); 

$cadastro_sucesso = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $cpf = $_POST['cpf'];
    $tipo = 'fornecedor';

    $sql = "INSERT INTO usuarios (nome, cpf, tipo) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Erro ao preparar a query: " . $conn->error);
    }

    $stmt->bind_param("sss", $nome, $cpf, $tipo);

    if ($stmt->execute()) {
        $cadastro_sucesso = true;
    }

    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cadastro SICAF</title>
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
    }

    .container {
      background-color: #fff;
      border-radius: 15px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      padding: 30px;
      max-width: 500px;
      width: 100%;
    }

    .voltar-link {
      text-align: center;
      margin-top: 20px;
    }

    .btn-voltar {
      display: inline-block;
      background-color: #4da6ff;
      color: white;
      padding: 10px 20px;
      text-decoration: none;
      font-weight: bold;
      border-radius: 8px;
      transition: background-color 0.3s ease;
    }

    .btn-voltar:hover {
      background-color: #3399ff;
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
    input[type="email"],
    input[type="password"],
    input[type="number"] {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 15px;
      box-sizing: border-box;
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
      background-color: #42b72a; /* tom de verde suave */
      border: none;
      border-radius: 10px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    .button-group button:hover {
      background-color: #36a420; /* tom mais escuro ao passar o mouse */
    }

    .cancelar-link {
      text-align: center;
      margin-top: 15px;
    }

    .cancelar-link a {
      color: red;
      text-decoration: none;
      font-weight: bold;
      font-size: 15px;
      transition: color 0.3s ease;
    }

    .cancelar-link a:hover {
      color: darkred;
    }

    .modal {
      display: none;
      position: fixed;
      z-index: 999;
      left: 0;
      top: 0;
      width: 100vw;
      height: 100vh;
      background-color: rgba(0,0,0,0.5);
      justify-content: center;
      align-items: center;
    }

    .modal-content {
      background-color: white;
      padding: 30px;
      border-radius: 15px;
      text-align: center;
      max-width: 400px;
      width: 90%;
    }

    .modal-content h2 {
      color: green;
      margin-bottom: 20px;
    }

    .modal-content a {
      display: inline-block;
      padding: 10px 20px;
      background-color: #4da6ff;
      color: white;
      text-decoration: none;
      border-radius: 8px;
      font-weight: bold;
    }

    .modal-content a:hover {
      background-color: #3399ff;
    }

    @media (max-width: 480px) {
      .container {
        padding: 20px;
      }

      .main-title {
        font-size: 20px;
      }

      .button-group button {
        width: 100%;
      }
    }
  </style>
</head>
<body>

<?php if ($cadastro_sucesso): ?>
  <div class="modal" id="successModal" style="display: flex;">
    <div class="modal-content">
      <h2>Fornecedor cadastrado com sucesso!</h2>
      <a href="index.php">Voltar ao Login</a>
    </div>
  </div>
<?php endif; ?>

<div class="container">
  <div class="main-title">Cadastro de Usuário</div>

  <form method="post" action="cadastro.php">
    
    <label>Nome</label>
    <input type="text" name="nome" required>

    <label>CPF</label>
    <input type="text" name="cpf" required>

    <div class="button-group">
      <button type="submit">Criar Conta</button>
    </div>

    <div class="voltar-link">
      <a href="index.php" class="btn-voltar">< Voltar</a>
    </div>

  </form>


</div>

</body>
</html>
