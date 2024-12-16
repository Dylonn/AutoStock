<?php
require_once 'configF.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$tipo = $_GET['tipo'];
$items = [];

if ($tipo === 'produto') {
    $sql = "SELECT id_produto AS id, nomeProduto AS nome FROM produto ORDER BY nomeProduto";
} elseif ($tipo === 'servico') {
    $sql = "SELECT id_servico AS id, nomeServico AS nome FROM servicos ORDER BY nomeServico";
} else {
    die("Tipo inválido");
}

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($items);

$conn->close();
?>
