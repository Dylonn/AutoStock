<?php
session_start();
require_once 'configF.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$response = ['preco' => '0.00'];

if (isset($_GET['id']) && isset($_GET['tipo'])) {
    $id = $_GET['id'];
    $tipo = $_GET['tipo'];

    if ($tipo === 'produto') {
        $sql = "SELECT valorProduto AS preco FROM produto WHERE id_produto = ?";
    } else {
        $sql = "SELECT preco FROM servicos WHERE id_servico = ?";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();
    
    if ($item) {
        $response['preco'] = number_format($item['preco'], 2, ',', '.');
    }
}

$conn->close();
echo json_encode($response);
?>
