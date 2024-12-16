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

// Obter o ID do perfil a ser editado
$id = $_GET['id'] ?? null;

// Verificar se o perfil pertence ao usuário logado
$sql = "SELECT * FROM perfilEmpresa WHERE id_usuario = (SELECT id_usuario FROM usuarios WHERE email = ?) AND id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $email_usuario, $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Você não tem permissão para editar este perfil.");
}

// Se o perfil existe, buscamos suas informações
$perfil = $result->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Captura os dados do formulário de perfil
    $nome = $_POST['nome'] ?? null;
    $endereco = $_POST['endereco'] ?? null;
    $cidade = $_POST['cidade'] ?? null;
    $estado = $_POST['estado'] ?? null;
    $cep = $_POST['cep'] ?? null;
    $telefone = $_POST['telefone'] ?? null;
    $horario_funcionamento = $_POST['horario_funcionamento'] ?? null;
    $dias_abertos = $_POST['dias_abertos'] ?? null;

    // Atualiza o perfil somente se o nome for fornecido
    if ($nome) {
        // Verifica se foi feita a atualização da foto
        if (!empty($_FILES['foto_perfil']['name'])) {
            $foto_perfil = 'uploads/' . basename($_FILES['foto_perfil']['name']);
            if (!move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $foto_perfil)) {
                die("Erro ao fazer upload da foto!");
            }
            $sql = "UPDATE perfilEmpresa SET nome=?, endereco=?, cidade=?, estado=?, cep=?, foto_perfil=?,telefone=?, horario_funcionamento=?, dias_abertos=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssssi", $nome, $endereco, $cidade, $estado,$telefone, $cep, $foto_perfil, $horario_funcionamento, $dias_abertos, $id);
        } else {
            $sql = "UPDATE perfilEmpresa SET nome=?, endereco=?, cidade=?, estado=?, cep=?,telefone=?, horario_funcionamento=?, dias_abertos=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssssi", $nome, $endereco, $cidade, $estado, $cep,$telefone, $horario_funcionamento, $dias_abertos, $id);
        }

        if ($stmt->execute()) {
            echo "Perfil atualizado com sucesso!";
            header("Location: editarPerfil.php?id=$id");
            exit;
        } else {
            echo "Erro: " . $stmt->error;
        }

        $stmt->close();
    }

    // Lógica para adicionar produtos
    if (isset($_POST['adicionar_produto'])) {
        $nome_produto = $_POST['nome_produto'] ?? null;
        $valor_produto = $_POST['valor_produto'] ?? null;

        if ($nome_produto && $valor_produto) {
            $sql = "INSERT INTO produtoPerfil (nomeProduto, valorProduto, id_empresa) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $nome_produto, $valor_produto, $id);
            if ($stmt->execute()) {
                echo "Produto adicionado com sucesso!";
                header("Location: editarPerfil.php?id=$id");
                exit;
            } else {
                echo "Erro ao adicionar produto: " . $stmt->error;
            }
            $stmt->close();
        }
    }

    // Lógica para editar produtos
    if (isset($_POST['editar_produto'])) {
        $id_produto = $_POST['id_produto'];
        $novo_nome = $_POST['novo_nome'] ?? null;
        $novo_valor = $_POST['novo_valor'] ?? null;

        if ($novo_nome || $novo_valor) {
            $sql = "UPDATE produtoPerfil SET nomeProduto = COALESCE(?, nomeProduto), valorProduto = COALESCE(?, valorProduto) WHERE id_produto = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $novo_nome, $novo_valor, $id_produto);
            if ($stmt->execute()) {
                echo "Produto atualizado com sucesso!";
                header("Location: editarPerfil.php?id=$id");
                exit;
            } else {
                echo "Erro ao atualizar produto: " . $stmt->error;
            }
            $stmt->close();
        }
    }

    // Lógica para adicionar serviços
    if (isset($_POST['adicionar_servico'])) {
        $nome_servico = $_POST['nome_servico'] ?? null;
        $preco_servico = $_POST['preco_servico'] ?? null;

        if ($nome_servico && $preco_servico) {
            $sql = "INSERT INTO servicoPerfil (nomeServico, preco, id_empresa) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $nome_servico, $preco_servico, $id);
            if ($stmt->execute()) {
                echo "Serviço adicionado com sucesso!";
                header("Location: editarPerfil.php?id=$id");
                exit;
            } else {
                echo "Erro ao adicionar serviço: " . $stmt->error;
            }
            $stmt->close();
        }
    }

    // Lógica para editar serviços
    if (isset($_POST['editar_servico'])) {
        $id_servico = $_POST['id_servico'];
        $novo_nome = $_POST['novo_nome'] ?? null;
        $novo_preco = $_POST['novo_preco'] ?? null;

        if ($novo_nome || $novo_preco) {
            $sql = "UPDATE servicoPerfil SET nomeServico = COALESCE(?, nomeServico), preco = COALESCE(?, preco) WHERE id_servico = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $novo_nome, $novo_preco, $id_servico);
            if ($stmt->execute()) {
                echo "Serviço atualizado com sucesso!";
                header("Location: editarPerfil.php?id=$id");
                exit;
            } else {
                echo "Erro ao atualizar serviço: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Buscar produtos e serviços associados ao perfil
$produtos_sql = "SELECT * FROM produtoPerfil WHERE id_empresa = ?";
$servicos_sql = "SELECT * FROM servicoPerfil WHERE id_empresa = ?";
$stmt = $conn->prepare($produtos_sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$produtos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$stmt = $conn->prepare($servicos_sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$servicos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil</title>
    <style>
        #preview {
            max-width: 200px; /* Limita a largura da imagem */
            max-height: 200px; /* Limita a altura da imagem */
            display: none; /* Inicialmente escondido */
        }

        /* Estilo básico */
body {
    font-family: 'Roboto', sans-serif;
    margin: 0;
    padding: 20px;
    background-color: #f5f5f5;
}

h1 {
    color: #5a329d; /* Roxa */
    text-align: center;
}

form {
    max-width: 800px; /* Aumentando a largura máxima para melhor visualização */
    margin: 0 auto;
    background-color: #fff;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

label {
    display: block;
    margin-bottom: 5px;
}

input, select, textarea {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 3px;
}

button[type="submit"] {
    background-color: #5a329d;
    color: #fff;
    padding: 10px 20px;
    border: none;
    border-radius: 3px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

button[type="submit"]:hover {
    background-color: #45298a;
}

/* Estilos específicos para a oficina mecânica */
.foto-perfil {
    display: block;
    margin: 0 auto;
    max-width: 200px;
}

#preview {
    max-width: 200px;
    max-height: 200px;
}

/* Estilos para listas de produtos e serviços */
ul {
    list-style: none;
    padding: 0;
}

li {
    margin-bottom: 10px;
}

/* Adicionando um pouco de estilo para os formulários de edição */
.edit-form {
    display: inline-block;
    margin-left: 10px;
}

/* Hover nos botões de edição */
.edit-form button {
    background-color: #f0f0f0;
    color: #5a329d;
    border: 1px solid #ccc;
}

.edit-form button:hover {
    background-color: #ddd;
}
    </style>
</head>
<body>
<h1>Editar Perfil da Empresa</h1>

<form id="perfilForm" method="POST" action="editarPerfil.php?id=<?= $perfil['id'] ?>" enctype="multipart/form-data">
    <label for="nome">Nome da Empresa:</label>
    <input type="text" id="nome" name="nome" value="<?= $perfil['nome'] ?>">

    <label for="endereco">Endereço:</label>
    <input type="text" id="endereco" name="endereco" value="<?= $perfil['endereco'] ?>">

    <label for="cidade">Cidade:</label>
    <input type="text" id="cidade" name="cidade" value="<?= $perfil['cidade'] ?>">

    <label for="estado">Estado:</label>
    <input type="text" id="estado" name="estado" value="<?= $perfil['estado'] ?>">

    <label for="cep">CEP:</label>
    <input type="text" id="cep" name="cep" value="<?= $perfil['cep'] ?>">

    <label for="telefone">Telefone:</label>
    <input type="text" id="telefone" name="telefone" value="<?= $perfil['telefone'] ?>">

    <label for="foto_perfil">Foto do Perfil (opcional):</label>
    <input type="file" id="foto_perfil" name="foto_perfil" accept="image/*" onchange="previewImage(event)">

    <img id="preview" alt="Prévia da imagem">

    <?php if (!empty($perfil['foto_perfil'])): ?>
        <h3>Imagem Atual:</h3>
        <img src="<?= $perfil['foto_perfil'] ?>" id="currentImage" alt="Imagem atual" style="max-width: 200px; max-height: 200px;">
    <?php endif; ?>

    <label for="horario_funcionamento">Horário de Funcionamento:</label>
    <input type="text" name="horario_funcionamento" id="horario_funcionamento" value="<?= $perfil['horario_funcionamento'] ?>">

    <label for="dias_abertos">Dias Abertos:</label>
    <input type="text" name="dias_abertos" id="dias_abertos" value="<?= $perfil['dias_abertos'] ?>">

    <button type="submit">Atualizar Perfil</button>
</form>


<h2>Adicionar Serviço</h2>
<form method="POST">
    <label for="nome_servico">Nome do Serviço:</label>
    <input type="text" name="nome_servico" id="nome_servico" required>

    <label for="preco_servico">Preço:</label>
    <input type="text" name="preco_servico" id="preco_servico" required>

    <button type="submit" name="adicionar_servico">Adicionar Serviço</button>
</form>

<h2>Serviços Existentes</h2>
<ul>
    <?php foreach ($servicos as $servico): ?>
        <li>
            <form method="POST">
                <input type="hidden" name="id_servico" value="<?= $servico['id_servico'] ?>">
                <input type="text" name="novo_nome" placeholder="Novo nome" value="<?= $servico['nomeServico'] ?>">
                <input type="text" name="novo_preco" placeholder="Novo preço" value="<?= $servico['preco'] ?>">
                <button type="submit" name="editar_servico">Editar Serviço</button>
            </form>
        </li>
    <?php endforeach; ?>
</ul>

<a href="telaPerfilEmpresa.php">Voltar</a>

<script>
function previewImage(event) {
    const file = event.target.files[0];
    const reader = new FileReader();

    reader.onload = function(e) {
        const img = document.getElementById('preview');
        img.src = e.target.result;
        img.style.display = 'block'; // Exibe a imagem
    };

    if (file) {
        reader.readAsDataURL(file); // Lê o arquivo como uma URL de dados
    }
}

// Se houver uma imagem atual, também exibe a prévia
if (document.getElementById('currentImage')) {
    document.getElementById('preview').style.display = 'none'; // Esconde a prévia se a imagem atual existir
}
</script>
</body>
</html>
