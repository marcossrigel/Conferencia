<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function validarCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) return false;

    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) return false;
    }

    return true;
}

session_start();
include_once("config.php");

$erro_login = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cpf = $_POST['cpf'] ?? '';

    if (!validarCPF($cpf)) {
        $erro_login = "CPF inválido.";
    } else {
        $query = "SELECT * FROM usuarios WHERE cpf = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $cpf);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 1) {
            $user = $resultado->fetch_assoc();
            $_SESSION['id_usuario'] = $user['id'];
            $_SESSION['nome'] = $user['nome'];
            $_SESSION['tipo_usuario'] = $user['tipo'];

            header("Location: home.php");
            exit;
        } else {
            $erro_login = "CPF não encontrado.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Monitoramento Creches</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

  <style>
    :root {
      --color-white: #ffffff;
      --color-gray: #e3e8ec;
      --color-dark: #1d2129;
      --color-blue: #4da6ff;
      --color-green: #42b72a;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background-color: var(--color-gray);
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      flex-direction: column;
    }

    .container {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
    }

    .login-container {
      background: var(--color-white);
      padding: 40px 30px;
      border-radius: 15px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 350px;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .main-title {
      font-size: 28px;
      font-weight: 600;
      color: var(--color-dark);
      text-align: center;
      margin-bottom: 30px;
    }

    .login-form {
      width: 100%;
      display: flex;
      flex-direction: column;
    }

    .login-form input {
      padding: 12px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 16px;
      outline: none;
    }

    .btn {
      width: 100%;
      padding: 12px;
      font-size: 17px;
      font-weight: bold;
      color: white;
      border: none;
      border-radius: 10px;
      cursor: pointer;
      margin-top: 10px;
      transition: background 0.3s;
    }

    .btn-entrar {
      background-color: var(--color-blue);
    }

    .btn-entrar:hover {
      background-color: #3399ff;
    }

    .btn-create {
      background-color: var(--color-green);
    }

    .btn-create:hover {
      background-color: #36a420;
    }

    .forgot-password {
      font-size: 14px;
      color: var(--color-blue);
      text-decoration: none;
      margin-top: 12px;
      text-align: center;
    }

    .forgot-password:hover {
      text-decoration: underline;
    }

    .divider {
      width: 100%;
      height: 1px;
      background: #dddfe2;
      margin: 20px 0;
    }

    @media (max-width: 480px) {
      .login-container {
        padding: 30px 20px;
      }

      .main-title {
        font-size: 22px;
      }

      .btn {
        font-size: 16px;
      }

      .login-form input {
        font-size: 15px;
      }
    }
  </style>
</head>

<body>

  
  <div class="container">
    <img src="logo.jpg" style="width: 120px; margin-bottom: 20px;" alt="Logo da Masterboi">
    <div class="login-container">
      <div class="main-title">Entrar</div>
      
      <form class="login-form" method="post">
        <input type="text" id="cpf" name="cpf" placeholder="CPF" required>
        <div class="divider"></div>
        <button type="submit" class="btn btn-entrar">Entrar</button>
      </form>


      <a href="cadastro.php" class="btn btn-create" style="text-align: center; text-decoration: none;">Criar uma conta</a>
      <a href="#" class="forgot-password">Esqueceu a conta?</a>

    </div>
    <?php if (!empty($erro_login)): ?>
      <div style="color: red; text-align: center; margin-bottom: 10px;">
        <?php echo $erro_login; ?>
      </div>
    <?php endif; ?>

  </div>


</body>
</html>
