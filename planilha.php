<?php
$nome_iniciativa = "Movimentação ";
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Movimentação</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: #eef2f3;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 960px;
            margin: 20px auto;
            padding: 15px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 1.6em;
            color: #333;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            min-width: 600px;
        }

        th {
            background: #3399ff;
            color: white;
            padding: 10px;
            font-size: 0.95em;
        }

        td {
            border: 1px solid #ccc;
            padding: 8px;
        }

        input[type="text"],
        input[type="date"] {
            width: 100%;
            padding: 8px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 0.95em;
        }

        input[name="quantidade_producao[]"],
        input[name="quantidade_produzida[]"] {
            max-width: 90px;
            margin: auto;
        }

        .buttons {
            text-align: center;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
        }

        .buttons button {
            padding: 10px 18px;
            border: none;
            background: #3399ff;
            color: white;
            border-radius: 6px;
            font-size: 0.95em;
            cursor: pointer;
            transition: background 0.3s;
        }

        .buttons button:hover {
            background: #237acc;
        }

        @media (max-width: 600px) {
            h2 {
                font-size: 1.3em;
            }

            th, td {
                font-size: 13px;
                padding: 6px;
            }

            input[type="text"],
            input[type="date"] {
                font-size: 0.85em;
            }

            input[name="quantidade_producao[]"],
            input[name="quantidade_produzida[]"] {
                max-width: 70px;
            }

            .buttons button {
                width: 100%;
                font-size: 1em;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h2><?php echo htmlspecialchars($nome_iniciativa); ?>Para Produção Loja</h2>

    <form method="post" action="salvar_movimentacoes.php">
        <div class="table-wrapper">
            <table id="movimentacao">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Produto P/ Produção</th>
                        <th>Quantidade</th>
                        <th>Produto Produzido</th>
                        <th>Quantidade</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><input type="date" name="data[]"></td>
                        <td><input type="text" name="produto_producao[]"></td>
                        <td><input type="text" name="quantidade_producao[]"></td>
                        <td><input type="text" name="produto_produzido[]"></td>
                        <td><input type="text" name="quantidade_produzida[]"></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="buttons">
            <button type="button" onclick="adicionarLinha()">Adicionar Linha</button>
            <button type="button" onclick="removerLinha()">Excluir Linha</button>
            <button type="button" onclick="window.location.href='home_sistemas.php'">< Voltar</button>
            <button type="submit" name="salvar">Salvar</button>
        </div>
    </form>
</div>

<script>
function adicionarLinha() {
    const tabela = document.getElementById('movimentacao').getElementsByTagName('tbody')[0];
    const novaLinha = tabela.insertRow();

    const campos = [
        { name: 'data[]', type: 'date' },
        { name: 'produto_producao[]', type: 'text' },
        { name: 'quantidade_producao[]', type: 'text' },
        { name: 'produto_produzido[]', type: 'text' },
        { name: 'quantidade_produzida[]', type: 'text' }
    ];

    campos.forEach(campo => {
        const celula = novaLinha.insertCell();
        const input = document.createElement('input');
        input.type = campo.type;
        input.name = campo.name;
        celula.appendChild(input);
    });
}

function removerLinha() {
    const tabela = document.getElementById('movimentacao').getElementsByTagName('tbody')[0];
    if (tabela.rows.length > 1) {
        tabela.deleteRow(-1);
    }
}
</script>

</body>
</html>
