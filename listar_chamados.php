<?php
session_start();
include_once("config.php"); // $conn = new mysqli(...)

if (!isset($_SESSION['id_usuario'])) {
  header("Location: index.php");
  exit;
}

$sql = "SELECT id, titulo, descricao, quem_abriu, LOWER(`status`) AS status, criado_em
        FROM chamados
        ORDER BY id DESC";
$res = $conn->query($sql);
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Meus Chamados</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
  <style>
    body{font-family:'Poppins',sans-serif;background:#e9eef1;margin:0;padding:20px}
    .container{max-width:820px;margin:auto}
    h1{font-size:24px;text-align:center;margin-bottom:20px;color:#000}
    .accordion{
      background:#fff; cursor:pointer; padding:16px; width:100%; border:none;
      text-align:left; outline:none; font-size:16px; border-radius:10px;
      box-shadow:0 2px 4px rgba(0,0,0,.1); margin-bottom:10px;
      display:flex; justify-content:space-between; align-items:center; gap:12px;
      transition: background-color 0.3s ease;
    }
    .accordion:hover{background:#f9f9f9}
    .accordion.finalizado{background-color:#c6f6c6;} /* verde claro */
    .titulo{font-weight:600; overflow:hidden; white-space:nowrap; text-overflow:ellipsis}
    .badge{font-size:12px; padding:4px 8px; border-radius:999px; background:#eef2ff}
    .badge.finalizado{background:#2ecc71; color:white;}
    .seta{font-size:20px; transition:transform .25s}
    .accordion.active .seta{transform:rotate(180deg)}
    .panel{
      display:none; background:#fff; padding:0 16px 16px; border-radius:0 0 10px 10px;
      box-shadow:0 2px 4px rgba(0,0,0,.1); margin:-8px 0 14px;
    }
    .panel p{margin:12px 4px; line-height:1.5}
    .muted{color:#666; font-size:13px}
    .btn-concluir{
      margin:12px 4px; padding:8px 14px; background-color:#2ecc71; color:white;
      border:none; border-radius:8px; cursor:pointer; font-weight:bold;
    }
    .btn-concluir:hover{background-color:#27ae60}

    /* Botão voltar centralizado */
    .botao-voltar {
      text-align: center;
      margin-top: 30px;
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
    <h1>Meus Chamados</h1>

    <?php if ($res && $res->num_rows): ?>
      <?php while($c = $res->fetch_assoc()): ?>
        <?php
          $titulo = htmlspecialchars($c['titulo'] ?? '');
          $desc   = nl2br(htmlspecialchars($c['descricao'] ?? ''));
          $quem   = htmlspecialchars($c['quem_abriu'] ?? '');
          $status = strtolower($c['status'] ?? 'aberto');
          $finalizadoClass = $status === 'finalizado' ? 'finalizado' : '';
          $quando = $c['criado_em'] ? date('d/m/Y H:i', strtotime($c['criado_em'])) : '';
        ?>
        <button class="accordion <?= $finalizadoClass ?>" data-id="<?= $c['id'] ?>">
          <span class="titulo">#<?= (int)$c['id'] ?> — <?= $titulo ?></span>
          <span class="badge <?= $finalizadoClass ?>"><?= $status ?></span>
          <span class="seta">⌄</span>
        </button>
        <div class="panel">
          <p><strong>Descrição:</strong><br><?= $desc ?></p>
          <p><strong>Quem abriu:</strong> <?= $quem ?></p>
          <?php if ($quando): ?><p class="muted">Criado em: <?= $quando ?></p><?php endif; ?>

          <?php if ($status !== 'finalizado'): ?>
            <button class="btn-concluir" data-id="<?= $c['id'] ?>">Concluído</button>
          <?php endif; ?>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p>Nenhum chamado encontrado.</p>
    <?php endif; ?>

    <!-- Botão Voltar -->
    <div class="botao-voltar">
      <button onclick="window.location.href='home_chamados.php';">&lt; Voltar</button>
    </div>
  </div>
  
<script>
    document.querySelectorAll(".btn-concluir").forEach(btn=>{
        btn.addEventListener("click", async (e)=>{
        e.stopPropagation();

        const id = btn.getAttribute("data-id");
        if(!confirm("Marcar chamado como finalizado?")) return;

        const resp = await fetch("finalizar_chamado.php", {
            method: "POST",
            headers: {"Content-Type":"application/x-www-form-urlencoded"},
            body: "id="+encodeURIComponent(id)
        });
        const data = await resp.json();

        if (data.ok) {
            const acc = document.querySelector(`.accordion[data-id='${id}']`);
            const badge = acc.querySelector(".badge");
            acc.classList.add("finalizado");
            badge.textContent = "finalizado";
            badge.classList.add("finalizado");
            btn.style.display = "none";
            return; // não mostra o alert de erro
        }

        alert(
            "Não finalizou.\n" +
            "linhas_afetadas: " + (data.linhas_afetadas ?? 'null') + "\n" +
            "status_atual: " + (data.status_atual ?? 'null') + "\n" +
            "db: " + (data.db ?? 'null')
        );
        });
    });
  // Abre/fecha accordion
  document.querySelectorAll(".accordion").forEach(acc=>{
    acc.addEventListener("click",()=>{
      acc.classList.toggle("active");
      const panel = acc.nextElementSibling;
      panel.style.display = panel.style.display === "block" ? "none" : "block";
    });
  });
  
</script>

</body>
</html>
