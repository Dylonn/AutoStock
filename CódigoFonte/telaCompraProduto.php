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
// Obter o ID da empresa
$sql = "SELECT id FROM perfilEmpresa WHERE id_usuario = (SELECT id_usuario FROM usuarios WHERE email = ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email_usuario);
$stmt->execute();
$id_empresa = $stmt->get_result()->fetch_assoc()['id'];
$stmt->close();

// Buscar produtos disponíveis
$sql_produtos = "SELECT * FROM produtoPerfil WHERE id_empresa = ?";
$stmt_produtos = $conn->prepare($sql_produtos);
$stmt_produtos->bind_param("i", $id_empresa);
$stmt_produtos->execute();
$result_produtos = $stmt_produtos->get_result();
$produtos = $result_produtos->fetch_all(MYSQLI_ASSOC);
$stmt_produtos->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Comprar Produto</title>
</head>
<body>
<h1>Comprar Produto</h1>
<form method="POST" action="confirmarCompra.php">
    <label for="produto">Selecione o Produto:</label>
    <select name="produto_id" id="produto" required>
        <?php foreach ($produtos as $produto): ?>
            <option value="<?= $produto['id_produto'] ?>"><?= htmlspecialchars($produto['nomeProduto']) ?> - R$ <?= number_format($produto['valorProduto'], 2, ',', '.') ?></option>
        <?php endforeach; ?>
    </select>

    <label for="forma_pagamento">Forma de Pagamento:</label>
    <input type="text" id="forma_pagamento" name="forma_pagamento" required>

    <button type="submit">Comprar</button>
</form>

<a href="telaPerfilEmpresa.php">Voltar</a>
</body>
</html>
