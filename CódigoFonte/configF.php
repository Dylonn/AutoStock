<?php
// Configurações do banco de dados
$servername = "127.0.0.1"; // Host do banco de dados (geralmente 'localhost')
$username = "root"; // Usuário do banco de dados
$password = ""; // Senha do banco de dados
$dbname = "sistema"; // Nome do banco de dados

// Cria a conexão usando MySQLi
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica a conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}





// Fechar conexão (opcional, o PHP fecha automaticamente ao finalizar o script)
$conn->close();
?>


