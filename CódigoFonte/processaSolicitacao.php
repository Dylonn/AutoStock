<?php
session_start();
require_once 'configF.php';

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'];
    $id = $_POST['id_agendamento'];
    $tipo = $_POST['tipo'];
    $justificativa = $_POST['justificativa'] ?? ''; // Justificativa opcional

    if ($acao === 'cancelar') {
        // Atualiza o status para 'cancelado' e inclui justificativa (se fornecida)
        $sql = "UPDATE agendamentos SET stats = 'cancelado', justificativa_cancelamento = ? WHERE id_agendamento = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $justificativa, $id); // bind_param para justificar a exclusão
        $stmt->execute();

        // Opcionalmente, você pode adicionar lógica extra para log de cancelamento ou notificação, se necessário.
    } elseif ($acao === 'confirmar') {
        // Atualiza o status para 'confirmado'
        $sql = "UPDATE agendamentos SET stats = 'confirmado' WHERE id_agendamento = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }

    // Redireciona para a página de gerenciamento
    header("Location: gerenciarAgendamentosEVendas.php");
    exit();
}
?>
