<?php
session_start();
require_once 'configF.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['email'])) {
    header("Location: tela1.php"); // Redireciona para a página de login
    exit();
}

// Conexão com o banco de dados
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Obtém o email do usuário logado
$email_usuario = $_SESSION['email'];

// Busca o ID do usuário logado
$sql_usuario = "SELECT id_usuario, nome_usuario FROM usuarios WHERE email = ?";
$stmt_usuario = $conn->prepare($sql_usuario);
$stmt_usuario->bind_param("s", $email_usuario);
$stmt_usuario->execute();
$result_usuario = $stmt_usuario->get_result();

if ($result_usuario->num_rows === 0) {
    die("Usuário não encontrado.");
}

$usuario = $result_usuario->fetch_assoc();
$id_usuario = $usuario['id_usuario'];
$nome_usuario = $usuario['nome_usuario'];
$stmt_usuario->close();

// Consulta para obter todas as vendas associadas ao usuário (caixa aberto e fechado)
$sql_vendas = "
    SELECT 
        v.dia, 
        v.forma_pagamento,
        CASE 
            WHEN iv.tipo = 'produto' THEN p.nomeProduto
            WHEN iv.tipo = 'servico' THEN s.nomeServico
        END AS item,
        iv.preco,
        iv.desconto,
        v.id_caixa
    FROM venda v
    INNER JOIN itens_venda iv ON v.id_venda = iv.id_venda
    LEFT JOIN produto p ON iv.tipo = 'produto' AND iv.id_item = p.id_produto
    LEFT JOIN servicos s ON iv.tipo = 'servico' AND iv.id_item = s.id_servico
    WHERE v.id_usuario = ?
    ORDER BY v.dia DESC;
";

$stmt_vendas = $conn->prepare($sql_vendas);
$stmt_vendas->bind_param("i", $id_usuario);
$stmt_vendas->execute();
$result_vendas = $stmt_vendas->get_result();

// Consulta o total das vendas do último caixa fechado
$sql_caixa = "SELECT id_caixa, data_abertura, data_fechamento FROM caixa WHERE status = 'fechado' ORDER BY data_fechamento DESC LIMIT 1";
$result_caixa = $conn->query($sql_caixa);
$total_vendas = 0;
$data_abertura = null;
$data_fechamento = null;

if ($result_caixa->num_rows > 0) {
    $caixa = $result_caixa->fetch_assoc();
    $id_caixa_fechado = $caixa['id_caixa'];
    $data_abertura = $caixa['data_abertura'];
    $data_fechamento = $caixa['data_fechamento'];

    $sql_total = "SELECT SUM(iv.preco - (iv.preco * iv.desconto / 100)) AS total 
                  FROM itens_venda iv 
                  INNER JOIN venda v ON iv.id_venda = v.id_venda 
                  WHERE v.id_caixa = ?";
    $stmt_total = $conn->prepare($sql_total);
    $stmt_total->bind_param("i", $id_caixa_fechado);
    $stmt_total->execute();
    $result_total = $stmt_total->get_result();
    $total_vendas = $result_total->fetch_assoc()['total'];
    $stmt_total->close();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Vendas</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@400;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Montserrat', sans-serif;
        }

        body {
            background: linear-gradient(to right, #e2e2e2, #c9d6ff);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            flex-direction: column;
        }

        h1, h2 {
            color: #512da8;
            text-align: center;
        }

        table {
            width: 80%;
            max-width: 900px;
            margin: 20px auto;
            border-collapse: collapse;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
            font-family: 'Roboto Mono', monospace;
        }

        th {
            background-color: #512da8;
            color: white;
        }

        td {
            font-size: 14px;
        }

        tr:hover {
            background-color: #f2f2f2;
        }

        h2 {
            margin-top: 20px;
        }

        a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            text-align: center;
            color: white;
            background-color: #512da8;
            border-radius: 5px;
            text-decoration: none;
        }

        a:hover {
            background-color: #311b92;
        }
    </style>
</head>
<body>
    <h1>Consulta de Vendas - Bem-vindo, <?= htmlspecialchars($nome_usuario) ?></h1>

    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Forma de Pagamento</th>
                <th>Item</th>
                <th>Preço</th>
                <th>Desconto</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($venda = $result_vendas->fetch_assoc()): ?>
                <tr>
                    <td><?= date("d/m/Y H:i:s", strtotime($venda['dia'])) ?></td>
                    <td><?= htmlspecialchars($venda['forma_pagamento']) ?></td>
                    <td><?= htmlspecialchars($venda['item'] ?? "Não especificado") ?></td>
                    <td>R$ <?= number_format($venda['preco'], 2, ',', '.') ?></td>
                    <td><?= number_format($venda['desconto'], 2, ',', '.') ?>%</td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <?php if ($data_abertura && $data_fechamento): ?>
        <h2>Caixa Fechado:</h2>
        <p>Data de Abertura: <?= date("d/m/Y H:i:s", strtotime($data_abertura)) ?></p>
        <p>Data de Fechamento: <?= date("d/m/Y H:i:s", strtotime($data_fechamento)) ?></p>
        <h2>Total das Vendas no Último Caixa Fechado: R$ <?= number_format($total_vendas, 2, ',', '.') ?></h2>
    <?php else: ?>
        <h2>Não há registros de fechamento de caixa.</h2>
    <?php endif; ?>

    <a href="tela3.php">Voltar</a>
</body>
</html>

<?php 
$stmt_vendas->close();
$conn->close();
?>
