<?php
session_start();
require_once 'configF.php';

// Verifica se o cliente está logado
if (!isset($_SESSION['id_cliente'])) {
    header("Location: loginCliente.php");
    exit();
}

// Conecta ao banco de dados
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

echo "Bem-vindo, " . htmlspecialchars($_SESSION['nome_cliente']);

// Verifica se o ID da empresa está definido
$id_empresa = $_GET['id'] ?? null;
if (!$id_empresa) {
    header("Location: menuCliente.php?msg=Empresa não especificada");
    exit();
}

// Buscar informações da empresa
$sql = "SELECT * FROM perfilEmpresa WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_empresa);
$stmt->execute();
$empresa = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$empresa) {
    die("Empresa não encontrada.");
}

// Buscar produtos da empresa
$sql_produtos = "SELECT * FROM produtoPerfil WHERE id_empresa = ?";
$stmt_produtos = $conn->prepare($sql_produtos);
$stmt_produtos->bind_param("i", $id_empresa);
$stmt_produtos->execute();
$produtos = $stmt_produtos->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_produtos->close();

// Buscar serviços da empresa
$sql_servicos = "SELECT * FROM servicoPerfil WHERE id_empresa = ?";
$stmt_servicos = $conn->prepare($sql_servicos);
$stmt_servicos->bind_param("i", $id_empresa);
$stmt_servicos->execute();
$servicos = $stmt_servicos->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_servicos->close();

// Buscar agendamentos feitos pelo cliente para essa empresa
$sql_agendamentos = "
    SELECT a.id_agendamento, a.data_agendamento, a.stats, s.nomeServico 
    FROM agendamentos a
    JOIN servicoPerfil s ON a.servico_id = s.id_servico
    WHERE a.cliente_id = ? AND s.id_empresa = ?
";
$stmt_agendamentos = $conn->prepare($sql_agendamentos);
$stmt_agendamentos->bind_param("ii", $_SESSION['id_cliente'], $id_empresa);
$stmt_agendamentos->execute();
$agendamentos = $stmt_agendamentos->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_agendamentos->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($empresa['nome']) ?></title>
    <style>
    /* Definindo as cores principais */
:root {
    --cor-roxa: #5e2a84;
    --cor-branca: #ffffff;
    --cor-cinza-claro: #f1f1f1;
    --cor-cinza-escuro: #333333;
}

/* Resetando margens e padding para todos os elementos */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Corpo da página */
body {
    font-family: 'Arial', sans-serif;
    background-color: var(--cor-cinza-claro);
    color: var(--cor-cinza-escuro);
    line-height: 1.6;
    padding: 20px;
}

/* Cabeçalho */
h1 {
    color: var(--cor-roxa);
    text-align: center;
    margin-bottom: 20px;
    font-size: 2.5em;
    text-transform: uppercase;
}

/* Estilizando a imagem da empresa */
img {
    display: block;
    margin: 0 auto;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Informações da empresa */
p {
    font-size: 1.1em;
    margin: 10px 0;
}

/* Títulos de seções */
h2 {
    color: var(--cor-roxa);
    margin-top: 40px;
    font-size: 1.8em;
    text-decoration: underline;
}

/* Tabelas */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    border: 1px solid var(--cor-roxa);
}

table th, table td {
    padding: 10px;
    text-align: left;
}

table th {
    background-color: var(--cor-roxa);
    color: var(--cor-branca);
}

table td {
    background-color: var(--cor-cinza-claro);
}

table tr:nth-child(even) td {
    background-color: #e0e0e0;
}

/* Links */
a {
    text-decoration: none;
    color: var(--cor-roxa);
    font-weight: bold;
}

a:hover {
    color: #3a135d;
}

/* Botão de voltar */
a:hover {
    text-decoration: underline;
}

/* Estilizando a borda da tabela */
table {
    border-radius: 8px;
    overflow: hidden;
}

/* Estilo para o link de "Voltar" */
a.voltar {
    display: inline-block;
    margin-top: 30px;
    padding: 10px 20px;
    background-color: var(--cor-roxa);
    color: var(--cor-branca);
    border-radius: 5px;
    font-size: 1.2em;
    text-align: center;
}

a.voltar:hover {
    background-color: #3a135d;
}

/* Estilos de responsividade */
@media (max-width: 768px) {
    h1 {
        font-size: 2em;
    }

    p, table th, table td {
        font-size: 1em;
    }

    table {
        font-size: 0.9em;
    }

    img {
        width: 150px;
    }
}

</style>
</head>
<body>
<h1><?= htmlspecialchars($empresa['nome']) ?></h1>
<img src="<?= htmlspecialchars($empresa['foto_perfil']) ?>" alt="Foto da Empresa" style="width:200px;">
<p>Endereço: <?= htmlspecialchars($empresa['endereco']) ?></p>
<p>Telefone: <?= htmlspecialchars($empresa['telefone']) ?></p>
<p>Cidade: <?= htmlspecialchars($empresa['cidade']) ?></p>
<p>Estado: <?= htmlspecialchars($empresa['estado']) ?></p>
<p>CEP: <?= htmlspecialchars($empresa['cep']) ?></p>
<p>Horário de Funcionamento: <?= htmlspecialchars($empresa['horario_funcionamento']) ?></p>
<p>Dias Abertos: <?= htmlspecialchars($empresa['dias_abertos']) ?></p>

<h2>Serviços</h2>
<table>
    <?php if ($servicos): ?>
        <?php foreach ($servicos as $servico): ?>
            <tr>
                <td><?= htmlspecialchars($servico['nomeServico']) ?></td>
                <td><?= number_format($servico['preco'], 2, ',', '.') ?></td>
                <td><a href="agendarServico.php?id_servico=<?= $servico['id_servico'] ?>&id_cliente=<?= $_SESSION['id_cliente'] ?>">Agendar</a></td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="3">Nenhum serviço disponível.</td></tr>
    <?php endif; ?>
</table>

<h2>Meus Agendamentos</h2>
<table>
    <?php if ($agendamentos): ?>
        <?php foreach ($agendamentos as $agendamento): ?>
            <tr>
                <td><?= htmlspecialchars($agendamento['nomeServico']) ?></td>
                <td><?= date('d/m/Y H:i', strtotime($agendamento['data_agendamento'])) ?></td>
                <td><?= htmlspecialchars($agendamento['stats']) ?></td>
                <!-- Exibe a justificativa, se houver -->
                <?php if ($agendamento['stats'] === 'cancelado' && !empty($agendamento['justificativa_cancelamento'])): ?>
                    <td>Justificativa: <?= htmlspecialchars($agendamento['justificativa_cancelamento']) ?></td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="4">Nenhum agendamento encontrado.</td></tr>
    <?php endif; ?>
</table>


<a href="menuCliente.php" class="voltar">Voltar</a>
</body>
</html>

<?php
$conn->close();
?>
