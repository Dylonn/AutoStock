<?php
session_start();
require_once 'configF.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Conecta ao banco de dados
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Verifica a conexão
    if ($conn->connect_error) {
        die("Conexão falhou: " . $conn->connect_error);
    }

    // Verifica se email e senha foram fornecidos
    if (isset($_POST['email'], $_POST['senha'])) {
        $email = $_POST['email'];
        $senha = $_POST['senha'];

        // Prepara a consulta SQL
        $sql = "SELECT id_cliente, nome FROM cliente WHERE email = ? AND senha = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $email, $senha);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $cliente = $result->fetch_assoc();
            // Armazena o ID do cliente e o nome na sessão
            $_SESSION['id_cliente'] = $cliente['id_cliente'];
            $_SESSION['nome_cliente'] = $cliente['nome'];

            // Redireciona para a página do cliente
            header("Location: menuCliente.php");
            exit();
        } else {
            // Falha na autenticação
            $_SESSION['login_error'] = 'Email ou senha inválidos';
            header("Location: loginCliente.php"); // Redireciona de volta para o login
            exit();
        }
    } else {
        echo "Por favor, preencha todos os campos.";
    }
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrada</title>
    <style>
     body {
    font-family: 'Arial', sans-serif;
    background-color: #f0f0f0; /* Light gray background */
    background-image: url('img/novoFundoCarro3.jpg');
    background-size: cover;
    color: #333;
}

.container {
    background-color: #fff; /* White background for the login form */
    border-radius: 10px;
    box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
    padding: 20px;
    margin: 20px auto;
    max-width: 400px;
}

h1, h2 {
    color: #663399; /* Purple heading color */
    text-align: center;
}

input[type="email"],
input[type="password"] {
    width: 100%;
    padding: 10px;
    margin-bottom: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
}

button {
    background-color: #663399; /* Purple button color */
    color: #fff;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
}

button:hover {
    background-color: #8040c0; /* Darker purple on hover */
}

a {
    color: #663399; /* Purple link color */
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}
    </style>
</head>
<body>
    <div class="container">
        <h1>Cliente Oficina</h1>
        <h2>Realize o login</h2>
        <form action="" method="POST" class="login">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
            <label for="senha">Senha</label>
            <input type="password" id="senha" name="senha" required>
            <button type="submit">Login</button>
            <a href="cadastroCliente.php">Não se cadastrou?</a>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Verifica se há uma mensagem de erro na variável de sessão
            <?php if (isset($_SESSION['login_error'])): ?>
                alert('<?php echo $_SESSION['login_error']; ?>');
                <?php unset($_SESSION['login_error']); // Limpa a mensagem após exibi-la ?>
            <?php endif; ?>
        });
    </script>
    

</body>
</html>
