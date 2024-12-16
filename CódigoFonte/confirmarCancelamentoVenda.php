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

$id_venda = $_POST['id_venda'];

// Cancelar a venda
$sql_cancelar = "DELETE FROM venda WHERE id_venda = ?";
$stmt_cancelar = $conn->prepare($sql_cancelar);
$stmt_cancelar->bind_param("i", $id_venda);

if ($stmt_cancelar->execute()) {
    echo "Venda cancelada com sucesso!";
} else {
    echo "Erro: " . $stmt_cancelar->error;
}

$stmt_cancelar->close();
$conn->close();
?>
<a href="menuCliente.php">Voltar</a>
