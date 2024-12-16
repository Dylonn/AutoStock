<?php
session_start();
require_once 'configF.php';

// Verifica se o cliente está logado
if (!isset($_SESSION['id_cliente'])) {
    header("Location: login.php");
    exit();
}

$id_produto = $_GET['id'] ?? null;
if (!$id_produto) {
    header("Location: menuCliente.php?msg=Produto não especificado");
    exit();
}

// Conecta ao banco de dados
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Buscar informações do produto
$sql_produto = "SELECT * FROM produtoPerfil WHERE id_produto = ?";
$stmt = $conn->prepare($sql_produto);
$stmt->bind_param("i", $id_produto);
$stmt->execute();
$produto = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$produto) {
    die("Produto não encontrado.");
}

// Processa o pedido de compra
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_cliente = $_SESSION['id_cliente'];
    
    $sql = "INSERT INTO pedidos_compra (id_cliente, id_produto, status, data_pedido) VALUES (?, ?, 'pendente', NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_cliente, $id_produto);
    
    if ($stmt->execute()) {
        echo "Pedido realizado com sucesso! Aguarde a confirmação.";
    } else {
        echo "Erro ao registrar o pedido.";
    }
    $stmt->close();
    $conn->close();
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Compra de Produto</title>
</head>
<body>
<h1>Comprar Produto</h1>
<p>Produto: <?= htmlspecialchars($produto['nomeProduto']) ?></p>
<p>Preço: R$ <?= number_format($produto['valorProduto'], 2, ',', '.') ?></p>

<form method="POST">
    <button type="submit">Confirmar Compra</button>
</form>
<a href="menuCliente.php">Cancelar</a>
</body>
</html>
<?php
$conn->close();
?>
