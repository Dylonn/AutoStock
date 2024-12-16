<?php
session_start();
require_once 'configF.php';

if (!isset($_SESSION['id_empresa'])) {
    die("Acesso restrito para empresas.");
}

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Confirmar ou cancelar pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pedido = $_POST['id_pedido'];
    $acao = $_POST['acao'];
    $justificativa = $_POST['justificativa'] ?? '';

    if ($acao == 'confirmar') {
        $sql = "UPDATE pedidos_compra SET status = 'confirmado' WHERE id_pedido = ?";
    } elseif ($acao == 'cancelar') {
        $sql = "UPDATE pedidos_compra SET status = 'cancelado', justificativa = ? WHERE id_pedido = ?";
    }

    $stmt = $conn->prepare($sql);
    if ($acao == 'cancelar') {
        $stmt->bind_param("si", $justificativa, $id_pedido);
    } else {
        $stmt->bind_param("i", $id_pedido);
    }

    if ($stmt->execute()) {
        echo ucfirst($acao) . " com sucesso!";
    } else {
        echo "Erro ao processar pedido.";
    }
    $stmt->close();
}

// Buscar pedidos pendentes
$sql_pedidos = "SELECT p.id_pedido, c.nome, pr.nomeProduto, p.status 
                FROM pedidos_compra p
                JOIN cliente c ON p.id_cliente = c.id_cliente
                JOIN produto pr ON p.id_produto = pr.id_produto
                WHERE pr.id_empresa = ? AND p.status = 'pendente'";
$stmt = $conn->prepare($sql_pedidos);
$stmt->bind_param("i", $_SESSION['id_empresa']);
$stmt->execute();
$pedidos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<!-- Lista os pedidos pendentes -->
<form method="POST">
    <?php foreach ($pedidos as $pedido): ?>
        <p><?= htmlspecialchars($pedido['nome']) ?> - <?= htmlspecialchars($pedido['nomeProduto']) ?></p>
        <button name="acao" value="confirmar">Confirmar</button>
        <button name="acao" value="cancelar">Cancelar</button>
        <input type="hidden" name="id_pedido" value="<?= $pedido['id_pedido'] ?>">
        <textarea name="justificativa" placeholder="Justificativa (para cancelamento)"></textarea>
    <?php endforeach; ?>
</form>
