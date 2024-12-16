<?php
session_start();
require_once 'configF.php';

if (!isset($_SESSION['email'])) {
    // Usuário não está logado, redireciona para a página de login
    header("Location: tela1.php"); // Substitua "tela1.php" pela sua página de login
    exit();
}

$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar a conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Obter o e-mail da sessão
$email_usuario = $_SESSION['email'];

// Consultar o nome completo e o ID do usuário no banco
$sql = "SELECT id_usuario, nome_usuario FROM usuarios WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email_usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $id_usuario = $row['id_usuario'];
    $nome_completo = $row['nome_usuario'];
} else {
    echo "Usuário não encontrado.";
    exit();
}

// Verifica se há um caixa aberto
$sql_verificar = "SELECT * FROM caixa WHERE status = 'aberto'";
$result_verificar = $conn->query($sql_verificar);
$caixa_aberto = $result_verificar->num_rows > 0;

// Abertura de Caixa
if (isset($_POST['abrir_caixa'])) {
    if (!$caixa_aberto) {
        $sql_abrir = "INSERT INTO caixa (id_usuario, data_abertura, status) VALUES (?, NOW(), 'aberto')";
        $stmt_abrir = $conn->prepare($sql_abrir);
        $stmt_abrir->bind_param("i", $id_usuario);
        $stmt_abrir->execute();
        echo "<script>alert('Caixa aberto com sucesso!');</script>";
        header("location: tela3.php");
    } else {
        echo "<script>alert('Já existe um caixa aberto.');</script>";
    }
}

// Fechamento de Caixa
if (isset($_POST['fechar_caixa'])) {
    if ($caixa_aberto) {
        $sql_fechar = "UPDATE caixa SET data_fechamento = NOW(), status = 'fechado' WHERE status = 'aberto'";
        $conn->query($sql_fechar);
        echo "<script>alert('Caixa fechado com sucesso!');</script>";
        header("location: tela3.php");
    } else {
        echo "<script>alert('Não há caixa aberto para fechar.');</script>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controle de Caixa</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        h1 {
            color: #333;
        }
        form {
            margin: 20px;
        }
        button {
            background-color: #512da8;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #673ab7;
        }
    </style>
</head>
<body>
    <h1>Controle de Caixa - Bem-vindo, <?= htmlspecialchars($nome_completo) ?></h1>
    <?php if ($caixa_aberto): ?>
        <p>O caixa está <strong>aberto</strong>.</p>
        <form method="post">
            <button type="submit" name="fechar_caixa">Fechar Caixa</button>

        </form>
    <?php else: ?>
        <p>O caixa está <strong>fechado</strong>.</p>
        <form method="post">
            <button type="submit" name="abrir_caixa">Abrir Caixa</button>
        </form>
    <?php endif; ?>
    <a href="tela3.php">Voltar</a>
</body>
</html>
