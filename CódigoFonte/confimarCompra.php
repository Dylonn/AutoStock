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
$produto_id = $_POST['produto_id'];
$forma_pagamento = $_POST['forma_pagamento'];

// Obter o ID do cliente
$sql_cliente = "SELECT id_cliente FROM cliente WHERE email = ?";
$stmt_cliente = $conn->prepare($sql_cliente);
$stmt_cliente->bind_param("s", $email_usuario);
$stmt_cliente->execute();
$id_cliente = $stmt_cliente->get_result()->fetch_assoc()['id_cliente'];
$stmt_cliente->close();

// Inserir venda
$sql_venda = "INSERT INTO venda (forma_pagamento) VALUES (?)";
$stmt_venda = $conn->prepare($sql_venda);
$stmt_venda->bind_param("s", $forma_pagamento);
$stmt_venda->execute();
$id_venda = $stmt_venda->insert_id; // Obter o ID da venda inserida
$stmt_venda->close();

// Inserir item na venda
$sql_item = "INSERT INTO itens_venda (id_venda, tipo, id_item, preco, desconto) VALUES (?, 'produto', ?, ?, 0)";
$stmt_item = $conn->prepare($sql_item);

// Obter preço do produto
$sql_produto = "SELECT valorProduto FROM produtoPerfil WHERE id_produto = ?";
$stmt_produto = $conn->prepare($sql_produto);
$stmt_produto->bind_param("i", $produto_id);
$stmt_produto->execute();
$preco = $stmt_produto->get_result()->fetch_assoc()['valorProduto'];
$stmt_produto->close();

$stmt_item->bind_param("iid", $id_venda, $produto_id, $preco);

if ($stmt_item->execute()) {
    echo "Compra realizada com sucesso!";
} else {
    echo "Erro: " . $stmt_item->error;
}

$stmt_item->close();
$conn->close();
?>
<a href="menuCliente.php">Voltar</a>
