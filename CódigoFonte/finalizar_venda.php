<?php
$servername = "localhost";
$username = "root";
$password = ""; // Substitua com sua senha do MySQL
$dbname = "sistema";

$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

$forma_pagamento = $_POST['forma_pagamento'];

// Finalizar a venda e registrar a forma de pagamento
// Adicione o código necessário para atualizar o banco de dados e registrar o pagamento

$conn->close();
header("Location: /caminho_para_tela_de_venda.php"); // Redirecionar de volta para a tela de venda
exit;
