<?php
session_start();
require_once 'configF.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$response = ['success' => false, 'message' => '', 'timestamp' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo = $_POST['tipo'];
    $item_id = (int) $_POST['item'];
    $desconto = (float) $_POST['desconto'];
    $pagamento = $_POST['pagamento'];

    // Validar tipo
    if (!in_array($tipo, ['produto', 'servico'])) {
        $response['message'] = "Tipo inválido";
        echo json_encode($response);
        exit;
    }

    // Validar forma de pagamento
    if (!in_array($pagamento, ['pix', 'dinheiro', 'debito', 'credito'])) {
        $response['message'] = "Forma de pagamento inválida";
        echo json_encode($response);
        exit;
    }

    // Validar desconto
    if ($desconto < 0) {
        $response['message'] = "O desconto não pode ser negativo.";
        echo json_encode($response);
        exit;
    }

    // Recuperar usuário logado
    if (!isset($_SESSION['email'])) {
        $response['message'] = "Usuário não autenticado.";
        echo json_encode($response);
        exit;
    }

    $email_usuario = $_SESSION['email'];
    $sql_usuario = "SELECT id_usuario FROM usuarios WHERE email = ?";
    $stmt_usuario = $conn->prepare($sql_usuario);
    $stmt_usuario->bind_param("s", $email_usuario);
    $stmt_usuario->execute();
    $result_usuario = $stmt_usuario->get_result();
    $usuario = $result_usuario->fetch_assoc();

    if (!$usuario) {
        $response['message'] = "Usuário não encontrado.";
        echo json_encode($response);
        exit;
    }
    $id_usuario = $usuario['id_usuario'];

    // Iniciar uma transação
    $conn->begin_transaction();

    try {
        // Inserir venda
        $sql_venda = "INSERT INTO venda (id_usuario, forma_pagamento, dia) VALUES (?, ?, NOW())";
        $stmt_venda = $conn->prepare($sql_venda);
        $stmt_venda->bind_param("is", $id_usuario, $pagamento);
        if ($stmt_venda->execute()) {
            $id_venda = $conn->insert_id;

            // Consultar o item selecionado
            if ($tipo === 'produto') {
                $sql_item = "SELECT nomeProduto AS nome, valorProduto AS preco FROM produto WHERE id_produto = ?";
            } else {
                $sql_item = "SELECT nomeServico AS nome, preco FROM servicos WHERE id_servico = ?";
            }

            $stmt_item = $conn->prepare($sql_item);
            $stmt_item->bind_param('i', $item_id);
            $stmt_item->execute();
            $result_item = $stmt_item->get_result();
            $item = $result_item->fetch_assoc();

            if ($item) {
                $preco = $item['preco'];

                // Inserir item de venda
                $sql_item_venda = "INSERT INTO itens_venda (id_venda, tipo, id_item, preco, desconto) VALUES (?, ?, ?, ?, ?)";
                $stmt_item_venda = $conn->prepare($sql_item_venda);
                $stmt_item_venda->bind_param('isidd', $id_venda, $tipo, $item_id, $preco, $desconto);
                $stmt_item_venda->execute();
            } else {
                throw new Exception("Item não encontrado.");
            }

            // Confirmar a transação
            $conn->commit();

            $response['success'] = true;
            $response['message'] = "Venda realizada com sucesso!";
            $response['timestamp'] = date('Y-m-d H:i:s'); // Timestamp direto
        } else {
            throw new Exception("Erro ao inserir venda: " . $stmt_venda->error);
        }
    } catch (Exception $e) {
        $conn->rollback();
        $response['message'] = "Erro: " . $e->getMessage();
    } finally {
        $conn->close();
    }

    echo json_encode($response);
}
?>
