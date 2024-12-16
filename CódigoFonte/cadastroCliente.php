<?php
    session_start();
    require_once 'configF.php';

    if($_SERVER['REQUEST_METHOD'] == 'POST'){


        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            die("Conexão falhou: " . $conn->connect_error);
        }

        $nome = $_POST['nome'];
        $email = $_POST['email'];
        $senha = $_POST['senha'];
        

        $sql = "INSERT INTO cliente (nome, email, senha)
            VALUES ('$nome', '$email', '$senha')";

        if ($conn->query($sql) === TRUE) {
         echo "<script>Cadastro realizado com sucesso!</script>";
          header("Location: loginCliente.php");
        } else {
             echo "<script>Erro ao cadastrar: </script>" . $conn->error;
    }

// Fecha a conexão
$conn->close();


    }
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro</title>
    <style>
     body {
    font-family: 'Arial', sans-serif;
    background-color: #f0f0f0; /* Light gray background */
    background-image: url('img/novoFundoCarro3.jpg');
    background-size: cover;
    color: #333;
}

.cadastro {
    background-color: #fff; /* White background for the form */
    border-radius: 10px;
    box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
    padding: 20px;
    margin: 20px auto;
    max-width: 400px;
}

h1 {
    color: #663399; /* Purple heading color */
    text-align: center;
}

label {
    display: block;
    margin-bottom: 5px;
}

input[type="text"],
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
    </style>
</head>
<>
    <form action="" method="POST" class="cadastro">
        <h1>Faça o cadastro.</h1>
        <label for="nome">Nome Completo</label>
        <input type="text" id="nome" name="nome" required>
        
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>
        
        <label for="senha">Senha</label>
        <input type="password" id="senha" name="senha" required>

        <button type="submit" class="b2">Cadastrar</button>
    </form>

    
    

</body>
</html>
