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

$id_agendamento = $_POST['id_agendamento'];

// Cancelar o agendamento
$sql_cancelar = "DELETE FROM agendamentos WHERE id_agendamento = ?";
$stmt_cancelar = $conn->prepare($sql_cancelar);
$stmt_cancelar->bind_param("i", $id_agendamento);

if ($stmt_cancelar->execute()) {
    echo "Agendamento cancelado com sucesso!";
} else {
    echo "Erro: " . $stmt_cancelar->error;
}

$stmt_cancelar->close();
$conn->close();
?>
<a href="gerenciarAgendamentosEVendas.php">Voltar</a>
