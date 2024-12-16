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

$produtoParaEditar = null;

// Definir diretório para upload
$diretorio = "uploads/";
if (!is_dir($diretorio)) {
    mkdir($diretorio, 0777, true); // Cria a pasta caso não exista
}

// Verificar se foi enviado um formulário para cadastro ou atualização
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar se o formulário de cadastro foi enviado
    if (isset($_POST['CadastrarProduto'])) {
        $nomeProduto = $_POST['NomeProduto'];
        $valorProduto = $_POST['ValorProduto'];
        $codigoNcm = $_POST['CodigoNcm'];
        $codigoTributacao = $_POST['CodigoTributacao'];
        $aliquotaImposto = $_POST['AliquotaImposto'];

        $uploadPermitido = true; 
        $foto = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == UPLOAD_ERR_OK) {
            $nomeArquivo = uniqid() . "_" . basename($_FILES["foto"]["name"]);
            $foto = $diretorio . $nomeArquivo;
            move_uploaded_file($_FILES["foto"]["tmp_name"], $foto);
        }

        // Inserir dados no banco, incluindo o caminho da imagem
        $sql = "INSERT INTO produto (nomeProduto, valorProduto, CodigoNcm, codigo_tributacao, aliquota_imposto, foto, data_cadastro)
                VALUES ('$nomeProduto', '$valorProduto', '$codigoNcm', '$codigoTributacao', '$aliquotaImposto', '$foto', NOW())";

        if ($conn->query($sql) === TRUE) {
            echo "<div class='message success'>Produto cadastrado com sucesso!</div>";
        } else {
            echo "<div class='message error'>Erro: " . $conn->error . "</div>";
        }
    }

    // Verificar se o formulário de atualização foi enviado
    if (isset($_POST['update'])) {
        $id = $_POST['id_produto'];
        $nomeProduto = $_POST['NomeProduto'];
        $valorProduto = $_POST['ValorProduto'];
        $codigoNcm = $_POST['CodigoNcm'];
        $codigoTributacao = $_POST['CodigoTributacao'];
        $aliquotaImposto = $_POST['AliquotaImposto'];

        $sql = "UPDATE produto SET nomeProduto='$nomeProduto', valorProduto='$valorProduto',
                CodigoNcm='$codigoNcm', codigo_tributacao='$codigoTributacao', aliquota_imposto='$aliquotaImposto',
                data_atualizacao = NOW()";

        // Verificar se uma nova imagem foi enviada
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == UPLOAD_ERR_OK) {
            $nomeArquivo = uniqid() . "_" . basename($_FILES["foto"]["name"]);
            $foto = $diretorio . $nomeArquivo;
            move_uploaded_file($_FILES["foto"]["tmp_name"], $foto);
            
            // Atualizar o campo `foto` no banco de dados
            $sql .= ", foto='$foto'";
        }

        $sql .= " WHERE id_produto=$id";

        if ($conn->query($sql) === TRUE) {
            echo "<div class='message success'>Produto atualizado com sucesso!</div>";
        } else {
            echo "<div class='message error'>Erro: " . $conn->error . "</div>";
        }
    }
}

// Verificar se uma exclusão foi solicitada
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    $sql = "DELETE FROM produto WHERE id_produto=$id";

    if ($conn->query($sql) === TRUE) {
        echo "<div class='message success'>Produto excluído com sucesso!</div>";
    } else {
        echo "<div class='message error'>Erro: " . $conn->error . "</div>";
    }
}

// Verificar se um produto deve ser editado
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $result = $conn->query("SELECT * FROM produto WHERE id_produto=$id");
    $produtoParaEditar = $result->fetch_assoc();
}

?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <title>Cadastro de Produtos</title>
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
            
        }

            .container {
            background-color: #fff;
            border-radius: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.35);
            position: relative;
            overflow: hidden;
            width: 80%;  
            padding: 30px;
        }


        h1 {
            color: #512da8;
            text-align: center;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-top: 10px;
            font-weight: 600;
        }

        input[type="text"],
        input[type="number"] {
            background-color: #eee;
            border-color: #512da8;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 10px;
            outline: none;
            font-size: 13px;
            font-family: 'Roboto Mono', monospace;
            width: 45%;
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
        }

        button:hover {
            background-color: #673ab7;
        }

        .message.success, .message.error {
        position: fixed;             /* Fixado no topo da tela */
        top: 20px;                   /* Distância do topo */
        left: 50%;                   /* Alinhamento horizontal */
        transform: translateX(-50%); /* Centralizar horizontalmente */
        z-index: 1000;               /* Colocar na frente dos outros elementos */
        background-color: #512da8;
        color: #fff;
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        margin-top: 10px;
        transition: background-color 0.3s, opacity 0.3s;
        opacity: 1;                  /* Totalmente visível */
        }

        /* Efeito para ocultar a mensagem após alguns segundos */
        .message.hidden {
        opacity: 0;                  /* Tornar invisível */
        pointer-events: none;        /* Impedir interações */
        }

        table {
            margin-top: 20px;
            border-collapse: collapse;
            width: 100%;
            color: #512da8;
        }

        th, td {
            border: 2px solid #512da8;
            padding: 10px;
            text-align: center;
            font-family: 'Roboto Mono', monospace;
        }

        th {
            background-color: #512da8;
            color: #fff;
        }

        .action-link {
            color: #512da8;
            text-decoration: none;
        }

        .action-link:hover {
            text-decoration: underline;
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
                right: 92%;
        }

        #botCadastroVoltar:hover {
            background-color: #311b92;
        }
        
        h2 {
        text-align: center;
        margin-bottom: 20px;
        color: #333;
    }
    
    table {
        width: 100%;
        table-layout: fixed; /* Faz com que todas as colunas tenham a mesma largura */
        border-collapse: collapse;
        margin: 0 auto;
        text-align: center;
    }

    thead tr {
        background-color: #512da8;
        color: white;
    }

    th, td {
        padding: 10px;
        border: 1px solid #ddd;
        text-align: center;
        overflow: hidden; /* Oculta o conteúdo que ultrapassar a largura */
        text-overflow: ellipsis;
        white-space: nowrap; /* Impede a quebra de linha */
    }

    tbody tr:hover {
        background-color: #f2f2f2;
    }

    /* Definindo uma largura fixa para cada coluna */
    th:nth-child(1), td:nth-child(1) { width: 5%; }
    th:nth-child(2), td:nth-child(2) { width: 20%; }
    th:nth-child(3), td:nth-child(3) { width: 10%; }
    th:nth-child(4), td:nth-child(4) { width: 10%; }
    th:nth-child(5), td:nth-child(5) { width: 15%; }
    th:nth-child(6), td:nth-child(6) { width: 10%; }
    th:nth-child(7), td:nth-child(7) { width: 15%; }
    th:nth-child(8), td:nth-child(8) { width: 15%; }
    th:nth-child(9), td:nth-child(9) { width: 10%; }

    .scrollable-column {
        max-width: 190px; /* Defina a largura máxima da coluna */
        max-height: 80px;
        overflow-x: auto;
        white-space: nowrap; /* Impede que o texto quebre para outra linha */
    }
        

    </style>
</head>
<body>

    

    <a href="tela3.php" id="botCadastroVoltar">Voltar</a>

    

    <div class="container" >
    
        <h1><?php echo $produtoParaEditar ? 'Editar Produto' : 'Cadastro de Produtos'; ?></h1>
        <h2><?php echo "Sessão iniciada com o valor de e-mail: " . ($_SESSION['email'] ?? "não definida.");?></h2>


        

        <form action="telaCadastroProduto.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id_produto" value="<?php echo $produtoParaEditar['id_produto'] ?? ''; ?>">
            <label for="NomeProduto">Nome do Produto</label>
            <input type="text" name="NomeProduto" id="NomeProduto" required value="<?php echo $produtoParaEditar['nomeProduto'] ?? ''; ?>">

            <label for="ValorProduto">Valor do Produto</label>
            <input type="text" name="ValorProduto" id="ValorProduto" required
            value="<?php echo $produtoParaEditar['valorProduto'] ?? ''; ?>"
            oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');">

            <label for="CodigoNcm">Código NCM</label>
            <input type="text" name="CodigoNcm" id="CodigoNcm" value="<?php echo $produtoParaEditar['CodigoNcm'] ?? ''; ?>" oninput="formatCodigoNcm(this)">

            <label for="CodigoTributacao">Código de Tributação</label>
            <input type="text" name="CodigoTributacao" id="CodigoTributacao" value="<?php echo $produtoParaEditar['codigo_tributacao'] ?? ''; ?>" oninput="formatCodigoTributacao(this)">

            <label for="AliquotaImposto">Alíquota de Tributação (%)</label>
            <input type="text" name="AliquotaImposto" id="AliquotaImposto" value="<?php echo $produtoParaEditar['aliquota_imposto'] ?? ''; ?>" oninput="formatAliquotaImposto(this)">
            
            <label for="imagem">Escolha uma imagem:</label>
            <input type="file" name="foto" accept="image/*">


            <br>
            <button type="submit" name="<?php echo $produtoParaEditar ? 'update' : 'CadastrarProduto'; ?>">
                <?php echo $produtoParaEditar ? 'Atualizar Produto' : 'Cadastrar Produto'; ?>
            </button>
            </form>


        <br>
        <h2>Produtos Cadastrados</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome do Produto</th>
                    <th>Valor</th>
                    <th>Código NCM</th>
                    <th>Código de Tributação</th>
                    <th>Alíquota de Tributação (%)</th>
                    <th>Data de Cadastro</th>
                    <th>Data de Atualização</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
            <?php
// Criar conexão novamente para buscar os produtos
$conn = new mysqli($servername, $username, $password, $dbname);
$result = $conn->query("SELECT * FROM produto");
?>
<style>
    .image-popup {
        position: absolute;
        display: none; /* Inicialmente oculto */
        border: 1px solid #ccc;
        background-color: #fff;
        padding: 5px;
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.5);
        z-index: 1000; /* Para garantir que fique sobre outros elementos */
    }
    .image-popup img {
        max-width: 200px;
        max-height: 200px;
    }
</style>

<!-- Div de popup de imagem -->
<div id="imagePopup" class="image-popup">
    <img src="" id="popupImage" alt="Imagem do Produto">
</div>

<!-- Tabela de produtos -->
<table>
<?php 
        while ($row = $result->fetch_assoc()) {
            $caminhoImagem = strpos($row['foto'], "uploads/") === 0 ? $row['foto'] : "uploads/" . $row['foto'];
        ?>
            <tr 
                onmouseover="showImage(event, '<?php echo $caminhoImagem; ?>')" 
                onmousemove="moveImage(event)" 
                onmouseout="hideImage()">
                <td><?php echo $row['id_produto']; ?></td>
                <td>
                    <div class="scrollable-column">
                        <?php echo $row['nomeProduto']; ?>
                    </div>
                </td>
                <td><?php echo number_format($row['valorProduto'], 2, ',', '.'); ?></td>
                <td><?php echo $row['CodigoNcm']; ?></td>
                <td><?php echo $row['codigo_tributacao']; ?></td>
                <td><?php echo $row['aliquota_imposto']; ?></td>
                <td>
                    <div class="scrollable-column">
                        <?php echo date('d/m/Y H:i:s', strtotime($row['data_cadastro'])); ?>
                    </div>
                </td>
                <td>
                    <div class="scrollable-column">
                        <?php echo date('d/m/Y H:i:s', strtotime($row['data_atualizacao'])); ?>
                    </div>
                </td>
                <td>
                    <a class="action-link" href="telaCadastroProduto.php?edit=<?php echo $row['id_produto']; ?>">Editar</a>
                    <a class="action-link" href="telaCadastroProduto.php?delete=<?php echo $row['id_produto']; ?>" onclick="return confirm('Tem certeza que deseja excluir?');">Excluir</a>
                </td>
            </tr>
        <?php } ?>
</table>

<!-- JavaScript para manipular o popup da imagem -->
<script>
    const imagePopup = document.getElementById('imagePopup');
    const popupImage = document.getElementById('popupImage');

    function showImage(event, imageUrl) {
        popupImage.src = imageUrl; // Define o caminho da imagem
        imagePopup.style.display = 'block'; // Exibe o popup
    }

    function moveImage(event) {
        imagePopup.style.top = (event.pageY + -170) + 'px';
        imagePopup.style.left = (event.pageX + 10) + 'px';
    }

    function hideImage() {
        imagePopup.style.display = 'none'; // Oculta o popup
        popupImage.src = ''; // Limpa o src para evitar carregar a imagem sem necessidade
    }
</script>
</tbody>
    <script>
        function formatCodigoNcm(input) {
            input.value = input.value.replace(/[^0-9]/g, '').replace(/(\d{2})(\d{2})(\d{2})(\d+)/, '$1.$2.$3/$4');
        }

        function formatCodigoTributacao(input) {
            input.value = input.value.replace(/[^0-9]/g, '');
        }

        function formatAliquotaImposto(input) {
            input.value = input.value.replace(/[^0-9]/g, '');
        }
        window.onscroll = function() {
        scrollFunction();
    };
    setTimeout(() => {
        const messages = document.querySelectorAll('.message');
        messages.forEach(message => message.classList.add('hidden'));
    }, 3000);


    </script>
</body>
</html>



