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



// Função para adicionar/editar o perfil da empresa e produtos/serviços
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Captura os dados do formulário
    $nome = $_POST['nome'] ?? null;
    $endereco = $_POST['endereco'] ?? null;
    $cidade = $_POST['cidade'] ?? null;
    $estado = $_POST['estado'] ?? null;
    $cep = $_POST['cep'] ?? null;
    $telefone = $_POST['telefone'] ?? null;
    $horario_funcionamento = $_POST['horario_funcionamento'] ?? null;
    $dias_abertos = $_POST['dias_abertos'] ?? null;
    $id = $_POST['id'] ?? null;
    $tipo = $_POST['produto_servico_tipo'] ?? null;
    $nome_item = $_POST['nome_item'] ?? null;
    $preco = $_POST['preco'] ?? null;

    // Validação dos campos do perfil
    if (!$nome || !$endereco || !$cidade || !$estado || !$cep || !$telefone || empty($_FILES['foto_perfil']['name'])) {
        die("Todos os campos do perfil são obrigatórios!");
    }

    $foto_perfil = 'uploads/' . basename($_FILES['foto_perfil']['name']);
    if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $foto_perfil)) {
        if ($id) {
            // Atualizando o perfil existente, incluindo o id_usuario
            $sql = "UPDATE perfilEmpresa SET nome=?, endereco=?, cidade=?, estado=?, cep=?, foto_perfil=?,telefone=?, horario_funcionamento=?, dias_abertos=?, id_usuario=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssssssi", $nome, $endereco, $cidade, $estado, $cep, $foto_perfil,$telefone, $horario_funcionamento, $dias_abertos, $id_usuario, $id);
        } else {
            // Inserindo um novo perfil, incluindo o id_usuario
            $sql = "INSERT INTO perfilEmpresa (nome, endereco, cidade, estado, cep,telefone, foto_perfil, horario_funcionamento, dias_abertos, id_usuario) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?,?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssssi", $nome, $endereco, $cidade, $estado, $cep, $foto_perfil,$telefone, $horario_funcionamento, $dias_abertos, $id_usuario);
        }

        if ($stmt->execute()) {
            echo "Perfil salvo com sucesso!";
        } else {
            echo "Erro: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Erro ao fazer upload da foto!";
    }

    // Adicionar produtos ou serviços
    if ($tipo && $nome_item && $preco) {
        $id_empresa = $id ?? $conn->insert_id; // Se for uma inserção, pega o ID do último inserido
        if ($tipo === 'produto') {
            $sql = "INSERT INTO produtoPerfil (nomeProduto, valorProduto, id_empresa) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $nome_item, $preco, $id_empresa);
        } else {
            $sql = "INSERT INTO servicoPerfil (nomeServico, preco, id_empresa) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $nome_item, $preco, $id_empresa);
        }

        if ($stmt->execute()) {
            echo "Cadastro de produto/serviço realizado com sucesso!";
        } else {
            echo "Erro: " . $stmt->error;
        }

        $stmt->close();
    }
}



// Buscar perfis associados ao e-mail do usuário logado
$sql_perfil = "SELECT * FROM perfilEmpresa WHERE id_usuario = ?";
$stmt_perfil = $conn->prepare($sql_perfil);
$stmt_perfil->bind_param("i", $id_usuario);
$stmt_perfil->execute();
$result_perfil = $stmt_perfil->get_result();

// Pega todos os perfis associados ao usuário
$perfis = $result_perfil->fetch_all(MYSQLI_ASSOC);

$stmt_perfil->close();

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil</title>
</head>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@400;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Montserrat', sans-serif;
        }

        body {
            background-image: url('img/.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-color: #c9d6ff;
            background: linear-gradient(to right, #e2e2e2, #c9d6ff);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            flex-direction: column;
        }

        h1, h2 {
            color: #512da8; /* Cor roxa */
            text-align: center;
        }

        form {
            background-color: #fff;
            border-radius: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.35);
            padding: 30px;
            width: 80%;
            max-width: 600px;
            margin: 20px 0;
        }

        label {
            display: block;
            margin-top: 10px;
            font-weight: 600;
        }

        input[type="text"], input[type="file"], input[type="number"], select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #512da8;
            border-radius: 8px;
            font-size: 13px;
            font-family: 'Roboto Mono', monospace;
        }

        button[type="submit"] {
            background-color: #512da8;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            width: 100%;
            transition: background-color 0.3s;
        }

        button[type="submit"]:hover {
            background-color: #673ab7;
        }

        h2 {
            color: #333;
            margin-bottom: 20px;
        }

        a {
            display: inline-block;
            margin-top: 20px;
            padding: 15px 30px;
            text-align: center;
            color: #fff;
            background-color: #512da8;
            border-radius: 5px;
            transition: background-color 0.3s;
            text-decoration: none;
        }

        a:hover {
            background-color: #311b92;
        }

        .foto-perfil {
            display: block;
            margin: 20px auto;
            max-width: 150px;
            border-radius: 50%;
        }

        .message.success, .message.error {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #512da8;
            color: #fff;
            padding: 10px 20px;
            border-radius: 8px;
            z-index: 1000;
            opacity: 1;
            transition: background-color 0.3s, opacity 0.3s;
        }

        .message.hidden {
            opacity: 0;
            pointer-events: none;
        }

        .table-container {
            width: 100%;
            max-width: 1000px;
            margin: 20px 0;
            overflow-x: auto;
        }

        table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            margin: 0 auto;
            text-align: center;
        }

        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        th {
            background-color: #512da8;
            color: white;
        }

        tbody tr:hover {
            background-color: #f2f2f2;
        }

        .action-link {
            color: #512da8;
            text-decoration: none;
        }

        .action-link:hover {
            text-decoration: underline;
        }

        .scrollable-column {
            max-width: 190px;
            max-height: 80px;
            overflow-x: auto;
            white-space: nowrap;
        }
</style>
<body>
<h1>Gerenciar Perfil</h1>
<h2><?php echo "Sessão iniciada com o valor de e-mail: " . ($_SESSION['email'] ?? "não definida."); ?></h2>

<form id="perfilForm" method="POST" action="telaPerfilEmpresa.php" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= isset($perfil['id']) ? $perfil['id'] : '' ?>">
    
    <label for="nome">Nome da Empresa:</label>
    <input type="text" id="nome" name="nome" required value="<?= isset($perfil['nome']) ? $perfil['nome'] : '' ?>">

    <label for="endereco">Endereço:</label>
    <input type="text" id="endereco" name="endereco" required value="<?= isset($perfil['endereco']) ? $perfil['endereco'] : '' ?>">

    <label for="cidade">Cidade:</label>
    <input type="text" id="cidade" name="cidade" required value="<?= isset($perfil['cidade']) ? $perfil['cidade'] : '' ?>">

    <label for="estado">Estado:</label>
    <input type="text" id="estado" name="estado" required value="<?= isset($perfil['estado']) ? $perfil['estado'] : '' ?>">

    <label for="cep">CEP:</label>
    <input type="text" id="cep" name="cep" required value="<?= isset($perfil['cep']) ? $perfil['cep'] : '' ?>">

    <label for="telefone">Telefone:</label>
    <input type="text" id="telefone" name="telefone" required value="<?= isset($perfil['telefone']) ? $perfil['telefone'] : '' ?>">

    <label for="foto_perfil">Foto do Perfil:</label>
    <input type="file" id="foto_perfil" name="foto_perfil" accept="image/*" required>

    <label for="horario_funcionamento">Horário de Funcionamento:</label>
    <input type="text" name="horario_funcionamento" id="horario_funcionamento" required value="<?= isset($perfil['horario_funcionamento']) ? $perfil['horario_funcionamento'] : '' ?>">

    <label for="dias_abertos">Dias Abertos:</label>
    <input type="text" name="dias_abertos" id="dias_abertos" required value="<?= isset($perfil['dias_abertos']) ? $perfil['dias_abertos'] : '' ?>">

    <h2> Serviço(opicional)</h2>
    <label for="nome_item">Nome:</label>
    <input type="text" name="nome_item" id="nome_item">

    <label for="preco">Preço:</label>
    <input type="text" name="preco" id="preco">

    <button type="submit">Salvar</button>
</form>

<a href="tela3.php">voltar</a>

<h2>Meus Perfis</h2>
<?php if ($perfis): ?>
    <ul>
        <?php foreach ($perfis as $perfil): ?>
            <li>
                <?= htmlspecialchars($perfil['nome']) ?> - <a href="editarPerfil.php?id=<?= $perfil['id'] ?>">Editar</a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>Nenhum perfil encontrado.</p>
<?php endif; ?>

</body>
</html>
