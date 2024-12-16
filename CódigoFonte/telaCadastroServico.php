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

$sql_usuario = "SELECT id_usuario, nome_usuario FROM usuarios WHERE email = ?";
$stmt_usuario = $conn->prepare($sql_usuario);
$stmt_usuario->bind_param("s", $email_usuario);
$stmt_usuario->execute();
$result_usuario = $stmt_usuario->get_result();

if ($result_usuario->num_rows === 0) {
    die("Usuário não encontrado.");
}

$usuario = $result_usuario->fetch_assoc();
$id_usuario = $usuario['id_usuario'];
$nome_usuario = $usuario['nome_usuario']; // Captura o nome do usuário
$stmt_usuario->close();


// Adicionando um novo serviço
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['CadastrarServico'])) {
    $nome = $conn->real_escape_string($_POST["NomeServico"]);
    $preco = $conn->real_escape_string($_POST["PrecoServico"]);

    $sql = "INSERT INTO servicos (nomeServico, preco, id_usuario) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdi", $nome, $preco, $id_usuario); // $id_usuario já é recuperado acima
    if ($stmt->execute()) {
        echo "<p class='message success'>Cadastro realizado com sucesso</p>";
    } else {
        echo "<p class='message error'>Falha no cadastro do serviço: " . $stmt->error . "</p>";
    }
    $stmt->close();
}


// Excluindo um serviço
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $sql = "DELETE FROM servicos WHERE id_servico = ? AND id_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id, $id_usuario);
    if ($stmt->execute()) {
        echo "<p class='message success'>Serviço excluído com sucesso</p>";
    } else {
        echo "<p class='message error'>Falha ao excluir serviço: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

// Atualizando um serviço
if (isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $nome = $conn->real_escape_string($_POST["NomeServico"]);
    $preco = $conn->real_escape_string($_POST["PrecoServico"]);

    $sql = "UPDATE servicos SET nomeServico = ?, preco = ? WHERE id_servico = ? AND id_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdii", $nome, $preco, $id, $id_usuario);
    if ($stmt->execute()) {
        echo "<p class='message success'>Serviço atualizado com sucesso</p>";
    } else {
        echo "<p class='message error'>Falha ao atualizar serviço: " . $stmt->error . "</p>";
    }
    $stmt->close();
}


// Consultando serviços
$sql = "SELECT * FROM servicos WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario); // $id_usuario já é definido anteriormente
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Serviços</title>
    <style>
        body {
            background-image: url('');
            background-size: cover;
            background-position: center;
            font-family: 'Montserrat', sans-serif;
            background-color: #c9d6ff;
            display: flex;
            flex-direction: column;
            align-items: center;
            height: 100vh;
            margin: 0;
            padding: 0;
        }

        h1, h2 {
            color: #512da8;
            text-align: center;
            margin-bottom: 20px;
        }

        form {
            background-color: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.35);
            width: 300px;
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"] {
            background-color: #eee;
            border: 1px solid #512da8;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 10px;
            outline: none;
            font-size: 14px;
            width: 90%;
        }

        button {
            background-color: #512da8;
            color: #fff;
            padding: 10px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 10px;
            transition: background-color 0.3s;
            font-weight: 600;
            width: 90%;
        }

        button:hover {
            background-color: #673ab7;
        }

        table {
            margin-top: 20px;
            border-collapse: collapse;
            width: 80%;
            color: #512da8;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.35);
        }

        th, td {
            border: 2px solid #512da8;
            padding: 10px;
            text-align: center;
        }

        th {
            background-color: #512da8;
            color: #fff;
        }

        .message.success {
            background-color: #512da8;
            color: #fff;
            padding: 10px;
            border: none;
            border-radius: 8px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }

        .message.error {
            background-color: #512da8;
            color: #fff;
            padding: 10px;
            border: none;
            border-radius: 8px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }

        .action-link {
            color: #512da8;
            text-decoration: none;
            margin: 0 5px;
        }

        .action-link:hover {
            text-decoration: underline;
        }

        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
        }

        .form-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
        }

        .table-container {
            width: 100%;
            display: flex;
            justify-content: center;
        }
        #botCadastroVoltar {
                display: inline-block;
                padding: 15px 30px;
                font-size: 18px;
                text-align: center;
                text-decoration: none;
                color: #fff;
                background-color: #512da8;
                border-radius: 5px;
                transition: background-color 0.3s ease;
                position: fixed;
                top: 3%;
                right: 90%;
        }

        #botCadastroVoltar:hover {
            background-color: #311b92;
        }
    </style>
</head>
<body>
    <a href="tela3.php" id="botCadastroVoltar">Voltar</a>
    <div class="container">
        <h1>Cadastro de Serviços</h1>
        <h2><?php echo "Sessão iniciada com o valor de e-mail: " . ($_SESSION['email'] ?? "não definida.");?></h2>

        <div class="form-container">
            <form action="telaCadastroServico.php" method="POST">
                <label for="NomeServico">Nome do Serviço</label>
                <input type="text" name="NomeServico" id="NomeServico" required>

                <label for="PrecoServico">Preço do Serviço</label>
                <input type="text" name="PrecoServico" id="PrecoServico" pattern="[0-9]+(\.[0-9]{1,2})?" title="Por favor, insira apenas números e pontos." required>


                

                <button type="submit" name="CadastrarServico">Cadastrar Serviço</button>
            </form>
        </div>

        <h2>Serviços Cadastrados</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome do Serviço</th>
                        <th>Preço</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $row['id_servico']; ?></td>
                            <td><?php echo $row['nomeServico']; ?></td>
                            <td><?php echo number_format($row['preco'], 2, ',', '.'); ?></td>
                            <td>
                                <a href="?edit=<?php echo $row['id_servico']; ?>" class="action-link">Editar</a> |
                                <a href="?delete=<?php echo $row['id_servico']; ?>" class="action-link" onclick="return confirm('Tem certeza que deseja excluir?');">Excluir</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <?php
        // Exibir formulário de edição se solicitado
        if (isset($_GET['edit'])) {
            $id = intval($_GET['edit']);
            $result = $conn->query("SELECT * FROM servicos WHERE id_servico = $id");
            $service = $result->fetch_assoc();
        ?>
            <h2>Editar Serviço</h2>
            <div class="form-container">
                <form action="telaCadastroServico.php" method="POST">
                    <input type="hidden" name="id" value="<?php echo $service['id_servico']; ?>">
                    <label for="NomeServico">Nome do Serviço</label>
                    <input type="text" name="NomeServico" id="NomeServico" value="<?php echo $service['nomeServico']; ?>" required>

                    <label for="PrecoServico">Preço do Serviço</label>
                    <input type="text" name="PrecoServico" id="PrecoServico" value="<?php echo $service['preco']; ?>" required>

                    <button type="submit" name="update">Atualizar Serviço</button>
                </form>
            </div>
        <?php
        }
        ?>
    </div>
</body>
</html>

<?php
$conn->close();
?>
