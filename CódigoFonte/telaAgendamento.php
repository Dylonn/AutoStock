<?php
session_start();
require_once 'configF.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

if (!isset($_SESSION['email'])) {
    die("Você precisa estar logado para acessar esta página.");
}

$email_usuario = $_SESSION['email'];

// Buscar ID da empresa
$sql = "SELECT id FROM perfilEmpresa WHERE id_usuario = (SELECT id FROM usuarios WHERE email = ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email_usuario);
$stmt->execute();
$id_empresa = $stmt->get_result()->fetch_assoc()['id'];
$stmt->close();

// Buscar agendamentos
$sql_agendamentos = "SELECT * FROM agendamentos WHERE id_servico IN (SELECT id_servico FROM servicoPerfil WHERE id_empresa = ?)";
$stmt_agendamentos = $conn->prepare($sql_agendamentos);
$stmt_agendamentos->bind_param("i", $id_empresa);
$stmt_agendamentos->execute();
$result_agendamentos = $stmt_agendamentos->get_result();
$agendamentos = $result_agendamentos->fetch_all(MYSQLI_ASSOC);
$stmt_agendamentos->close();

// Buscar compras
$sql_compras = "SELECT * FROM compras WHERE id_produto IN (SELECT id_produto FROM produtoPerfil WHERE id_empresa = ?)";
$stmt_compras = $conn->prepare($sql_compras);
$stmt_compras->bind_param("i", $id_empresa);
$stmt_compras->execute();
$result_compras = $stmt_compras->get_result();
$compras = $result_compras->fetch_all(MYSQLI_ASSOC);
$stmt_compras->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Solicitações</title>
</head>
<body>
<h1>Gerenciar Agendamentos e Compras</h1>

<h2>Agendamentos</h2>
<table>
    <?php if ($agendamentos): ?>
        <?php foreach ($agendamentos as $agendamento): ?>
            <tr>
                <td><?= htmlspecialchars($agendamento['nome_cliente']) ?></td>
                <td><?= htmlspecialchars($agendamento['email_cliente']) ?></td>
                <td><?= htmlspecialchars($agendamento['data_agendamento']) ?></td>
                <td>
                    <form method="POST" action="confirmarAgendamento.php">
                        <input type="hidden" name="id_agendamento" value="<?= $agendamento['id_agendamento'] ?>">
                        <button type="submit" name="confirmar">Confirmar</button>
                        <button type="submit" name="cancelar">Cancelar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="4">Nenhum agendamento disponível.</td></tr>
    <?php endif; ?>
</table>

<h2>Compras</h2>
<table>
    <?php if ($compras): ?>
        <?php foreach ($compras as $compra): ?>
            <tr>
                <td><?= htmlspecialchars($compra['nome_cliente']) ?></td>
                <td><?= htmlspecialchars($compra['email_cliente']) ?></td>
                <td>
                    <form method="POST" action="confirmarCompra.php">
                        <input type="hidden" name="id_compra" value="<?= $compra['id_compra'] ?>">
                        <button type="submit" name="confirmar">Confirmar</button>
                        <button type="submit" name="cancelar">Cancelar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="3">Nenhuma compra disponível.</td></tr>
    <?php endif; ?>
</table>

<a href="menuDono.php">Voltar</a>
</body>
</html>
