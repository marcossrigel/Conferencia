<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Home - Sistema de Monitoramento</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<style>
:root {
  --color-white: #ffffff;
  --color-gray: #e3e8ec;
  --color-dark: #1d2129;
  --color-green: #42b72a;
  --color-green-hover: #36a420;
  --color-gray-dark: #6c757d;
  --color-gray-hover: #5a6268;
}

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  background-color: var(--color-gray);
  font-family: 'Poppins', sans-serif;
  padding: 20px;
  display: flex;
  justify-content: center;
  min-height: 100vh;
}

.container {
  width: 90%;
  max-width: 1200px;
  background-color: var(--color-white);
  padding: 40px;
  border-radius: 15px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  margin: auto;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
  flex-wrap: wrap;
}

.header-text {
  flex: 1;
  min-width: 250px;
}

.header-text h1 {
  font-size: 28px;
  font-weight: 700;
  color: var(--color-dark);
  margin-bottom: 10px;
}

.header-text p {
  font-size: 16px;
  color: #666;
}

.button-group {
  display: flex;
  flex-direction: column;
  gap: 10px;
  min-width: 200px;
  align-items: flex-end;
}

.btn {
  background-color: var(--color-green);
  color: var(--color-white);
  text-decoration: none;
  padding: 14px 20px;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 600;
  text-align: center;
  transition: background 0.3s;
  width: 200px;
  display: inline-block; 
  border: none;          
  outline: none;
  cursor: pointer; 
}

.btn:hover {
  background-color: var(--color-green-hover);
}

.btn-secondary {
  background-color: var(--color-gray-dark);
}

.btn-secondary:hover {
  background-color: var(--color-gray-hover);
}

.btn-sair {
  text-align: center;
  display: inline-block;
  color: var(--color-blue);
  display: inline-block;
  font-size: 14px;
  margin-top: 20px auto;
  display: block;
}

.accordion {
  border-top: 1px solid #ccc;
  padding-top: 20px;
  margin-top: 30px;
  cursor: pointer;
}

.accordion-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.accordion-header h2 {
  font-size: 18px;
  font-weight: 600;
  color: var(--color-dark);
}

.accordion-content {
  margin-top: 15px;
  text-align: left;
  color: #555;
  font-size: 14px;
}

.hidden {
  display: none;
}

.hidden {
  display: none;
}

.button-group a.texto-login {
  align-self: center;
  margin-top: 10px;
}

.texto-login {
  text-align: center;
  display: inline-block;
  color: var(--color-blue);
  display: inline-block;
  font-size: 14px;
  margin-top: 20px auto;
  display: block;
}

a.texto-login{
  color: red;               
  text-decoration: none;  
  font-weight: bold;
}

a.texto-login:hover {
  text-decoration: none;
}

@media (max-width: 600px) {
  body {
    padding: 20px;
    align-items: flex-start;
  }

  .container {
    padding: 20px;
  }

  .header {
    flex-direction: column;
    align-items: flex-start;
    gap: 20px;
  }

  .header-text h1 {
    font-size: 22px;
  }

  .header-text p {
    font-size: 14px;
  }

  .button-group {
    width: 100%;
    align-items: center;
  }

  .btn {
    width: 100%;
    font-size: 15px;
  }

  .accordion-header h2 {
    font-size: 16px;
  }

  .accordion-content {
    font-size: 13px;
  }

  .texto-login {
    font-size: 13px;
  }
}

</style>

</head>
<body>

<div class="container">
  
  <div class="header">
    <div class="header-text">
    <img src="logo.png" style="width: 120px; margin-bottom: 20px;" alt="Logo da Masterboi">  
      <h1>Conferencia de Entregas</h1>
      <p>Organize e cadastre suas informações com eficiência e facilidade.</p>
    </div>

    <div class="button-group">
      <a href="formulario.php" class="btn">Entregas</a>
      <a href="visualizar.php" class="btn btn-secondary">Minhas Entregas</a>
      <a href="index.php" class="texto-login">Sair</a>
    </div>
  </div>

  <div class="accordion" onclick="toggleAccordion()">
    <div class="accordion-header">
      <h2>Ajuda</h2>
      <span id="accordion-icon">⌄</span>
    </div>
    <div id="accordion-content" class="accordion-content hidden">
      <p>Clique no botao de "Entregas" para fornecer seus dados de conferência.</p>
      <p>No botão "Minhas Entregas" é possível visualizar os dados cadastrados anteriormente.</p>
    </div>
  </div>

</div>

<script>
  
  function toggleAccordion() {
    const content = document.getElementById('accordion-content');
    const icon = document.getElementById('accordion-icon');
    content.classList.toggle('hidden');
    icon.textContent = content.classList.contains('hidden') ? '⌄' : '⌃';
  }

</script>

</body>
</html>