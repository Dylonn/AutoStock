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

// Consultar o nome completo e o e-mail do cliente no banco
$sql = "SELECT nome_usuario, email FROM usuarios WHERE email = '$email_usuario'";
$result = $conn->query($sql);

// Verificar se encontrou o usuário
if ($result->num_rows > 0) {
    // Obtendo os dados do usuário
    $row = $result->fetch_assoc();
    $nome_completo = $row['nome_usuario'];
    $email = $row['email'];
} else {
    echo "Usuário não encontrado.";
    exit();
}





$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu</title>
    <style>
        body {
            background-image: url('img/novoFundoCarro2.jpg');
            background-size: cover;
            font-family: Arial, sans-serif;
            text-align: center;
            color: #fff; /* Set default text color to white */
        }

        h1,h2 {
            color: #fff;
            font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5); /* Add a subtle shadow */
        }

        .container {
          display: flex;
         justify-content: center; /* Centraliza horizontalmente */
         align-items: center; /* Centraliza verticalmente */
         height: 50vh; /* Usa 100% da altura da tela */
    }

        .menu {
            display: flex;
            width: 700px;
            justify-content: center;
            flex-wrap: wrap;
            gap: 10px;
            padding: 20px;
            border: 2px solid #512da8; /* Purple border */
            background-color: white;
            border-radius: 10px;
            border-width: 10px;
        }

        .menu-item {
            text-decoration: none;
            color: #512da8; /* Purple text color */
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 10px;
            border: 5px solid #ccc;
            border-radius: 10px;
            background-color: #fff;
            transition: background-color 0.3s, border-color 0.3s;
        }

        .menu-item:hover {
            background-color: #e0e0e0;
            border-color: #888;
        }

        .icon {
            width: 48px;
            height: 48px;
            margin-bottom: 5px;
        }

        #botLogout {
            display: flex;
            align-items: center;
            background-color: #512da8;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            margin-top: 15px;
            cursor: pointer;
        }

        #botLogout img {
            margin-right: 10px;
        }

        #botLogout:hover {
            background-color: #d32f2f;
        }

        .user-info {
            background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent black */
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin: 20px auto;
            width: 60%;
            text-align: center;
        }
    </style>
</head>
<body>
    
    <h1>Escolha um serviço</h1><br><br>
    <div>
    <div style="text-align: center; margin-top: 20px;">
        <img src="img/IconeSistema.png" alt="Ícone do Sistema" style="width: 100px; height: auto;">
            <h2 style="margin-top: 10px; color: white; font-family: 'Montserrat', sans-serif;">AutoStock</h2>
    </div>

    <!-- Exibir informações do usuário logado -->
    <div class="user-info">
        <h2>Bem-vindo, <?php echo htmlspecialchars($nome_completo); ?></h2>
        <p>Email: <?php echo htmlspecialchars($email); ?></p>
    </div>

    <div class="container" >

    <div class="menu">
        <a href="telaVenda.php" class="menu-item">
            <img src="img/iconeVenda.png" alt="Serviço 1" class="icon">
            <span>Realizar venda/serviço</span>
        </a><br><br>

        <a href="telaCadastroProduto.php" class="menu-item">
            <img src="img/iconeCadastroProduto.png" alt="Serviço 2" class="icon">
            <span>Cadastrar/consultar: Produto</span>
        </a><br><br>

        <a href="telaCadastroServico.php" class="menu-item">
            <img src="img/iconeCadastroServico.png" alt="Servico3" class="icon" >
            <span>Cadastrar/consultar: serviço</span>
        </a><br><br>

        <a href="telaPerfilEmpresa.php" class="menu-item">
            <img src="img/iconePerfilEmpresa.png" alt="Servico4" class="icon" >
            <span>Cadastrar/editar: perfil</span>
        </a><br><br>

        <a href="gerenciarAgendamentosEVendas.php" class="menu-item">
            <img src="img/iconeAgendamento.png" alt="Servico5" class="icon" >
            <span>Agendamento</span>
        </a><br><br>

        <a href="telaConsultaVenda.php" class="menu-item">
            <img src="img/iconeConsultaVenda.png" alt="Servico6" class="icon" >
            <span>Consultar suas vendas</span>
        </a><br><br>

        <a href="AbrirFecharCaixa.php" class="menu-item">
            <img src="img/iconeCaixa.png" alt="Servico7" class="icon" >
            <span>Abrir/fechar caixa</span>
        </a><br><br>

        <a href="telaOrçamento.php" class="menu-item">
            <img src="img/iconeOrçamento.png" alt="Servico8" class="icon" >
            <span>Realizar orçamento</span>
        </a><br><br>

        <a href="telaConsultaOrcamento.php" class="menu-item">
            <img src="img/iconeConsultaOrcamento.png" alt="Servico9" class="icon" >
            <span>Consultar orçamento</span>
        </a><br><br>

        <form action="logout.php" method="post">
            <button type="submit" name="logout" id="botLogout">
                <img src="img/logout.png" alt="Logout" width="30" height="30">
                Logout
            </button>
        </form>
    </div>
    </div>

    
</body>
</html>
